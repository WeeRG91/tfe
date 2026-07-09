<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;

class ImageController extends Controller
{
    /**
     * @param Image $image
     * @param ImageService $imageService
     * @return JsonResponse
     */
    public function setMainImage(Image $image, ImageService $imageService): JsonResponse
    {
        $this->authorize('update', $image);

        $imageService->setMainImage($image);

        return response()->json([
            'message' => 'Main image successfully set',
        ]);
    }

    /**
     * @param Image $image
     * @param ImageService $imageService
     * @return JsonResponse
     */
    public function destroy(Image $image, ImageService $imageService): JsonResponse
    {
        $this->authorize('delete', $image);

        $imageService->delete($image);

        return response()->json([
            'message' => 'Image successfully deleted',
        ]);
    }
}
