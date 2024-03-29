<?php

namespace Tec\Slug\Providers;

use Tec\Base\Facades\BaseHelper;
use Tec\Base\Facades\DashboardMenu;
use Tec\Base\Facades\MacroableModels;
use Tec\Base\Models\BaseModel;
use Tec\Base\Supports\ServiceProvider;
use Tec\Base\Traits\LoadAndPublishDataTrait;
use Tec\Page\Models\Page;
use Tec\Slug\Facades\SlugHelper;
use Tec\Slug\Models\Slug;
use Tec\Slug\Repositories\Eloquent\SlugRepository;
use Tec\Slug\Repositories\Interfaces\SlugInterface;
use Tec\Slug\SlugCompiler;
use Illuminate\Routing\Events\RouteMatched;

class SlugServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    protected bool $defer = true;

    public function register(): void
    {
        $this
            ->setNamespace('packages/slug')
            ->loadAndPublishTranslations();

        $this->app->bind(SlugInterface::class, function () {
            return new SlugRepository(new Slug());
        });

        $this->app->singleton(SlugHelper::class, function () {
            return new SlugHelper(new SlugCompiler());
        });
    }

    public function boot(): void
    {
        $this
            ->loadAndPublishConfigurations(['general'])
            ->loadHelpers()
            ->loadAndPublishViews()
            ->loadRoutes()
            ->loadMigrations()
            ->publishAssets();

        $this->app->register(EventServiceProvider::class);
        $this->app->register(CommandServiceProvider::class);

        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-packages-slug-permalink',
                'priority' => 5,
                'parent_id' => 'cms-core-settings',
                'name' => 'packages/slug::slug.permalink_settings',
                'icon' => null,
                'url' => route('slug.settings'),
                'permissions' => ['settings.options'],
            ]);
        });

        $this->app->booted(function () {
            $this->app->register(FormServiceProvider::class);

            foreach (array_keys( SlugHelper::supportedModels()) as $item) {
                if (! class_exists($item)) {
                    continue;
                }

                /**
                 * @var BaseModel $item
                 */
                $item::resolveRelationUsing('slugable', function ($model) {
                    return $model->morphOne(Slug::class, 'reference')->select([
                        'id',
                        'key',
                        'reference_type',
                        'reference_id',
                        'prefix',
                    ]);
                });

                if (! method_exists($item, 'getSlugAttribute') && ! method_exists($item, 'slug') && ! property_exists($item, 'slug')) {
                    MacroableModels::addMacro($item, 'getSlugAttribute', function () {
                        /**
                         * @var BaseModel $this
                         */
                        return $this->slugable ? $this->slugable->key : '';
                    });
                }

                if (! method_exists($item, 'getSlugIdAttribute') && ! method_exists($item, 'slugId') && ! property_exists($item, 'slug_id')) {
                    MacroableModels::addMacro($item, 'getSlugIdAttribute', function () {
                        /**
                         * @var BaseModel $this
                         */
                        return $this->slugable ? $this->slugable->getKey() : '';
                    });
                }

                if (! method_exists($item, 'getUrlAttribute') && ! method_exists($item, 'url') && ! property_exists($item, 'url')) {
                    MacroableModels::addMacro(
                        $item,
                        'getUrlAttribute',
                        function () {
                            /**
                             * @var BaseModel $this
                             */
                            $model = $this;

                            $slug = $model->slugable;

                            if (
                                ! $slug ||
                                ! $slug->key ||
                                (get_class($model) == Page::class && BaseHelper::isHomepage($model->getKey()))
                            ) {
                                return route('public.index');
                            }

                            $prefix = SlugHelper::getTranslator()->compile(
                                apply_filters(FILTER_SLUG_PREFIX, $slug->prefix),
                                $model
                            );

                            return apply_filters(
                                'slug_filter_url',
                                url(ltrim($prefix . '/' . $slug->key, '/')) . SlugHelper::getPublicSingleEndingURL()
                            );
                        }
                    );
                }
            }

            $this->app->register(HookServiceProvider::class);
        });
    }

    public function provides(): array
    {
        return [
            SlugHelper::class,
        ];
    }
}
