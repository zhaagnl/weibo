<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;


class UsersController extends Controller
{
    public function __construct()
    {
        // 除了['show', 'create','store']以外的所有方法都需要登录才能访问
        $this->middleware('auth',[
            // except 除了...以外，黑名单方式，推荐使用！only 只允许...，白名单方式
            'except' => ['show', 'create','store','index','confirmEmail']
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

        // 注册后直接登录操作
        // auth::login() 方法用于在用户注册成功后，自动将新注册的用户登录到系统中。它接收一个用户实例作为参数，并将该用户标记为已认证状态，从而允许用户在注册后立即访问受保护的资源，而无需再次输入凭证。
        // Auth::login($user);
        // session->flash()方法用于在会话中存储一条临时消息，这条消息只会在下一次请求中可用，之后就会被删除。这里的'success'是消息的类型，'欢迎，您将在这里开启一段新的旅程~'是具体的消息内容。
        // session()->flash('success','欢迎，您将在这里开启一段新的旅程~');
        // return redirect()->route('users.show',[$user]);

        // 添加邮件激活验证
        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件以发送到你的注册邮箱上，请注意查收。');
        return redirect('/');

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
        // $users = User::all();
        // paginate()方法用于对查询结果进行分页处理，它会根据指定的每页记录数，将查询结果分割成多个页面，并返回当前页面的数据。在这里，User::paginate(6)表示每页显示6条用户记录，并返回当前页的用户数据集合。
        $users = User::paginate(6);
        // compact()函数用于将变量打包成一个数组，这样可以方便地将数据传递给视图。在这里，compact('users')会创建一个包含'users'键的数组，其值为$users变量的值。

        return view('users.index',compact('users'));
    }

    public function destroy(User $user)
    {
        // authorize()方法用于进行授权检查，它会根据指定的策略方法来判断当前用户是否有权限执行某个操作。在这里，$this->authorize('destroy', $user)表示调用UserPolicy中的destroy方法，检查当前登录用户是否有权限删除指定的$user实例。
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

    // 发送邮件激活验证
    public function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'zslemail@qq.com';
        $name = 'MrZhang';
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject){
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }

    // 处理邮箱激活逻辑
    public function confirmEmail($token)
    {
        // firstOrFail() 方法用于从数据库中检索与给定条件匹配的第一条记录。如果找不到匹配的记录，它会抛出一个 ModelNotFoundException 异常，从而触发 404 错误页面。这在处理用户激活链接时非常有用，因为如果提供的激活令牌无效或不存在，应用程序会返回一个适当的错误响应，而不是继续执行后续操作。
        $user = User::where('activation_token', $token)->firstOrFail();
        // activated属性用于表示用户是否已激活。在这里，$user->activated = true;将用户的激活状态设置为已激活，表示该用户已经完成了邮箱验证过程。
        $user->activated = true;
        // activation_token属性用于存储用户的激活令牌。在这里，$user->activation_token = null;将激活令牌设置为null，表示该用户的激活过程已经完成，不再需要使用该令牌进行验证。
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }



}
