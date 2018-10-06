<?php

namespace App\Http\Controllers;

use App\Coordinate;
use Illuminate\Http\Request;
use App\Bottle;
use App\Baby;
use Illuminate\Support\Facades\DB;

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
        // find the baby
        $baby = Baby::find($data['baby']);
        // data is valid
        $bottle = new Bottle();
        $bottle->baby_id = $baby->id;
        $bottle->available = $data['available'];
        $bottle->lastMoved = NULL;
        // figure out where to put this bottle in the system
        // get the bottles from the fridge
        $bottlesReady = $baby->bottles()->where('location', 0)->get();
        if (count($bottlesReady) < 1)
        {
            $bottle->location = 0;
        }
        else
        {
            $bottlesFridge = $baby->bottles()->where('location', 1)->get();
            $bottlesFrozen = $baby->bottles()->where('location', 2)->get();
            // compute the max amount of storage is in the fridge per kid
            $proportionalBottles = config('fridge_storage.MAX_BOTTLES')/config('fridge_storage.MAX_KIDS');
            // check the fridge for space
            if (count($bottlesFridge) < $proportionalBottles)
            {
                $bottle->location = 1;
            }
            // check the freezer for space
            else if (count($bottlesFrozen) < $proportionalBottles)
            {
                $bottle->location = 2;
            }
            // otherwise we have too much
            else {
                return response()->json(['success'=>false]);
            }
        }
        // find an open coordinate slot for them
        $coord = Coordinate::where('location_id', $bottle->location)->where('space_used', '<', config('fridge_storage.COORDINATE_SPACE'))->get();
        if (count($coord) > 0) {
            // save the coordinate id
            $bottle->coordinate_id = $coord[0]->id;
            // increment space used
            $bottle->add_coordinate();
        }
        else {
            return response()->json(['success'=>false]);
        }
        // save the object
        $bottle->save();
        // retrieve the coordinate info
        $coord = $bottle->coordinate;
        $coordStr = $coord->shelf."{$coord->column}";
        // return a success to the user
        return response()->json(['success'=>true, 'id'=>$bottle->id, 'location'=>$bottle->location, 'coordinate'=>$coordStr]);
    }

    public function delete(Bottle $bottle)
    {
        $notificationSent = false;
        // get the bottle's owner
        $baby = Baby::find($bottle->baby_id);
        // calculate storage prop
        $storageProportion = config('fridge_storage.THAW_TIME')/config('fridge_storage.EAT_RATE') - config('fridge_storage.MAX_BOTTLES')/config('fridge_storage.MAX_KIDS');
        // pull the next bottle from the next location
        for ($i=$bottle->location+1; $i<3; $i++)
        {
            // figure out which bottle to move
            if ($i == 2)
            {
                $curBottles = $baby->bottles()->where('location', $i)->where('lastMoved', '>=', DB::raw('NOW() - INTERVAL ' . config('fridge_storage.THAW_TIME')) . ' DAY')->orderBy('created_at', 'asc')->get();
            }
            else
            {
                $curBottles = $baby->bottles()->where('location', $i)->orderBy('created_at', 'asc')->get();
            }
            // if there is a bottle to move
            if (count($curBottles) > 0)
            {
                $idealDeficit = config('fridge_storage.MAX_BOTTLES')/config('fridge_storage.MAX_KIDS') - config('fridge_storage.THAW_TIME')/config('fridge_storage.EAT_RATE');
                // check if we're a freezer and if we can move a bottle down to the fridge
                if ($i == 3 && $idealDeficit > 0) {
                    $fridgeBottles = $baby->bottles()->where('location', $i-1)->where('lastMoved', '>=', DB::raw('NOW() - INTERVAL ' . config('fridge_storage.THAW_TIME')) . ' DAY')->get();
                    // check how many slots are open for thawing bottles and only move if we're allowed to
                    if (count($fridgeBottles) > $idealDeficit)
                    {
                        // move the bottle down
                        $curBottles[0]->location = $i-1;
                        // decrement old coordinate
                        $curBottles[0]->remove_coordinate();
                        // save when we moved it
                        $curBottles[0]->lastMoved = date("Y-m-d H:i:s");
                        // find an open coordinate slot for them
                        $coord = Coordinate::where('location_id', $curBottles[0]->location)->where('space_used', '<', config('fridge_storage.COORDINATE_SPACE'))->get();
                        if (count($coord) > 0) {
                            // save the coordinate id
                            $curBottles[0]->coordinate_id = $coord[0]->id;
                            // increment space used
                            $curBottles[0]->add_coordinate();
                        }
                        // save it
                        $curBottles[0]->save();
                    }
                    else {
                        if (!$notificationSent)
                        {
                            // TODO: send the notification
                            $notificationSent = true;
                        }
                    }
                }
                // if its 1 (the last bottle moved) we need to send a notification and it was from a freezer
                else if ($i == 3 && count($curBottles) == 1)
                {
                    // move the bottle down
                    $curBottles[0]->location = $i-1;
                    // decrement old coordinate
                    $curBottles[0]->remove_coordinate();
                    // save when we moved it
                    $curBottles[0]->lastMoved = date("Y-m-d H:i:s");
                    // find an open coordinate slot for them
                    $coord = Coordinate::where('location_id', $curBottles[0]->location)->where('space_used', '<', config('fridge_storage.COORDINATE_SPACE'))->get();
                    if (count($coord) > 0) {
                        // save the coordinate id
                        $curBottles[0]->coordinate_id = $coord[0]->id;
                        // increment space used
                        $curBottles[0]->add_coordinate();
                    }
                    // save it
                    $curBottles[0]->save();
                    if (!$notificationSent)
                    {
                        // TODO: send the notification
                        $notificationSent = true;
                    }
                }
                // otherwise we're fine to move things (if they exist)
                else if (count($curBottles) > 0) {
                    // move the bottle down
                    $curBottles[0]->location = $i-1;
                    // decrement old coordinate
                    $curBottles[0]->remove_coordinate();
                    // find an open coordinate slot for them
                    $coord = Coordinate::where('location_id', $curBottles[0]->location)->where('space_used', '<', config('fridge_storage.COORDINATE_SPACE'))->get();
                    if (count($coord) > 0) {
                        // save the coordinate id
                        $curBottles[0]->coordinate_id = $coord[0]->id;
                        // increment space used
                        $curBottles[0]->add_coordinate();
                    }
                    $curBottles[0]->save();
                }
            }
            else {
                if (!$notificationSent)
                {
                    // TODO: send the notification
                    $notificationSent = true;
                }
            }
        }
        // retrieve the coordinate info
        $coord = $bottle->coordinate;
        $coordStr = $coord->shelf."{$coord->column}";
        // decrement old coordinate
        $bottle->remove_coordinate();
        // delete the bottle
        $bottle->delete();
        // return a success to the user
        return response()->json(['success'=>true, 'id'=>$bottle->id, 'notification_sent'=>$notificationSent,
            'location'=>$bottle->location, 'coordinate'=>$coordStr]);
    }
}
