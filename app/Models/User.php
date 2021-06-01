<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use App\Notifications\VerifyEmail;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Lumen\Auth\Authorizable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;

class User extends Model implements  AuthenticatableContract, AuthorizableContract, MustVerifyEmail, CanResetPasswordContract
{
    use HasApiTokens,Authenticatable, Authorizable, HasFactory, Notifiable, CanResetPassword;
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'phone_number'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];
    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return ! is_null($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
                                    'email_verified_at' => $this->freshTimestamp(),
                                ])->save();
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }

    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

//    public function getHasVerificationAttribute() {
//        $verification = UserVerification::query()->where('user_id', $this->id);
//        if ($verification->exists()) {
//            $verification = $verification->first();
//            if ($verification->is_user_verified) return true;
//
//            return false;
//        }
//
//        return false;
//    }

    public function enterpriseData(){
        return $this->hasOne(EnterpriseData::class,'user_id','id');
    }
}
