<?php

namespace Tec\Slug\Providers;

use Tec\Base\Contracts\BaseModel;
use Tec\Base\Forms\FormAbstract;
use Tec\Base\Supports\ServiceProvider;
use Tec\Slug\Facades\SlugHelper;
use Tec\Slug\Forms\Fields\PermalinkField;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        FormAbstract::beforeRendering([$this, 'addSlugBox'], 17);

        add_filter('core_slug_language', [$this, 'setSlugLanguageForGenerator'], 17);
    }

    public function addSlugBox(FormAbstract $form): FormAbstract
    {
        $model = $form->getModel();

        if (! $model instanceof BaseModel || ! SlugHelper::isSupportedModel($model::class)) {
            return $form;
        }

        if (array_key_exists('slug', $form->getFields())) {
            return $form;
        }

        return $form
            ->addAfter(SlugHelper::getColumnNameToGenerateSlug($model), 'slug', PermalinkField::class, [
                'model' => $model,
                'colspan' => 'full',
            ]);
    }

    public function setSlugLanguageForGenerator(): bool|string
    {
        return SlugHelper::turnOffAutomaticUrlTranslationIntoLatin() ? false : 'en';
    }
}
