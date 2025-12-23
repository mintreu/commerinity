<?php

namespace App\Helpers\SpatieMediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class UniquePathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return md5($media->id.config('app.key')).'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return md5($media->id.config('app.key')).'/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return md5($media->id.config('app.key')).'/responsive-images/';
    }
}
