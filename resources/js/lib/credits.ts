const formatter = new Intl.NumberFormat('en-US', {
    style: 'decimal',
});

export function formatCredits(credits: number | string): string {
    return formatter.format(Number(credits));
}
