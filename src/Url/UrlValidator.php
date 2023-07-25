<?php

namespace Bengr\Support\Url;

class UrlValidator
{
    public function matches(UrlHolder $holder, string $url)
    {
        $path = '/' . trim($url, '/');

        return preg_match($holder->getCompiled()->getRegex(), rawurldecode($path));
    }
}
