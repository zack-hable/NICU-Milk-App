<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Bottle;
use App\Baby;

class BottleController extends Controller
{
    public function listAll()
    {
        $bottles = Bottle::all();
        return response()->json($bottles);
    }

    public function insert(Request $request)
    {
        $data = $request->validate([
            'baby' => 'required|exists:babies,id',
            'available' => 'required|integer',
        ]);
        // data is valid
        $bottle = new Bottle();
        $bottle->baby_id = Baby::find($data['baby'])->id;
        $bottle->available = $data['available'];
        // figure out where to put this bottle in the system
        $bottle->location = 0;
        // save the object
        $bottle->save();
        // return a success to the user
        return response()->json(['success'=>true, 'id'=>$bottle->id]);
    }

    public function delete(Bottle $bottle)
    {
        $bottle->delete();
        // return a success to the user
        return response()->json(['success'=>true, 'id'=>$bottle->id]);
    }
}
