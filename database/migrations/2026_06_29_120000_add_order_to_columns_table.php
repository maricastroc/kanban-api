<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('columns', function (Blueprint $table): void {
            $table->unsignedInteger('order')->default(1)->after('name');
        });

        // Backfill a stable 1-based order per board, based on insertion (id).
        $boardIds = DB::table('columns')->distinct()->pluck('board_id');

        foreach ($boardIds as $boardId) {
            $ids = DB::table('columns')
                ->where('board_id', $boardId)
                ->orderBy('id')
                ->pluck('id');

            foreach ($ids as $index => $id) {
                DB::table('columns')->where('id', $id)->update(['order' => $index + 1]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('columns', function (Blueprint $table): void {
            $table->dropColumn('order');
        });
    }
};
