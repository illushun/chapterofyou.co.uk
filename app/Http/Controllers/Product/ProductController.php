<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        dd(auth()->id());
        $product = "test";
        Log::info('Fetching product ' . $product);
    }

    public function showProduct(Request $request): \Inertia\Response
    {
        dd($request);
    }
}
