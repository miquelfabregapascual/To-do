<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'due_date')) {
                $table->date('due_date')->nullable()->after('title');
            }
            if (!Schema::hasColumn('tasks', 'description')) {
                $table->text('description')->nullable()->after('due_date');
            }
            // Do NOT add user_id/completed here if you already have them.
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'due_date')) {
                $table->dropColumn('due_date');
            }
            if (Schema::hasColumn('tasks', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
