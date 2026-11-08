<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FoundItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

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
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $photo = $validated['photo'] ?? null;
        unset($validated['photo']);

        $photoPath = null;

        try {
            if ($photo !== null) {
                $photoPath = $photo->store('found-items', 'local');

                if ($photoPath === false) {
                    throw new RuntimeException(
                        'Foto barang gagal disimpan.'
                    );
                }
            }

            FoundItem::create([
                ...$validated,
                'photo_path' => $photoPath,
                'status' => 'Draft',
            ]);
        } catch (Throwable $exception) {
            if ($photoPath !== null) {
                Storage::disk('local')->delete($photoPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('found-items.create')
            ->with('success', 'Barang temuan berhasil disimpan sebagai Draft.');
    }
}
