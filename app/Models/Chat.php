<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable =[
        'user_id','chat','chat_type','flag'
    ];

    protected $appends = ['file'];


    public function getfileAttribute(){
       if($this->chat_type == 'file'){
        return url('public'.$this->chat);
       }else{
        return '';
       }
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

}
