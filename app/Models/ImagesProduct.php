<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ImagesProduct
 * 
 * @property string $id
 * @property string $product_id
 * @property string $image
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Product $product
 *
 * @package App\Models
 */
class ImagesProduct extends Model
{
	protected $table = 'images_product';
	public $incrementing = false;

	protected $fillable = [
		'product_id',
		'image'
	];

	public function product()
	{
		return $this->belongsTo(Product::class);
	}
}
