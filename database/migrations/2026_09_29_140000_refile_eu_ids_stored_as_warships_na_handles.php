<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const EU_ACCOUNT_ID = '/^[5-9][0-9]{8}$/';

    public function up(): void
    {
        $na = DB::table('handles')->where('type', 'warships_na')->value('id');
        $eu = DB::table('handles')->where('type', 'warships_eu')->value('id');

        if (! $na || ! $eu) {
            return;
        }

        DB::transaction(function () use ($na, $eu) {
            DB::table('handle_member')
                ->where('handle_id', $na)
                ->get()
                ->filter(fn ($row) => preg_match(self::EU_ACCOUNT_ID, trim($row->value)))
                ->each(fn ($row) => $this->refile($row, $na, $eu));
        });
    }

    public function down(): void {}

    private function refile(object $row, int $na, int $eu): void
    {
        $value  = trim($row->value);
        $euRows = DB::table('handle_member')
            ->where('member_id', $row->member_id)
            ->where('handle_id', $eu)
            ->get();

        if ($euRows->contains(fn ($euRow) => trim($euRow->value) === $value)) {
            DB::table('handle_member')->where('id', $row->id)->delete();
        } else {
            DB::table('handle_member')->where('id', $row->id)->update([
                'handle_id'  => $eu,
                'value'      => $value,
                'primary'    => $euRows->isEmpty(),
                'updated_at' => now(),
            ]);
        }

        $this->ensurePrimary($row->member_id, $na);
    }

    private function ensurePrimary(int $memberId, int $handleId): void
    {
        $rows = DB::table('handle_member')
            ->where('member_id', $memberId)
            ->where('handle_id', $handleId)
            ->orderBy('id')
            ->get();

        if ($rows->isNotEmpty() && ! $rows->contains('primary', true)) {
            DB::table('handle_member')->where('id', $rows->last()->id)->update(['primary' => true]);
        }
    }
};
