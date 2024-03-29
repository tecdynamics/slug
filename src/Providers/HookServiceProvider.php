<?php

namespace Tec\Slug\Providers;

use Tec\Base\Facades\Assets;
use Tec\Base\Supports\ServiceProvider;
use Tec\Slug\Facades\SlugHelper;
use Illuminate\Database\Eloquent\Model;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(BASE_FILTER_SLUG_AREA, [$this, 'addSlugBox'], 17, 2);

        add_filter('core_slug_language', [$this, 'setSlugLanguageForGenerator'], 17);
    }

    public function addSlugBox(string|null $html = null, ?Model $object = null): string|null
    {
        if ($object && SlugHelper::isSupportedModel($class = get_class($object))) {
            Assets::addScriptsDirectly('vendor/core/packages/slug/js/slug.js')
                ->addStylesDirectly('vendor/core/packages/slug/css/slug.css');

            $prefix = SlugHelper::getPrefix($class);

            return $html . view('packages/slug::partials.slug', compact('object', 'prefix'))->render();
        }

        return $html;
    }

    public function setSlugLanguageForGenerator(): bool|string
    {
        return ! SlugHelper::turnOffAutomaticUrlTranslationIntoLatin() ? 'en' : false;
    }
}
