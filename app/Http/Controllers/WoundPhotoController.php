<?php

namespace App\Http\Controllers;

use App\Models\WcWoundPhoto;
use Illuminate\Support\Facades\Storage;

class WoundPhotoController extends Controller
{
    /**
     * Serve photo files safely from storage.
     */
    public function show(string $photoId)
    {
        $photo = WcWoundPhoto::findOrFail($photoId);

        if (! Storage::disk('public')->exists($photo->WC_PHOTO_PATH)) {
            abort(404, 'صورة الجرح غير موجودة');
        }

        $fullPath = Storage::disk('public')->path($photo->WC_PHOTO_PATH);
        $mimeType = Storage::disk('public')->mimeType($photo->WC_PHOTO_PATH);

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
