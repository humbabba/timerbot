<?php

namespace App\Traits;

trait Searchable
{
    /**
     * Scope to search across specified columns.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $search
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, ?string $search, array $columns = ['name'])
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$search}%");
            }
        });
    }

    /**
     * Scope to filter by created_at date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $from
     * @param  string|null  $to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCreatedBetween($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }
}
