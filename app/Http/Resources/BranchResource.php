<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'address'     => $this->address ?? null,
            'user'        => $this->whenLoaded('user', function () {
                return [
                    'id'    => $this->user?->id,
                    'name'  => $this->user?->name,
                    'email' => $this->user?->email,
                ];
            }),
            'courses'     => $this->whenLoaded('courses', function () {
                return $this->courses->map(function ($course) {
                    return [
                        'id'    => $course->id,
                        'name'  => $course->name,
                        'title' => $course->title,
                    ];
                });
            }),
            'students'    => $this->whenLoaded('students', function () {
                return $this->students->map(function ($student) {
                    return [
                        'id'   => $student->id,
                        'name' => $student->name,
                        'code' => $student->code,
                    ];
                });
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
