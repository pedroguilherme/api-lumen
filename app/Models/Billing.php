<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as ModelEloquent;
use App\Contracts\DefaultModelContracts;
use App\Traits\DefaultModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Billing extends ModelEloquent implements DefaultModelContracts
{
    use DefaultModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reference',
        'status',
        'description',
        'payment_method',
        'expiration',
        'payed_at',
        'value',
        'cred_card_info',
        'plan_pf',
        'plan_id',
        'vehicle_id',
        'publisher_id',
        'boleto_url',
        'boleto_barcode',
        'external_transaction_id'
    ];

    /**
     * The attributes that are sortable.
     *
     * @var array
     */
    protected static array $sortable = [
        'id',
        'reference',
        'status',
        'description',
        'payment_method',
        'expiration',
        'payed_at',
        'value',
        'cred_card_info',
        'plan_pf',
        'plan_id',
        'vehicle_id',
        'publisher_id',
        'external_transaction_id'
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
    private static array $like = [];

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
        'publisher'
    ];

    /**
     * @return BelongsTo
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
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
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

}
