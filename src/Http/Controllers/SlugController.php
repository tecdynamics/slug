<?php

namespace Tec\Slug\Http\Controllers;

use Tec\Base\Facades\PageTitle;
use Tec\Base\Http\Controllers\BaseController;
use Tec\Base\Http\Responses\BaseHttpResponse;
use Tec\Menu\Facades\Menu;
use Tec\Setting\Supports\SettingStore;
use Tec\Slug\Events\UpdatedPermalinkSettings;
use Tec\Slug\Http\Requests\SlugRequest;
use Tec\Slug\Http\Requests\SlugSettingsRequest;
use Tec\Slug\Models\Slug;
use Tec\Slug\Services\SlugService;
use Illuminate\Support\Str;

class SlugController extends BaseController
{
    public function store(SlugRequest $request, SlugService $slugService)
    {
        return $slugService->create(
            $request->input('value'),
            $request->input('slug_id'),
            $request->input('model')
        );
    }

    public function getSettings()
    {
        PageTitle::setTitle(trans('packages/slug::slug.settings.title'));

        return view('packages/slug::settings');
    }

    public function postSettings(
        SlugSettingsRequest $request,
        BaseHttpResponse $response,
        SettingStore $settingStore
    ) {
        $hasChangedEndingUrl = false;

        foreach ($request->except(['_token', 'ref_lang']) as $settingKey => $settingValue) {
            if (Str::contains($settingKey, '-model-key')) {
                continue;
            }

            if ($settingKey == 'public_single_ending_url') {
                if ($settingValue) {
                    $settingValue = ltrim($settingValue, '.');
                }

                if ($settingStore->get($settingKey) !== $settingValue) {
                    $hasChangedEndingUrl = true;
                }
            }

            $prefix = (string)$settingValue;
            $reference = $request->input($settingKey . '-model-key');

            if ($reference && $settingStore->get($settingKey) !== $prefix) {
                if (! $request->filled('ref_lang')) {
                    Slug::query()
                        ->where('reference_type', $reference)
                        ->update(['prefix' => $prefix]);
                }

                event(new UpdatedPermalinkSettings($reference, $prefix, $request));

                Menu::clearCacheMenuItems();
            }

            $settingStore->set($settingKey, $prefix);
        }

        $settingStore->save();

        if ($hasChangedEndingUrl) {
            Menu::clearCacheMenuItems();
        }

        return $response
            ->setPreviousUrl(route('slug.settings'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }
}
