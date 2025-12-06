<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Modules\Admin\Models\Admin;
use Modules\User\Models\User;

if (! function_exists('getCurrentLang')) {
    function getCurrentLang(): string
    {
        return app()->getLocale();
    }
}

if (! function_exists('uploadMedia')) {
    function uploadMedia($name, $files, ?Model $model, $clearMedia = false): void
    {
        if ($clearMedia) {
            $model?->clearMediaCollection($name);
        }
        if (is_array($files)) {
            foreach ($files as $file) {
                uploadMedia($name, $file, $model, $clearMedia);
            }
        }
        if ($files instanceof UploadedFile) {
            $model->addMedia($files)->toMediaCollection($name);
        }

        if (base64_encode(base64_decode($files, true)) === $files) {
            $model->addMediaFromBase64($files)->usingFileName(Str::finish(Str::random(), '.png'))->toMediaCollection($name);
        }
    }
}

if (! function_exists('registeredModules')) {
    function registeredModules(): array
    {
        return [
            'Auth',
            'Dashboard',
            'Permission',
            'User',
            'SallaIntegration',
            'HiloxaIntegration',
            'Whatsapp',
        ];
    }
}
// Active Guard Function
if (! function_exists('activeGuard')) {
    function activeGuard($guard = null): bool|int|string|null
    {

        if ($guard) {
            return auth($guard)->check();
        }
        foreach (array_keys(config('auth.guards')) as $guard) {
            if (auth($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }
}

// Get auth user
if (! function_exists('user')) {
    /*
     * @param string|null $attribute
     * @param string|null $guard
     * @return Illuminate\Contracts\Auth\Authenticatable|string|null
     */
    function user($attribute = null, $guard = null): User|Admin|string|null
    {
        if ($attribute) {
            return auth(activeGuard($guard))->user()?->{$attribute};
        }

        return auth(activeGuard($guard))->user();
    }
}
