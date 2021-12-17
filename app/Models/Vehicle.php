<?php

namespace App\Models;

use App\Helpers\Helpers;
use App\Helpers\HelpersSite;
use Illuminate\Database\Eloquent\Model as ModelEloquent;
use App\Contracts\DefaultModelContracts;
use App\Traits\DefaultModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Vehicle extends ModelEloquent implements DefaultModelContracts
{
    use DefaultModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'plate',
        'year_manufacture',
        'year_model',
        'mileage',
        'doors',
        'value',
        'description',
        'delivery',
        'ipva_paid',
        'warranty',
        'armored',
        'only_owner',
        'seven_places',
        'review',
        'spotlight',
        'publisher_id',
        'city_id',
        'fuel_id',
        'transmission_id',
        'color_id',
        'bodytype_id',
        'brand_id',
        'model_id',
        'version_id',
    ];

    /**
     * The attributes that are sortable.
     *
     * @var array
     */
    protected static array $sortable = [
        'id',
        'type',
        'brand_id',
        'model_id',
        'version_id',
        'publisher_id',
        'spotlight',
        'city_id',
        'plate',
        'year_manufacture',
        'year_model',
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
    ];

    /**
     * The attribute to orderBy Default
     *
     * @var array
     */
    private static array $orderBy = [
        'created_at' => 'desc',
        'deleted_at' => 'asc',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    private static array $showWith = [
        'city',
        'accessories',
        'fuel',
        'transmission',
        'color',
        'bodyType',
        'version',
        'model',
        'brand',
        'images',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        $seo = '';
        $seo .= ($this->type == 'C' ? 'carros' : 'outros') . '/';
        $seo .= ($this->type == 'C' ? 'usados-seminovos' : 'usadas-seminovas') . '-zerokm/';
        $seo .= (Helpers::sanitizeString(strtolower($this->city->state_id), '-')) . '/';
        $seo .= (Helpers::sanitizeString(strtolower($this->city->name), '-')) . '/';
        $seo .= (Helpers::sanitizeString(strtolower($this->brand->name), '-')) . '/';
        $seo .= (Helpers::sanitizeString(strtolower($this->model->name), '-')) . '/';
        $seo .= (Helpers::sanitizeString(strtolower($this->version->name), '-')) . '-' . $this->year_model . '/';

        return "/anuncio/" . $seo . $this->id;
    }

    /**
     * @return BelongsTo
     */
    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    /**
     * @return BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * @return BelongsToMany
     */
    public function accessories()
    {
        return $this->belongsToMany(VehicleField::class, 'vehicles_accessories', 'vehicle_id', 'accessory_id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function fuel()
    {
        return $this->belongsTo(VehicleField::class, 'fuel_id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function transmission()
    {
        return $this->belongsTo(VehicleField::class, 'transmission_id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function color()
    {
        return $this->belongsTo(VehicleField::class, 'color_id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function bodyType()
    {
        return $this->belongsTo(VehicleField::class, 'bodytype_id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function version()
    {
        return $this->belongsTo(Version::class)->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function model()
    {
        return $this->belongsTo(Model::class)->withTrashed();
    }

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
    public function images()
    {
        return $this->hasMany(File::class)->where('type', 'N')->orderBy('order', 'asc');
    }

    /**
     * @return HasMany
     */
    public function billings()
    {
        return $this->hasMany(Billing::class, 'vehicle_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function lastBilling()
    {
        return $this->hasOne(Billing::class, 'vehicle_id', 'id')->orderBy('id', 'desc');
    }
}
