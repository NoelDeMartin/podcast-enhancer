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
        : `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
}

export function formatEntryTimestamp(seconds: number, totalDuration: number): string {
    const totalSeconds = Math.max(0, Math.floor(seconds));
    const useHours = totalDuration >= 3600;

    if (useHours) {
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const secondsRemainder = totalSeconds % 60;

        return [hours, minutes, secondsRemainder]
            .map((unit) => String(unit).padStart(2, '0'))
            .join(':');
    }

    return [Math.floor(totalSeconds / 60), totalSeconds % 60]
        .map((unit) => String(unit).padStart(2, '0'))
        .join(':');
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

export function formatDate(value: string | Date | null | undefined): string {
    if (!value) {
        return '-';
    }

    const date = typeof value === 'string' ? new Date(value) : value;

    if (Number.isNaN(date.getTime())) {
        return '-';
    }

    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
    }).format(date);
}

export function formatDateTime(value: string | Date | null | undefined): string {
    if (!value) {
        return '-';
    }

    const date = typeof value === 'string' ? new Date(value) : value;

    if (Number.isNaN(date.getTime())) {
        return '-';
    }

    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
}
