<?php

namespace App\Traits;

use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasImages
{
    /**
     * @return void
     */
    public function uploadImage(): void
    {
        $request = app('request');

        $folder = property_exists($this, 'folder') && $this->folder ? $this->folder : 'images/' . Str::snake(class_basename($this));

        $inputName = property_exists($this, 'imageInput') && $this->imageInput ? $this->imageInput : 'images';

        if ($request->hasFile($inputName)) {
            foreach ($request->file($inputName) as $image) {
                $fileName = Str::uuid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs($folder, $fileName, 'public');

                $this->images()->create([
                    'name' => $image->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $image->getClientMimeType(),
                    'size' => $image->getSize(),
                ]);
            }
        }
    }


    /**
     * @param Image $image
     * @return void
     */
    public function deleteImage(Image $image): void
    {
        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }
        $image->delete();
    }

    /**
     * @return void
     */
    public function deleteAllImages(): void
    {
        foreach ($this->images as $image) {
            $this->deleteImage($image);
        }
    }
}
