import subprocess
import os
import threading
import json
from datetime import datetime
from flask import Flask, request, jsonify

app = Flask(__name__)
OUTPUT_DIR = '/output'
ALLOWED_TOOLS = ['subfinder', 'httpx', 'katana', 'dnsx', 'nuclei', 'naabu']


def ensure_scan_dir(scan_id: str) -> str:
    path = os.path.join(OUTPUT_DIR, f'scan-{scan_id}')
    os.makedirs(path, exist_ok=True)
    return path


def write_status(scan_dir: str, tool: str, status: str, extra: dict = {}):
    status_file = os.path.join(scan_dir, f'{tool}.status.json')
    with open(status_file, 'w') as f:
        json.dump({'status': status, 'updated_at': datetime.utcnow().isoformat(), **extra}, f)


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
    data = request.get_json()
    tool = data.get('tool')
    args = data.get('args', [])
    scan_id = str(data.get('scan_id', ''))

    if not tool or tool not in ALLOWED_TOOLS:
        return jsonify({'error': f'Tool not allowed'}), 400
    if not scan_id:
        return jsonify({'error': 'scan_id is required'}), 400

    scan_dir = ensure_scan_dir(scan_id)
    output_file = os.path.join(scan_dir, f'{tool}.txt')

    write_status(scan_dir, tool, 'running')

    def run():
        try:
            result = subprocess.run(
                [tool] + args + ['-o', output_file],
                capture_output=True,
                text=True,
                timeout=7200  # ساعتين max
            )
            write_status(scan_dir, tool, 'completed', {
                'exit_code': result.returncode,
                'stderr': result.stderr[-500:] if result.stderr else '',
            })
        except subprocess.TimeoutExpired:
            write_status(scan_dir, tool, 'timeout')
        except Exception as e:
            write_status(scan_dir, tool, 'failed', {'error': str(e)})

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

    with open(status_file, 'r') as f:
        return jsonify(json.load(f))


@app.route('/output/<scan_id>/<tool>', methods=['GET'])
def get_output(scan_id: str, tool: str):
    output_file = os.path.join(OUTPUT_DIR, f'scan-{scan_id}', f'{tool}.txt')

    if not os.path.exists(output_file):
        return jsonify({'ready': False}), 404

    with open(output_file, 'r') as f:
        lines = [l.strip() for l in f if l.strip()]

    return jsonify({'ready': True, 'count': len(lines), 'results': lines})


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=8080, debug=False)