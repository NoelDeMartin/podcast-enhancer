import DOMPurify from 'dompurify';
import linkifyHtml from 'linkify-html';

export type BatchStatus = 'pending' | 'failed' | 'completed' | null;

export function getBatchStatus(entry: any): BatchStatus {
    const batch = entry.latest_job_batch?.job_batch;

    if (!batch) {
        return null;
    }

    if (batch.cancelled_at !== null) {
        return 'failed';
    }

    if (batch.finished_at !== null) {
        return 'completed';
    }

    return 'pending';
}

export function formatTimestamp(seconds: number): string {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);

    return h > 0
        ? `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
        : `${m}:${String(s).padStart(2, '0')}`;
}

export function formatSummary(summary: string | null | undefined): string {
    if (!summary) {
        return '';
    }

    const linkedSummary = linkifyHtml(summary, {
        defaultProtocol: 'https',
        target: '_blank',
        rel: 'noopener noreferrer',
    });

    return DOMPurify.sanitize(linkedSummary, { ADD_ATTR: ['target'] });
}

export function parsedTranscription(entry: any): any[] | null {
    if (!entry?.transcription) {
        return null;
    }

    try {
        return JSON.parse(entry.transcription);
    } catch {
        return null;
    }
}
