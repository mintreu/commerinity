<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Naukri\NaukriIndexResource;
use App\Http\Resources\Naukri\NaukriResource;
use App\Models\Naukri;
use Illuminate\Http\Request;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

class RecruitmentController extends Controller
{


    public function index(Request $request)
    {
        $allRecruitments = Naukri::withinDate()
            ->with([
                'media' => fn($query) => $query->where('collection_name','displayImage')
            ])
            ->where('status',PublishableStatusCast::PUBLISHED)
            ->get();
        return NaukriIndexResource::collection($allRecruitments);
    }


    public function show(Naukri $naukri)
    {
        $naukri->load(['media']);
        return NaukriResource::make($naukri);
    }



    public function apply(Naukri $naukri,Request $request)
    {

    }





}
