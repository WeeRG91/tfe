<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * @param Image $image
     * @return RedirectResponse
     */
    public function destroy(Image $image)
    {
        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        $image->delete();

        return redirect()->back()->with('success', 'Image deleted successfully.');
    }
}
