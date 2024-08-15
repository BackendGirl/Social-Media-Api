<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Helpers\NotificationHelper;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function chat_get_admin($user_id)
    {
        $chats = Chat::where('user_id', $user_id)->get();
        Chat::where('user_id', $user_id)->orderBy('id', 'asc')->where('admin_read_flag', 0)->update(['admin_read_flag' => 1]);
        return response()->json([
            'chats' => $chats,
            'status' => 200,
            'success' => true
        ]);
    }

    public function chat_admin(Request $request, $user_id)
    {
        $validator = Validator::make($request->all(), [
            'chat' => 'required',
            'chat_type' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400,
                'success' => false
            ]);
        } else {
            $user = User::findOrFail($user_id);
            if ($request->chat_type == 'text') {
                $chat = Chat::create([
                    'chat' => $request->chat,
                    'chat_type' => $request->chat_type,
                    'user_id' => $user_id,
                    'flag' => 1 //admin
                ]);
            } else {
                try {
                    $file = $request->chat;
                    // return $file;
                    $filename = time() . '.' . $file->extension();
                    $filepath = public_path() . '/chats';

                    $file->move($filepath, $filename);

                    $post_add = '/chats/' . $filename;
                } catch (\Exception $e) {
                    return $e;
                }
                $chat = Chat::create([
                    'chat' => $post_add,
                    'chat_type' => $request->chat_type,
                    'user_id' => $user_id,
                    'flag' => 1 //admin
                ]);

            }
            $notify = Notification::create([
                'note' => 'guruji replied to your message',
                'note_type' => 'chat',
                'user_id' => $user_id,
                'note_img' => Auth::user()->profile
            ]);

                $token = $user->device_id;
                
                $notification = [
                    'title' => 'AstroPandit Haridwar',
                    'body' => 'Guruji replied to your message',
                ];
                // fcmnotify($token, $notification);
                $notificationHelper = new NotificationHelper();
                $notificationHelper->fcmnotify($token, $notification);
            
            if ($chat && $notify) {

                return response()->json([
                    'message' => 'chat created successfully',
                    'status' => 201,
                    'success' => true
                ], 201);
            } else {
                return response()->json([
                    'message' => 'something went wrong',
                    'status' => 400,
                    'success' => false
                ], 400);
            }
        }
    }

    public function chat_get_user()
    {
        $chats = Chat::where('user_id', Auth::user()->id)->get();
        Chat::where('user_id', Auth::user()->id)->orderBy('id', 'asc')->where('user_read_flag', 0)->update(['user_read_flag' => 1]);
        return response()->json([
            'chats' => $chats,
            'status' => 200,
            'success' => true
        ]);
    }

    public function chat_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat' => 'required',
            'chat_type' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400,
                'success' => false
            ]);
        } else {
            if ($request->chat_type == 'text') {
                $chat = Chat::create([
                    'chat' => $request->chat,
                    'chat_type' => $request->chat_type,
                    'user_id' => Auth::user()->id,
                    'flag' => 0 //user
                ]);
            } else {
                try {
                    $file = $request->chat;
                    // return $file;
                    $filename = time() . '.' . $file->extension();
                    $filepath = public_path() . '/chats';

                    $file->move($filepath, $filename);

                    $post_add = '/chats/' . $filename;
                } catch (\Exception $e) {
                    return $e;
                }
                $chat = Chat::create([
                    'chat' => $post_add,
                    'chat_type' => $request->chat_type,
                    'user_id' => Auth::user()->id,
                    'flag' => 0 //user
                ]);

            }
            if ($chat) {
                return response()->json([
                    'message' => 'chat created successfully',
                    'status' => 201,
                    'success' => true
                ], 201);
            } else {
                return response()->json([
                    'message' => 'something went wrong',
                    'status' => 400,
                    'success' => false
                ], 400);
            }
        }
    }

    public function all_chat_get_admin()
    {
        $users = User::where('is_admin', 0)
            // ->with('chat')
            ->join(DB::raw('(SELECT user_id, MAX(created_at) AS latest_chat_date FROM chats GROUP BY user_id) as latest_chats'), function ($join) {
                $join->on('users.id', '=', 'latest_chats.user_id');
            })
            ->join('chats', function ($join) {
                $join->on('users.id', '=', 'chats.user_id')
                    ->on('chats.created_at', '=', 'latest_chats.latest_chat_date');
            })
            ->orderBy('latest_chats.latest_chat_date', 'desc')
            ->select('users.id', 'users.name', 'chats.chat_type', 'chats.chat', 'chats.created_at', 'chats.flag', 'chats.admin_read_flag')
            ->get();
        return response()->json([
            'users' => $users,
            'status' => 200,
            'success' => true
        ]);
    }



    public function chat_delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required',
            // 'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400,
                'success' => false
            ],400);
        } else {
            $user_id = Auth::user()->id;
            $chat = Chat::where('id', $request->chat_id)
                ->where('user_id', $user_id)
                ->delete();

            if ($chat) {
                return response()->json([
                    'message' => 'Chat deleted successfully',
                    'status' => 200,
                    'success' => true
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Chat not found or unauthorized',
                    'status' => 400,
                    'success' => false
                ], 400);
            }
        }
    }

}
