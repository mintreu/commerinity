<?php

namespace App\Http\BackUp;

use App\Http\Controllers\Controller;
use App\Http\Resources\Lifecycle\LevelResource;
use App\Http\Resources\Lifecycle\StageResource;
use App\Models\Lifecycle\Level;
use App\Models\Lifecycle\Stage;
use Illuminate\Http\Request;

class LifecycleController extends Controller
{


    public function getTimeline(Request $request)
    {
        $allAvailableStages = Stage::with([
            'levels' => fn($query) => $query->orderBy('id', 'desc')
        ])->where('status',true)->latest('id')->get();
        $user = $request->user();
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

    public function getLevel(Level $level): LevelResource
    {
        return LevelResource::make($level);
    }



    public function getUserSubscribableStageAndLevel(Request $request): StageResource
    {
        $user = $request->user();

        $stage = $user->getNextLifecycleStage();


        return StageResource::make($stage);
    }





}
