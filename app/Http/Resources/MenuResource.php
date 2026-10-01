<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Menu
 */
class MenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->route,
            'icon' => $this->icon,
            'permission' => $this->permission_name,
            'order' => $this->order,
            'parent_id' => $this->parent_id,
            'children' => MenuResource::collection($this->whenLoaded('children')),
            'parent' => new MenuResource($this->whenLoaded('parent')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
