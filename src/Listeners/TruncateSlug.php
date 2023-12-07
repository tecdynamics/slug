<?php

namespace Tec\Slug\Listeners;

use Tec\Slug\Models\Slug;

class TruncateSlug
{
    public function handle(): void
    {
        Slug::query()->truncate();
    }
}
