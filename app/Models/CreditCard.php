<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as ModelEloquent;
use App\Contracts\DefaultModelContracts;
use App\Traits\DefaultModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditCard extends ModelEloquent implements DefaultModelContracts
{
    use DefaultModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'default',
        'number',
        'encrypted',
        'publisher_id',
    ];

    /**
     * The attributes that are sortable.
     *
     * @var array
     */
    protected static array $sortable = [
        'id',
        'default',
        'number',
        'publisher_id',
    ];


    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'encrypted'
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
        'id' => 'asc',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    private static array $showWith = [];

    /**
     * @return BelongsTo
     */
    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

}
