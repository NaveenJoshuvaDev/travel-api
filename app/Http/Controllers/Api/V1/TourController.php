<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TourResource;
use App\Models\Tour;
use Illuminate\Http\Request;
use App\Models\Travel;

class TourController extends Controller
{
    //


    public function index(Travel $travel)
    {
        // return  Tour::where('travel_id', $travel->id)
        // ->orderBy('starting_date')
        // ->get();
        //above is shorter version of this

        //mainly first line
        // $travel->tours()
        // ->orderBy('starting_date')
        //  ->get();

        //final version

        $tours = $travel->tours()
        ->orderBy('starting_date')
        ->paginate();
     return TourResource::collection($tours);
    }
}   
