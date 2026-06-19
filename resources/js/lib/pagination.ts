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

export function collapsePaginationLinks(links: PaginationLink[]): PaginationLink[] {
    if (links.length <= 3) {
        return links;
    }

    const hasPrev = navLinkType(links[0].label) === 'previous';
    const hasNext = navLinkType(links[links.length - 1].label) === 'next';

    const prevLink = hasPrev ? links[0] : null;
    const nextLink = hasNext ? links[links.length - 1] : null;
    const pageLinks = links.slice(hasPrev ? 1 : 0, hasNext ? -1 : undefined);

    const parsedPages = pageLinks
        .map((link) => {
            const pageNum = parseInt(link.label, 10);
            return {
                pageNum,
                link,
            };
        })
        .filter((item) => !isNaN(item.pageNum));

    if (parsedPages.length === 0) {
        return links;
    }

    const activeItem = parsedPages.find((item) => item.link.active);
    const currentPage = activeItem ? activeItem.pageNum : 1;
    const totalPages = parsedPages[parsedPages.length - 1].pageNum;

    const pagesToShow = new Set<number>();
    pagesToShow.add(1);
    pagesToShow.add(totalPages);
    pagesToShow.add(currentPage);
    if (currentPage > 1) {
        pagesToShow.add(currentPage - 1);
    }
    if (currentPage < totalPages) {
        pagesToShow.add(currentPage + 1);
    }

    // Connect gaps of size 2 (e.g., if page 1 and page 3 are shown, also show page 2)
    for (let p = 1; p <= totalPages; p++) {
        if (pagesToShow.has(p) && pagesToShow.has(p + 2)) {
            pagesToShow.add(p + 1);
        }
    }

    const pageMap = new Map<number, PaginationLink>();
    for (const item of parsedPages) {
        pageMap.set(item.pageNum, item.link);
    }

    const collapsedLinks: PaginationLink[] = [];

    if (prevLink) {
        collapsedLinks.push(prevLink);
    }

    const sortedPages = Array.from(pagesToShow).sort((a, b) => a - b);
    for (let i = 0; i < sortedPages.length; i++) {
        const pageNum = sortedPages[i];
        const link = pageMap.get(pageNum);
        if (link) {
            collapsedLinks.push(link);
        }

        if (i < sortedPages.length - 1) {
            const nextPageNum = sortedPages[i + 1];
            if (nextPageNum - pageNum > 1) {
                collapsedLinks.push({
                    url: null,
                    label: '...',
                    active: false,
                });
            }
        }
    }

    if (nextLink) {
        collapsedLinks.push(nextLink);
    }

    return collapsedLinks;
}
