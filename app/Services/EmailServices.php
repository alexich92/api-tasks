<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\User;


class EmailServices
{
    /**
     * Send code on email for forgot password
     *
     * @param User $user
     */
    public function sendForgotPassword(User $user)
    {
        Mail::send('emails.forgot', ['user' => $user], function ($message) use ($user) {
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $message->subject('Forgot password code');

            $message->to($user->email);
        });
    }

}