<?php

namespace App\Http\Controllers;

use App\Models\SecurityPost;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPostAssignmentController extends Controller
{
    /**
     * Tampilkan daftar petugas/admin dan pos satpam aktif untuk penugasan.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', User::class);

        $staffUsers = User::query()
            ->with('securityPost')
            ->whereIn('role', User::STAFF_ROLES)
            ->orderBy('name')
            ->get();

        $securityPosts = SecurityPost::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.post-assignments.index', compact(
            'staffUsers',
            'securityPosts'
        ));
    }

    /**
     * Perbarui penugasan pos satpam untuk admin atau petugas keamanan.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('assignSecurityPost', $user);

        $validated = $request->validate([
            'security_post_id' => [
                'nullable',
                'integer',
                Rule::exists('security_posts', 'id')->where('is_active', true),
            ],
        ]);

        $postId = $validated['security_post_id'] ?? null;

        if ($postId === null) {
            $user->unassignSecurityPost();

            return redirect()
                ->route('admin.post-assignments.index')
                ->with('success', "Penugasan pos untuk {$user->name} berhasil dihapus.");
        }

        $post = SecurityPost::query()
            ->where('is_active', true)
            ->findOrFail($postId);

        Gate::authorize('assignSecurityPost', [$user, $post]);

        $user->assignSecurityPost($post);

        return redirect()
            ->route('admin.post-assignments.index')
            ->with('success', "{$user->name} berhasil ditugaskan ke {$post->name}.");
    }
}
