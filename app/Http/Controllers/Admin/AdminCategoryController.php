<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Category;
use App\Models\Product;

class AdminCategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        $categories = Category::query()
            ->select('id', 'name', 'image', 'status', 'created_at')
            ->orderByDesc('created_at')
            ->paginate(15);


        return Inertia::render('admin/category/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource. (Create)
     */
    public function create()
    {
        $categories = Category::select('id', 'name')->get();
        $parentProducts = Product::select('id', 'name')->get();

        return Inertia::render('admin/product/CreateEdit', [
            'categories' => $categories,
            'parentProducts' => $parentProducts,
            'isEditing' => false,
            'productImages' => [],
        ]);
    }
}
