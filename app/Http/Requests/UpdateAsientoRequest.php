<?php

namespace App\Http\Requests;

class UpdateAsientoRequest extends StoreAsientoRequest
{
    public function rules(): array
    {
        return parent::rules();
    }
}
