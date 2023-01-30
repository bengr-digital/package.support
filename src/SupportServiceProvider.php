<?php

namespace Bengr\Support;

use Bengr\Support\Rules\BengrFile;
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
        Rule::macro('bengr-file', fn (...$disks) => new BengrFile($disks));
    }
}
