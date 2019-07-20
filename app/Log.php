<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'text',
    ];

    protected $dates = ['deleted_at'];

    public function category(){
        return $this->belongsTo(Logcategory::class , 'category_id' , 'id') ;
    }
}
