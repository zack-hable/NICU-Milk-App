<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Baby extends Model
{
    public function bottles()
    {
        return $this->hasMany('App\Bottle');
    }
}
