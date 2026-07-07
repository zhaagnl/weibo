<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        //unguard()方法用于关闭模型的批量赋值保护机制，这样在执行数据库填充操作时，可以批量插入数据而不会触发保护机制。reguard()方法则用于重新启用批量赋值保护机制，确保在填充操作完成后，模型的安全性得到恢复。
        Model::unguard();
        // call()方法用于调用其他的Seeder类，这样可以将多个Seeder类组合在一起执行。在这里，$this->call(UsersTableSeeder::class)表示调用UsersTableSeeder类来执行用户数据的填充操作。
        $this->call(UsersTableSeeder::class);
        // reguard()方法用于重新启用批量赋值保护机制，确保在填充操作完成后，模型的安全性得到恢复。
        Model::reguard();
    }
}
