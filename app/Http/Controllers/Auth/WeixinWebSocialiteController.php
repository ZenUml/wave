<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use TCG\Voyager\Models\Role;

class WeixinWebSocialiteController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('weixinweb')->redirect();
    }

    public function callback()
    {
        $wechatUser = Socialite::driver('weixinweb')->user();
        $role   = Role::where('name', '=', config('voyager.user.default_role'))->first();

        $trial_days    = setting('billing.trial_days', 14);
        $trial_ends_at = null;

        if (intval($trial_days) > 0) {
            $trial_ends_at = now()->addDays(setting('billing.trial_days', 14));
        }
        $user = User::query()
            ->where('provider_id', $wechatUser->getId())
            ->first();

        if ($user !== null) {
            return $this->login($user, 'Successfully logged in.');
        }

        $user = User::query()
                ->where('email', $wechatUser->getId().'@weixin.com')
                ->first();

        if ($user !== null) {
            $user->update([
                'provider_id' => $wechatUser->getId(),
            ]);

            return $this->login($user, 'Successfully logged in.');
        }

        $user = User::query()->create([
            'provider_id'   => $wechatUser->getId(),
            'email'         => $wechatUser->getId().'@weixin.com',
            'name'          => $wechatUser->getNickname(),
            'password'      => bcrypt(str_random()),
            'username'      => $wechatUser->getNickname(),
            'verified'      => 1,
            'trial_ends_at' => $trial_ends_at,
            'role'          => $role->id,
        ]);

        event(new Registered($user));

        return $this->login($user, 'Thanks for signing up!');
    }

    private function login($user, $message) {
        auth()->guard()->login($user, false);

        return redirect()
            ->route('wave.dashboard')
            ->with([
                'message'      => $message,
                'message_type' => 'success',
            ]);
    }
}
