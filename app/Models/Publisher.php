<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as ModelEloquent;
use App\Contracts\DefaultModelContracts;
use App\Traits\DefaultModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Publisher extends ModelEloquent implements DefaultModelContracts
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
        'company_name',
        'cpf_cnpj',
        'cep',
        'number',
        'neighborhood',
        'address',
        'complement',
        'description',
        'payment_method',
        'payment_situation',
        'api_token',
        'work_schedule',
        'plan_id',
        'city_id',
        'deleted_reason',
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
        'company_name',
        'payment_situation',
        'cpf_cnpj',
        'plan_id',
        'city_id',
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
        'name',
        'company_name',
        'cpf_cnpj',
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
        'plan',
        'contacts',
        'city.state',
        'user',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'logo',
    ];

    protected $appends = ['logo_url', 'free_active_date_format'];

    public function getLogoUrlAttribute()
    {
        if (!empty($this->logo)) {
            return Storage::disk('s3')->url($this->logo);
        } else {
            return null;
        }
    }

    public function getFreeActiveDateFormatAttribute()
    {
        if (!empty($this->free_active_date)) {
            return Carbon::parse($this->free_active_date)->format('d/m/Y');
        } else {
            return null;
        }
    }

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
    public function futurePlan()
    {
        return $this->belongsTo(Plan::class, 'future_plan_id');
    }

    /**
     * @return BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * @return HasMany
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * @return HasMany
     */
    public function phones()
    {
        return $this->contacts()->whereIn('key', ['contactWhatsapp', 'telephone'])->orderBy('key', 'desc');
    }

    /**
     * @return HasMany
     */
    public function emails()
    {
        return $this->contacts()->whereIn('key', ['offerEmail', 'contactEmail'])->orderBy('key');
    }

    /**
     * @return HasMany
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * @return HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class)->withTrashed();
    }

    /**
     * @return HasOne
     */
    public function creditCardDefault()
    {
        return $this->hasOne(CreditCard::class, 'publisher_id', 'id')->where('default', true);
    }

    /**
     * @return HasMany
     */
    public function creditCards()
    {
        return $this->hasMany(CreditCard::class, 'publisher_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function billings()
    {
        return $this->hasMany(Billing::class, 'publisher_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function lastBilling()
    {
        return $this->hasOne(Billing::class, 'publisher_id', 'id')->orderBy('id', 'desc');
    }

}
