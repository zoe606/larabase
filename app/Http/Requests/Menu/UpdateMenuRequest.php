<?php

declare(strict_types=1);

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('menu'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $menuId = $this->route('menu')->id ?? $this->route('menu');

        return [
            'title' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'route' => ['nullable', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:menus,id',
                Rule::notIn([$menuId]), // Prevent self-reference
            ],
            'permission_name' => ['nullable', 'string', 'exists:permissions,name'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
