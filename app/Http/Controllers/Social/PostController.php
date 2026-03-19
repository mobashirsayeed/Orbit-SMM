<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Jobs\Social\PublishPostJob;
use App\Models\Post;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Social/Posts', [
            'posts' => Post::with('creator')
                ->when($request->status, fn($q, $s) => $q->where('status', $s))
                ->orderByDesc('created_at')
                ->paginate(20),
        ]);
    }

    public function create()
    {
        return Inertia::render('Social/Composer', [
            'accounts' => SocialAccount::select('id', 'platform', 'account_name', 'account_avatar')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'body' => 'required|string|max:5000',
            'platforms' => 'required|array|min:1',
            'media_urls' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $post = Post::create([
            'workspace_id' => app('workspace')->id,
            'created_by' => $request->user()->id,
            'body' => $v['body'],
            'platforms' => $v['platforms'],
            'media_urls' => $v['media_urls'] ?? [],
            'status' => $v['scheduled_at'] ? 'scheduled' : 'draft',
            'scheduled_at' => $v['scheduled_at'] ?? null,
        ]);

        if (!$v['scheduled_at']) {
            PublishPostJob::dispatch($post);
            $post->update(['status' => 'publishing']);
        }

        return redirect()->route('social.posts')->with('success',
            $v['scheduled_at'] ? 'Post scheduled.' : 'Publishing...'
        );
    }

    public function reschedule(Request $request, Post $post)
    {
        $v = $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        $post->update([
            'scheduled_at' => $v['scheduled_at'],
            'status' => 'scheduled',
        ]);

        return back()->with('success', 'Rescheduled.');
    }
}
