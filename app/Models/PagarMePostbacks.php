<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as ModelEloquent;
use App\Contracts\DefaultModelContracts;
use App\Traits\DefaultModel;

class PagarMePostbacks extends ModelEloquent implements DefaultModelContracts
{
    use DefaultModel;

    protected $table = 'pagarme_postbacks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'postback',
    ];

    /**
     * The attributes that are sortable.
     *
     * @var array
     */
    protected static array $sortable = [
        'id',
        'type',
        'postback',
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
    private static array $orderBy = [];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    private static array $showWith = [];

}
