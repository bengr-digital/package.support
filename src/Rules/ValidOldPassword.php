<?php

namespace Bengr\Support\Rules;

use Bengr\Support\Rules\Concerns\CustomRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class ValidOldPassword implements Rule
{
    use CustomRule;

    protected ?string $guard = null;

    public function __construct($guard = null)
    {
        $this->guard = $guard;
    }

    public function handle($attribute, $value)
    {
        if (!auth($this->guard)->user() || !Hash::check($value, auth($this->guard)->user()->password)) {
            $this->setError(__('validation.incorrect'));
        }
    }
}
