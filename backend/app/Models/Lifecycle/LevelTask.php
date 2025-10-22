<?php

namespace App\Models\Lifecycle;

use Database\Factories\Lifecycle\LevelTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelTask extends Model
{
    /** @use HasFactory<LevelTaskFactory> */
    use HasFactory;






    public function level()
    {
        return $this->belongsTo(Level::class,'level_id');
    }




}
