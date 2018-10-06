<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Baby extends Model
{
    public function bottles()
    {
        return $this->hasMany('App\Bottle', 'baby_id', 'id');
    }

    public function best_bottles()
    {
        return $this->hasMany('App\Bottle', 'baby_id', 'id')->orderBy('location', 'ASC')
            ->orderBy('lastMoved');
    }
}
