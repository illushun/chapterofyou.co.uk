<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        //dd(auth()->id());
        Log::info('Fetching all products,....');

        $products = Product::where("status", "enabled")->get();
        dd($products);
    }

    public function showProduct(Request $request): \Inertia\Response
    {
        dd($request);
    }
}
