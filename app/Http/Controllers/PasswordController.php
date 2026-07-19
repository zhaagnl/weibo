<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class PasswordController extends Controller
{
    public function __construct()
    {
        // $this->middleware('throttle:2,1', [
        //     'only' => ['showLinkRequestForm']
        // ]);
        // $this->middleware('throttle:3,10', [
        //     'only' => ['sendResetLinkEmail']
        // ]);
    }

    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        // 1.验证邮箱
        $request->validate(['email'=> 'required|email']);
        $email = $request->email;

        // 2.获取对应用户
        $user = User::where("email", $email)->first();

        // 3.如果不存在
        if(is_null($user)){
            session()->flash('danger', '邮箱未注册 ');
            // withInput()把当前请求的所有输入数据（$request->all()）一次性闪存到 Session 中，键名为 _old_input
            return redirect()->back()->withInput();
        }

        // 4.生成token，会在视图 emails.reset_link 里拼接链接
        // Str::random(40)：随机生成一个 40 字符的随机字符串。
        // config('app.key')：读取 config/app.php 中的 key 值（即 APP_KEY，用于加密/哈希）。
        // hash_hmac('sha256', ..., ...)：使用 HMAC-SHA256 算法，将随机字符串与 app.key 混合哈希，生成一个更安全、不可猜测的令牌。
        $token = hash_hmac('sha256', Str::random(40), config('app.key'));

        // 5.入库， 使用 updateOrInsert 来保持 Email 唯一
        DB::table('password_resets')->updateOrInsert(['email' => $email],
        [
            'email' => $email,
            // 对令牌进行 bcrypt 哈希（默认使用 bcrypt 算法），然后存入数据库。
            // 注意：这里存储的是令牌的哈希值，而不是原始令牌。用户收到的邮件里是原始 $token，验证时需要把用户提交的令牌用 Hash::check() 与库里的哈希值比对。
            'token' => Hash::make($token),
            // 记录令牌生成时间（Carbon 是 Laravel 的日期时间类），可用于判断令牌是否过期。
            'created_at' => new Carbon,
        ]);

        // 6.将Token 链接发送给用户
        Mail::send('emails.reset_link', compact('token'), function($message) use ($email){
            $message->to($email)->subject("忘记密码");
        });

        session()->flash('success', '重置邮件发送成功，请查收');
        // redirect()->back() 方法用于生成一个重定向响应，指示浏览器返回到上一个请求的 URL。它通常用于在表单提交后，将用户重定向回原来的页面，以便他们可以查看操作结果或继续进行其他操作。
        return redirect()->back();

    }

    public function showResetForm(Request $request)
    {
        //  $request->route()获取当前匹配成功的路由对象;->parameter('token')：从该路由中提取名为 token 的 URL 参数
        $token = $request->route()->parameter('token');
        return view('auth.passwords.reset', compact('token'));
    }

    public function reset(Request $request)
    {
        // 1.验证数据是否合规
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);
        $email = $request->email;
        $token = $request->token;
        // 找回密码链接的有效时间
        $expires = 60 * 10;

        // 2.获取对应用户
        $user = User::where("email",$email)->first();

        // 3.如果不存在
        if(is_null($user)){
            session()->flash('danger', '邮箱未注册');
            return redirect()->back()->withInput();
        }

        // 4.读取重置记录
        $record = (array)DB::table('password_resets')->where('email',$email)->first();

        // 5.记录存在
        if($record){
            // 5.1检查是否过期
            if(Carbon::parse($record['created_at'])->addSeconds($expires)->isPast()){
                session()->flash('danger', '链接已过期，请重新尝试');
                return redirect()->back();
            }

            // 5.2检查是否正确
            if(! Hash::check($token,$record['token'])){
                session()->flash('danger', '令牌错误');
                return redirect()->back();
            }

            // 5.3一切正常，更新用户密码
            $user->update(['password' => bcrypt($request->password)]);

            // 5.4提示用户更新成功
            session()->flash('success', '密码重置成功，请使用新密码登录');
            return redirect()->route('login');
        }

        //6.记录不存在
        session()->flash('danger', '未找到重置记录');
        return redirect()->back();
    }
}
