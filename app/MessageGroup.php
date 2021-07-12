<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MessageGroup extends Model
{
    public $fillable=['user_id','name'];

    public function message_group_members(){
        return $this->hasMany(MessageGroupMember::class);
    }
    public function user_messages(){
        return $this->hasMany(UserMessage::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }

}
