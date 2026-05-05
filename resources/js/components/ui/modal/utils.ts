import { useModal as _useModal } from '@noeldemartin/vue-modals';

export function useModal<T = never>() {
    return _useModal<T>({ removeOnCloseAfterDelay: 300 });
}
