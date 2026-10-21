<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $categories = Category::withCount('products')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(Request $request)
    {
        $this->authorizeAdmin($request);

        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')],
            'slug' => ['required', 'string', 'max:140', 'alpha_dash', Rule::unique('categories', 'slug')],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon' => ['nullable', 'string', 'max:64'],
        ]);

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Request $request, Category $category)
    {
        $this->authorizeAdmin($request);

        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'slug' => ['required', 'string', 'max:140', 'alpha_dash', Rule::unique('categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon' => ['nullable', 'string', 'max:64'],
        ]);

        DB::transaction(function () use ($category, $data): void {
            $category->update($data);
            // Keep the original text column synchronized for older views and integrations.
            $category->products()->update(['category' => $data['name']]);
        });

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Request $request, Category $category)
    {
        $this->authorizeAdmin($request);

        if ($category->products()->exists()) {
            return back()->with('error', 'This category still has products. Reassign those products before deleting it.');
        }

        try {
            $category->delete();
        } catch (QueryException) {
            return back()->with('error', 'This category is still assigned to a product and cannot be deleted.');
        }

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }

    public function show(Request $request, Category $category)
    {
        $query = $category->products()->with(['farmer', 'photos', 'categoryRecord'])->latest();

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where('name', 'like', '%'.$term.'%');
        }

        $products = $query->paginate(12)->withQueryString();

        return view('categories.show', compact('category', 'products'));
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }
}
