import { showModal } from '@noeldemartin/vue-modals';

import FailureModal from '@/components/modals/FailureModal/FailureModal.vue';

export function useFailureModal() {
    function viewFailure(entry: { name: string; latest_job_batch?: any }) {
        return showModal(FailureModal as any, {
            title: 'Processing failed',
            description: `For "${entry.name}" episode.`,
            details:
                entry.latest_job_batch?.job_batch?.failed_job_details?.[0]?.exception ??
                'No error details available.',
        });
    }

    return { viewFailure };
}
