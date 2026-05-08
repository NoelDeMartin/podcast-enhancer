<?= '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL ?>
<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:psc="http://podlove.org/simple-chapters">
    <script src="{{ asset('podcast-rss-style.js') }}" xmlns="http://www.w3.org/1999/xhtml"></script>
    <channel>
        <title>{{ $feed->title }}</title>
        <link>{{ url('/') }}</link>
        <description>{{ $feed->description ?? 'A podcast feed for ' . $feed->title . '. Subscribe in your podcast player to get the latest episodes.' }}</description>
        <language>en-us</language>
        <pubDate>{{ $feed->created_at->toRfc2822String() }}</pubDate>
        <lastBuildDate>{{ now()->toRfc2822String() }}</lastBuildDate>
        <itunes:author>Podcast Enhancer</itunes:author>
        <itunes:summary>{{ $feed->description ?? 'A podcast feed for ' . $feed->title . '. Subscribe in your podcast player to get the latest episodes.' }}</itunes:summary>
        <itunes:type>episodic</itunes:type>
        @if($feed->image_url)
            <itunes:image href="{{ $feed->absolute_image_url }}"/>
        @endif

        @foreach($entries as $entry)
            <item>
                <title>{{ $entry->name }}</title>
                <description><![CDATA[{!! $entry->rss_description !!}]]></description>
                <content:encoded><![CDATA[{!! $entry->rss_description !!}]]></content:encoded>
                <link>{{ $entry->absolute_audio_url }}</link>
                <guid isPermaLink="false">{{ $entry->id }}</guid>
                <pubDate>{{ $entry->published_at->toRfc2822String() }}</pubDate>
                <itunes:summary>{{ strip_tags($entry->original_summary ?? $entry->summary ?? '') }}</itunes:summary>
                <itunes:episodeType>full</itunes:episodeType>
                @if($entry->duration)
                    <itunes:duration>{{ $entry->duration }}</itunes:duration>
                @endif
                @php
                    $effectiveImageUrl = $entry->absolute_image_url ?: $feed->absolute_image_url;
                @endphp
                @if($effectiveImageUrl)
                    <itunes:image href="{{ $effectiveImageUrl }}"/>
                @endif
                @if($entry->audio_url)
                    <enclosure url="{{ $entry->absolute_audio_url }}" type="audio/mpeg" length="{{ $entry->audio_file_size }}"/>
                @endif
                @if($entry->chapters)
                    <psc:chapters version="1.2">
                        @foreach($entry->chapters as $chapter)
                            <psc:chapter start="{{ gmdate('H:i:s', (int) $chapter['startTime']) }}" title="{{ $chapter['title'] }}" />
                        @endforeach
                    </psc:chapters>
                @endif
            </item>
        @endforeach
    </channel>
</rss>
