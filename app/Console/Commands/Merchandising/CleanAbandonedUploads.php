<?php

namespace App\Console\Commands\Merchandising;

use App\Models\Merchandising\Upload;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('merchandising:clean-uploads {--hours=72 : Age after which an unsubmitted upload is removed}')]
#[Description('Delete photos and videos uploaded for visits that were never submitted.')]
class CleanAbandonedUploads extends Command
{
    public function handle(): void
    {
        $removed = 0;

        Upload::whereNull('visit_id')
            ->where('updated_at', '<', now()->subHours((int) $this->option('hours')))
            ->chunkById(200, function ($uploads) use (&$removed) {
                foreach ($uploads as $upload) {
                    Storage::disk('local')->delete($upload->temp_path);
                    $upload->delete();
                    $removed++;
                }
            });

        $this->info("Removed {$removed} abandoned uploads.");
    }
}
