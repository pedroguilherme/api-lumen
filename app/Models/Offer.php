<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as ModelEloquent;
use App\Contracts\DefaultModelContracts;
use App\Traits\DefaultModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends ModelEloquent implements DefaultModelContracts
{
    use DefaultModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'client_name',
        'client_contact',
        'client_email',
        'client_init_message',
        'client_car_version_id',
        'client_car_year_manufacture',
        'client_car_year_model',
        'client_car_details',
        'client_car_discount',
        'email_sended',
        'read',
        'publisher_id',
        'vehicle_id',
    ];

    /**
     * The attributes that are sortable.
     *
     * @var array
     */
    protected static array $sortable = [
        'id',
        'type',
        'client_name',
        'client_contact',
        'client_email',
        'client_init_message',
        'client_car_version_id',
        'client_car_year_manufacture',
        'client_car_year_model',
        'client_car_details',
        'client_car_discount',
        'email_sended',
        'read',
        'publisher_id',
        'vehicle_id',
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
        'client_name',
        'client_email',
        'client_init_message',
    ];

    /**
     * The attribute to orderBy Default
     *
     * @var array
     */
    private static array $orderBy = [
        'created_at' => 'desc',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    private static array $showWith = [
        'vehicle'
    ];


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
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class)->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function version()
    {
        return $this->belongsTo(Version::class, 'client_car_version_id');
    }
}
