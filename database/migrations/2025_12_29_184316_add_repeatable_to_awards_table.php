<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $repeatableAwardIds = [171, 321, 56, 194];

    private array $dedupeAwardIds = [257, 317, 288, 19, 314, 316, 327];

    public function up(): void
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->boolean('repeatable')->default(false)->after('allow_request');
        });

        DB::table('awards')
            ->whereIn('id', $this->repeatableAwardIds)
            ->update(['repeatable' => true]);

        foreach ($this->dedupeAwardIds as $awardId) {
            $this->removeDuplicates($awardId);
        }
    }

    private function removeDuplicates(int $awardId): void
    {
        $duplicates = DB::table('award_member')
            ->select('member_id', DB::raw('MIN(id) as keep_id'))
            ->where('award_id', $awardId)
            ->groupBy('member_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('award_member')
                ->where('award_id', $awardId)
                ->where('member_id', $dup->member_id)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }
    }

    public function down(): void
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->dropColumn('repeatable');
        });
    }
};
