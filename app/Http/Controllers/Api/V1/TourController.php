<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ToursListRequest;
use App\Http\Resources\TourResource;
use App\Models\Tour;
use Illuminate\Http\Request;
use App\Models\Travel;
use Illuminate\Validation\Rule;
class TourController extends Controller
{
    //


    public function index(Travel $travel,ToursListRequest $request)
    {
        // return  Tour::where('travel_id', $travel->id)
        // ->orderBy('starting_date')
        // ->get();
        //above is shorter version of this

        //mainly first line
        // $travel->tours()
        // ->orderBy('starting_date')
        //  ->get();

        //second version

    //     $tours = $travel->tours()
    //     ->orderBy('starting_date')
    //     ->paginate();
    //  return TourResource::collection($tours);

    //dd($travel->tours()->toSql(), $request->all());
    // dd($request->dateTo);
//     $request->validate([
//         'priceFrom' => 'numeric',
//         'priceTo' => 'numeric',
//         'dateFrom' => 'date',
//         'dateTo' => 'date',
//         'sortBy' => Rule::in(['price']),
//         'sortOrder' => Rule::in(['asc', 'desc']),

//     ],
//     [
//         'sortBy' => 'price value accept',
//         'sortOrder' => 'expecting asc,desc value',

//     ]
// );
// Validate the request
// $validated = $request->validate([
//     'priceFrom' => 'numeric|nullable',
//     'priceTo' => 'numeric|nullable',
//     'dateFrom' => 'date|nullable',
//     'dateTo' => 'date|nullable',
//     'sortBy' => [
//         'nullable',
//         Rule::in(['price']),
//     ],
//     'sortOrder' => [
//         'nullable',
//         Rule::in(['asc', 'desc']),
//     ],
// ], [
//     'sortBy.in' => 'Only price value is accepted for sorting.',
//     'sortOrder.in' => 'Only asc or desc values are accepted for sort order.',
// ]);

        $tours =$travel->tours()
        ->when($request->priceFrom, function ($query) use ($request){
            $query->where('price','>=', $request->priceFrom * 100);
        })

        ->when($request->priceTo, function ($query) use ($request){
            $query->where('price','<=', $request->priceTo * 100);
        })
        ->when($request->dateFrom, function ($query) use ($request){
            $query->where('starting_date','>=', $request->dateFrom);
        })


        ->when($request->dateTo, function ($query) use ($request){
            $query->where('starting_date','<=', $request->dateTo);
        })
        ->when($request->sortBy && $request->sortOrder, function ($query) use ($request){
            $query->orderBy($request->sortBy, $request->sortOrder);
       })
        ->orderBy('starting_date')
        ->paginate();
        return TourResource::collection($tours);
    }
}
