<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'user', 'refresh', 'authenthicate']]);
    }

    /**
     * Sign In function with return jwt token.
     * It checks if the user is valid or not.
     *
     * @param Request request The request object.
     * Request username: string, password: string
     */
    public function login(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'user_name' => 'required',
                'password' => 'required',
            ]
        );
        $credential = [
            'employee_id' => $request->user_name,
            'password' =>  Hash::make($request->password)
        ];

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $queryImage = DB::raw("
                CASE
                    WHEN pic is null THEN 'https://merudy.s3-ap-southeast-1.amazonaws.com/conex/vendor/avatar-1.jpg'
                    WHEN pic = '' THEN 'https://merudy.s3-ap-southeast-1.amazonaws.com/conex/vendor/avatar-1.jpg'
                    ELSE CONCAT('https://files.merudy.com/users/',pic)
                END AS pic
            ");
        $checkUser = User::Where('employee_id', $request->user_name)
            ->where('password', md5($request->password))
            ->select(
                'id',
                'shop_id',
                'manager_id',
                'employee_id',
                'user_type',
                'name',
                'nickname',
                'email',
                $queryImage
            )
            ->first();

        if (!$checkUser) {
            return response()->json(['status' => 'FAILED', 'msg' => "User name not found"], 401);
        } else {
            $token_validity = (72 * 60);
            $this->guard()->factory()->setTTL($token_validity);
            $token = $this->guard()->login($checkUser);
            if (!$token) {
                return response()->json(['error' => 'User name or password is invalid'], 401);
            }

            return $this->respondWithToken($token);
        }
    }

    /**
     * It checks if the email address is already in use, if it is, it returns a message saying so, if it
     * isn't, it creates a new user and returns a token
     *
     * @param Request request The request object.
     *
     * @return The token is being returned.
     */
    public function register(Request $request)
    {
        $users = User::where('email', '=', $request->email)->first();
        if ($users === null) {
            $request['password'] = Hash::make($request['password']);
            $request['remember_token'] = Str::random(10);
            $user = User::create($request->toArray());
            $token = auth()->login($user);

            return $this->respondWithToken($token);
        } else {
            return response()->json(['msg' => 'This email already exists'], 200);
        }
    }

    /**
     * It returns a JSON response of the authenticated user
     *
     * @return The user object
     */
    public function user()
    {
        return response()->json(auth()->user(), 200);
    }


    /**
     * It logs the user out
     *
     * @return A JSON response with a message of "Successfully logged out" and a status code of 200.
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out'], 200);
    }


    /**
     * It returns a new token for the user
     *
     * @return The token is being returned.
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * It returns a JSON response with the access token, token type, expiration time, and the user object
     *
     * @param token The JWT
     *
     * @return The token, the type of token, the time to live, and the user.
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'expires_in_day' => ((auth()->factory()->getTTL() * 60) / 60) / 60 / 24,
            'user' => auth()->user()
        ]);
    }

    /**
     * > This function returns the guard that is currently being used by the application
     *
     * @return The guard method returns the guard instance being used by the application.
     */
    protected function guard()
    {
        return Auth::guard();
    }



    ################################################################## Start - Login with Email and Username ################################################################
    /**
     * > This custom function is use login with email or username.
     * Expire the session token : 3 day.
     * @param string $username.
     * @param string $email.
     *
     * @return array : Array of objects with informaiton and token.
     */
    public function authenthicate(Request $request)
    {
        $queryRaw = DB::raw("
                CASE
                    WHEN pic is null THEN 'https://merudy.s3-ap-southeast-1.amazonaws.com/conex/vendor/avatar-1.jpg'
                    WHEN pic = '' THEN 'https://merudy.s3-ap-southeast-1.amazonaws.com/conex/vendor/avatar-1.jpg'
                    ELSE CONCAT('https://files.merudy.com/users/',pic)
                END AS pic
            ");
        if (!empty($request->username)) {
            $checkUser = User::Where('employee_id', $request->username)
                ->select(
                    'id',
                    'shop_id',
                    'manager_id',
                    'employee_id',
                    'user_type',
                    'name',
                    'nickname',
                    'email',
                    $queryRaw
                )
                ->first();
            if (empty($checkUser)) {
                $emailExist = User::Where('email', $request->email)
                    ->select(
                        'id',
                        'shop_id',
                        'manager_id',
                        'employee_id',
                        'user_type',
                        'name',
                        'nickname',
                        'email',
                        $queryRaw
                    )
                    ->first();


                $token_validity = (72 * 60);
                $this->guard()->factory()->setTTL($token_validity);
                $token = $this->guard()->login($emailExist);
                if (!$token) {
                    return response()->json(['error' => 'User name or password is invalid'], 401);
                }

                return $this->respondWithToken($token);
            }
        } else {
            $checkUser = User::Where('email', $request->email)
                ->select(
                    'id',
                    'shop_id',
                    'manager_id',
                    'employee_id',
                    'user_type',
                    'name',
                    'nickname',
                    'email',
                    $queryRaw
                )
                ->first();
        }

        if (!$checkUser) {
            return response()->json([
                'status'  => 'FAILED',
                'msg'     => 'This user name not found.'
            ], 400);
        } else {
            $token_validity = (72 * 60);
            $this->guard()->factory()->setTTL($token_validity);
            $token = $this->guard()->login($checkUser);
            if (!$token) {
                return response()->json(['error' => 'User name or password is invalid'], 401);
            }

            return $this->respondWithToken($token);
        }
    }



    ################################################################## End - Login with Email and Username ##################################################################

}
