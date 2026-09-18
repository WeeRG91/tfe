<?php

namespace App\Http\Resources\Admin\User;

use App\Http\Resources\Admin\Role\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar
                ? Storage::disk('public')->url($this->avatar->path)
                : null,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'roles' => RoleResource::collection(
                $this->whenLoaded('roles')
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
