<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FoundItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $query = FoundItem::query()
            ->with('category')
            ->where('status', '!=', 'Archived')
            ->where(function ($query) {
                $query->whereNotNull('published_at')
                    ->orWhere('status', 'Returned');
            });

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
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
