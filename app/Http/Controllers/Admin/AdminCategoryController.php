<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminCategoryController extends Controller
{
    private function handleImageUpload(Category $category, $file): void
    {
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs('category_images', $fileName, 'public');

        $category->update(['image' => $path]);
    }

    public function index()
    {
        $categories = Category::query()
            ->select('id', 'name', 'slug', 'image', 'status', 'created_at')
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('admin/category/Index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/category/CreateEdit', [
            'isEditing' => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255', Rule::unique('category', 'slug')],
            'description'      => ['nullable', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'status'           => ['required', Rule::in(['enabled', 'disabled'])],
            'new_image'        => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,webp'],
        ]);

        return DB::transaction(function () use ($validated, $request) {
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            $imageFile = $request->file('new_image');
            unset($validated['new_image']);

            $category = Category::create($validated);

            if ($imageFile) {
                $this->handleImageUpload($category, $imageFile);
            }

            cache()->forget('sitemap.xml');

            return redirect()->route('admin.categories.index')
                ->with('success', "Category '{$category->name}' created successfully!");
        });
    }

    public function edit(Category $category)
    {
        return Inertia::render('admin/category/CreateEdit', [
            'category'  => $category,
            'isEditing' => true,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255',
                                   Rule::unique('category', 'slug')->ignore($category->id)],
            'description'      => ['nullable', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'status'           => ['required', Rule::in(['enabled', 'disabled'])],
            'new_image'        => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,webp'],
            'remove_image'     => ['boolean'],
        ]);

        return DB::transaction(function () use ($request, $validated, $category) {
            $imageFile   = $request->file('new_image');
            $removeImage = $request->boolean('remove_image');

            unset($validated['new_image'], $validated['remove_image']);

            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            $category->update($validated);

            if ($removeImage && $category->image) {
                Storage::disk('public')->delete($category->image);
                $category->update(['image' => null]);
            }

            if ($imageFile) {
                $this->handleImageUpload($category, $imageFile);
            }

            cache()->forget('sitemap.xml');

            return redirect()->route('admin.categories.index')
                ->with('success', "Category '{$category->name}' updated successfully!");
        });
    }

    public function destroy(Category $category)
    {
        $categoryName = $category->name;

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();
        cache()->forget('sitemap.xml');

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$categoryName}' deleted successfully.");
    }
}
