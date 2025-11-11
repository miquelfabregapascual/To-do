<?php

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
        Schema::create('recurring_anchors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('timezone', 64)->default('UTC');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'day_of_week']);
        });

        Schema::create('anchor_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_anchor_id')->constrained()->cascadeOnDelete();
            $table->date('anchor_date');
            $table->string('action', 32)->default('skip');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['recurring_anchor_id', 'anchor_date']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('is_anchor')->default(false)->after('completed');
            $table->foreignId('recurring_anchor_id')->nullable()->after('is_anchor')->constrained('recurring_anchors')->cascadeOnDelete();
            $table->time('anchor_start_time')->nullable()->after('recurring_anchor_id');
            $table->time('anchor_end_time')->nullable()->after('anchor_start_time');
            $table->unique(['recurring_anchor_id', 'due_date'], 'tasks_recurring_anchor_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropUnique('tasks_recurring_anchor_date_unique');
            $table->dropForeign(['recurring_anchor_id']);
            $table->dropColumn(['is_anchor', 'recurring_anchor_id', 'anchor_start_time', 'anchor_end_time']);
        });

        Schema::dropIfExists('anchor_exceptions');
        Schema::dropIfExists('recurring_anchors');
    }
};
