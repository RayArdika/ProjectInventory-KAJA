<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $productName = trim((string) ($row['product_name'] ?? ''));

        if (! preg_match('/^(Kantong|Sachet)\s+/i', $productName)) {
            return null;
        }

        return new Product([

            'sku' => Product::nextSku(),

            'product_name' => $productName,

            'stock' => $row['stock'],

            'worker_fee' => $row['worker_fee'],

        ]);
    }
}
