<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as ModelEloquent;
use App\Contracts\DefaultModelContracts;
use App\Traits\DefaultModel;

class Plan extends ModelEloquent implements DefaultModelContracts
{
    use DefaultModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'name',
        'description',
        'normal',
        'silver',
        'gold',
        'diamond',
        'recurrence',
        'fantasy_value',
        'value',
    ];

    /**
     * The attributes that are sortable.
     *
     * @var array
     */
    protected static array $sortable = [
        'id',
        'type',
        'name',
        'description',
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
    private static array $showWith = [];

}
