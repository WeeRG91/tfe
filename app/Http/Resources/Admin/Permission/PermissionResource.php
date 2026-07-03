<?php

namespace App\Http\Resources\Admin\Permission;

use App\Enums\Permissions\PermissionCategoryEnum;
use App\Services\PermissionEnumResolver;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
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
            'name' => PermissionEnumResolver::label($this->name),
            'category' => PermissionCategoryEnum::from($this->category)->label(),
        ];
    }
}
