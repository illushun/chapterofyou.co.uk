<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminCategoryController extends Controller
{
    /**
     * Helper to store uploaded images and create database records.
     */
    private function handleImageUpload(Category $category, string $image): void
    {
        $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

        // Store file in the 'category_images' directory within the 'public' disk
        $path = $file->storeAs('category_images', $fileName, 'public');
        $category->update(['image' => Storage::url($path)]);
    }

    /**
     * Display a listing of categories.
     */
    public function index()
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
        return Inertia::render('admin/category/CreateEdit', [
            'isEditing' => false,
        ]);
    }

    /**
     * Store a newly created resource in storage. (Store)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,webp'],
            'status' => ['required', Rule::in(['enabled', 'disabled'])],
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $category = Category::create($validated);

            if ($request->hasFile('image')) {
                $this->handleImageUpload($category, $request->file('image'));
            }

            return redirect()->route('admin.categories.index')
                ->with('success', "Category '{$category->name}' created successfully!");
        });
    }

    /**
     * Show the form for editing the specified resource. (Edit)
     */
    public function edit(Category $category)
    {
        return Inertia::render('admin/category/CreateEdit', [
            'category' => $category,
            'isEditing' => true,
        ]);
    }

    /**
     * Update the specified resource in storage. (Update)
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,webp'],
            'status' => ['required', Rule::in(['enabled', 'disabled'])],

            'new_image' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,webp'],

            'images_to_delete' => ['nullable', 'array'],
            'images_to_toggle' => ['nullable', 'array'],
        ]);

        return DB::transaction(function () use ($request, $validated, $category) {
            $category->update($validated);

            if (!empty($validated['images_to_delete'])) {

                dd($validated['images_to_delete']);

                /*foreach ($validated['images_to_delete'] as $image) {
                    Storage::disk('public')->delete($image->image);
                    $image->delete();
                }*/
            }

            if (!empty($validated['images_to_toggle'])) {

                dd($validated['images_to_toggle']);

                /*foreach ($validated['images_to_toggle'] as $image) {
                    $newStatus = $image->status === 'enabled' ? 'disabled' : 'enabled';
                    $image->update(['status' => $newStatus]);
                }*/
            }

            if ($request->hasFile('new_image')) {
                $this->handleImageUpload($category, $request->file('new_image'));
            }

            return redirect()->route('admin.categories.index')
                ->with('success', "Category '{$category->name}' updated successfully!");
        });
    }

    /**
     * Remove the specified resource from storage. (Destroy)
     */
    public function destroy(Category $category)
    {
        $categoryName = $category->name;
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$categoryName}' deleted successfully.");
    }
}
