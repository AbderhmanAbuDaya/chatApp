<?php

namespace App\Http\Controllers;

use App\Events\GroupMessageEvent;
use App\Events\PrivateMessageEvent;
use App\Message;
use App\MessageGroup;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }
public $data=[];
    public function conversation($userId){
        $users=User::where('id','!=',Auth::id())->get();
        $friendInfo=User::findOrFail($userId);
        $myInfo=User::find(Auth::id());
        $this->data['users']=$users;
        $this->data['friendInfo']=$friendInfo;
        $this->data['myInfo']=$myInfo;
        $this->data['userId']=$userId;
         $this->data['groups']=MessageGroup::with(['message_group_members.user'])->get();
        return view('message.conversation',$this->data);

    }


    public function sendMessage(Request $request) {

        $request->validate([
            'message' => 'required',
            'receiver_id' => 'required'
        ]);

        $sender_id = Auth::id();
        $receiver_id = $request->receiver_id;

        $message = new Message();
        $message->message = $request->message;

        if ($message->save()) {
            try {
                $message->users()->attach($sender_id, ['receiver_id' => $receiver_id]);
                $sender = User::where('id', '=', $sender_id)->first();

                $data = [];
                $data['sender_id'] = $sender_id;
                $data['sender_name'] = $sender->name;
                $data['receiver_id'] = $receiver_id;
                $data['content'] = $message->message;
                $data['created_at'] = $message->created_at;
                $data['message_id'] = $message->id;

          broadcast(new PrivateMessageEvent($data));

                return response()->json([
                    'data' => $data,
                    'success' => true,
                    'message' => 'Message sent successfully',

                ]);
            } catch (\Exception $e) {
                $message->delete();
                return response()->json([

                    'success' => false,
                    'message' => 'Message sent not successfully'
                ]);
            }
        }
    }
    public function sendMessageGroups(Request $request) {

        $request->validate([
            'message' => 'required',
            'message_group_id' => 'required'
        ]);

        $sender_id = Auth::id();
        $messageGroupId = $request->message_group_id;

        $message = new Message();
        $message->message = $request->message;

        if ($message->save()) {
            try {
                $message->users()->attach($sender_id, ['message_group_id' => $messageGroupId]);
                $sender = User::where('id', '=', $sender_id)->first();

                $data = [];
                $data['sender_id'] = $sender_id;
                $data['sender_name'] = $sender->name;
                $data['group_id'] = $messageGroupId;
                $data['content'] = $message->message;
                $data['created_at'] = $message->created_at;
                $data['message_id'] = $message->id;
                $data['type']=2;

                broadcast(new GroupMessageEvent($data));

                return response()->json([
                    'data' => $data,
                    'success' => true,
                    'message' => 'Message sent successfully',

                ]);
            } catch (\Exception $e) {
                $message->delete();
                return response()->json([

                    'success' => false,
                    'message' => 'Message sent not successfully'
                ]);
            }
        }
    }
}

