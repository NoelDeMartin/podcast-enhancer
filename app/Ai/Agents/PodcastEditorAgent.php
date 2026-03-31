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

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are an expert podcast producer. You will be provided with a raw podcast transcript.
The transcript is formatted line-by-line as `[timestamp_in_seconds] spoken text`.
Timestamps are whole seconds and always come from the transcript itself.
Example: `[14.5] Welcome to the show.` means the text was spoken at 14.5 seconds.

YOUR TASK:
1. Write a summary of the episode.
2. Create a logical list of chapters for the episode with clear, descriptive titles.
3. For each chapter, extract the exact `startTime` in seconds based on the bracketed numbers in the transcript.

CRITICAL RULES:
- Every chapter `startTime` MUST exactly match one of the bracketed timestamps present in the transcript.
- Do NOT guess timestamps. If you are unsure, choose the closest earlier timestamp that is clearly relevant.
- Create enough chapters so no single chapter spans an excessively large portion of the episode.
PROMPT;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'summary' => $schema->string()->required()->description('Summary of the episode'),
            'chapters' => $schema->array(
                $schema->object([
                    'title' => $schema->string()->required()->description('Title of the chapter'),
                    'startTime' => $schema->integer()->required()->description('Start of the chapter in seconds'),
                ])
            )->required()->description('List of chapters'),
        ];
    }
}
