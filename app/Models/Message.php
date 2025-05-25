<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Pest\Mutate\Mutators\Visibility\FunctionPublicToProtected;

class Message extends Model
{

    use SoftDeletes;
    protected $fillable =[
        'sender_id',
        'receiver_id',
        'message',
        'file_name',
        'file_original_name',
        'folder_path',
        'file_type',
        'is_read',
        'reaction',
        'is_deleted',
        'parent_id'
    ];

    public function sender()  {
        return $this->belongsTo(User::class,'sender_id', 'id' );
    }

    public function receiver()  {
        return $this->belongsTo(User::class,'receiver_id', 'id' );
    }

    public function parent(){
        return $this->belongsTo(Message::class, 'parent_id', 'id')->withTrashed();
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
