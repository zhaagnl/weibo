<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
    public function create()
    {
        return view('sessions.create');
    }

    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);
        // Auth::attempt() 方法会接收一个包含用户凭证的数组作为参数，并尝试使用这些凭证来验证用户。如果验证成功，用户将被登录，并且方法返回 true；如果验证失败，方法返回 false。
        if(Auth::attempt($credentials,$request->has('remember'))){
            session()->flash('success', '欢迎回来！');
            // Auth::user() 方法用于获取当前经过身份验证的用户实例。它返回一个表示当前登录用户的 User 模型对象。通过调用 Auth::user()，你可以访问当前登录用户的属性和方法，例如获取用户的 ID、姓名、邮箱等信息。
            return redirect()->route('users.show', [Auth::user()]);
        }else{
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
            // back() 方法用于生成一个重定向响应，指示浏览器返回到上一个请求的 URL。它通常用于在表单提交失败或验证错误时，将用户重定向回原来的页面，以便他们可以重新填写表单或查看错误消息。
            // withInput() 方法用于在重定向时将用户输入的数据保存在会话中，以便在下次请求时可以重新填充表单字段。它通常与表单验证失败的情况一起使用，以便用户不必重新输入所有数据。
            return redirect()->back()->withInput();
        }
       
    }

    public function destroy()
    {
        Auth::logout();
        session()->flash('success', '您已成功退出！');
        return redirect('login');
    }

}
