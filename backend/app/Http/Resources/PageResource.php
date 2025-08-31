<?php

namespace App\Http\Resources;

class PageResource extends PageIndexResource
{
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'content' => $this->content,
            'template' => $this->template,
            'meta' => $this->meta,
            'sections' => $this->sections,
            'custom_css' => $this->custom_css,
            'custom_js' => $this->custom_js,
        ]);
    }
}
