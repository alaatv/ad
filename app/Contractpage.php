<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contractpage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'conttract_id',
        'url',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function contract(){
        return $this->belongsTo(Contract::class) ;
    }
}
