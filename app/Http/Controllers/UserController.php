<?php

namespace App\Http\Controllers;

use App\Models\role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Log;

class UserController extends BaseController
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['role_id'] = role::where('name', 'user')->first()->id;
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->plainTextToken;
        $success['name'] =  $user->name;

        return $this->sendResponse($success, 'User register successfully.');
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $input = $request->all();

        $testReset = User::where('email', $input['email'])->first();
        if ($testReset && $testReset->reset_password) {
            $testReset->password = $input['password'];
            $testReset->reset_password = false;
            $testReset->save();
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            if ($user->verified_by) {
                $success['token'] =  $user->createToken('MyApp')->plainTextToken;
                $success['name'] =  $user->name;
            } else {
                return $this->sendError('User not verified yet.', ['error' => 'Unauthorised']);
            }

            return $this->sendResponse($success, 'User login successfully.');
        } else {
            return $this->sendError('Unauthorised.', ['error' => 'Unauthorised']);
        }
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return $this->sendResponse([], "Berhasil Logout");
    }

    public function Users()
    {
        if (Auth::user()->role->name != 'admin') {
            return $this->sendError('Unauthorized Error.', []);
        }
        return $this->sendResponse(User::all(), "all user");
    }

    public function resetPassword(Request $request): JsonResponse
    {
        if (Auth::user()->role->name != 'admin') {
            return $this->sendError('Unauthorized Error.', []);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $user = User::where('email', $input['email'])->first();

        if ($user) {
            if ($user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }
            $user->reset_password = true;
            $user->save();
            return $this->sendResponse($user, 'Password reset successfully. Relogin suppliying new password to get new token');
        } else {
            return $this->sendError('User not found.', []);
        }
    }

    public function changePassword(Request $request): JsonResponse
    {
        if (Auth::user()->role->name != 'user') {
            return $this->sendError('Endpoint only for users.', []);
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'new_password' => 'required',
            'c_password' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::find(Auth::user()->id);
        $input = $request->all();

        $credentials = [
            'email' => $user->email,
            'password' => $input['password'],
        ];

        if (!Auth::guard('web')->attempt($credentials)) {
            return $this->sendError('Unauthorised.', ['error' => 'Unauthorised']);
        }

        $user->password = bcrypt($input['new_password']);
        $user->save();

        Auth::user()->currentAccessToken()->delete();

        return $this->sendResponse($user, 'Password changed successfully. Relogin to get new token');
    }

    public function addVerifikator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }


        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['verified_by'] = Auth::user()->id;
        $input['role_id'] = role::where('name', 'verifikator')->first()->id;
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->plainTextToken;
        $success['name'] =  $user->name;

        return $this->sendResponse($success, 'User register successfully.');
    }

    public function changeRole(Request $request)
    {
        if (Auth::user()->role->name == 'admin') {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'role' => 'required'
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }
            $input = $request->all();
            $user = User::where('email', $input['email'])->first();
            $user->role_id = $input['role'];
            $user->save();


            return $this->sendResponse($user, 'User role has been change successfully.');
        } else {
            return $this->sendError('You are not admin', []);
        }
    }

    public function verifyUser(Request $request)
    {
        if (Auth::user()->role->name != 'verifikator') {
            return $this->sendError('Unauthorized Error.', []);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $input = $request->all();
        $user = User::where('email', $input['email'])->first();
        $user->verified_by = Auth::user()->id;
        $user->save();
        return $this->sendResponse($user, 'User has been verified.');
    }
}
