<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Post;
use App\Helpers\NotificationHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\Video\X264;
use Pawlox\VideoThumbnail\Facade\VideoThumbnail;
class AdminController extends Controller
{
    public function getusers(){
        $users = User::where('is_admin',0)->get();
        return response()->json([
            'users' => $users,
            'status' =>200,
            'success' =>true
        ]);
    }

    public function addPost(Request $request){
        $validator = Validator::make($request->all(),[
            'file' => 'required',
            'file_type'=>'required|in:image,video',
            'title' => 'required',
            'description' => 'required'
        ]);
        $validator->sometimes('thumbnail', 'required', function ($input) {
            return $input->file_type == 'video';
        });
        if($validator->fails()){
            return response()->json([
                'error'=> $validator->errors(),
                'status' => 400 ,
                'success' => false
            ]);
        }else{

            try{
                $file = $request->file('file');
                // return $file;
                $filewithoutext = time();
                $filename = $filewithoutext.'.'.$file->extension();
                $filepath = public_path(). '/posts';

                $file->move($filepath, $filename);
                
                $post_add = '/posts/'.$filename;

               if($request->file_type == 'video'){
                    // Generate thumbnail
                    // return $request->thumbnail;
                    $thumbnail = $request->thumbnail;
                    $thumbnailname = time().'.'.$thumbnail->extension();
                    $thumbnailpath = public_path(). '/thumbnails';
    
                    $thumbnail->move($thumbnailpath, $thumbnailname);
                    
                    $thumbnail_add = '/thumbnails/'.$thumbnailname;

    
                    $notify = Notification::create([
                        'note' => 'guruji added a new post of '.$request->title,
                        'note_type' => 'post',
                        'note_img' => '/thumbnails/'.$thumbnailname,
                    ]);
               }else{
                $thumbnail_add = '';
                $notify = Notification::create([
                    'note' => 'guruji added a new post of '.$request->title,
                    'note_type' => 'post',
                    'note_img' => $post_add,
                ]);
               }
               }catch(\Exception $e){
                return $e;
               } 

            $post = Post::create([
                'file'=> $post_add,
                'file_type' => $request->file_type,
                'title' => $request->title,
                'description' =>$request->description,
                'thumbnail' =>$thumbnail_add
            ]);

            if($notify){
                $users = User::where('status',1)->get('device_id');
                foreach ($users as $user) {
                    $token = $user->device_id;
                    $notification = [
                        'title' => 'AstroPandit Haridwar ',
                        'body' => 'guruji added a new post of '.$request->title,
                    ];
                    // fcmnotify($token,$notification);
                    $notificationHelper = new NotificationHelper();
                    $notificationHelper->fcmnotify($token, $notification);
                }
            }

            if($post && $notify){
                return response()->json([
                    'message'=>'post created successfully',
                    'status' => 201 ,
                    'success' => true
                ],201);
            }else{
                return response()->json([
                    'message'=> 'something went wrong',
                    'status' => 400 ,
                    'success' => false
                ],400);
            }
        }
    }

    public function addNotification(Request $request){
        $validator = Validator::make($request->all(),[
            'note' => 'required',
        ]);
        if($validator->fails()){
            return response()->json([
                'error'=> $validator->errors(),
                'status' => 400 ,
                'success' => false
            ]);
        }else{
            $notify = Notification::create([
                'note' => $request->note,
                'note_type' => 'notification',
                'note_img'=>Auth::user()->profile
            ]);
            if($notify){
                return response()->json([
                    'message'=>'notification created successfully',
                    'status' => 201 ,
                    'success' => true
                ],201);
            }else{
                return response()->json([
                    'message'=> 'something went wrong',
                    'status' => 400 ,
                    'success' => false
                ],400);
            }
        }
    }

    public function block_user(Request $request){
        $validator = Validator::make($request->all(),[
            'user_id' => 'required|integer',
        ]);
        if($validator->fails()){
            return response()->json([
                'error'=> $validator->errors(),
                'status' => 400 ,
                'success' => false
            ]);
        }else{
            $user = User::where('id',$request->user_id)->where('status',1)->first();
            
            if($user){
                // return $user;
                $user->status = 0;
                $user->save();
                
                if($user->status == 0){
                    return response()->json([
                        'message'=>'blocked successfully',
                        'status' => 200 ,
                        'success' => true
                    ],201);
                }else{
                    return response()->json([
                        'message'=> 'something went wrong',
                        'status' => 400 ,
                        'success' => false
                    ],400);
                }
                
            }else{
                return response()->json([
                    'message'=> 'User Not found or blocked',
                    'status' => 400 ,
                    'success' => false
                ],400);
            }
        }
    }

    public function unblock_user(Request $request){
        $validator = Validator::make( $request->all(),[
            'user_id' => 'required|integer',
        ]); 
        if($validator->fails()){
            return response()->json([
                'error'=> $validator->errors(),
                'status' => 400 ,
                'success' => false
            ]);
        }else{
            $user = User::where('id',$request->user_id)->where('status',0)->first();
            if($user){
                $user->status = 1;
                $user->save();
                if($user->status == 1){
                    return response()->json([
                        'message'=>'unblocked successfully',
                        'status' => 200 ,
                        'success' => true
                    ],200);
                }else{
                    return response()->json([
                        'message'=> 'something went wrong',
                        'status' => 400 ,
                        'success' => false
                    ],400);
                }
            }else{
                return response()->json([
                    'message'=> 'User Not found or already Unblocked',
                    'status' => 400 ,
                    'success' => false
                ],400);
            }
        }
    }
    

    public function post_delete(Request $request){
        $validator = Validator::make($request->all(),[
            'post_id' => 'required',
        ]);
        if($validator->fails()){
            return response()->json([
                'error'=> $validator->errors(),
                'status' => 400 ,
                'success' => false
            ]);
        }else{
                $post = Post::find($request->post_id);
                if($post){
                    $delete = $post->delete();
                    if($delete){
                        return response()->json([
                            'message' => 'Post deleted successfully',
                            'status' =>200,
                            'success' =>true
                        ],200);
                    }else{
                        return response()->json([
                            'message' => 'something went wrong',
                            'status' =>400,
                            'success' =>false
                        ],400);
                    }
                }else{
                    return response()->json([
                        'message' => 'post not found',
                        'status' =>400,
                        'success' =>false
                    ],400);
                }
            
        }
    }

}
