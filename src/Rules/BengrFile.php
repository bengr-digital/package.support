<?php

namespace Bengr\Support\Rules;

use Bengr\Support\Rules\Concerns\CustomRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BengrFile implements Rule
{
    use CustomRule;

    public function __construct()
    {
    }

    public function handle($attribute, $value)
    {
        if (!$value) return;

        if (!array_key_exists('path', $value) || !array_key_exists('temporary', $value) || !array_key_exists('uuid', $value)) {
            $this->setError(__('validation.bengr_file_format'));
        }


        if ($value['temporary'] && !Storage::disk('local')->exists($value['path'] ? $value['path'] : '')) {
            $this->setError(__('validation.bengr_file_exists'));
        }

        $path = Str::of($value['path'])->remove(config('app.url'));

        if (!$value['temporary'] && $value['uuid'] && !Media::where('uuid', $value['uuid'])->exists()) {
            $this->setError(__('validation.bengr_file_exists'));
        }
    }
}
