<?php

namespace Tec\Slug\Listeners;

use Tec\Base\Contracts\BaseModel;
use Tec\Base\Events\DeletedContentEvent;
use Tec\Slug\Facades\SlugHelper;
use Tec\Slug\Models\Slug;

class DeletedContentListener
{
    public function handle(DeletedContentEvent $event): void
    {
        if ($event->data instanceof BaseModel && SlugHelper::isSupportedModel($event->data::class)) {
            Slug::query()->where([
                'reference_id' => $event->data->getKey(),
                'reference_type' => $event->data::class,
            ])->delete();
        }
    }
}
