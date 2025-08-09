<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Lifecycle\LevelResource;
use App\Http\Resources\Lifecycle\StageResource;
use App\Models\Lifecycle\Level;
use App\Models\Lifecycle\Stage;
use Illuminate\Http\Request;

class LifecycleController extends Controller
{


    public function getTimeline()
    {
        $allAvailableStages = Stage::with('levels')->where('status',true)->latest('id')->get();
        return StageResource::collection($allAvailableStages);
    }


    public function getAllStages(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $allAvailableStages = Stage::with('levels')->where('status',true)->get();
        return StageResource::collection($allAvailableStages);
    }


    public function getStage(Stage $stage): StageResource
    {
        $stage->load('levels');
        return StageResource::make($stage);
    }

    public function getLevel(Level $level)
    {
        return LevelResource::make($level);
    }



}
