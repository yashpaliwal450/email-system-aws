<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmailRequest;
use App\Models\Attachment;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Email;
use App\Models\EmailBcc;
use App\Models\EmailCc;
use App\Models\UserPhoto;
use Illuminate\Support\Facades\DB;
use Auth;


class MailController extends Controller
{
    public function send_mail(StoreEmailRequest $request){// saving email
        DB::beginTransaction();
        try {
            $user = Auth::guard('api')->user();
            $email = new Email;
            $email->subject = $request->subject;
            $email->body = $request->body;
            $email->sender_id = $user->id;
            $reciver_id = User::select('id')->where('email',$request->reciver_mail)->first();
            if(is_null($reciver_id)){
                return response()->json([
                    'error' => true
                ]);
            }
            $email->reciver_id = $reciver_id->id; 
            $email->save();
            $cc = $request->cc;
            if(isset($cc)){
                $this->saveCcBcc("ccs", $cc,$email);
            }
            $bcc = $request->bcc;
            if(isset($bcc)){
                $this->saveCcBcc("bccs", $bcc,$email);
            }
            $attachments=$request->attachments;
            if(isset($attachments)){
                $this->saveAttachments($attachments,$email);
            }
            DB::commit();
            return 1;
        }
        catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'error' => true,
                'mess' =>$e
            ]);
        }

    }
    public function saveAttachments($attachments ,$email){
            foreach($attachments as $file){
                $fileName = time() . '_' . $file->getClientOriginalName();
                \Storage::disk('s3')->put($fileName, file_get_contents($file));
                $attachment = new Attachment;
                $attachment->file_name=$fileName;
                $attachment->file_path=$fileName;
                $attachment->email_id = $email->id;
                $attachment->save();
            }
        
    }
    public function saveCcBcc($type, $emails,$email){
        $processedData = str_replace(",", " ", $emails);
        $list = explode(" ", $processedData);
        $tableName="email_".$type;
        foreach($list as $data){
            if($data!=null || $data!=" "){
                $email_id=DB::table('users')->select('id')->where('email',$data)->first()->id;
                if(is_null($email_id)){
                    return response()->json([
                        'error' => true,
                        'message' =>$type." email not found"
                    ]);
                }else{
                DB::table($tableName)->insert(['user_id'=>$email_id,'email_id'=>$email->id]);
            }
            }
        }
    }
    public function sent_mail(){
        try{
            $user = Auth::guard('api')->user();
            $emails =  Email::with(['receiver' => function ($query) {
                $query->select('id', 'email'); }])
            ->where('sender_id', $user->id)
            ->orderByDesc('emails.email_id')
            ->cursorPaginate();
            $photo = UserPhoto::select('file_path','file_name')
            ->where('user_id',$user->id)->first();
            $fileContents=null;
            if($photo!=null){
                $fileContents = \Storage::disk('s3')->url($photo->file_path);
            }
            return response()->json([
                'success' => true,
                "mails"=>$emails,
                "user"=>$user,
                'image' => $fileContents
            ]);   
        }catch(\Exception $e){
            return response()->json([
                'error' => true,
                'message' => $e
            ]);
        }
    }

    public function dashboard(){
        try{
            $user = Auth::guard('api')->user();
            $emails = Email::with(['sender' => function ($query) {
                $query->select('id', 'email'); }])
                    ->where('reciver_id',$user->id)
                    ->orderByDesc('emails.email_id')->cursorPaginate();
                $photo = UserPhoto::select('file_path','file_name')
                    ->where('user_id',$user->id)->first();
                $fileContents=null;
                if($photo!=null){
                    $fileContents = \Storage::disk('s3')->url($photo->file_path);
                }
            return response()->json([
                'success' => true,
                'user' => $user,
                'emails' => $emails,
                'image' => $fileContents
            ]);
        }catch(\Exception $e){
            return response()->json([
                'error' => true,
                'message' => $e
            ]);
        }
    }

    public function mailDetails($id){
        try{
            $user = Auth::guard('api')->user();
            $email = Email::with(['sender' => function ($query) {
                $query->select('id', 'email'); },
                'cc.user'=> function($query){
                    $query->select('id', 'email');
                },
                'bcc.user'=> function($query){
                    $query->select('id', 'email');
                },
                'attachments'
                ])
                ->where('emails.email_id',$id)->first();
            $links = array();
            foreach($email->attachments as $attachemt){
                $file=\Storage::disk('s3')->url( $attachemt->file_path);
                array_push($links,  $file);
            }
            $photo = UserPhoto::select('file_path','file_name')
            ->where('user_id',$user->id)->first();
            $fileContents=null;
            if($photo!=null){
                $fileContents = \Storage::disk('s3')->url($photo->file_path);
            }
            return response()->json([
                'success' => true,
                'user' => $user,
                'id'=>$id,
                'email' => $email,
                'image' => $fileContents,
                'links'=>$links
            ]);
        }catch(\Exception $e){
            return response()->json([
                'error' => true,
                'message' => $e
            ]);
        }
    }
    
    public function sentMailDetails($id){
        try{
            $user =  Auth::guard('api')->user();
            $email = Email::with(['receiver' => function ($query) {
                $query->select('id', 'email'); },
                'cc.user'=> function($query){
                    $query->select('id', 'email');
                },
                'bcc.user'=> function($query){
                    $query->select('id', 'email');
                },
                'attachments'
                ])
                ->where('emails.email_id',$id)->first();
            $links = array();
            foreach($email->attachments as $attachemt){
                $file=\Storage::disk('s3')->url( $attachemt->file_path);
                array_push($links,  $file);
            }
            $photo = UserPhoto::select('file_path','file_name')
            ->where('user_id',$user->id)->first();
            $fileContents=null;
            if($photo!=null){
                $fileContents = \Storage::disk('s3')->url($photo->file_path);
            }
            return response()->json([
                'success' => true,
                'user' => $user,
                'id'=>$id,
                'email' => $email,
                'image' => $fileContents,
                'links'=>$links
            ]);
        }
        catch(\Exception $e){
            return response()->json([
                'error' => true,
                'message' => $e
            ]);
        }
        
    }
}
