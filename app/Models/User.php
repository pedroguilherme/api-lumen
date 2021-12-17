<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as ModelEloquent;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use App\Contracts\DefaultModelContracts;
use App\Traits\DefaultModel;

class User extends ModelEloquent implements AuthenticatableContract, AuthorizableContract, JWTSubject, DefaultModelContracts
{
    use Authenticatable, Authorizable, DefaultModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'name',
        'email',
        'publisher_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token'
    ];

    /**
     * The attributes that are sortable.
     *
     * @var array
     */
    protected static array $sortable = [
        'id',
        'type',
        'email',
    ];

    /**
     * The attributes that are LIKE %%.
     *
     * @var array
     */
    private static array $like = [
        'email'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The attribute to orderBy Default
     *
     * @var array
     */
    private static array $orderBy = [
        'email' => 'asc',
        'deleted_at' => 'asc',
    ];

    /**
     * The attributes that are sortable joins.
     *
     * @var array
     */
    protected static array $sortableHas = [];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    private static array $showWith = [];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * @return BelongsTo
     */
    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }
}
