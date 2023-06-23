<?php

namespace Bengr\Support\Rules\Concerns;

trait CustomRule
{
    protected array $errors = [];

    public function passes($attribute, $value)
    {
        $this->handle($attribute, $value);

        if (count($this->errors)) return false;

        return true;
    }

    public function setError(string $error)
    {
        array_push($this->errors, $error);
    }

    public function message()
    {
        return $this->errors;
    }
}
