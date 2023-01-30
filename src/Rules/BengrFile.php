<?php

namespace Bengr\Support\Rules;

use Bengr\Support\Rules\Concerns\CustomRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BengrFile implements Rule
{
    use CustomRule;

    public function __construct()
    {
    }

    public function handle($attribute, $value)
    {
        if (!array_key_exists('path', $value) || !array_key_exists('temporary', $value) || !array_key_exists('id', $value)) {
            $this->setError(__('validation.bengr_file_format'));
        }

        if ($value['temporary'] && !Storage::disk('local')->exists($value['path'])) {
            $this->setError(__('validation.bengr_file_exists'));
        }

        if (!$value['temporary'] && !Media::where('uuid', $value['id'])->exists()) {
            $this->setError(__('validation.bengr_file_exists'));
        }
    }
}
