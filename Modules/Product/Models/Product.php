<?php

namespace Modules\Product\Models;

use App\Support\Media\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Category\Models\Category;
use Modules\Vendor\Models\Vendor;
use Spatie\MediaLibrary\HasMedia;


class Product extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, HasFactory;

    protected static function newFactory()
    {
        return \Modules\Product\Database\Factories\ProductFactory::new();
    }

    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'slug',
        'description',
        'sku',
        'price',
        'discounted_price',
        'stock',
        'attributes',
        'clothing_attributes',
        'keywords',
        'sales_count',
        'avg_rating',
        'rating_count',
        'status',
    ];

    protected $casts = [
        'attributes' => 'array',
        'clothing_attributes' => 'array',
        'price' => 'decimal:2',
        'discounted_price' => 'decimal:2',
        'avg_rating' => 'decimal:2',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class, 'color_product');
    }

    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'product_size');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }
}
