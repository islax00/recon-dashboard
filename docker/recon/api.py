import json
import os
import subprocess
import threading
from datetime import datetime

from flask import Flask, jsonify, request

app = Flask(__name__)
OUTPUT_DIR = '/output'
ALLOWED_TOOLS = ['subfinder', 'httpx', 'katana', 'nuclei', 'naabu']

# Laravel Sail paths that share the same volume as OUTPUT_DIR in this container.
LARAVEL_PATH_PREFIXES = (
    '/var/www/html/storage/recon',
    '/recon-output',
)

PATH_FLAG_ARGS = {'-o', '-l', '-list', '-u', '-d'}


def ensure_scan_dir(scan_id: str) -> str:
    path = os.path.join(OUTPUT_DIR, f'scan-{scan_id}')
    os.makedirs(path, exist_ok=True)
    return path


def rewrite_path(path: str) -> str:
    normalized = path.replace('\\', '/')

    for prefix in LARAVEL_PATH_PREFIXES:
        if normalized.startswith(prefix):
            relative = normalized[len(prefix):].lstrip('/')
            return os.path.join(OUTPUT_DIR, relative)

    return path


def normalize_args(tool: str, args: list, scan_dir: str) -> tuple[list, str]:
    rewritten = []
    output_file = None
    index = 0

    while index < len(args):
        flag = args[index]

        if flag in PATH_FLAG_ARGS and index + 1 < len(args):
            value = rewrite_path(args[index + 1])

            if flag == '-o':
                output_file = value

            rewritten.extend([flag, value])
            index += 2
            continue

        rewritten.append(flag)
        index += 1

    if output_file is None:
        output_file = os.path.join(scan_dir, f'{tool}.txt')
        rewritten.extend(['-o', output_file])

    return rewritten, output_file


def write_status(scan_dir: str, tool: str, status: str, extra=None):
    if extra is None:
        extra = {}

    status_file = os.path.join(scan_dir, f'{tool}.status.json')

    with open(status_file, 'w', encoding='utf-8') as handle:
        json.dump(
            {
                'status': status,
                'updated_at': datetime.utcnow().isoformat(),
                **extra,
            },
            handle,
        )


def read_output_lines(output_file: str) -> list[str]:
    with open(output_file, 'r', encoding='utf-8', errors='replace') as handle:
        return [line.strip() for line in handle if line.strip()]


@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'ok'})


@app.route('/tools', methods=['GET'])
def list_tools():
    available = []

    for tool in ALLOWED_TOOLS:
        result = subprocess.run(['which', tool], capture_output=True, text=True)
        available.append({
            'name': tool,
            'available': result.returncode == 0,
        })

    return jsonify({'tools': available})


@app.route('/run', methods=['POST'])
def run_tool():
    data = request.get_json(silent=True) or {}
    tool = data.get('tool')
    args = data.get('args', [])
    scan_id = str(data.get('scan_id', ''))

    if not tool or tool not in ALLOWED_TOOLS:
        return jsonify({'error': 'Tool not allowed'}), 400

    if not scan_id:
        return jsonify({'error': 'scan_id is required'}), 400

    scan_dir = ensure_scan_dir(scan_id)
    normalized_args, output_file = normalize_args(tool, args, scan_dir)

    write_status(scan_dir, tool, 'running', {'output_file': output_file})

    def run():
        try:
            result = subprocess.run(
                [tool, *normalized_args],
                capture_output=True,
                text=True,
                timeout=7200,
            )
            status = 'completed' if result.returncode == 0 else 'failed'

            write_status(scan_dir, tool, status, {
                'exit_code': result.returncode,
                'output_file': output_file,
                'stderr': result.stderr[-1000:] if result.stderr else '',
            })
        except subprocess.TimeoutExpired:
            write_status(scan_dir, tool, 'timeout', {'output_file': output_file})
        except Exception as error:
            write_status(scan_dir, tool, 'failed', {
                'output_file': output_file,
                'error': str(error),
            })

    thread = threading.Thread(target=run, daemon=True)
    thread.start()

    return jsonify({
        'success': True,
        'message': f'{tool} started',
        'output_file': output_file,
    }), 202


@app.route('/status/<scan_id>/<tool>', methods=['GET'])
def get_status(scan_id: str, tool: str):
    scan_dir = os.path.join(OUTPUT_DIR, f'scan-{scan_id}')
    status_file = os.path.join(scan_dir, f'{tool}.status.json')

    if not os.path.exists(status_file):
        return jsonify({'status': 'not_started'}), 404

    with open(status_file, 'r', encoding='utf-8') as handle:
        return jsonify(json.load(handle))


@app.route('/output/<scan_id>/<tool>', methods=['GET'])
def get_output(scan_id: str, tool: str):
    scan_dir = os.path.join(OUTPUT_DIR, f'scan-{scan_id}')
    status_file = os.path.join(scan_dir, f'{tool}.status.json')
    output_file = os.path.join(scan_dir, f'{tool}.txt')
    status_data = {}

    if os.path.exists(status_file):
        with open(status_file, 'r', encoding='utf-8') as handle:
            status_data = json.load(handle)

        output_file = status_data.get('output_file', output_file)

        if status_data.get('status') in {'failed', 'timeout'}:
            return jsonify({
                'ready': False,
                'error': status_data.get('stderr') or status_data.get('error') or 'Tool failed',
                'exit_code': status_data.get('exit_code'),
            })

    if not os.path.exists(output_file):
        return jsonify({'ready': False, 'error': 'Output file not found'}), 404

    lines = read_output_lines(output_file)

    return jsonify({'ready': True, 'count': len(lines), 'results': lines})


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=8080, debug=False)
