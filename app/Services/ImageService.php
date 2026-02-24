<?php

namespace App\Services;

use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * @param $model
     * @param array $files
     * @param string|null $folder
     * @return void
     */
    public function upload($model, array $files, string $folder = null): void
    {
        $folder ??= 'images/' . Str::snake(class_basename($model));

        foreach ($files as $file) {
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($folder, $fileName, 'public');

            $model->images()->create([
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    /**
     * @param Image $image
     * @return void
     */
    public function delete(Image $image): void
    {
        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }
        $image->delete();
    }
}
