<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait Uploadable
{
    public function uploadImage(UploadedFile $file, ?string $folder = null, string $disk = 'public'): string
    {
        return $file->store($folder, $disk);
    }

    public function deleteImage(string $path, string $disk = 'public'): void
    {
        Storage::disk($disk)->delete($path);
    }
}
