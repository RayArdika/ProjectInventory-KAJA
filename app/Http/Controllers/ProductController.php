<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

use App\Imports\ProductImport;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $products = Product::latest()->get();

        return view('products', compact('products'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'product_name' => [
                'required',
                'unique:products',
                'regex:/^(Kantong|Sachet)\s+/i',
            ],

        ]);

        /*
        |--------------------------------------------------------------------------
        | CREATE PRODUCT
        |--------------------------------------------------------------------------
        */

        Product::create([

            'sku' => Product::nextSku(),

            'product_name' => $request->product_name,

            'stock' => $request->stock,

            'worker_fee' => $request->worker_fee,

        ]);

        return redirect()->back();
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT EXCEL
    |--------------------------------------------------------------------------
    */

    public function import(Request $request)
    {
        Excel::import(
            new ProductImport,
            $request->file('file')
        );

        return redirect()->back();
    }

public function update(Request $request, $id)
{
    $product = Product::findOrFail($id);

    $request->validate([
        'product_name' => [
            'required',
            'regex:/^(Kantong|Sachet)\s+/i',
        ],
    ]);

    $product->update([

        'product_name' => $request->product_name,

        'stock' => $request->stock,

        'worker_fee' => $request->worker_fee,

    ]);

    return redirect()->back();
}
public function destroy($id)
{
    $product = Product::findOrFail($id);

    $product->delete();

    return redirect()->back();
}
public function edit($id)
{
    $product = Product::findOrFail($id);

    return view('edit-product', compact('product'));
}
}
