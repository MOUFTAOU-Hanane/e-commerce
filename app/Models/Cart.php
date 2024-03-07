<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Cart
 * 
 * @property string $id
 * @property string $product_id
 * @property string $user_id
 * @property bool $is_paid
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Product $product
 * @property User $user
 *
 * @package App\Models
 */
class Cart extends Model
{
	protected $table = 'carts';
	public $incrementing = false;

	protected $casts = [
		'is_paid' => 'bool'
	];

	protected $fillable = [
		'product_id',
		'user_id',
		'is_paid'
	];

	public function product()
	{
		return $this->belongsTo(Product::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
