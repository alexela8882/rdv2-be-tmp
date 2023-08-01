<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;

use Socialite;

class AuthController extends BaseController
{

  // ---------------------
  // SOCIALITE AUTH
  // ---------------------
  protected function _registerOrLoginUser($data){
  $user = User::where('email',$data->email)->first();
    if(!$user){
        $user = new User();
        $user->name = $data->name;
        $user->email = $data->email;
        $user->password = bcrypt('password123');
        $user->save();
    }

    Auth::login($user);
  }

  // providers
  public function azureRedirect () {
    return Socialite::driver('azure')->stateless()->redirect();
  }

  public function azureCallback () {
    $userSocial = Socialite::driver('azure')->stateless()->user();
    $this->_registerorLoginUser($userSocial);

    return redirect()->away("http://localhost:3000");
    // return redirect()->route('home');

    // if(Auth::attempt($credentials)){
    //   $user = Auth::user();
    //   $success['token'] =  $user->createToken('MyApp')->plainTextToken;
    //   $success['name'] =  $user->name;

    //   // return $this->sendResponse($success, 'User login successfully.');
    //   // return redirect()->to('http://localhost:3000');
    //   return redirect()->route('home');
    // }
    // else{ 
    //   // return $this->sendError('Failed.', ['error'=>'Login failed']);
    //   return response()->json("Login failed", 200);
    // }

    // return redirect()->route('plogin', ['email' => $user->email]);
  }




  // ---------------------
  // SANCTUM AUTH
  // ---------------------

  /**
   * Register api
   *
   * @return \Illuminate\Http\Response
   */
  public function register(Request $request) {
    $validator = Validator::make($request->all(), [
      'name' => 'required',
      'email' => 'required|email',
      'password' => 'required',
      'c_password' => 'required|same:password',
    ]);

    if($validator->fails()){
      return $this->sendError('Validation Error.', $validator->errors());       
    }

    $input = $request->all();
    $input['password'] = bcrypt($input['password']);
    $user = User::create($input);
    $success['token'] =  $user->createToken('MyApp')->plainTextToken;
    $success['name'] =  $user->name;

    return $this->sendResponse($success, 'User register successfully.');
  }

  /**
   * Login api
   *
   * @return \Illuminate\Http\Response
   */
  public function login(Request $request) {
    if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
      $user = Auth::user();
      $success['token'] =  $user->createToken('MyApp')->plainTextToken;
      $success['name'] =  $user->name;

      // return $this->sendResponse($success, 'User login successfully.');
      return response()->json("User login successfully", 200);
    }
    else{ 
      // return $this->sendError('Failed.', ['error'=>'Login failed']);
      return response()->json("Login failed", 200);
    }
  }

  public function logout () {
    $user = auth('sanctum')->user();
    $user->tokens()->delete();

    Auth::guard('web')->logout();

    $response = [
      'message' => 'successfully logged out',
      'user' => $user
    ];

    return response()->json($response, 200);
  }
}
