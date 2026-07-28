<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class QueueJobResult extends Model
{
    protected $table = 'queue_job_results';


    protected $keyType = 'string';


    public $incrementing = false;


    protected $guarded = [];


    protected $casts = [

        'result' => 'array',

        'error' => 'array',

        'started_at' => 'datetime',

        'completed_at' => 'datetime',

    ];


    protected static function boot(): void
    {
        parent::boot();


        static::creating(function ($model) {

            if (!$model->id) {

                $model->id = (string) Str::uuid();
            }
        });
    }
}
