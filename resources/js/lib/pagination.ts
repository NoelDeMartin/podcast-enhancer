export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type NavLinkType = 'previous' | 'next';

export function navLinkType(label: string): NavLinkType | null {
    if (label.includes('&larr;') || label.includes('&laquo;')) {
        return 'previous';
    }

    if (label.includes('&rarr;') || label.includes('&raquo;')) {
        return 'next';
    }

    return null;
}

export function arrowHtml(label: string, type: NavLinkType): string {
    if (type === 'previous') {
        return label.replace(/\s*previous\s*/i, '').trim();
    }

    return label.replace(/\s*next\s*/i, '').trim();
}

export function navText(label: string): string {
    return label
        .replace(/<[^>]*>/g, '')
        .replace(/&(?:larr|rarr|laquo|raquo);/gi, '')
        .trim();
}

export function paginationLinkRel(label: string): 'prev' | 'next' | null {
    const type = navLinkType(label);

    if (type === 'previous') {
        return 'prev';
    }

    if (type === 'next') {
        return 'next';
    }

    return null;
}

export function paginationLinkClasses(link: PaginationLink): string {
    const isNav = navLinkType(link.label) !== null;

    return [
        'border-neo-dark border-3 text-xs leading-4 sm:text-sm',
        isNav
            ? 'inline-flex shrink-0 items-center justify-center px-2.5 py-2 sm:px-4 sm:py-3'
            : 'px-3 py-2 sm:px-4 sm:py-3',
        link.url === null
            ? 'bg-white text-gray-400 opacity-50'
            : 'hover:bg-primary focus-visible:ring-primary cursor-pointer transition-all hover:text-white focus-visible:ring-2 focus-visible:ring-offset-1 focus-visible:outline-none',
        link.active ? 'bg-primary hover:bg-primary text-white' : 'bg-white',
    ].join(' ');
}
