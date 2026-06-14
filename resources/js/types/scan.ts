export type ScanStatus = 'pending' | 'running' | 'completed' | 'failed';

export type ScanLog = {
    id: number;
    scan_id: number;
    tool: string;
    level: 'info' | 'warning' | 'error';
    message: string;
    created_at: string;
};

export type Scan = {
    id: number;
    user_id: number;
    domain: string;
    status: ScanStatus;
    options: Record<string, unknown> | null;
    started_at: string | null;
    completed_at: string | null;
    created_at: string;
    updated_at: string;
    logs?: ScanLog[];
};

export type ScanProgressEvent = {
    scan_id: number;
    status: ScanStatus;
    stage: string;
    progress: number;
    message: string | null;
};

export type GraphNode = {
    id: string;
    type: string;
    label: string;
    metadata: Record<string, unknown> | null;
    risk_level: string;
};

export type GraphEdge = {
    source: string;
    target: string;
    relation: string;
};

export type GraphData = {
    nodes: GraphNode[];
    edges: GraphEdge[];
};

export type Finding = {
    id: number;
    title: string;
    description: string;
    severity: string;
    type: string;
};

export type Report = {
    id: number;
    scan_id: number;
    risk_score: number;
    risk_level: string;
    subdomains_count: number;
    endpoints_count: number;
    secrets_count: number;
    vulnerabilities_count: number;
    ai_summary: string | null;
    findings?: Finding[];
};

export type ScanStats = {
    subdomains: number;
    endpoints: number;
    secrets: number;
    technologies: number;
};

export type ChatMessage = {
    role: 'user' | 'assistant';
    content: string;
};
