<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class GenerateEntryChaptersAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a helpful assistant that generates chapters for an audio transcript.';
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'chapters' => $schema->array(
                $schema->object([
                    'title' => $schema->string()->description('The title of the chapter'),
                    'description' => $schema->string()->description('A short description of the chapter'),
                ])
            )->required()->description('The list of chapters'),
        ];
    }
}
