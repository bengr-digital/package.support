<?php

namespace Bengr\Support\Url;

class UrlResolver
{
    protected string $url;

    protected array $urlList;

    protected array $urlHolders;

    public function __construct(string $url, array $urlList = [])
    {
        $this->url = trim($url, '/');
        $this->urlList = collect($urlList)->map(fn ($url) => trim($url, '/'))->toArray();

        foreach ($this->urlList as $url) {
            $this->urlHolders[] = new UrlHolder($url);
        }
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getUrlHolders(): array
    {
        return $this->urlHolders;
    }

    public function getUrlList(): array
    {
        return $this->urlList;
    }

    public function resolve(): ?UrlHolder
    {
        $urlHolder = collect($this->getUrlHolders())->first(function (UrlHolder $urlHolder) {
            return $urlHolder->matches($this->url);
        });

        if ($urlHolder) {
            $urlHolder->bind($this->url);
        }

        return $urlHolder;
    }
}
