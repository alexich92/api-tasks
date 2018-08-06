<?php
/**
 * Created by PhpStorm.
 * User: iongh
 * Date: 8/1/2018
 * Time: 3:37 PM
 */

namespace App\Http\Controllers\v1;


use App\Http\Controllers\Controller;
use App\User;
use App\Role;
use Carbon\Carbon;
use GenTux\Jwt\JwtToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Services\UserServices;
class UserController extends Controller
{
    /**
     * Login User
     *
     * @param Request $request
     * @param User $userModel
     * @param JwtToken $jwtToken
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \GenTux\Jwt\Exceptions\NoTokenException
     */
    public function login(Request $request, User $userModel, JwtToken $jwtToken)
    {
        $rules = [
            'email'    => 'required|email',
            'password' => 'required'
        ];

        $messages = [
            'email.required' => 'Email required',
            'email.email'    => 'Email invalid',
            'password.required'    => 'Password empty'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ( ! $validator->passes()) {
            return $this->returnBadRequest($validator->messages());
        }

        $user = $userModel->login($request->email, $request->password);

        if (!$user) {
            return $this->returnNotFound('User sau parola gresite');
        }

        if ($user->status === User::ACCOUNT_NOT_ACTIVATED) {
            return $this->returnError('Account is not activated');
        }


        $token = $jwtToken->createToken($user);

        $data = [
            'user' => $user,
            'jwt'  => $token->token()
        ];

        return $this->returnSuccess($data);
    }

    public function register(Request $request)
    {
        try{
            /** instantiate UserService class */
            $userServices = new UserServices();

            $validator = $userServices->validateRegister($request);

            if (!$validator->passes()) {
                return $this->returnBadRequest($validator->messages());
            }


            $user = new User();
            $user->name = $request->get('name');
            $user->email = $request->get('email');
            $user->password = Hash::make($request->get('password'));
            $user->role_id = Role::NORMAL_USER;
            $user->status = User::ACCOUNT_NOT_ACTIVATED;
            $user->save();
            return $this->returnSuccess($user);

        }catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }

    }


    public function activate(Request $request)
    {
        $rules = [
            'email' => 'required|email|exists:users'
        ];

        $messages = [
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email',
            'email.exists' => 'Email doesnt exist in our database',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if (!$validator->passes()) {
            return $this->returnBadRequest($validator->messages());
        }

        $user = User::where('email', $request->get('email'))->get()->first();

            if (!$user) {
                return $this->returnError('Something went wrong');
            }

            $user->status = User::ACCOUNT_ACTIVATED;
            $user->save();
            return $this->returnSuccess('The account with the email ' .$user->email . ' has been activated');

    }


    public function forgotPassword(Request $request,User $userModel)
    {
        //check to see if the request has the code
        if ($request->has('code')) {
            return $this->changePassword($request,$userModel);
        }

       try {
           /** instantiate UserServices class */
            $userServices = new UserServices();

           $validator = $userServices->validateForgotPassword($request);

            if (!$validator->passes()) {
                return $this->returnBadRequest($validator->messages());
            }

            $user = User::where('email', $request->get('email'))->get()->first();

            if ($user->status === User::ACCOUNT_NOT_ACTIVATED) {
                return $this->returnError('Account not activated');
            }

            if ($user->updatedAt > Carbon::now()->subMinute()->format('Y-m-d H:i:s')) {
                return $this->returnError('Error!Wait a minute and try again');
            }

            $userServices->sendForgotCode($user);

            return $this->returnSuccess('The reset code  was sent to your email');
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }

    }



    /**
     * Change user password
     *
     * @param Request $request
     * @param User $userModel
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function changePassword(Request $request, User $userModel)
    {
        try {
            /** instantiate UserServices class */
            $userService = new UserServices();

            $validator = $userService->validateChangePassword($request);

            if (!$validator->passes()) {
                return $this->returnBadRequest($validator->messages());
            }

            $request->merge(['password' => Hash::make($request->password)]);

            if (!$user = $userModel->changePassword($request->only('code', 'password'))) {
                return $this->returnError('Invalid code');
            }

            return $this->returnSuccess('Password reset successfully! Log in to check it out:)');
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    //display all users -testing purpose
    public function getAllUsers()
    {
        $users = User::all();
        if($users){
            return $this->returnSuccess($users);
        }
        $this->returnError('There are no users');
    }


    //update info  for normal users
    public function updateInfo(Request $request)
    {

        try{

            $user = $this->validateSession();

            $userService = new UserServices();

            $validator = $userService->validateUpdateUserInfo($request,$user->id);


            if (!$validator->passes()) {
                return $this->returnBadRequest($validator->messages());
            }

            $user->password = Hash::make($request->new_password);

            if (!$user->update($request->only('name','email','password'))) {

                return $this->returnError();
            }

            return $this->returnSuccess($user);

        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }

    }


    //admin function for updating any user
    public function updateUser(Request $request, $id)
    {

        try{

            $user = User::find($id);
            /** instantiate UserService class */
            $userServices = new UserServices();

            $validator = $userServices->validateUpdateUserById($request,$user->id);

            if (!$validator->passes()) {
                return $this->returnBadRequest($validator->messages());
            }

            $user->password = Hash::make($request->password);
            if (!$user->update($request->all())) {

                return $this->returnError();
            }

            return $this->returnSuccess($user);

        }catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }

    }

}