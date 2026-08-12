<?php

namespace App\Http\Requests;

use App\Models\Cylinder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:date',
            'purchase_type' => 'required|in:gas_only,gas_with_cylinder,cylinder_only,exchange',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,online,credit',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'reference_no' => 'nullable|string|max:255',
            'notes' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.gas_product_id' => 'nullable|exists:gas_products,id',
            'items.*.gas_quantity' => 'nullable|numeric|min:0.01',
            'items.*.gas_price' => 'nullable|numeric|min:0',
            'items.*.cylinder_id' => 'nullable|exists:cylinders,id',
            'items.*.cylinder_action' => 'nullable|in:purchase,exchange,return_to_supplier',
            'items.*.cylinder_quantity' => 'nullable|integer|min:1',
            'items.*.cylinder_unit_price' => 'nullable|numeric|min:0',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $items = $this->input('items', []);
            $subtotal = 0;
            $cylinderTotal = 0;
            $returnDemand = [];

            foreach ($items as $index => $item) {
                $hasGas = !empty($item['gas_product_id']);
                $hasCylinder = !empty($item['cylinder_id']);

                if (!$hasGas && !$hasCylinder) {
                    $validator->errors()->add("items.$index", 'Each line must have a gas product, a cylinder, or both.');
                    continue;
                }

                if ($hasGas) {
                    if (empty($item['gas_quantity']) || !isset($item['gas_price'])) {
                        $validator->errors()->add("items.$index.gas_quantity", 'Gas quantity and price are required for this line.');
                    } else {
                        $subtotal += $item['gas_quantity'] * $item['gas_price'];
                    }
                }

                if ($hasCylinder) {
                    if (empty($item['cylinder_action']) || empty($item['cylinder_quantity'])) {
                        $validator->errors()->add("items.$index.cylinder_action", 'Cylinder action and quantity are required when a cylinder is selected.');
                        continue;
                    }

                    if ($item['cylinder_action'] === 'purchase') {
                        if (empty($item['cylinder_unit_price'])) {
                            $validator->errors()->add("items.$index.cylinder_unit_price", 'Unit price is required when purchasing cylinders.');
                        }
                        $cylinderTotal += $item['cylinder_quantity'] * ($item['cylinder_unit_price'] ?? 0);
                    }

                    if ($item['cylinder_action'] === 'return_to_supplier') {
                        $returnDemand[$item['cylinder_id']] = ($returnDemand[$item['cylinder_id']] ?? 0) + $item['cylinder_quantity'];
                    }
                }
            }

            foreach ($returnDemand as $cylinderId => $quantity) {
                $cylinder = Cylinder::find($cylinderId);
                if ($cylinder && $quantity > $cylinder->stock_quantity) {
                    $validator->errors()->add('items', "Cannot return {$quantity} of {$cylinder->cylinder_number} to the supplier: only {$cylinder->stock_quantity} in stock.");
                }
            }

            $discount = (float) $this->input('discount', 0);
            $tax = (float) $this->input('tax', 0);
            $grandTotal = $subtotal + $cylinderTotal - $discount + $tax;
            $amountPaid = (float) $this->input('amount_paid', 0);

            if ($amountPaid > $grandTotal + 0.01) {
                $validator->errors()->add('amount_paid', 'Amount paid cannot exceed the purchase total (' . number_format($grandTotal, 2) . ').');
            }
        });
    }
}
