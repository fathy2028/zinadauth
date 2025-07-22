<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\HasRolePermissions;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasRolePermissions, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_name',
        'image',
        'type',
        'theme',
        'user_id',
        'web_engine',
        'is_active',
        'is_deleted',
        'last_signed_in',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_signed_in' => 'datetime',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
        'id' => 'string',
        'user_id' => 'string',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'created_by', 'id');
    }

    public function workshops(): BelongsToMany
    {
        return $this->belongsToMany(Workshop::class, 'user_workshops')
            ->using(new class extends Pivot {
                use HasUuids;
            })->withPivot(['status']);
    }
}
