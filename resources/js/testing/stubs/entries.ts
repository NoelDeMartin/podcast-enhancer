export const entries = [
    {
        id: 1,
        name: 'Laravel 11 Released',
        slug: 'laravel-11-released',
        published_at: '2024-03-12T10:00:00Z',
        duration: 3661,
        absolute_audio_url: 'https://example.com/audio1.mp3',
        absolute_image_url: 'https://placecats.com/200/200',
        summary: 'A deep dive into the new features of Laravel 11.',
        chapters: [
            { timestamp: '00:00', title: 'Introduction' },
            { timestamp: '05:00', title: 'New Directory Structure' },
            { timestamp: '15:00', title: 'Conclusion' },
        ],
        transcription_path: 'transcriptions/1.json',
    },
    {
        id: 2,
        name: 'Pest 3.0 Deep Dive',
        slug: 'pest-3-deep-dive',
        published_at: '2024-04-15T14:30:00Z',
        duration: 1200,
        absolute_audio_url: null,
        absolute_image_url: null,
        original_summary:
            '<p>Join us as we explore the powerful new features in <strong>Pest 3.0</strong>, including architecture testing, browser testing, and more. We dive into real-world examples and discuss how these features can transform your testing workflow.</p>',
    },
    {
        id: 3,
        name: 'Empty Episode',
        slug: 'empty-episode',
        published_at: '2024-05-01T09:00:00Z',
        absolute_audio_url: null,
        absolute_image_url: null,
    },
];
