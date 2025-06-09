<?php

namespace Kartikey\Core\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BunnyCdnService
{
    public static function uploadFiles(array $files, $folder = 'media')
    {
        $uploadedUrls = [];

        foreach ($files as $file) {
            $randomFileName = Str::random(16) . '.' . $file->getClientOriginalExtension();
            $filePath = "{$folder}/{$randomFileName}";

            $uploadSuccess = Storage::disk('bunnycdn')->put($filePath, file_get_contents($file));

            if ($uploadSuccess) {
                $uploadedUrls[] = rtrim(config('filesystems.disks.bunnycdn.url'), '/') . '/' . ltrim($filePath, '/');
            } else {
                throw new \Exception('File upload failed.');
            }
        }

        return $uploadedUrls;
    }
}
