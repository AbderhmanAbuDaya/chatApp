<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserMessage extends Model
{
    protected $guarded=[];
   public function message(){
   return $this->belongsTo(Message::class);
   }
   public function message_groups(){
       return $this->belongsTo(MessageGroup::class);
   }
}
