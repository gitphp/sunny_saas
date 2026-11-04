<?php

/**
 * 用户工厂
 *
 * @package     Database\Factories
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace Database\Factories;

use App\Modules\Iam\Infrastructure\Persistence\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * 用户工厂
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    /**
     * 生成一条默认测试数据
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'mobile' => null,
            'password' => static::$password ??= Hash::make('password'),
            'status' => 1,
            'remember_token' => '',
        ];
    }
}

