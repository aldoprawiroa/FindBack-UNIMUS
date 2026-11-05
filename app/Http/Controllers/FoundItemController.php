<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FoundItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FoundItemController extends Controller
{
    public function create(): View
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('found-items.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'found_at' => ['required', 'date'],
        ]);

        FoundItem::create([
            ...$validated,
            'status' => 'Draft',
        ]);

        return redirect()
            ->route('found-items.create')
            ->with('success', 'Barang temuan berhasil disimpan sebagai Draft.');
    }
}
