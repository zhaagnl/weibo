<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UsersController extends Controller
{
    public function create()
    {
        return view('users.create');
    }
    public function show(User $user)
    {
        // compact()函数用于将变量打包成一个数组，这样可以方便地将数据传递给视图。在这里，compact('user')会创建一个包含'user'键的数组，其值为$user变量的值。
        return view('users.show',compact('user'));
    }

    public function store(Request $request)
    {   
        // validate数据验证
        $this->validate($request,[
            'name' => 'required|unique:users|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            // bcrypt()函数是Laravel提供的一个哈希函数，用于对密码进行加密存储，确保用户的密码安全。
            'password' => bcrypt($request->password),
        ]);

        // session->flash()方法用于在会话中存储一条临时消息，这条消息只会在下一次请求中可用，之后就会被删除。这里的'success'是消息的类型，'欢迎，您将在这里开启一段新的旅程~'是具体的消息内容。
        session()->flash('success','欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show',[$user]); 

    }
}
