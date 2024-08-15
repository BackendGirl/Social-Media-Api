<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'file','file_type','title','description','thumbnail'
    ];

    protected $appends = ['post_data','likes','like_flag','total_comments','date','time','time_difference','thumbnail_data'];


    public function getPostDataAttribute(){
        return url('public/'.$this->file);
    }

    public function getThumbnailDataAttribute(){
        return url('public/'.$this->thumbnail);
    }

    public function getLikesAttribute(){
        return Like::where('post_id',$this->id)->count();
    }
    public function getLikeFlagAttribute(){
        $like =  Like::where('post_id',$this->id)->where('user_id',Auth::user()->id)->first();
        if($like){
            return true;
        }else{
            return false;
        }
    }

    public function getTotalCommentsAttribute(){
        return Comment::where('post_id',$this->id)->count();
    }
    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id','id');
    }

    public function getDateAttribute(){
        return Carbon::parse($this->created_at)->format('d-m-Y');
    }

    public function getTimeAttribute(){
        return Carbon::parse($this->created_at)->format('h:i');
    }

    public function getTimeDifferenceAttribute(){
        $createdTime = Carbon::parse($this->created_at); // Assuming created_at is the column name
        $now = Carbon::now();
        $monthsDifference = $createdTime->diffInMonths($now);
        $daysDifference = $createdTime->diffInDays($now);
        $remainingHours = $createdTime->diff($now)->h;
        $remainingMinutes = $createdTime->diff($now)->i;
    
        // Output the formatted time difference
        if($monthsDifference == 0){
            if($daysDifference == 0){
                if($remainingHours == 0){
                    return $remainingMinutes.'m';
                }else{
                    return $remainingHours.'h';
                }
            } else {
                return $daysDifference.'d';
            }
        } else {
            return $monthsDifference.'mon';
        }
    }

}
