<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'code'         => $this->code,
            'name'         => $this->name,
            'status'       => $this->status,
            'email'        => $this->email ?? null,
            'phone'        => $this->phone ?? null,
            'password'     => $this->password_plain ? Crypt::decryptString($this->password_plain) : null,
            'gender'       => $this->gender ?? null,
            'date_of_birth' => $this->date_of_birth ?? null,
            'address'      => $this->address ?? null,
            'created_at'   => $this->created_at?->toDateTimeString(),
            'updated_at'   => $this->updated_at?->toDateTimeString(),
            'course_start_at' => $this->course_start_at,
            'course_end_at' => $this->course_end_at,


            'course' => $this->whenLoaded('course', function () {
                return [
                    'id'    => $this->course?->id,
                    'name'  => $this->course?->name,
                    'title' => $this->course?->title,
                ];
            }),

            'branch' => $this->whenLoaded('branch', function () {
                return [
                    'id'   => $this->branch?->id,
                    'name' => $this->branch?->name,
                ];
            }),

            'user' => $this->whenLoaded('user', function () {
                return [
                    'id'    => $this->user?->id,
                    'name'  => $this->user?->name,
                    'email' => $this->user?->email,
                ];
            }),
        ];
    }
}
