<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}