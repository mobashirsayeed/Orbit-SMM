<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Intervention\Image\Laravel\Facades\Image;

class MediaLibraryController extends Controller
{
    public function index(Request $request)
    {
        $query = MediaLibrary::where('tenant_id', app('tenant')->id)
            ->with('user');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('folder')) {
            $query->inFolder($request->folder);
        }

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        $media = $query->orderBy('created_at', 'desc')
            ->paginate(24)
            ->withQueryString();

        $folders = MediaLibrary::where('tenant_id', app('tenant')->id)
            ->select('folder')
            ->distinct()
            ->pluck('folder')
            ->filter()
            ->values();

        return Inertia::render('Media/Index', [
            'media' => $media,
            'folders' => $folders,
            'currentFolder' => $request->folder,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'image|max:10240', // 10MB max
            'folder' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
        ]);

        $uploaded = [];

        foreach ($request->file('files') as $file) {
            $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('media/' . app('tenant')->id, $filename, 'public');

            // Optimize image
            $image = Image::read($file->getRealPath());
            $width = $image->width();
            $height = $image->height();

            // Create thumbnail
            $thumbnail = $image->scale(400, 400);
            $thumbnail->save(storage_path('app/public/thumbnails/' . $filename));

            $media = MediaLibrary::create([
                'tenant_id' => app('tenant')->id,
                'user_id' => Auth::id(),
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'disk' => 'public',
                'path' => $path,
                'url' => Storage::url($path),
                'width' => $width,
                'height' => $height,
                'folder' => $request->folder,
                'tags' => $request->tags,
            ]);

            $uploaded[] = $media;
        }

        return response()->json([
            'success' => true,
            'media' => $uploaded,
        ]);
    }

    public function destroy(MediaLibrary $media)
    {
        $this->authorize('delete', $media);

        Storage::disk($media->disk)->delete($media->path);
        Storage::disk($media->disk)->delete('thumbnails/' . $media->filename);

        $media->delete();

        return back()->with('success', 'Media deleted successfully');
    }

    public function update(MediaLibrary $media, Request $request)
    {
        $this->authorize('update', $media);

        $validated = $request->validate([
            'folder' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
        ]);

        $media->update($validated);

        return back()->with('success', 'Media updated successfully');
    }

    public function folders(Request $request)
    {
        $folders = MediaLibrary::where('tenant_id', app('tenant')->id)
            ->select('folder')
            ->distinct()
            ->pluck('folder')
            ->filter()
            ->values();

        return response()->json(['folders' => $folders]);
    }
}
