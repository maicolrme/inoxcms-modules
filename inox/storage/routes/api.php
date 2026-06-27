<?php

use Illuminate\Support\Facades\Route;
use Inox\Storage\Models\Media;

Route::middleware('auth:sanctum')->prefix('api')->name('api.')->group(function () {
    Route::get('/storage', function () {
        return Media::orderBy('created_at', 'desc')->paginate(24);
    })->name('storage.index');

    Route::post('/storage', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'file' => 'required|file|mimes:' . implode(',', config('storage.allowed_mimes', [])) . '|max:' . config('storage.max_file_size', 10240),
        ]);

        $file = $request->file('file');
        $path = $file->store('/' . date('Y/m'), 'storage');

        $media = Media::create([
            'user_id' => $request->user()->id,
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => 'storage',
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json($media, 201);
    })->name('storage.store');

    Route::get('/storage/{media}', function (Media $media) {
        return $media;
    })->name('storage.show');

    Route::put('/storage/{media}', function (\Illuminate\Http\Request $request, Media $media) {
        $validated = $request->validate([
            'name' => 'sometimes|max:255',
            'alt_text' => 'nullable|max:500',
            'caption' => 'nullable|max:1000',
        ]);

        $media->update($validated);

        return response()->json($media);
    })->name('storage.update');

    Route::delete('/storage/{media}', function (Media $media) {
        \Illuminate\Support\Facades\Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return response()->json(['message' => 'Media deleted.']);
    })->name('storage.destroy');
});
