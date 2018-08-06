<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\User;
use GenTux\Jwt\JwtToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            'email.required' => 'Email empty',
            'email.email'    => 'Email invalid',
            'password.required'    => 'Password empty'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ( ! $validator->passes()) {
            return $this->returnBadRequest();
        }

        $user = $userModel->login($request->email, $request->password);

        if ( ! $user) {
            return $this->returnNotFound('User sau parola gresite');
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
        try {

            $userService = new UserService();
            $validator = $userService->validateRegister($request);
            if (!$validator->passes()) {
                return $this->returnBadRequest($validator->messages());
            }
            $request->merge(['password' => Hash::make($request->get('password'))]);
            $userService->registerUser($request);
            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }


    // Return user
   
    public function get()
    {
        try {
            $user = $this->validateSession();
            if (!$user) {
                return $this->returnError('error.token');
            }
            return $this->returnSuccess($user);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function save()
    {
        return $this->returnSuccess();
    }

    public function logout(Request $request)
    {
        try {
            $user = $this->validateSession();
            if (!$user) {
                return $this->returnError('error.token');
            }
            if ($request->has('rememberToken')) {
                UserToken::where('token', $request->get('rememberToken'))->where('user_id', $user->id)->delete();
            }
            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function forgotPassword(Request $request, User $userModel)
    {
        if ($request->has('code')) {
            return $this->changePassword($request, $userModel);
        }
        try {
            $userService = new UserService();
            $validator = $userService->validateForgotPassword($request);
            if (!$validator->passes()) {
                return $this->returnBadRequest($validator->messages());
            }
            $user = $userModel::where('email', $request->get('email'))->get()->first();
            if ($user->status === User::STATUS_UNCONFIRMED) {
                return $this->returnError('error.account_not_activated');
            }
            if ($user->updatedAt > Carbon::now()->subMinute()->format('Y-m-d H:i:s')) {
                return $this->returnError('error.resend_cooldown');
            }
            $userService->sendForgotCode($user);
            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    private function changePassword(Request $request, User $userModel)
    {
        try {
            $userService = new UserService();
            $validator = $userService->validateChangePassword($request);
            if (!$validator->passes()) {
                return $this->returnBadRequest($validator->messages());
            }
            $request->merge(['password' => Hash::make($request->password)]);
            if (!$user = $userModel->changePassword($request->only('code', 'password'))) {
                return $this->returnError('error.code_invalid');
            }
            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function activate(Request $request, User $userModel)
    {
        try {
            /** @var UserService $userService */
            $userService = new UserService();
            $validator = $userService->validateActivate($request);
            if (!$validator->passes()) {
                return $this->returnBadRequest($validator->messages());
            }
            $user = $userModel::where('email', $request->get('email'))
                ->where('activation_code', $request->get('code'))
                ->get()->first();
            if (!$user) {
                return $this->returnError('error.code_invalid');
            }
            $user->status = User::STATUS_CONFIRMED;
            $user->activationCode = '';
            $user->save();
            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }


}