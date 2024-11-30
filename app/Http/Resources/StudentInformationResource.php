<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentInformationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'matric'=>$this->Matric,
            'programme'=>$this->programid,
            'surname'=>$this->Surname,
            'othername'=>$this->Othernames,
            'status'=>200

        ];
    }
}
