<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Laravel\Socialite\Facades\Socialite;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class AuthController extends Controller
{
    /**
     * Redirect user to Google OAuth
     */
    public function redirectToGoogle(Request $request)
    {
        // Lưu state vào session để redirect về trang trước đó sau khi đăng nhập
        $redirectUrl = $request->get('state', '/');
        session(['auth_redirect' => $redirectUrl]);
        
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            if (!$request->has('code')) {
                return redirect('/')->with('error', 'Đăng nhập thất bại: Không nhận được mã xác thực từ Google.');
            }

            $httpClient = new Client([
                'verify' => false,
                'http_errors' => false,
                'timeout' => 30
            ]);
            
            $googleUser = Socialite::driver('google')
                ->setHttpClient($httpClient)
                ->stateless()
                ->user();

            // Tìm hoặc tạo user
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($user) {
                if (!$user->google_id) {
                    $user->google_id = $googleUser->getId();
                }
                if (!$user->avatar && $googleUser->getAvatar()) {
                    $user->avatar = $googleUser->getAvatar();
                }
                if (!$user->name) {
                    $user->name = $googleUser->getName();
                }
                $user->last_login_at = Carbon::now();
                $user->save();
            } else {
                $user = User::create([
                    'google_id' => $googleUser->getId(),
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => Carbon::now(),
                    'last_login_at' => Carbon::now(),
                ]);
            }

            Auth::login($user, true);
            session([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                ]
            ]);

            $redirectUrl = session('auth_redirect', '/');
            session()->forget('auth_redirect');
            
            return redirect($redirectUrl)->with('user_logged_in', true);
        } catch (\Exception $e) {
            Log::error('Google OAuth Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return redirect('/')->with('error', 'Đăng nhập thất bại. Vui lòng thử lại.');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        $cookieName = 'remember_web_' . config('auth.defaults.guard');
        $cookie = Cookie::forget($cookieName);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 1,
                'message' => 'Đăng xuất thành công'
            ])->withCookie($cookie);
        }

        return redirect('/')->withCookie($cookie);
    }

    /**
     * Lấy thông tin user hiện tại
     */
    public function user(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            return response()->json([
                'status' => 1,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                ]
            ]);
        }

        return response()->json([
            'status' => 0,
            'message' => 'Chưa đăng nhập'
        ]);
    }
}
