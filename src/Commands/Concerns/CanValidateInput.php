<?php

namespace Bengr\Support\Commands\Concerns;

use Closure;
use Illuminate\Support\Facades\Validator;

trait CanValidateInput
{
    protected function validate(Closure $callback, string $field, array $rules): string
    {
        $input = $callback();

        $validator = Validator::make(
            [$field => $input],
            [$field => $rules],
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            $input = $this->validate($callback, $field, $rules);
        }

        return $input;
    }
}
