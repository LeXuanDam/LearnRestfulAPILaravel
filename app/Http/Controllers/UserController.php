<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Helper\JONWebToken as JWT;
use Validator;

class UserController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/login",
     *   tags={"User"},
     *   summary="Đăng nhập",
     *   operationId="login",
     *   @OA\Parameter(
     *     name="user_name",
     *     in="query",
     *     description="Số điện thoại",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="password",
     *     in="query",
     *     description="Mật khẩu",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Gửi yêu cầu thành công",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *     )
     *   )
     * )
     */
    public function register(Request $request)
    {
        $params = $request->all();
        $this->validateRegister($request);
        $user = User::where('user_name', $params['user_name'])->exits();
        if ($user) {
            return $this->response(404, 'user da ton tai');
        }
        if ($params['password'] != $params['confirm_password']) {
             return $this->response(401, 'confirm password sai');
        }
        User::insert([
            'user_name' => $params['user_name'],
            'password' => bcrypt($params['password'])
        ]);
         return $this->response(200, 'dang ky thanh cong');

    }

    public function login(Request $request)
    {
        $params = $request->all();
        $this->validateLogin($request);
        $user = User::where('user_name', $params['user_name'])->first();
        if (!$user) {
             return $this->response(404, 'user khong ton tai');
        }
        if (!password_verify($params['password'], $user->password)) {
             return $this->response(401, 'password khong dung');
        }
         return $this->response(200, 'dang nhap thanh cong', [
            'access_token' => JWT::encode($user)
        ]);
    }

    private function validateRegister($request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required',
            'password' => 'required',
            'confirm_password' => 'required'
        ], [
            'user_name.required' => 'The user name field is required.',
            'password.required' => 'The password field is required.',
            'confirm_password.required' => 'The confirm password field is required.',
        ]);
        if ($validator->fails()) {
             return $this->response(400, $validator->errors()->toArray());
        }
    }

    private function validateLogin($request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required',
            'password' => 'required',
        ], [
            'user_name.required' => 'The user name field is required.',
            'password.required' => 'The password field is required.',
        ]);
        if ($validator->fails()) {
             return $this->response(400, $validator->errors()->toArray());
        }
    }

    public function response($code, $message = null, $data = null)
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
    }
}
