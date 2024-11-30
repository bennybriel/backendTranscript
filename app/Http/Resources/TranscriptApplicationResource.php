<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TranscriptApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return
         [
            'id' => (string)$this->id,
             'matric'=>$this->matricno,
             'attributes' =>
                [
                    'name'=>$this->name,
                    'email'=>$this->email,
                    'programmes'=>$this->programme,
                    'state'=>$this->state,
                    'country'=>$this->country
                ],
              'payments'=>
                [
                   'payment' =>$this->ispaid,
                   'status' =>$this->status,
                ]
          ];

    }
}
