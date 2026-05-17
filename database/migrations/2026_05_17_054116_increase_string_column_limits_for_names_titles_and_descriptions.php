<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->text('name')->change();
        });

        Schema::table('feeds', function (Blueprint $table) {
            $table->text('title')->change();
        });

        Schema::table('credit_top_ups', function (Blueprint $table) {
            $table->text('description')->change();
        });
    }

    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->string('name')->change();
        });

        Schema::table('feeds', function (Blueprint $table) {
            $table->string('title')->change();
        });

        Schema::table('credit_top_ups', function (Blueprint $table) {
            $table->string('description')->change();
        });
    }
};
