<?php

namespace App\Support\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait UploadsFiles
{
    public function storeFile(string $disk, string $path, UploadedFile $file, string $filename = ""): string
    {
        return Storage::disk($disk)->put($path, $file);
    }
}
