<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CarFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'file_path',
        'file_type',
        'category',
    ];

    // Category constants
    const CATEGORY_AUCTION = 'auction';
    const CATEGORY_PICKUP = 'pickup';
    const CATEGORY_WAREHOUSE = 'warehouse';
    const CATEGORY_POTI = 'poti';

    // Legacy category mappings (for old data)
    const CATEGORY_PORT = 'port';  // maps to warehouse
    const CATEGORY_TERMINAL = 'terminal';  // maps to poti

    // Type constants
    const TYPE_IMAGE = 'image';
    const TYPE_DOCUMENT = 'document';
    const TYPE_VIDEO = 'video';

    public static function getCategories(): array
    {
        return [
            self::CATEGORY_AUCTION => 'აუქციონი',
            self::CATEGORY_PICKUP => 'აყვანა',
            self::CATEGORY_WAREHOUSE => 'საწყობი',
            self::CATEGORY_POTI => 'ფოთი',
        ];
    }

    public static function getAllCategories(): array
    {
        // Include legacy categories for display
        return [
            self::CATEGORY_AUCTION => 'აუქციონი',
            self::CATEGORY_PICKUP => 'აყვანა',
            self::CATEGORY_WAREHOUSE => 'საწყობი',
            self::CATEGORY_PORT => 'საწყობი',  // legacy
            self::CATEGORY_POTI => 'ფოთი',
            self::CATEGORY_TERMINAL => 'ფოთი',  // legacy
        ];
    }

    // Relationships
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    // Scopes
    public function scopeImages($query)
    {
        return $query->where('file_type', self::TYPE_IMAGE);
    }

    public function scopeDocuments($query)
    {
        return $query->where('file_type', self::TYPE_DOCUMENT);
    }

    public function scopeVideos($query)
    {
        return $query->where('file_type', self::TYPE_VIDEO);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Accessors
    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->file_path, 'http')) {
            return $this->file_path;
        }

        // Use direct storage URL
        return Storage::url($this->file_path);
    }

    public function getFilenameAttribute(): string
    {
        return basename($this->file_path);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::getAllCategories()[$this->category] ?? 'უცნობი';
    }

    // Helpers
    public function isImage(): bool
    {
        return $this->file_type === self::TYPE_IMAGE;
    }

    public function isVideo(): bool
    {
        return $this->file_type === self::TYPE_VIDEO;
    }

    public function isDocument(): bool
    {
        return $this->file_type === self::TYPE_DOCUMENT;
    }

    public function delete()
    {
        // Try to delete the physical file using Storage facade
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }

        return parent::delete();
    }
}
