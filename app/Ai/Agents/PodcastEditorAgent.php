<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class PodcastEditorAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are an expert podcast producer.
You will be provided with a raw podcast transcript, and possibly an existing episode summary as context.

The transcript is formatted line-by-line as `[timestamp_in_seconds] spoken text`.
Timestamps are whole seconds and always come from the transcript itself.
Example: `[14.5] Welcome to the show.` means the text was spoken at 14.5 seconds.

YOUR TASK:
1. Write a summary of the episode.
2. Create a logical list of chapters for the episode with clear, descriptive titles.
3. For each chapter, extract the exact `startTime` in seconds based on the bracketed numbers in the transcript.
4. For each chapter, write a summary of what is discussed.

CRITICAL RULES:
- Every chapter `startTime` MUST exactly match one of the bracketed timestamps present in the transcript.
- Do NOT guess timestamps. If you are unsure, choose the closest earlier timestamp that is clearly relevant.
- Create enough chapters so no single chapter spans an excessively large portion of the episode.
- Use the same language as the transcription for both the summary and the chapters.
- If the original summary includes chapter timestamps, generate chapters that match those chapters exactly.
- If you detect some sponsored section or advertisement, create a chapter for it that starts with "Sponsor: " in the title.
PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'summary' => $schema->string()->required()->description('Summary of the episode'),
            'chapters' => $schema->array()
                ->items($schema->object([
                    'title' => $schema->string()->required()->description('Title of the chapter'),
                    'startTime' => $schema->integer()->required()->description('Start of the chapter in seconds'),
                    'summary' => $schema->string()->required()->description('Summary of what is discussed in the chapter'),
                ])->required())
                ->required()
                ->description('List of chapters'),
        ];
    }
}
