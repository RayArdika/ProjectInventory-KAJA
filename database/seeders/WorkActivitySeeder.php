<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Product;
use App\Models\WorkActivity;
use Illuminate\Database\Seeder;

class WorkActivitySeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            'Kantong Yellow Helow' => [
                'Sticker Kantong Yellow',
                'Yellow Helow Bubuk',
            ],
            'Kantong Reddish Wish' => [
                'Sticker Kantong Reddish',
                'Reddish Wish Bubuk',
            ],
            'Kantong Simple Purple' => [
                'Sticker Kantong Simple',
                'Simple Purple Bubuk',
            ],
        ];

        foreach ($configs as $productName => $specificMaterials) {
            $product = Product::where('product_name', $productName)->first();

            if (! $product) {
                continue;
            }

            $activity = WorkActivity::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'activity_name' => 'Packing Kantong',
                ],
                [
                    'unit' => 'pcs',
                    'fee_per_unit' => 120,
                    'is_active' => true,
                ]
            );

            $materialNames = array_merge(['Kantong Teh Polos Personal'], $specificMaterials);

            foreach ($materialNames as $materialName) {
                $material = Material::where('material_name', $materialName)->first();

                if (! $material) {
                    continue;
                }

                $activity->materialRequirements()->updateOrCreate(
                    ['material_id' => $material->id],
                    ['qty_needed' => 1]
                );
            }
        }
    }
}
