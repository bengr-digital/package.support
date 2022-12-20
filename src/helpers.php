<?php

namespace Bengr\Support;

use Bengr\Support\Http\Response;

if (!function_exists('Bengr\Support\response')) {
    function response($content = ''): Response
    {
        return Response::make($content);
    }
}
