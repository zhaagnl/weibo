<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *definition()方法用于定义模型的默认状态，即在使用工厂创建用户实例时，应该如何生成用户的属性值。在这里，它使用Faker库生成随机的姓名、唯一的安全邮箱地址，并设置其他属性的默认值，如email_verified_at、activated、password和remember_token。
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'activated' => true,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *unverified()方法用于定义一个状态转换，使得生成的用户实例的email_verified_at属性为null，表示该用户的邮箱地址尚未验证。这在测试或模拟未验证用户的场景中非常有用。
     * @return static
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
