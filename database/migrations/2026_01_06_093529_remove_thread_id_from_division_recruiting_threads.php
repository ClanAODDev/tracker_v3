<?php

use App\Models\Division;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Division::all()->each(function ($division) {
            $settings = $division->settings;

            if (! isset($settings['recruiting_threads'])) {
                return;
            }

            $threads = $settings['recruiting_threads'];
            $modified = false;

            foreach ($threads as &$thread) {
                if (isset($thread['thread_id'])) {
                    unset($thread['thread_id']);
                    $modified = true;
                }
            }

            if ($modified) {
                $settings['recruiting_threads'] = $threads;
                $division->settings = $settings;
                $division->save();
            }
        });
    }

    public function down(): void
    {
        // Cannot restore thread_ids - they were redundant with thread_url
    }
};
