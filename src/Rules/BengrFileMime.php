<?php

namespace Bengr\Support\Rules;

use Bengr\Support\Rules\Concerns\CustomRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BengrFileMime implements Rule
{
    use CustomRule;

    protected array $mimes = [];

    public function __construct(array $mimes)
    {
        $this->mimes = $mimes;
    }

    public function getMimes()
    {
        return $this->mimes;
    }

    public function handle($attribute, $value)
    {
        if ($value['temporary'] && Storage::disk('local')->exists($value['path'])) {

            if (!in_array(Str::of($value['path'])->explode('.')->last(), $this->getMimes())) {
                $this->setError(__('validation.bengr_file_mimes'));
            }
        }
    }
}
