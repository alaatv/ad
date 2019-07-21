<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'source_id',
        'foreign_id',
        'name',
        'image',
        'link',
        'enable',
    ];

    protected $dates = ['deleted_at'];

    public function source(){
        return $this->belongsTo(Source::class) ;
    }
}
