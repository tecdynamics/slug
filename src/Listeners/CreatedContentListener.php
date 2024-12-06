<?php

namespace Tec\Slug\Listeners;

use Tec\Base\Contracts\BaseModel;
use Tec\Base\Events\CreatedContentEvent;
use Tec\Base\Facades\BaseHelper;
use Tec\Slug\Facades\SlugHelper;
use Tec\Slug\Models\Slug;
use Tec\Slug\Services\SlugService;
use Exception;
use Illuminate\Support\Str;

class CreatedContentListener
{
    public function handle(CreatedContentEvent $event): void
    {
        if ($event->data instanceof BaseModel && SlugHelper::isSupportedModel($class = $event->data::class) && $event->request->input('is_slug_editable', 0)) {
            try {
                $slug = $event->request->input('slug');

                $fieldNameToGenerateSlug = SlugHelper::getColumnNameToGenerateSlug($event->data);

                if (! $slug) {
                    $slug = $event->request->input($fieldNameToGenerateSlug);
                }

                if (! $slug && $event->data->{$fieldNameToGenerateSlug}) {
                    if (! SlugHelper::turnOffAutomaticUrlTranslationIntoLatin()) {
                        $slug = Str::slug($event->data->{$fieldNameToGenerateSlug});
                    } else {
                        $slug = $event->data->{$fieldNameToGenerateSlug};
                    }
                }

                if (! $slug) {
                    $slug = time();
                }

                $slugService = new SlugService();

                Slug::query()->create([
                    'key' => $slugService->create($slug, (int) $event->data->slug_id, $class),
                    'reference_type' => $class,
                    'reference_id' => $event->data->getKey(),
                    'prefix' => SlugHelper::getPrefix($class, '', false),
                ]);
            } catch (Exception $exception) {
                BaseHelper::logError($exception);
            }
        }
    }
}
