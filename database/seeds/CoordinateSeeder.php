<?php

use Illuminate\Database\Seeder;

class CoordinateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=0; $i<3; $i++)
        {
            // make a coordinate for each row in each shelf
            for ($s=ord('a'); $s<ord('a')+config('fridge_storage.MAX_SHELFS'); $s++)
            {
                for ($r=0; $r<config('fridge_storage.MAX_ROWS'); $r++)
                {
                    $c = new \App\Coordinate();
                    $c->shelf = chr($s);
                    $c->column = $r;
                    $c->space_used = 0;
                    $c->location_id = $i;
                    $c->save();
                }
            }
        }
    }
}
