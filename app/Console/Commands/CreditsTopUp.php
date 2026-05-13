<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class CreditsTopUp extends Command
{
    protected $signature = 'credits:top-up {--user= : The user ID or email} {--credits= : The number of credits to add} {--description= : A description of why the credits are being added}';

    protected $description = 'Top up a user\'s credits and record the transaction.';

    public function handle(): int
    {
        $identifier = $this->option('user') ?? select(
            label: 'Who should receive the credits?',
            options: User::query()
                ->select(['id', 'name', 'email'])
                ->get()
                ->mapWithKeys(fn (User $user) => [$user->id => "{$user->name} ({$user->email})"])
                ->all(),
            required: true
        );

        $creditsInput = $this->option('credits') ?? text(
            label: 'How many credits to add?',
            placeholder: 'e.g. 50',
            required: true,
            validate: fn (string $value) => is_numeric($value) && (int) $value > 0
                ? null
                : 'The credits must be a positive integer.'
        );

        $credits = (int) $creditsInput;

        $description = $this->option('description') ?? text(
            label: 'What is the reason for this top-up?',
            placeholder: 'e.g. Loyalty bonus',
            required: true
        );

        $user = is_numeric($identifier)
            ? User::find($identifier)
            : User::where('email', $identifier)->first();

        if (! $user) {
            error("User [{$identifier}] not found.");

            return self::FAILURE;
        }

        DB::transaction(function () use ($user, $credits, $description) {
            $user->creditTopUps()->create([
                'credits' => $credits,
                'description' => $description,
            ]);

            $user->increment('credits', $credits);
        });

        info("Successfully added {$credits} credits to {$user->name} ({$user->email}).");
        info("New balance: {$user->fresh()->credits} credits.");

        return self::SUCCESS;
    }
}
