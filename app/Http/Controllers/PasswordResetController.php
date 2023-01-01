<?php

namespace App\Http\Controllers;

use App\Models\PasswordReset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail; 
use Illuminate\Support\Str; 

class PasswordResetController extends Controller
{
    public function send_reset_password_email (Request $request) {
        $request->validate([
            'email'=> 'required|email'
        ]);

        $email = $request->email;

        // Check if user's email exists or not
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response([
                'message' => 'User does not exist',
                'status' => 'failed'
            ], 404);
        }

        // Generate Token
        $token = Str::random(60);

        // Save data to password reset table
        PasswordReset::create([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        // Sending email with password reset view
        Mail::send('reset', ['token'=> $token], function (Message $message) use ($email) {
            $message->subject('Reset your password');
            $message->to($email);
        });


        return response([
            'message' => 'Email sent. Check your email',
            'status' => 'success'
        ], 200);

    }

    public function reset (Request $request, $token) {
        // Delete token older than 1 minute
        $formatted = Carbon::now()->subMinutes(1)->toDateTimeString();
        
        PasswordReset::where('created_at', '<=', $formatted)->delete();

        $request->validate([
            'password'=> 'required|confirmed'
        ]);

        $passwordreset = PasswordReset::where('token', $token)->first();

        if (!$passwordreset) {
            return response([
                'message' => 'Token is invalid or expired',
                'status' => 'failed'
            ], 404);
        }

        $user = User::where('email', $passwordreset->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        //Delete token after reseting password
        PasswordReset::where('email', $user->email)->delete();

        return response([
            'message' => 'Password reset success',
            'status' => 'success'
        ], 200);
    }
}
