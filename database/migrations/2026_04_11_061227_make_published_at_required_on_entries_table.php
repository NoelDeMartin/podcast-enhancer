<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('entries')
            ->whereNull('published_at')
            ->update([
                'published_at' => DB::raw('created_at'),
            ]);

        Schema::table('entries', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->change();
        });
    }
};
