<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $guarded=[];
    public function user_message(){
        return $this->hasMany(UserMessage::class);
    }
    public function users(){
        return $this->belongsToMany(UserMessage::class,'user_messages','message_id','sender_id')
            ->withTimestamps();
    }
}
