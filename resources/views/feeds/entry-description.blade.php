@php
    $aiSummary = $entry->summary ?? '';
    $originalSummary = $entry->original_summary ?? '';
    $showNotesUrl = route('entries.show', [$entry->feed, $entry]);
    $appUrl = url('/');
    $isEnhanced = $entry->summary || $entry->transcription_path || $entry->chapters;
@endphp

@if($aiSummary)
<p>{!! nl2br($aiSummary) !!}</p>
@endif

@if($isEnhanced)
<p>🧙 Enhanced by <a href="{{ $appUrl }}">Podcasts Enhancer</a></p>
<p>👉 <a href="{{ $showNotesUrl }}">Read episode transcription</a></p>
@else
<p>👉 <a href="{{ $showNotesUrl }}">Enhance with Podcasts Enhancer</a></p>
@endif

@if($entry->chapters)
<h2>Timestamps</h2>
<ul>
    @foreach($entry->chapters as $chapter)
        <li>{{ gmdate($chapter['startTime'] >= 3600 ? 'H:i:s' : 'i:s', (int) $chapter['startTime']) }} - {{ e($chapter['title']) }}</li>
    @endforeach
</ul>
@endif

@if(filled($originalSummary))
<h2>Original Description</h2>
<p>{!! nl2br($originalSummary) !!}</p>
@endif
