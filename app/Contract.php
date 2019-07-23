<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'source_id',
        'since',
        'till',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function source(){
        return $this->belongsTo(Source::class);
    }

    public function contractpage(){
        return $this->hasMany(Contractpage::class);
    }
}
