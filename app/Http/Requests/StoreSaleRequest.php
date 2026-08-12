<?php

namespace App\Http\Requests;

use App\Models\Cylinder;
use App\Models\GasProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,online,credit',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',

            'items' => 'required|array|min:1',
            'items.*.gas_product_id' => 'nullable|exists:gas_products,id',
            'items.*.gas_quantity' => 'nullable|numeric|min:0.01',
            'items.*.gas_price' => 'nullable|numeric|min:0',
            'items.*.cylinder_id' => 'nullable|exists:cylinders,id',
            'items.*.cylinder_action' => 'nullable|in:issue,sell',
            'items.*.cylinder_quantity' => 'nullable|integer|min:1',
            'items.*.cylinder_unit_price' => 'nullable|numeric|min:0',
            'items.*.is_customer_cylinder' => 'nullable|boolean',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $items = $this->input('items', []);
            $gasDemand = [];
            $cylinderDemand = [];
            $subtotal = 0;
            $depositTotal = 0;
            $cylinderSaleTotal = 0;

            foreach ($items as $index => $item) {
                $hasGas = !empty($item['gas_product_id']);
                $hasCylinder = !empty($item['cylinder_id']);

                if (!$hasGas && !$hasCylinder) {
                    $validator->errors()->add("items.$index", 'Each line must have a gas product, a cylinder, or both.');
                    continue;
                }

                if ($hasGas && !$hasCylinder && empty($item['is_customer_cylinder'])) {
                    $validator->errors()->add("items.$index.is_customer_cylinder", 'Gas sold without one of our cylinders must be confirmed as refilled into the customer\'s own cylinder.');
                }

                if ($hasGas) {
                    if (empty($item['gas_quantity']) || !isset($item['gas_price'])) {
                        $validator->errors()->add("items.$index.gas_quantity", 'Gas quantity and price are required for this line.');
                    } else {
                        $gasDemand[$item['gas_product_id']] = ($gasDemand[$item['gas_product_id']] ?? 0) + $item['gas_quantity'];
                        $subtotal += $item['gas_quantity'] * $item['gas_price'];
                    }
                }

                if ($hasCylinder) {
                    if (empty($item['cylinder_action']) || empty($item['cylinder_quantity'])) {
                        $validator->errors()->add("items.$index.cylinder_action", 'Cylinder action and quantity are required when a cylinder is selected.');
                    } else {
                        if ($item['cylinder_action'] === 'sell' && empty($item['cylinder_unit_price'])) {
                            $validator->errors()->add("items.$index.cylinder_unit_price", 'Sale price is required when selling a cylinder.');
                        }
                        $cylinderDemand[$item['cylinder_id']] = ($cylinderDemand[$item['cylinder_id']] ?? 0) + $item['cylinder_quantity'];
                        $lineTotal = $item['cylinder_quantity'] * ($item['cylinder_unit_price'] ?? 0);
                        if ($item['cylinder_action'] === 'issue') {
                            $depositTotal += $lineTotal;
                        } else {
                            $cylinderSaleTotal += $lineTotal;
                        }
                    }
                }
            }

            foreach ($gasDemand as $gasProductId => $quantity) {
                $gasProduct = GasProduct::find($gasProductId);
                if ($gasProduct && $quantity > $gasProduct->current_stock) {
                    $validator->errors()->add('items', "Insufficient stock for {$gasProduct->name}: requested {$quantity} {$gasProduct->uom}, only {$gasProduct->current_stock} available.");
                }
            }

            foreach ($cylinderDemand as $cylinderId => $quantity) {
                $cylinder = Cylinder::find($cylinderId);
                if ($cylinder && $quantity > $cylinder->available_quantity) {
                    $validator->errors()->add('items', "Insufficient cylinder stock for {$cylinder->cylinder_number}: requested {$quantity}, only {$cylinder->available_quantity} available.");
                }
            }

            $discount = (float) $this->input('discount', 0);
            $tax = (float) $this->input('tax', 0);
            $grandTotal = $subtotal + $depositTotal + $cylinderSaleTotal - $discount + $tax;
            $amountPaid = (float) $this->input('amount_paid', 0);

            if ($amountPaid > $grandTotal + 0.01) {
                $validator->errors()->add('amount_paid', 'Amount paid cannot exceed the invoice total (' . number_format($grandTotal, 2) . ').');
            }
        });
    }
}
