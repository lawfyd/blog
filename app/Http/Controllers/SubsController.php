<?php

namespace App\Http\Controllers;

use App\Mail\SubscribeEmail;
use App\Subscription;
use Illuminate\Http\Request;

class SubsController extends Controller
{
    public function subscribe(Request $request)
    {
        $message = [
            'required' => 'The email field is required.'
        ];
        $this->validate($request, [
           'subs-email' => 'required|email|unique:subscriptions,email'
        ], $message);

        $subs = Subscription::add($request->get('subs-email'));
        $subs->generateToken();

        \Mail::to($subs)->send(new SubscribeEmail($subs));

        return redirect()->back()->with('status', 'Проверьте вашу почту');
    }

    public function verify($token)
    {
        $subs = Subscription::where('token', $token)->firstOrFail();
        $subs->token = null;
        $subs->save();

        return redirect('/')->with('status', 'Ваша почта подтверждена');
    }
}
