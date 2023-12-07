<?php

namespace Tec\Slug\Listeners;

use Tec\Base\Events\DeletedContentEvent;
use Tec\Slug\Facades\SlugHelper;
use Tec\Slug\Models\Slug;

class DeletedContentListener
{
    public function handle(DeletedContentEvent $event): void
    {
        if (SlugHelper::isSupportedModel(get_class($event->data))) {
            Slug::query()->where([
                'reference_id' => $event->data->getKey(),
                'reference_type' => get_class($event->data),
            ])->delete();
        }
    }
}
