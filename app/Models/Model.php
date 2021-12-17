<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as ModelEloquent;
use App\Contracts\DefaultModelContracts;
use App\Traits\DefaultModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Model extends ModelEloquent implements DefaultModelContracts
{
    use DefaultModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'brand_id',
    ];

    /**
     * The attributes that are sortable.
     *
     * @var array
     */
    protected static array $sortable = [
        'id',
        'name',
        'brand_id',
    ];

    /**
     * The attributes that are sortable joins.
     *
     * @var array
     */
    protected static array $sortableHas = [];

    /**
     * The attributes that are LIKE %%.
     *
     * @var array
     */
    private static array $like = [
        'name'
    ];

    /**
     * The attribute to orderBy Default
     *
     * @var array
     */
    private static array $orderBy = [
        'name' => 'asc',
        'deleted_at' => 'asc',
    ];


    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    private static array $showWith = [
        'brand'
    ];

    /**
     * @return BelongsTo
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class)->withTrashed();
    }

    /**
     * @return HasMany
     */
    public function versions()
    {
        return $this->hasMany(Version::class)->withTrashed();;
    }

    /**
     * @return HasMany
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class)->withTrashed();
    }
}
