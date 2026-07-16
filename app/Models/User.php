<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *$fillable属性用于定义模型中可以批量赋值的字段。在这里，'name'、'email'和'password'被指定为可批量赋值的字段，这意味着在创建或更新用户实例时，可以通过数组一次性设置这些属性的值。
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *$hidden属性用于定义在模型序列化（如转换为JSON）时应隐藏的属性。在这里，'password'和'remember_token'被隐藏，以确保敏感信息不会暴露给外部系统或API消费者。
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *$casts属性用于定义模型属性的类型转换规则。在这里，'email_verified_at' => 'datetime'表示将email_verified_at属性自动转换为Carbon实例，以便在处理日期和时间时更加方便。
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        // return "https://cdn.v2ex.com/gravatar/$hash?s=$size";
        return "https://cravatar.cn/avatar/$hash?s=$size&d=mp";
    }

    public static function boot()
    {
        //parent::boot()方法用于调用父类的boot方法，确保在子类中也能继承和执行父类的初始化逻辑。在这里，它确保了User模型在创建新用户实例时，仍然会执行父类Authenticatable中的初始化逻辑。
        parent::boot();
        // static::creating()方法用于在创建新用户实例之前执行一个回调函数。在这里，它会在用户创建时生成一个随机的激活令牌，并将其赋值给activation_token属性。这是为了实现用户注册后的邮箱验证功能，确保每个新用户都有一个唯一的激活令牌。
        static::creating(function ($user) {
            $user->activation_token = Str::random(10);
        });
    }
}
