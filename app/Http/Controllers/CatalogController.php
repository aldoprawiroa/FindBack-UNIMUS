<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FoundItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer'],
        ]);

        $query = FoundItem::query()
            ->with('category')
            ->where('status', '!=', 'Archived')
            ->where(function ($query) {
                $query->whereNotNull('published_at')
                    ->orWhere('status', 'Returned');
            });

        if (! empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (! empty($validated['category'])) {
            $query->where('category_id', $validated['category']);
        }

        $items = $query
            ->latest('found_at')
            ->paginate(9)
            ->withQueryString();

        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('catalog.index', compact(
            'items',
            'categories'
        ));
    }
}
