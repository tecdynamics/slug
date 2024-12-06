<?php

namespace Tec\Slug\Providers;

use Tec\Base\Events\CreatedContentEvent;
use Tec\Base\Events\DeletedContentEvent;
use Tec\Base\Events\FinishedSeederEvent;
use Tec\Base\Events\SeederPrepared;
use Tec\Base\Events\UpdatedContentEvent;
use Tec\Slug\Listeners\CreatedContentListener;
use Tec\Slug\Listeners\CreateMissingSlug;
use Tec\Slug\Listeners\DeletedContentListener;
use Tec\Slug\Listeners\TruncateSlug;
use Tec\Slug\Listeners\UpdatedContentListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UpdatedContentEvent::class => [
            UpdatedContentListener::class,
        ],
        CreatedContentEvent::class => [
            CreatedContentListener::class,
        ],
        DeletedContentEvent::class => [
            DeletedContentListener::class,
        ],
        SeederPrepared::class => [
            TruncateSlug::class,
        ],
        FinishedSeederEvent::class => [
            CreateMissingSlug::class,
        ],
    ];
}
