<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as ModelEloquent;
use App\Contracts\DefaultModelContracts;
use App\Traits\DefaultModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleField extends ModelEloquent implements DefaultModelContracts
{
    use DefaultModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vehicle_type',
        'key',
        'value',
    ];

    /**
     * The attributes that are sortable.
     *
     * @var array
     */
    protected static array $sortable = [
        'id',
        'vehicle_type',
        'key',
        'value',
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
        'value'
    ];

    /**
     * The attribute to orderBy Default
     *
     * @var array
     */
    private static array $orderBy = [
        'value' => 'asc',
        'deleted_at' => 'asc',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    private static array $showWith = [];

    /**
     * @return HasMany
     */
    public function vehiclesFuels()
    {
        return $this->hasMany(Vehicle::class, 'fuel_id')->withTrashed();
    }

    /**
     * @return HasMany
     */
    public function vehiclesTransmissions()
    {
        return $this->hasMany(Vehicle::class, 'transmission_id')->withTrashed();
    }

    /**
     * @return HasMany
     */
    public function vehiclesColors()
    {
        return $this->hasMany(Vehicle::class, 'color_id')->withTrashed();
    }

    /**
     * @return HasMany
     */
    public function vehiclesBodyType()
    {
        return $this->hasMany(Vehicle::class, 'bodytype_id')->withTrashed();
    }

    /**
     * @return HasMany
     */
    public function vehiclesAccessories()
    {
        return $this->belongsToMany(Vehicle::class, 'vehicles_accessories', 'accessory_id', 'vehicle_id')->withTrashed();
    }

}
