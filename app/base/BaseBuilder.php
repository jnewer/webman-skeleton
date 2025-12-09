<?php

namespace app\base;

use Illuminate\Database\Eloquent\Builder;

class BaseBuilder extends Builder
{
    /**
     * Paginate the given query.
     *
     * @param  int|null|\Closure  $perPage
     * @param  array|string  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @param  \Closure|int|null  $total
     * @return \Illuminate\Pagination\LengthAwarePaginator
     *
     * @throws \InvalidArgumentException
     */    public function customPaginate($perPage = 15, $page = null, $columns = ['*'], $pageName = 'page', $total = null)
    {
        $paginator = $this->paginate($perPage, $columns, $pageName, $page, $total);

        return [
            'items' => $paginator->items(),
            'total' => $paginator->total(),
        ];
    }
}
