<?php

namespace App\Jobs;

use App\Ai\Agents\PodcastEditorAgent;
use App\Concerns\HandlesAiRateLimits;
use App\Models\Entry;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Exceptions\RateLimitedException;

#[Timeout(300)]
#[Tries(8)]
class ProduceEntryJob implements ShouldQueue
{
    use Batchable, HandlesAiRateLimits, InteractsWithQueue, Queueable;

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

        if ($this->entry->original_summary) {
            $prompt = "ORIGINAL EPISODE SUMMARY:\n{$this->entry->original_summary}\n\nTRANSCRIPT:\n".$transcription;
        }

        try {
            $response = (new PodcastEditorAgent)->prompt($prompt, timeout: 300);
        } catch (RateLimitedException $e) {
            $this->postponeIfRateLimited($e, [
                'entry_id' => $this->entry->id,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        $this->entry->update([
            'summary' => $response['summary'],
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
            ->map(fn (array $texts, int $windowStart) => "[{$windowStart}] ".preg_replace('/\s+/', ' ', implode(' ', $texts)))
            ->implode("\n");
    }
}
