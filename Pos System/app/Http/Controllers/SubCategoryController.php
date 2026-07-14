<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function index()
    {
        $subCategories = SubCategory::with('category')->latest()->paginate(15);
        return view('sub-categories.index', compact('subCategories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('sub-categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        SubCategory::create([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('sub-categories.index')
            ->with('success', 'Sub-category created successfully.');
    }

    public function edit(SubCategory $subCategory)
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('sub-categories.edit', compact('subCategory', 'categories'));
    }

    public function update(Request $request, SubCategory $subCategory)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $subCategory->update([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('sub-categories.index')
            ->with('success', 'Sub-category updated successfully.');
    }

    public function destroy(SubCategory $subCategory)
    {
        if ($subCategory->products()->count() > 0) {
            return back()->with('error', 'Cannot delete sub-category with associated products.');
        }
        $subCategory->delete();
        return redirect()->route('sub-categories.index')
            ->with('success', 'Sub-category deleted successfully.');
    }
}
