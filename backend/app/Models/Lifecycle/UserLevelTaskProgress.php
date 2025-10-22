<?php

namespace App\Models\Lifecycle;

use Database\Factories\Lifecycle\UserLevelTaskProgressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLevelTaskProgress extends Model
{
    /** @use HasFactory<UserLevelTaskProgressFactory> */
    use HasFactory;
}
