<?php

declare(strict_types=1);

namespace App\Http\Requests\Menu;

use App\Models\Menu;
use Illuminate\Foundation\Http\FormRequest;

class ReorderMenuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', Menu::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'menus' => ['required', 'array'],
            'menus.*.id' => ['required', 'integer', 'exists:menus,id'],
            'menus.*.children' => ['nullable', 'array'],
            'menus.*.children.*.id' => ['required', 'integer', 'exists:menus,id'],
        ];
    }
}
