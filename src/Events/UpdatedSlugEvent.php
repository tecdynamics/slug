<?php

namespace Tec\Slug\Events;

use Tec\Base\Events\Event;
use Tec\Slug\Models\Slug;
use Eloquent;
use Illuminate\Queue\SerializesModels;

class UpdatedSlugEvent extends Event
{
    use SerializesModels;

    /**
     * @var Eloquent|false
     */
    public $data;

    /**
     * @var Slug
     */
    public $slug;

    /**
     * UpdatedSlugEvent constructor.
     * @param Eloquent $data
     * @param Slug $slug
     */
    public function __construct($data, Slug $slug)
    {
        $this->data = $data;
        $this->slug = $slug;
    }
}
