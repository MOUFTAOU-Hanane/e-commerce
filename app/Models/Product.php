<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 * 
 * @property string $id
 * @property string $category_id
 * @property string $name
 * @property string $description
 * @property int $in_stock
 * @property int $available_stock
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $price
 * 
 * @property Category $category
 * @property Collection|Cart[] $carts
 * @property Collection|Comment[] $comments
 * @property Collection|ImagesProduct[] $images_products
 *
 * @package App\Models
 */
class Product extends Model
{
	protected $table = 'products';
	public $incrementing = false;

	protected $casts = [
		'in_stock' => 'int',
		'available_stock' => 'int',
		'price' => 'int'
	];

	protected $fillable = [
		'category_id',
		'name',
		'description',
		'in_stock',
		'available_stock',
		'price'
	];

	public function category()
	{
		return $this->belongsTo(Category::class);
	}

	public function carts()
	{
		return $this->hasMany(Cart::class);
	}

	public function comments()
	{
		return $this->hasMany(Comment::class);
	}

	public function images_products()
	{
		return $this->hasMany(ImagesProduct::class);
	}
}
