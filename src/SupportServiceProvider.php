<?php

namespace Bengr\Support;

use Bengr\Support\Rules\BengrFile;
use Bengr\Support\Rules\BengrFileMax;
use Bengr\Support\Rules\BengrFileMime;
use Bengr\Support\Rules\BengrFileMin;
use Bengr\Support\Rules\ValidOldPassword;
use Illuminate\Validation\Rule;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SupportServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('bengr-support');
    }

    public function packageBooted()
    {
        Rule::macro('validOldPassword', fn ($guard = null) => new ValidOldPassword($guard));
        Rule::macro('bengrFile', fn () => new BengrFile());
        Rule::macro('bengrFileMax', fn ($size) => new BengrFileMax($size));
        Rule::macro('bengrFileMin', fn ($size) => new BengrFileMin($size));
        Rule::macro('bengrFileMime', fn ($mimes) => new BengrFileMime($mimes));
    }
}
