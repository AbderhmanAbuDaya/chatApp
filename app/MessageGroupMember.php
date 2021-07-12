<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MessageGroupMember extends Model
{
    protected $fillable=['user_id','message_group_id','status'];

    public function message_groups(){
        return $this->belongsTo(MessageGroup::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
