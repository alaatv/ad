<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fetch extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'source_id',
        'first_item_id',
        'page',
        'per_page',
        'done',
    ];

    protected $dates = ['deleted_at'];

    public function source(){
        return $this->belongsTo(Source::class) ;
    }
}
