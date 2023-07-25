<?php

namespace Bengr\Support\Tests;

use Bengr\Support\SupportServiceProvider;
use Bengr\Support\Tests\Support\TestResources\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            function (string $modelName) {
                return 'Bengr\\Support\\Database\\Factories\\' . class_basename($modelName) . 'Factory';
            }
        );
        $this->setUpDatabase();
        $this->setUpAuth();
    }

    protected function getPackageProviders($app)
    {
        return [
            SupportServiceProvider::class,
        ];
    }

    protected function setUpDatabase()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    protected function setUpAuth()
    {
        config([
            'auth.guards.testing' => [
                'driver' => 'session',
                'provider' => 'testing'
            ],
            'auth.providers.testing' => [
                'driver' => 'eloquent',
                'model' => User::class
            ]
        ]);
    }
}
