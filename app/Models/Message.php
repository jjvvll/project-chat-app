<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable =[
        'sender_id',
        'receiver_id',
        'message',
        'file_name',
        'file_original_name',
        'folder_path',
        'file_type',
        'is_read',
    ];

    public function sender()  {
        return $this->belongsTo(User::class,'sender_id', 'id' );
    }

    public function receiver()  {
        return $this->belongsTo(User::class,'receiver_id', 'id' );
    }

    /**
     * override created_at time
     */
    // protected static function boot(){
    //     parent::boot();

    //     static::creating(function ($model){
    //         $model->created_at = Carbon::now();
    //     });
    // }
}
