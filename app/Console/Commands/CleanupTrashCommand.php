<?php

namespace App\Console\Commands;

use App\Models\AppSetting;
use App\Models\Trash;
use Illuminate\Console\Command;

class CleanupTrashCommand extends Command
{
    protected $signature = 'trash:cleanup
                            {--days= : Override the retention days setting}
                            {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Permanently delete trash items older than the retention period';

    public function handle(): int
    {
        $autoCleanupEnabled = AppSetting::get('trash_auto_cleanup_enabled', true);

        if (!$autoCleanupEnabled && !$this->option('days')) {
            $this->info('Automatic trash cleanup is disabled.');
            return self::SUCCESS;
        }

        $days = $this->option('days') ?? AppSetting::get('trash_retention_days', 30);
        $isDryRun = $this->option('dry-run');

        $query = Trash::olderThan($days);
        $count = $query->count();

        if ($count === 0) {
            $this->info("No trash items older than {$days} days found.");
            return self::SUCCESS;
        }

        if ($isDryRun) {
            $this->info("[Dry Run] Would delete {$count} trash item(s) older than {$days} days.");

            $items = $query->get();
            foreach ($items as $item) {
                $modelName = class_basename($item->trashable_type);
                $this->line("  - {$modelName}: {$item->display_name} (deleted {$item->deleted_at->diffForHumans()})");
            }

            return self::SUCCESS;
        }

        $query->get()->each->delete();

        $this->info("Deleted {$count} trash item(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
