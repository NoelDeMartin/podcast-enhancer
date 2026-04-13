<?= '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL ?>
<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:content="http://purl.org/rss/1.0/modules/content/">
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
                <description><![CDATA[{!! $entry->rss_description !!}]]></description>
                <content:encoded><![CDATA[{!! $entry->rss_description !!}]]></content:encoded>
                <link>{{ $entry->audio_url ? route('entries.file', $entry) : '' }}</link>
                <guid isPermaLink="false">{{ $entry->id }}</guid>
                <pubDate>{{ $entry->published_at->toRfc2822String() }}</pubDate>
                <itunes:summary>{{ $entry->summary ?? '' }}</itunes:summary>
                <itunes:episodeType>full</itunes:episodeType>
                @if($entry->audio_url)
                    @php
                        $isUrl = filter_var($entry->audio_url, FILTER_VALIDATE_URL);
                        $url = $isUrl ? $entry->audio_url : route('entries.file', $entry);
                        $length = $isUrl ? 0 : (Storage::exists($entry->audio_url) ? Storage::size($entry->audio_url) : 0);
                    @endphp
                    <enclosure url="{{ $url }}" type="audio/mpeg" length="{{ $length }}"/>
                @endif
            </item>
        @endforeach
    </channel>
</rss>
