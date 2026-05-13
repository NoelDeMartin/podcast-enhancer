export interface CreditUsage {
    id: number;
    credits: number;
    created_at: string;
    type: 'usage' | 'topup';
    description?: string;
    entry?: {
        id: number;
        name: string;
        slug: string;
        feed?: {
            slug: string;
        };
        latest_job_batch?: {
            job_batch: {
                id: string;
                finished_at: number | null;
                cancelled_at: number | null;
                failed_job_details?: Array<{
                    exception: string;
                }>;
            };
        };
    };
}
