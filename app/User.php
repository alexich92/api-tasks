<?php

namespace App;

use Carbon\Carbon;
use GenTux\Jwt\JwtPayloadInterface;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JwtPayloadInterface
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email','role_id', 'status','forgot_password_code',
        'generated_forgot_password',
    ];


    const ACCOUNT_NOT_ACTIVATED = 0;

    const ACCOUNT_ACTIVATED= 1;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'forgot_password_code',
        'generated_forgot_password',
    ];

    public function getPayload()
    {
        return [
            'id' => $this->id,
            'exp' => time() + 7200,
            'context' => [
                'email' => $this->email
            ]
        ];
    }

    public function role()
    {
        return $this->belongsTo('App\Role');
    }


    //check to see if user is an admin
    public function isAdmin()
    {
        if($this->role_id === Role::ADMIN_USER){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Login user
     *
     * @param $userEmail
     * @param $userPassword
     *
     * @return bool
     */
    public function login($userEmail, $userPassword)
    {
        $user = $this->where([
            'email' => $userEmail,
        ])->get()->first();

        if (!$user) {
            return false;
        }

        $password = $user->password;

        if (app('hash')->check($userPassword, $password)) {
            return $user;
        }

        return false;
    }

    /**
     * Change user password
     *
     * @param $userDetails
     *
     * @return User|bool
     */
    public function changePassword($userDetails)
    {
        /** @var User $user */
        $user = $this->where('forgot_password_code', $userDetails['code'])
            ->where('generated_forgot_password', '<', Carbon::now()->addHour()->format('Y-m-d H:i:s'))
            ->get()->first();

        if (!$user) {
            return false;
        }

        $user->forgot_password_code = '';
        $user->password = $userDetails['password'];
        $user->save();

        return $user;
    }
}
