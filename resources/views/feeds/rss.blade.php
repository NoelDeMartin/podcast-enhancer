<?= '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL ?>
<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:podcast="https://podcastindex.org/namespace/1.0">
    <script src="{{ asset('podcast-rss-style.js') }}" xmlns="http://www.w3.org/1999/xhtml"></script>
    <channel>
        <title>{{ $feed->title }}</title>
        <link>{{ url('/') }}</link>
        <description>{{ $feed->description ?? 'A podcast feed for ' . $feed->title . '. Subscribe in your podcast player to get the latest episodes.' }}</description>
        <language>en-us</language>
        <pubDate>{{ $feed->created_at->toRfc2822String() }}</pubDate>
        <lastBuildDate>{{ now()->toRfc2822String() }}</lastBuildDate>
        <itunes:author>Podcasts Enhancer</itunes:author>
        <itunes:summary>{{ $feed->description ?? 'A podcast feed for ' . $feed->title . '. Subscribe in your podcast player to get the latest episodes.' }}</itunes:summary>
        <itunes:type>episodic</itunes:type>

        @foreach($entries as $entry)
            <item>
                <title>{{ $entry->name }}</title>
                <description>{{ $entry->summary ?? '' }}</description>
                <link>{{ $entry->file_path ? route('entries.file', $entry) : '' }}</link>
                <guid isPermaLink="false">{{ $entry->id }}</guid>
                <pubDate>{{ $entry->created_at->toRfc2822String() }}</pubDate>
                <itunes:summary>{{ $entry->summary ?? '' }}</itunes:summary>
                <itunes:episodeType>full</itunes:episodeType>
                @if($entry->file_path)
                    <enclosure url="{{ route('entries.file', $entry) }}" type="audio/mpeg" length="{{ Storage::exists($entry->file_path) ? Storage::size($entry->file_path) : 0 }}"/>
                @endif
                @if($entry->chapters)
                    <podcast:chapters url="{{ route('entries.chapters', $entry) }}" type="application/json+chapters"/>
                @endif
            </item>
        @endforeach
    </channel>
</rss>
