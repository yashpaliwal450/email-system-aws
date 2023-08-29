<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\email;
use App\Models\User;
use App\Models\Userphoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;

use Exception;
use Laravel\Passport\Token;


class UserController extends Controller
{

    // login route -> login controller method -> return login page view -> html
    // login page view -> login.js -> ajax call -> json response (success/fail) -> on success ->redirect dashboard
    // dashboard route -> return dashboard view
    // dashboard view -> dashboard.js -> ajax call -> json response


    public function addUsers(StoreUserRequest $request){
        try{
            $user = new User;
            $user->first_name= $request->firstname;
            $user->last_name= $request->lastname;
            $user->email= $request->email;
            $user->age= $request->age;
            $user->password= $request->password;
            $user->country= $request->country;
            $user->save();
            return response()->json([
                'success' => true,
            ]);
        }
        catch(\Exception $e){
            return response()->json([
                'error' => true,
                'message' => $e
            ]);
        }
    }
    public function validateLogin(Request $request){
        $input = $request->all();
        if (Auth::attempt(['email' => $input['email'],'password' => $input['password']])) {
            $user  = Auth::user();
            
            return response()->json([
                'success' => true,
                'access_token' => 'Bearer '.$user->createToken("login")->accessToken,
                'token_type' => 'Bearer',
                'user' => $user
            ]);
        }else{
            return response()->json([
                ['error' => 'Invalid credentials'], 401
            ]);
        }
    }
    public function updateUserForm(Request $request){
        try{
            $user=Auth::guard('api')->user();
            $photo = Userphoto::select('file_path','file_name')
            ->where('user_id',$user->id)->first();
            return response()->json([
                'success' => true,
                'user' => $user,
                'photo' => $photo
            ]);
        }catch(\Exception $e){
            return response()->json([
                'error' => true,
                'message' => $e
            ]);
        }
    }
    public function updateUser(Request $request){
        
        $user=Auth::guard('api')->user();
        DB::beginTransaction();
        try{
            DB::table('users')->where('id',$user->id)->update([
                'first_name'=>$request->firstname,
                'last_name'=>$request->lastname,
                'age'=>$request->age
            ]);
            $file=$request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            \Storage::disk('s3')->put($fileName, file_get_contents($file));
            DB::table('user_photos')
            ->insert(['file_name'=>$fileName,'file_path'=>$fileName,'user_id'=>$user->id]);
            DB::commit();
            return response()->json([
                'success' => true,
            ]);
        }
        catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => $e
            ]);
        }
    }

    public function logout(Request $request){
        $user=Auth::guard('api')->user();
        $user->tokens()->delete();
        return response()->json([
            'success' => true,
        ]);
    }


    public function sessionCheck(){
            return view('home');
    }
}
