<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NaukriApplication extends Model
{
    /** @use HasFactory<\Database\Factories\NaukriApplicationFactory> */
    use HasFactory;


    protected $fillable = [
        'uuid',
        'name',
        'mobile',
        'email',
        'gender',
        'dob',
        'guardian_name',

        'educations',
        'skills',
        'experience',

        'reference_name',
        'reference_contact',
        'submitted_at',

        'status',
        'status_feedback',
    ];

    protected $casts = [
        'dob' => 'date',
        'submitted_at' => 'datetime',

        'educations' => 'array',
        'skills' => 'array',
        'experience' => 'array',
    ];

    public function naukri(): BelongsTo
    {
        return $this->belongsTo(Naukri::class,'naukri_id','id');
    }







}
