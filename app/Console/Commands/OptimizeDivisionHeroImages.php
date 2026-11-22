<?php

namespace App\Console\Commands;

use App\Models\Division;
use App\Support\HeroImageOptimizer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Support\Facades\Storage;
use Throwable;

#[Signature('tracker:optimize-division-hero-images
                            {--dry-run : Report the savings without writing anything}')]
#[Description('Re-encode uploaded division header images at the web size and quality the site uses')]
class OptimizeDivisionHeroImages extends BaseCommand
{
    public function handle(): int
    {
        $disk   = Storage::disk('public');
        $dryRun = (bool) $this->option('dry-run');
        $before = 0;
        $after  = 0;

        foreach (Division::whereNotNull('hero_image')->get() as $division) {
            if (! $disk->exists($division->hero_image)) {
                $this->logWarning("{$division->slug}: {$division->hero_image} is missing, skipped");

                continue;
            }

            try {
                $original  = $disk->get($division->hero_image);
                $optimized = HeroImageOptimizer::optimize($original);
            } catch (Throwable $exception) {
                $this->logError("{$division->slug}: could not optimize {$division->hero_image}", $exception);

                continue;
            }

            if (strlen($optimized) >= strlen($original)) {
                $this->line("{$division->slug}: already smaller than the optimized version, kept");

                continue;
            }

            $before += strlen($original);
            $after += strlen($optimized);
            $this->logInfo(sprintf('%s: %s KB -> %s KB', $division->slug, number_format(strlen($original) / 1024), number_format(strlen($optimized) / 1024)));

            if ($dryRun) {
                continue;
            }

            $previous = $division->hero_image;
            $path     = HeroImageOptimizer::store($original, dirname($previous));

            $division->update(['hero_image' => $path]);
            $disk->delete($previous);
        }

        $this->info(sprintf('%s%s KB -> %s KB', $dryRun ? 'Dry run: ' : '', number_format($before / 1024), number_format($after / 1024)));

        return self::SUCCESS;
    }
}
