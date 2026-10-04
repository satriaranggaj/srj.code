<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('id')->unique();
            $table->string('short_description', 300)->nullable()->after('title');
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('project_type')->nullable();
            $table->json('tech_stack')->nullable();
            $table->string('live_url')->nullable();
            $table->string('github_url')->nullable();
            $table->boolean('featured')->default(false);
            $table->string('status')->default('live');
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('problem')->nullable();
            $table->text('solution')->nullable();
            $table->json('highlights')->nullable();
            $table->text('challenges')->nullable();
            $table->text('outcome')->nullable();
            $table->string('role')->nullable();
            $table->string('year', 4)->nullable();
            $table->json('screenshots')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['slug']);

            $table->dropColumn([
                'slug',
                'short_description',
                'description',
                'thumbnail',
                'project_type',
                'tech_stack',
                'live_url',
                'github_url',
                'featured',
                'status',
                'sort_order',
                'problem',
                'solution',
                'highlights',
                'challenges',
                'outcome',
                'role',
                'year',
                'screenshots',
            ]);
        });
    }
};
