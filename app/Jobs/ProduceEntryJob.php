<?php

namespace App\Jobs;

use App\Ai\Agents\PodcastEditorAgent;
use App\Models\Entry;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

#[Timeout(300)]
#[Tries(1)]
class ProduceEntryJob implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(public Entry $entry) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            Log::info(static::class.' skipped (batch cancelled)', [
                'entry_id' => $this->entry->id,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        if (! $this->entry->transcription_path) {
            Log::info(static::class.' skipped (no transcription_path)', [
                'entry_id' => $this->entry->id,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        Log::info(static::class.' started', [
            'entry_id' => $this->entry->id,
            'transcription_path' => $this->entry->transcription_path,
            'batch_id' => $this->batchId,
        ]);

        $segments = json_decode(Storage::get($this->entry->transcription_path), true);

        $transcription = $this->buildPromptTranscript($segments);

        $prompt = $transcription;

        if (preg_match('/(<original_summary>.*?<\/original_summary>)/s', $this->entry->summary ?? '', $matches)) {
            $prompt = $matches[1]."\n\n".$transcription;
        }

        $response = (new PodcastEditorAgent)->prompt($prompt, timeout: 300);

        $summary = $response['summary'];

        if (preg_match('/(<original_summary>.*?<\/original_summary>)/s', $this->entry->summary ?? '', $matches)) {
            $summary = $matches[1]."\n\n[Auto-generated summary]\n\n".$summary;
        }

        $this->entry->update([
            'summary' => $summary,
            'chapters' => $response['chapters'],
        ]);

        Log::info(static::class.' finished (updated entry)', [
            'entry_id' => $this->entry->id,
            'batch_id' => $this->batchId,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error(static::class.' failed: '.$exception->getMessage(), [
            'entry_id' => $this->entry->id,
            'transcription_path' => $this->entry->transcription_path,
            'batch_id' => $this->batchId,
            'exception' => $exception,
        ]);
    }

    /**
     * Build a condensed transcript for the LLM prompt.
     *
     * The raw transcription can contain thousands of short segments with long
     * decimal timestamps, which can exceed model context and cause the agent to
     * guess chapter times. We merge segments into fixed time windows and round
     * timestamps to whole seconds to keep the prompt compact and anchorable.
     *
     * @param  array<int, array{text: string, start_seconds: float|int|string}>  $segments
     */
    private function buildPromptTranscript(array $segments, int $windowSeconds = 15): string
    {
        $windows = [];

        foreach ($segments as $segment) {
            $text = is_string($segment['text'] ?? null) ? trim($segment['text']) : '';
            if ($text === '') {
                continue;
            }

            $startSeconds = (float) ($segment['start_seconds'] ?? 0);
            $startSecondsInt = (int) floor($startSeconds);
            $windowIndex = intdiv($startSecondsInt, $windowSeconds);
            $windowStart = $windowIndex * $windowSeconds;

            $windows[$windowStart] ??= [];
            $windows[$windowStart][] = $text;
        }

        ksort($windows);

        return collect($windows)
            ->map(function (array $texts, int $windowStart) {
                $lineText = preg_replace('/\s+/', ' ', implode(' ', $texts)) ?? implode(' ', $texts);

                return "[{$windowStart}] {$lineText}";
            })
            ->implode("\n");
    }
}
