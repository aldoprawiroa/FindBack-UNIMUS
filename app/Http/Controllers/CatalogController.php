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
        // Validasi parameter pencarian dan kategori.
        // Parameter array akan ditolak oleh validasi Laravel.
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        // Aturan visibilitas katalog.
        $query = FoundItem::query()
            ->with('category')
            ->whereIn('status', ['Published', 'Returned'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        // Pencarian judul.
        if (! empty($validated['search'])) {
            $search = $validated['search'];

            $query->where('title', 'like', "%{$search}%");
        }

        // Filter kategori.
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
