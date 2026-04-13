<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEnserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('patrimonio.gestion') ?? false;
    }

    public function rules(): array
    {
        return [
            'edit_enser_id' => ['nullable', 'integer', 'exists:enseres,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'categoria_id' => ['required', 'integer', 'exists:categorias_patrimonio,id'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'autor' => ['nullable', 'string', 'max:255'],
            'año_creacion' => ['nullable', 'integer', 'min:1000', 'max:2100'],
            'materiales' => ['nullable', 'string', 'max:500'],
            'estado_conservacion_id' => ['required', 'integer', 'exists:estados_conservacion_patrimonio,id'],
            'valor_estimado' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'descripcion_detallada' => ['nullable', 'string', 'max:20000'],
            'ultima_revision' => ['nullable', 'date'],
            'imagen_principal' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
