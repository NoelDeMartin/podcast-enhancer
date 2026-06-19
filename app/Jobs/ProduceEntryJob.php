<?php

namespace App\Jobs;

use App\Ai\Agents\PodcastEditorAgent;
use App\Concerns\HandlesAiErrors;
use App\Models\Entry;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Exceptions\RateLimitedException;
use Laravel\Ai\Responses\StructuredAgentResponse;

#[Timeout(300)]
#[Tries(8)]
class ProduceEntryJob implements ShouldQueue
{
    use Batchable, HandlesAiErrors, InteractsWithQueue, Queueable;

    public function __construct(public int $entryId) {}

    public function handle(): void
    {
        $entry = Entry::findOrFail($this->entryId);

        if ($this->batch()?->cancelled()) {
            Log::info(static::class.' skipped (batch cancelled)', [
                'entry_id' => $entry->id,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        if (! $entry->transcription_path) {
            Log::info(static::class.' skipped (no transcription_path)', [
                'entry_id' => $entry->id,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        Log::info(static::class.' started', [
            'entry_id' => $entry->id,
            'transcription_path' => $entry->transcription_path,
            'batch_id' => $this->batchId,
        ]);

        $segments = json_decode(Storage::get($entry->transcription_path), true);

        $transcription = $this->buildPromptTranscript($segments);

        $prompt = $transcription;

        if ($entry->original_summary) {
            $prompt = "ORIGINAL EPISODE SUMMARY:\n{$entry->original_summary}\n\nTRANSCRIPT:\n".$transcription;
        }

        try {
            $response = (new PodcastEditorAgent)->prompt($prompt, timeout: 300, provider: $this->provider());
        } catch (RateLimitedException $e) {
            $this->postponeIfRateLimited($e, [
                'entry_id' => $entry->id,
                'batch_id' => $this->batchId,
            ]);

            return;
        } catch (ProviderOverloadedException $e) {
            $this->postponeIfOverloaded($e, [
                'entry_id' => $entry->id,
                'batch_id' => $this->batchId,
            ]);

            return;
        }

        $entry->update($this->sanitizeAiResponse($response));

        Log::info(static::class.' finished (updated entry)', [
            'entry_id' => $entry->id,
            'batch_id' => $this->batchId,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error(static::class.' failed: '.$exception->getMessage(), [
            'entry_id' => $this->entryId,
            'batch_id' => $this->batchId,
            'exception' => $exception,
        ]);
    }

    protected function provider(): string|array
    {
        return $this->attempts() >= 4
            ? [config('ai.default'), config('ai.default_failover')]
            : config('ai.default');
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

    /**
     * @return array{summary: string, chapters: array<int, array{title: string, startTime: int, summary: string}>}
     */
    private function sanitizeAiResponse(StructuredAgentResponse|array $response): array
    {
        $response = $response instanceof StructuredAgentResponse ? $response->toArray() : $response;

        $summary = trim($this->stripControlCharacters((string) ($response['summary'] ?? '')));

        $chapters = collect((array) ($response['chapters'] ?? []))
            ->filter(fn ($chapter) => is_array($chapter))
            ->map(fn (array $chapter) => [
                'title' => trim($this->stripControlCharacters((string) ($chapter['title'] ?? ''))),
                'startTime' => (int) ($chapter['startTime'] ?? 0),
                'summary' => trim($this->stripControlCharacters((string) ($chapter['summary'] ?? ''))),
            ])
            ->filter(fn (array $chapter) => $chapter['title'] !== '')
            ->values()
            ->all();

        return [
            'summary' => $summary,
            'chapters' => $chapters,
        ];
    }

    private function stripControlCharacters(string $value): string
    {
        return (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
    }
}
