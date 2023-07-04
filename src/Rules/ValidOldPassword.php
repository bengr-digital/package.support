<?php

namespace Bengr\Support\Rules;

use Bengr\Support\Rules\Concerns\CustomRule;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class ValidOldPassword implements Rule
{
    use CustomRule;

    protected ?string $guard = null;

    protected ?Authenticatable $user = null;

    public function __construct($guard = null, Authenticatable $user = null)
    {
        $this->guard = $guard;
        $this->user = $user ?? auth($this->guard)->user();
    }

    public function handle($attribute, $value)
    {
        if (!$this->user || !Hash::check($value, $this->user->password)) {
            $this->setError(__('validation.incorrect'));
        }
    }
}
