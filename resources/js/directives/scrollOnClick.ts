import type { Directive } from 'vue';

type ScrollOnClickOptions = {
    offset?: number;
    behavior?: ScrollBehavior;
};

const handlerKey = Symbol('v-scroll-on-click-handler');

function getTargetFromEl(el: HTMLElement, bindingValue: unknown) {
    if (typeof bindingValue === 'string' && bindingValue.trim().length > 0) {
        return bindingValue.trim();
    }

    return el.getAttribute('href') ?? '';
}

function prefersReducedMotion() {
    return window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches ?? false;
}

export const vScrollOnClick: Directive<HTMLElement, string | ScrollOnClickOptions | undefined> & {
    getSSRProps?: () => any;
} = {
    getSSRProps() {
        return {};
    },
    mounted(el, binding) {
        const onClick = (e: MouseEvent) => {
            const target = getTargetFromEl(el, binding.value);
            if (!target.startsWith('#')) return;

            const id = target.slice(1);
            const targetEl = document.getElementById(id);
            if (!targetEl) return;

            e.preventDefault();

            const opts =
                typeof binding.value === 'object' && binding.value ? binding.value : undefined;
            const offset = opts?.offset ?? 0;
            const behavior = opts?.behavior ?? (prefersReducedMotion() ? 'auto' : 'smooth');

            const top = targetEl.getBoundingClientRect().top + window.scrollY + offset;

            window.scrollTo({ top, behavior });
            window.history.pushState(null, '', `#${id}`);
        };

        // @ts-expect-error store handler for cleanup
        el[handlerKey] = onClick;
        el.addEventListener('click', onClick);
    },
    beforeUnmount(el) {
        // @ts-expect-error retrieve handler for cleanup
        const onClick = el[handlerKey] as ((e: MouseEvent) => void) | undefined;
        if (onClick) el.removeEventListener('click', onClick);
    },
};
