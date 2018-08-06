<?php
namespace App\Services;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class UserServices
{


    /**
     * Validate request on register user
     *
     * @param Request $request
     *
     * @return Validator
     */

    public function validateRegister(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'confirmPassword' => 'required_with:password|same:password'
        ];

        $messages = [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email is invalid',
            'email.unique' => 'Email already exists in our database',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters long',
            'confirmPassword.required_with' => 'Confirm password is required',
            'confirmPassword.same' => 'Password does not match the confirm password',
        ];

         return  Validator::make($request->all(), $rules, $messages);
    }



    /**
     * Validate request on forgot password
     *
     * @param Request $request
     *
     * @return Validator
     */
    public function validateForgotPassword(Request $request)
    {
        $rules = [
            'email' => 'required|email|exists:users'
        ];

        $messages = [
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email',
            'email.exists' => 'Email not registered',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Send code on email for forgot password
     *
     * @param User $user
     */
    public function sendForgotCode(User $user)
    {
        $user->forgot_password_code = strtoupper(str_random(6));
        $user->generated_forgot_password = Carbon::now()->format('Y-m-d H:i:s');

        $user->save();

        /** @var EmailServices $emailServices */
        $emailServices = new EmailServices();

        $emailServices->sendForgotPassword($user);

        $user->updated_at = Carbon::now()->format('Y-m-d H:i:s');
        $user->save();
    }



    /**
     * Validate request on forgot change password
     *
     * @param Request $request
     *
     * @return Validator
     */
    public function validateChangePassword(Request $request)
    {
        $rules = [
            'code' => 'required',
            'password' => 'required|min:6',
            'confirmPassword' => 'required_with:password|same:password'
        ];

        $messages = [
            'code.required' => 'Code required',
            'password.required' => 'Password is required',
            'password.min' => 'Password need to have at least 6 characters',
            'confirmPassword.required_with' => 'Confirm password is required',
            'confirmPassword.same' => 'Password does not match the confirm password',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }



    public function validateUpdateUserInfo(Request $request ,$user_id)
    {

        $rules = [
            'name' =>'required',
            'email' => 'required|unique:users,email,'.$user_id,
            'new_password'=>'required|min:6',
            'confirm_new_password'=>'required_with:new_password|same:new_password'
        ];

        $messages = [
            'email.required' => 'Email is required',
            'email.email' => 'Email is not valid',
            'email.exists' => 'Email taken',
            'new_password.required'=>'New password is required',
            'new_password.min'=>'New password need to have at least 6 characters',
            'confirm_new_password.required_with'=>'Confirm_new_password field is required',
            'confirm_new_password.same'=>'Confirm_new_password field  must match  new_password field'
        ];

        return  Validator::make($request->all(), $rules, $messages);

    }



    public function validateUpdateUserById(Request $request,$user_id)
    {
        $rules = [
            'name' =>'required',
            'email' => 'required|unique:users,email,'.$user_id,
            'status'=>'required',
            'role_id'=>'required',
            'password'=>'required|min:6',
            'confirm_password'=>'required_with:password|same:password'
        ];

        $messages = [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email is not valid',
            'email.exists' => 'Email taken',
            'status.required' =>'Status is required',
            'role_id.required'=>'Role_id is required',
            'password.required' => 'Password is required',
            'password.min' => 'Password need to have at least 6 characters',
            'confirm_password.required_with' => 'Confirm password is required',
            'confirm_password.same' => 'Password does not match the confirm password',
        ];

        return Validator::make($request->all(), $rules, $messages);

    }


}