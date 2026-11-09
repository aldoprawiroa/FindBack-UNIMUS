<?php

namespace App\Http\Controllers;

use App\Models\FoundItem;
use App\Models\ReturnRecord;
use App\Models\SecurityPost;
use Illuminate\Http\Request;
class ReturnController extends Controller
{
    public function create(): View
    {
        $items = FoundItem::query()
            ->whereNotNull('published_at')
            ->whereNotIn('status', ['Returned', 'Archived'])
            ->orderBy('title')
            ->get();

        $securityPosts = SecurityPost::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('returns.create', compact(
            'items',
            'securityPosts'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'found_item_id' => [
                'required',
                'integer',
                'exists:found_items,id',
            ],
            'recipient_name' => [
                'required',
                'string',
                'max:255',
            ],
            'security_post_id' => [
                'required',
                'integer',
                'exists:security_posts,id',
            ],
            'returned_at' => [
                'required',
                'date',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $item = FoundItem::query()
                ->lockForUpdate()
                ->findOrFail($validated['found_item_id']);

            if (
                in_array($item->status, ['Returned', 'Archived'], true)
                || $item->published_at === null
            ) {
                throw ValidationException::withMessages([
                    'found_item_id' => 'Barang tidak tersedia untuk dikembalikan.',
                ]);
            }

            $post = SecurityPost::query()
                ->where('is_active', true)
                ->findOrFail($validated['security_post_id']);

            ReturnRecord::create([
                'found_item_id' => $item->id,
                'recipient_name' => $validated['recipient_name'],
                'security_post_id' => $post->id,
                'processed_by' => $request->user()->id,
                'returned_at' => $validated['returned_at'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $item->update([
                'status' => 'Returned',
            ]);
        });

        return redirect()
            ->route('returns.index')
            ->with('success', 'Pengembalian barang berhasil dicatat.');
    }

    public function index(Request $request): View
    {
        $query = ReturnRecord::query()
            ->with(['foundItem', 'securityPost', 'processor'])
            ->latest('returned_at');

        if ($request->user()->role === 'security') {
            $query->where('processed_by', $request->user()->id);
        }

        $returns = $query->paginate(10);

        return view('returns.index', compact('returns'));
    }
}
