<?= '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL ?>
<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title>{{ $feed->title }}</title>
        <link>{{ url('/') }}</link>
        <description>{{ $feed->title }}</description>
        <language>en-us</language>
        <pubDate>{{ $feed->created_at->toRfc2822String() }}</pubDate>
        <lastBuildDate>{{ now()->toRfc2822String() }}</lastBuildDate>
        <itunes:author>Podcasts Enhancer</itunes:author>
        <itunes:summary>{{ $feed->title }}</itunes:summary>

        @foreach($entries as $entry)
            <item>
                <title>{{ $entry->name }}</title>
                <description>{{ $entry->description ?? '' }}</description>
                <link>{{ $entry->file_path ? route('entries.file', $entry) : '' }}</link>
                <guid isPermaLink="false">{{ $entry->id }}</guid>
                <pubDate>{{ $entry->created_at->toRfc2822String() }}</pubDate>
                <itunes:summary>{{ $entry->description ?? '' }}</itunes:summary>
                @if($entry->file_path)
                    <enclosure url="{{ route('entries.file', $entry) }}" type="audio/mpeg" length="{{ Storage::exists($entry->file_path) ? Storage::size($entry->file_path) : 0 }}"/>
                @endif
            </item>
        @endforeach
    </channel>
</rss>
