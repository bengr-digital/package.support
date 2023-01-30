<?php

namespace Bengr\Support\Rules;

use Bengr\Support\Rules\Concerns\CustomRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class BengrFileMax implements Rule
{
    use CustomRule;

    protected int $size = 0;

    public function __construct(int $size)
    {
        $this->size = $size * 1024;
    }

    public function handle($attribute, $value)
    {
        if ($value['temporary'] && Storage::disk('local')->exists($value['path'])) {
            if (Storage::disk('local')->size($value['path']) > $this->size) {
                $this->setError(__('validation.bengr_file_max'));
            }
        }
    }
}
