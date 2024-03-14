<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 *
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $password
 * @property string $email
 * @property string $phone_number
 * @property string $role
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $localisation
 *
 * @property Collection|Cart[] $carts
 * @property Collection|Comment[] $comments
 *
 * @package App\Models
 */
class User extends Model
{

	protected $table = 'users';
	public $incrementing = false;

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'first_name',
		'last_name',
		'password',
		'email',
		'phone_number',
		'role',
		'localisation'
	];

	public function carts()
	{
		return $this->hasMany(Cart::class);
	}

	public function comments()
	{
		return $this->hasMany(Comment::class);
	}
}
