<?php

use App\Models\Entry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->text('original_summary')->nullable()->after('summary');
        });

        // Migrate existing data
        Entry::query()
            ->where('summary', 'LIKE', '%<original_summary>%')
            ->each(function (Entry $entry) {
                if (preg_match('/<original_summary>(.*?)<\/original_summary>/s', $entry->summary, $matches)) {
                    $originalSummary = trim($matches[1]);
                    $aiSummary = trim(preg_replace('/<original_summary>.*?<\/original_summary>\s*(\[Auto-generated summary\])?\s*/s', '', $entry->summary));

                    $entry->update([
                        'original_summary' => $originalSummary,
                        'summary' => $aiSummary ?: null,
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropColumn('original_summary');
        });
    }
};
