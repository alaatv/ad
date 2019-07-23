<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed UUID
 */
class Ad extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'UUID',
        'source_id',
        'foreign_id',
        'name',
        'image',
        'link',
        'enable',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function source(){
        return $this->belongsTo(Source::class) ;
    }

}
