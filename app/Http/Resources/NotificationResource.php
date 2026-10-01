<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $type
 * @property array<string, mixed> $data
 * @property \Carbon\Carbon|null $read_at
 * @property \Carbon\Carbon $created_at
 */
final class NotificationResource extends JsonResource
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
            'type_key' => $this->getTypeKey(),
            'title' => $this->data['title'] ?? 'Notification',
            'message' => $this->data['message'] ?? '',
            'is_read' => $this->read_at !== null,
            'time_ago' => $this->created_at->diffForHumans(),
            'link' => $this->data['link'] ?? null,
            'created_at' => $this->created_at->toISOString(),
        ];
    }

    /**
     * Extract short type key from full notification class name.
     */
    private function getTypeKey(): string
    {
        // Convert 'App\Notifications\AccountUpdatedNotification' to 'account_depletion'
        $className = class_basename($this->type);
        $name = Str::replaceLast('Notification', '', $className);

        return Str::snake($name);
    }
}
