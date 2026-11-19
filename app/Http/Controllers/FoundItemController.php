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
    public function show(FoundItem $foundItem): View
    {
        abort_unless(
            in_array($foundItem->status, ['Published', 'Returned'], true)
            && $foundItem->published_at !== null
            && $foundItem->published_at->lte(now()),
            404
        );

        $foundItem->load('category');

        return view('found-items.show', [
            'item' => $foundItem,
        ]);
    }
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
            'description' => ['nullable', 'string', 'max:2000'],
            'category_id' => [
                'bail',
                'required',
                'integer',
                'exists:categories,id',
            ],
            'found_at' => [
                'required',
                'date',
                'after_or_equal:1970-01-01 00:00:01',
                'before_or_equal:2038-01-19 03:14:07',
            ],
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

    /**
     * Publish a draft found item.
     */
    public function publish(FoundItem $foundItem): RedirectResponse
    {
        abort_unless(
            $foundItem->status === 'Draft',
            409,
            'Hanya barang dengan status Draft yang dapat dipublikasikan.'
        );

        $foundItem->update([
            'status' => 'Published',
            'published_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Barang temuan berhasil dipublikasikan.');
    }

    /**
     * Archive a published found item.
     */
    public function archive(FoundItem $foundItem): RedirectResponse
    {
        abort_unless(
            $foundItem->status === 'Published',
            409,
            'Hanya barang berstatus Published yang dapat diarsipkan.'
        );

        $foundItem->update([
            'status' => 'Archived',
        ]);

        return redirect()
            ->back()
            ->with('success', 'Barang temuan berhasil diarsipkan.');
    }
}
