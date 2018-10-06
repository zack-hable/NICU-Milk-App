<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bottle extends Model
{
    public function coordinate()
    {
        return $this->hasOne('App\Coordinate', 'id', 'coordinate_id');
    }

    public function baby()
    {
        return $this->hasOne('App\Baby', 'id', 'baby_id');
    }

    public function remove_coordinate()
    {
        return $this->hasOne('App\Coordinate', 'id', 'coordinate_id')->decrement('space_used');
    }

    public function add_coordinate()
    {
        return $this->hasOne('App\Coordinate', 'id', 'coordinate_id')->increment('space_used');
    }
}
