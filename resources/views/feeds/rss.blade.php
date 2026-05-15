<?= '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL ?>
<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:psc="http://podlove.org/simple-chapters" xmlns:atom="http://www.w3.org/2005/Atom">
    <script src="{{ asset('podcast-rss-style.js') }}" xmlns="http://www.w3.org/1999/xhtml"></script>
    <channel>
        <title>{{ $feed->title }}</title>
        <link>{{ url('/') }}</link>
        <description>{{ strip_tags(html_entity_decode($feed->description ?? 'A podcast feed for ' . $feed->title . '. Subscribe in your podcast player to get the latest episodes.', ENT_QUOTES, 'UTF-8')) }}</description>
        <language>en-us</language>
        <pubDate>{{ $feed->created_at->toRfc2822String() }}</pubDate>
        <lastBuildDate>{{ now()->toRfc2822String() }}</lastBuildDate>
        <atom:link href="{{ route('feeds.rss', $feed) }}" rel="self" type="application/rss+xml" />
        <itunes:author>Podcast Enhancer</itunes:author>
        <itunes:summary>{{ strip_tags(html_entity_decode($feed->description ?? 'A podcast feed for ' . $feed->title . '. Subscribe in your podcast player to get the latest episodes.', ENT_QUOTES, 'UTF-8')) }}</itunes:summary>
        <itunes:type>episodic</itunes:type>
        @if($feed->categories)
            @foreach($feed->categories as $category)
                <itunes:category text="{{ $category }}" />
            @endforeach
        @endif
        @if($feed->explicit)
            <itunes:explicit>{{ $feed->explicit }}</itunes:explicit>
        @endif
        @if($feed->image_url)
            <itunes:image href="{{ $feed->absolute_image_url }}"/>
        @endif

        @foreach($entries as $entry)
            <item>
                <title>{{ strip_tags($entry->name) }}</title>
                <description><![CDATA[{!! $entry->rss_description !!}]]></description>
                <content:encoded><![CDATA[{!! $entry->rss_description !!}]]></content:encoded>
                <link>{{ $entry->absolute_audio_url }}</link>
                <guid isPermaLink="true">{{ $entry->absolute_url }}</guid>
                <pubDate>{{ $entry->published_at->toRfc2822String() }}</pubDate>
                <itunes:summary>{{ strip_tags(html_entity_decode($entry->original_summary ?? $entry->summary ?? '', ENT_QUOTES, 'UTF-8')) }}</itunes:summary>
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
