<?php

namespace App\Actions\Products;

use App\Models\Product;

class ProductsTotalAction
{
    public function execute(array $productIds): float
    {
        return Product::query()->whereIn('id', $productIds)->sum('price');
    }
}
