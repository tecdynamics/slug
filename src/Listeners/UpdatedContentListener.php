<?php

namespace Tec\Slug\Listeners;

use Tec\Base\Events\UpdatedContentEvent;
use Tec\Base\Facades\BaseHelper;
use Tec\Slug\Events\UpdatedSlugEvent;
use Tec\Slug\Facades\SlugHelper;
use Tec\Slug\Models\Slug;
use Tec\Slug\Services\SlugService;
use Exception;
use Illuminate\Support\Str;

class UpdatedContentListener
{
    public function handle(UpdatedContentEvent $event): void
    {
        if (SlugHelper::isSupportedModel($class = get_class($event->data)) && $event->request->input('is_slug_editable', 0)) {
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

                $item = Slug::query()
                    ->where([
                        'reference_type' => $class,
                        'reference_id' => $event->data->getKey(),
                    ])
                    ->first();

                if ($item) {
                    if ($item->key != $slug) {
                        $slugService = new SlugService();
                        $item->key = $slugService->create($slug, (int)$event->data->slug_id);
                        $item->prefix = SlugHelper::getPrefix($class, '', false);
                        $item->save();
                    }
                } else {
                    $item = Slug::query()->create([
                        'key' => $slug,
                        'reference_type' => $class,
                        'reference_id' => $event->data->getKey(),
                        'prefix' => SlugHelper::getPrefix($class, '', false),
                    ]);
                }

                /**
                 * @var Slug $item
                 */
                event(new UpdatedSlugEvent($event->data, $item));
            } catch (Exception $exception) {
                BaseHelper::logError($exception);
            }
        }
    }
}
