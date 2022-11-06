<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'user_id'];

    /**
     * Get the path of file.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function path(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => 'app\\' . config('file.directory') . '\\' . $attributes['name'],
        );
    }

    /**
     * Get the user that owns the file.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include files for the logged in user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeLoggedInUser($query)
    {
        $query->where('user_id', auth()->id());
    }
}
