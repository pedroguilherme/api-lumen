<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OwenIt\Auditing\Auditable;
use DateTimeInterface;

trait DefaultModel
{
    use Auditable, SoftDeletes, HasFactory;

    /**
     * @param Request $request
     * @return DefaultModel
     */
    public function applyRequest(Request $request)
    {
        return $this->apply($request->all());
    }

    /**
     * @param array $filters
     * @return DefaultModel
     */
    public function applyShowWith(array $filters = [])
    {
        $query = $this->apply($filters);

        if (isset(self::$showWith)) {
            foreach (self::$showWith as $with) {
                $query = $query->with($with);
            }
        }

        return $query;
    }

    /**
     * @param array $filters
     * @return DefaultModel
     */
    public function apply(array $filters = [])
    {
        $query = $this;

        if (isset($filters['active']) && !empty($filters['active'])) {
            $query = $filters['active'] == "true" ? $query : $query->onlyTrashed();
        } else {
            $query = $query->withTrashed();
        }

        if (isset($filters['orderBy']) && !empty($filters['orderBy'])) {
            [$column, $direction] = explode('|', $filters['orderBy']);
            $query = $query->orderBy($column, $direction);
        } else {
            foreach (self::$orderBy as $column => $direction) {
                $query = $query->orderBy($column, $direction);
            }
        }

        $filtersHas = Arr::only($filters, array_keys(self::$sortableHas));

        foreach ($filtersHas as $column => $value) {
            if (!empty($value)) {
                $relation = Arr::get(self::$sortableHas, $column);
                $query = $query->whereHas($relation, function ($where) use ($column, $value) {
                    $where->where($column, $value);
                });
            }
        }

        $filters = Arr::only($filters, self::$sortable);

        foreach ($filters as $param => $value) {
            if (!empty($value)) {
                if (array_search($param, self::$like) !== false) {
                    $query = $query->where($param, 'ILIKE', '%' . $value . '%');
                } else {
                    $query = $query->where($param, '=', $value);
                }
            }
        }

        return $query;
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');

    }
}
