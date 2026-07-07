<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class UsersController extends Controller
{
    public function __construct()
    {
        // 除了['show', 'create','store']以外的所有方法都需要登录才能访问
        $this->middleware('auth',[
            // except 除了...以外，黑名单方式，推荐使用！only 只允许...，白名单方式
            'except' => ['show', 'create','store','index']
        ]);
        
        // 只允许未登录用户访问注册页面，已登录用户访问注册页面会被重定向到首页
        $this->middleware('guest',[
            'only' => ['create']
        ]);
    }

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

        // auth::login() 方法用于在用户注册成功后，自动将新注册的用户登录到系统中。它接收一个用户实例作为参数，并将该用户标记为已认证状态，从而允许用户在注册后立即访问受保护的资源，而无需再次输入凭证。
        Auth::login($user);
        // session->flash()方法用于在会话中存储一条临时消息，这条消息只会在下一次请求中可用，之后就会被删除。这里的'success'是消息的类型，'欢迎，您将在这里开启一段新的旅程~'是具体的消息内容。
        session()->flash('success','欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show',[$user]); 

    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit',compact('user'));
    }

    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $data = [];
        $data['name'] = $request->name;
        // 空字符串验证不了
        // if($request->password) {
        //     $data['password'] = bcrypt($request->password);
        // }

        // 方法一：使用filled()判断字段存在且不为空(排除null和空字符串)
        // if($request->filled('password')){
        //     $data['password'] = bcrypt($request->password);
        // }

        // 方法二：更严格，连空格也排除
        if($request->has('password') && trim($request->password) !== ''){
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $user);
        
    }

    public function index()
    {
        $users = User::all();
        // compact()函数用于将变量打包成一个数组，这样可以方便地将数据传递给视图。在这里，compact('users')会创建一个包含'users'键的数组，其值为$users变量的值。

        return view('users.index',compact('users'));
    }
}
