<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = [
        'note','note_type','note_img','user_id'
    ];

    protected $appends = ['picture_data'];


    public function getPictureDataAttribute(){
        return url('public/'.$this->note_img);
    }

    public function notification_user()
    {
        return $this->hasOne(NotificationUser::class);
    }


    protected static function boot()
    {
        parent::boot();

        static::created(function ($notification) {
            // dd($notification);
            $users = User::where('status',1)->get();
            foreach($users as $user){
                NotificationUser::insert(['notification_id'=>$notification->id,'user_id'=>$user->id]);
            }
        });
    }
}
