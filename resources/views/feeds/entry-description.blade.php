@php
    $aiSummary = $entry->summary ?? '';
    $originalSummary = $entry->original_summary ?? '';
    $showNotesUrl = route('entries.show', [$entry->feed, $entry]);
    $appUrl = url('/');
    $isEnhanced = $entry->summary || $entry->transcription_path || $entry->chapters;

    $renderSummary = function ($summary) {
        if (preg_match('/<[a-z][\s\S]*>/i', $summary)) {
            return $summary;
        }
        return '<p>' . nl2br(e($summary)) . '</p>';
    };
@endphp

{!! $renderSummary($aiSummary) !!}

@if($isEnhanced)
<p>🧙 Enhanced by <a href="{{ $appUrl }}">Podcast Enhancer</a></p>
<p>👉 <a href="{{ $showNotesUrl }}">Read episode transcription</a></p>
@else
<p>👉 <a href="{{ $showNotesUrl }}">Enhance with Podcast Enhancer</a></p>
@endif

@if($entry->chapters)
<h2>Timestamps</h2>
<ul>
    @foreach($entry->chapters as $chapter)
        <li>{{ gmdate($chapter['startTime'] >= 3600 ? 'H:i:s' : 'i:s', (int) $chapter['startTime']) }} - {{ $chapter['title'] }}</li>
    @endforeach
</ul>
@endif

@if(filled($originalSummary))
<h2>Show Notes</h2>
{!! $renderSummary($originalSummary) !!}
@endif
