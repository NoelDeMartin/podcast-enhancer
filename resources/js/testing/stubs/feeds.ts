export const feeds = [
    {
        id: 1,
        title: 'The Laravel Podcast',
        slug: 'the-laravel-podcast',
        absolute_image_url: 'https://placecats.com/300/300',
        entries_count: 156,
        rss_url: 'https://feeds.transistor.fm/the-laravel-podcast',
        last_synced_at: new Date().toISOString(),
        can: {
            sync: true,
        },
    },
    {
        id: 2,
        title: 'Syntax - Tasty Web Development Treats',
        slug: 'syntax',
        absolute_image_url: 'https://placecats.com/400/400',
        entries_count: 700,
        rss_url: 'https://feed.syntax.fm/rss',
        last_synced_at: new Date(Date.now() - 2 * 3600 * 1000).toISOString(), // 2 hours ago
        can: {
            sync: true,
        },
    },
    {
        id: 3,
        title: 'Manual Feed',
        slug: 'manual-feed',
        absolute_image_url: null,
        entries_count: 5,
        rss_url: null,
        last_synced_at: null,
    },
];
