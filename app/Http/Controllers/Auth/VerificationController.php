<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class VerificationController extends Controller
{
    public function verify($id, Request $request) {
        try{

            if (!app('api.url')->version('v1')->hasValidSignature($request)) {

                return response()->json(
                    [
                        "success" => false,
                        "message" => trans('verify.invalid')
                    ], 410,[],JSON_UNESCAPED_UNICODE
                );
            }
            $user = User::findOrFail($id);

            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            return response()->json(
                [
                    "success" => true,
                    "message" => trans('verify.success')
                ], 202,[],JSON_UNESCAPED_UNICODE
            );
        }catch (\Exception $exception){

            return response()->json(
                [
                    'success' => false,
                    'message' => $exception->getMessage(),
                ],500
            );
        }
    }

    public function resend() {
        try{

            if (auth()->user()->hasVerifiedEmail()) {

                return response()->json(
                    [
                        "success" => false,
                        "message" => trans('verify.verified'),
                    ], 409,[],JSON_UNESCAPED_UNICODE);
            }
            auth()->user()->sendEmailVerificationNotification();

            return response()->json(
                [
                    "success" => true,
                    "message" => trans('verify.sent'),
                ],200,[],JSON_UNESCAPED_UNICODE
            );
        }catch (\Exception $exception){

            return response()->json(
                [
                    'success' => false,
                    'message' => $exception->getMessage(),
                ],500
            );
        }
    }
}
