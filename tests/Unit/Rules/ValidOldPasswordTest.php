<?php

namespace Bengr\Support\Tests\Unit\Rules;

use Bengr\Support\Rules\ValidOldPassword;
use Bengr\Support\Tests\Support\TestResources\Models\User;
use Bengr\Support\Tests\TestCase;

class ValidOldPasswordTest extends TestCase
{
    public function test_validating_old_password_with_assigned_user()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@test.test',
            'password' => bcrypt('test')
        ]);

        $rule = new ValidOldPassword('testing', $user);

        $this->assertTrue($rule->passes('password', 'test'));
    }

    public function test_validating_old_password_with_auth_user()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@test.test',
            'password' => bcrypt('test')
        ]);

        auth('testing')->login($user);

        $rule = new ValidOldPassword('testing');

        $this->assertTrue($rule->passes('password', 'test'));
    }

    public function test_validating_old_password_with_incorrect_password_and_assigned_user()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@test.test',
            'password' => bcrypt('test')
        ]);

        $rule = new ValidOldPassword('testing', $user);

        $this->assertFalse($rule->passes('password', 'madeuptestingpassword'));
    }

    public function test_validating_old_password_with_incorrect_password_and_auth_user()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@test.test',
            'password' => bcrypt('test')
        ]);

        auth('testing')->login($user);

        $rule = new ValidOldPassword('testing');

        $this->assertFalse($rule->passes('password', 'madeuptestingpassword'));
    }
}
