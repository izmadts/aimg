<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCylinderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $cylinderId = $this->route('cylinder')?->id;

        return [
            'cylinder_number' => ['nullable', 'string', 'max:50', Rule::unique('cylinders', 'cylinder_number')->ignore($cylinderId)],
            'gas_product_id' => 'required|exists:gas_products,id',
            'type' => 'required|string|max:50',
            'manufacturer' => 'nullable|string|max:100',
            'tare_weight' => 'required|numeric|min:0.01',
            'capacity' => 'required|numeric|min:0.01',
            'stock_quantity' => 'required|integer|min:0',
            'filled_quantity' => 'required|integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|gte:purchase_price',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'sale_price.gte' => 'Sale price must be greater than or equal to the purchase price.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $stock = (int) $this->input('stock_quantity');
            $filled = (int) $this->input('filled_quantity');

            if ($filled > $stock) {
                $validator->errors()->add('filled_quantity', 'Filled quantity can\'t be more than the total stock quantity.');
            }
        });
    }
}
