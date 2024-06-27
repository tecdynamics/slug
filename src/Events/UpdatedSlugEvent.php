<?php

namespace Tec\Slug\Events;

use Tec\Base\Events\Event;
use Tec\Slug\Models\Slug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class UpdatedSlugEvent extends Event
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public bool|Model|null $data, public Slug $slug)
    {
    }
}
