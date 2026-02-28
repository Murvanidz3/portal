<?php

namespace App\Services;

use App\Models\Car;
use App\Models\CarFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    protected array $allowedImageTypes = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
    protected array $allowedVideoTypes = ['mp4', 'webm', 'mov'];
    protected array $allowedDocTypes = ['pdf', 'doc', 'docx'];
    protected int $maxFileSize = 10 * 1024 * 1024; // 10MB

    /**
     * Upload car photos.
     */
    public function uploadCarPhotos(Car $car, array $files, string $category = 'auction'): array
    {
        $uploaded = [];
        $vin = trim($car->vin);
        $uploadPath = 'uploads/' . $vin;

        foreach ($files as $index => $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            if (!$this->validateFile($file)) {
                continue;
            }

            $result = $this->uploadFile($file, $uploadPath, $index);

            if ($result) {
                $carFile = CarFile::create([
                    'car_id' => $car->id,
                    'file_path' => $result['path'],
                    'file_type' => $result['type'],
                    'category' => $category,
                ]);

                $uploaded[] = $carFile;

                // Set as main photo if car doesn't have one
                if (empty($car->main_photo) && $result['type'] === 'image') {
                    $car->main_photo = $result['path'];
                    $car->save();
                }
            }
        }

        return $uploaded;
    }

    /**
     * Upload a single file.
     */
    public function uploadFile(UploadedFile $file, string $path, int $index = 0): ?array
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());
            $filename = time() . '_' . $index . '_' . Str::random(8) . '.' . $extension;

            // Check if it's an image and needs processing
            if ($this->getFileType($extension) === 'image') {
                // Change extension to jpg for consistency if we process it
                $filename = pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
                $fullPath = $path . '/' . $filename;

                if ($this->processAndSaveImage($file, $fullPath)) {
                    return [
                        'path' => $fullPath,
                        'type' => 'image',
                        'original_name' => $file->getClientOriginalName(),
                        'size' => Storage::disk('public')->size($fullPath),
                    ];
                }
            }

            // Fallback or non-image types
            $filePath = $file->storeAs($path, $filename, 'public');

            if (!$filePath) {
                return null;
            }

            return [
                'path' => $filePath,
                'type' => $this->getFileType($extension),
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ];
        } catch (\Exception $e) {
            \Log::error('File upload failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);
            return null;
        }
    }

    /**
     * Process and save image with resizing and compression.
     */
    protected function processAndSaveImage(UploadedFile $file, string $path): bool
    {
        try {
            // Read image using Intervention Image
            $image = \Intervention\Image\Laravel\Facades\Image::read($file->getRealPath());

            // Get original dimensions
            $width = $image->width();
            $height = $image->height();

            // Resize if larger than 1920px on the longest side (preserving aspect ratio)
            $maxDimension = 1920;
            if ($width > $maxDimension || $height > $maxDimension) {
                if ($width > $height) {
                    $image->scale(width: $maxDimension);
                } else {
                    $image->scale(height: $maxDimension);
                }
            }

            // Encode to JPEG with 85% quality
            $encoded = $image->encode(new \Intervention\Image\Encoders\JpegEncoder(quality: 85));

            // Save to storage
            Storage::disk('public')->put($path, (string) $encoded);

            return true;
        } catch (\Exception $e) {
            \Log::error('Image processing failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Delete a car file.
     */
    public function deleteCarFile(CarFile $file): bool
    {
        try {
            // Delete from storage
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }

            // Check if this is the main photo
            $car = $file->car;
            if ($car && $car->main_photo === $file->file_path) {
                // Find another photo to set as main
                $newMain = $car->files()
                    ->where('id', '!=', $file->id)
                    ->where('file_type', 'image')
                    ->first();

                $car->main_photo = $newMain?->file_path;
                $car->save();
            }

            // Delete from database
            $file->delete();

            return true;
        } catch (\Exception $e) {
            \Log::error('File deletion failed', [
                'error' => $e->getMessage(),
                'file_id' => $file->id,
            ]);
            return false;
        }
    }

    /**
     * Delete all files for a car.
     */
    public function deleteAllCarFiles(Car $car): int
    {
        $count = 0;

        foreach ($car->files as $file) {
            if ($this->deleteCarFile($file)) {
                $count++;
            }
        }

        // Also try to delete the upload directory
        $vin = trim($car->vin);
        $uploadPath = 'uploads/' . $vin;

        if (Storage::disk('public')->exists($uploadPath)) {
            Storage::disk('public')->deleteDirectory($uploadPath);
        }

        return $count;
    }

    /**
     * Validate file before upload.
     */
    public function validateFile(UploadedFile $file): bool
    {
        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            return false;
        }

        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedTypes = array_merge(
            $this->allowedImageTypes,
            $this->allowedVideoTypes,
            $this->allowedDocTypes
        );

        if (!in_array($extension, $allowedTypes)) {
            return false;
        }

        // Additional check for images
        if (in_array($extension, $this->allowedImageTypes)) {
            $imageInfo = @getimagesize($file->getPathname());
            if ($imageInfo === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get file type from extension.
     */
    protected function getFileType(string $extension): string
    {
        if (in_array($extension, $this->allowedImageTypes)) {
            return 'image';
        }

        if (in_array($extension, $this->allowedVideoTypes)) {
            return 'video';
        }

        if (in_array($extension, $this->allowedDocTypes)) {
            return 'document';
        }

        return 'other';
    }

    /**
     * Get file URL.
     */
    public function getFileUrl(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // Use direct storage URL instead of route
        return Storage::url($path);
    }

    /**
     * Clean orphaned files.
     */
    public function cleanOrphanedFiles(): int
    {
        $count = 0;
        $uploadsPath = storage_path('app/public/uploads');

        if (!is_dir($uploadsPath)) {
            return 0;
        }

        $directories = glob($uploadsPath . '/*', GLOB_ONLYDIR);

        foreach ($directories as $dir) {
            $vin = basename($dir);

            // Check if car exists
            $carExists = Car::where('vin', $vin)->exists();

            if (!$carExists) {
                // Delete orphaned directory
                $this->deleteDirectory($dir);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Delete directory recursively.
     */
    protected function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        return rmdir($dir);
    }
}
