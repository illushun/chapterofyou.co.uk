<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Category;

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
}
