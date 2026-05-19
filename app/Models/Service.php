<?php

namespace App\Models;

use App\Core\Model;

class Service extends Model
{
    protected string $table = 'services';

    public function all(string $orderBy = 'sort_order', string $dir = 'ASC'): array
    {
        return parent::all($orderBy, $dir);
    }
}
