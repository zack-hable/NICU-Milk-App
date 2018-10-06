<?php

namespace App\Http\Controllers;

use App\Baby;
use Illuminate\Http\Request;

class BabyController extends Controller
{
    public function listAll()
    {
        $babies = Baby::all();
        return response()->json($babies);
    }

    public function listBottle(Baby $baby)
    {
        $bottles = $baby->bottles;
        return response()->json($bottles);
    }

    public function bestBottle(Baby $baby)
    {
        // return the best bottle for the baby
        $bottles = $baby->best_bottles;
        return response()->json($bottles[0]);
    }

    public function insert(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:255',
            'nextFeed' => 'required|integer',
        ]);
        // data is valid
        $baby = new Baby();
        $baby->name = $data['name'];
        $baby->nextFeed = $data['nextFeed'];
        // lets make sure the value they entered is in the future
        $now = strtotime('now');
        while ($baby->nextFeed < $now) {
            $baby->nextFeed += 60*60*3;
        }
        // convert the time from seconds to a date object
        $baby->nextFeed = date("Y-m-d H:i:s", $baby->nextFeed);
        // save the baby
        $baby->save();
        // return a success to the user
        return response()->json(['success'=>true, 'id'=>$baby->id]);
    }

    public function delete(Baby $baby)
    {
        $baby->delete();
        // return a success to the user
        return response()->json(['success'=>true, 'id'=>$baby->id]);
    }
}
