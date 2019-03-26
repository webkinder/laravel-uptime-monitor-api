<?php

namespace LKDevelopment\UptimeMonitorAPI\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MonitorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $parent = parent::toArray($request);
        $parent['url'] = $parent['url']->getHost();
        return $parent;
    }
}
