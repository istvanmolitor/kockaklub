<?php

namespace App\Observers;

use App\Models\Site;

class SiteObserver
{
    public function saved(Site $site): void
    {
        if ($site->is_main) {
            Site::whereKeyNot($site->id)
                ->where('is_main', true)
                ->update(['is_main' => false]);
        }
    }
}
