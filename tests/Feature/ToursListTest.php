<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use SebastianBergmann\Type\VoidType;
use Tests\TestCase;

class ToursListTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    // public function test_example(): void
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }
    use RefreshDatabase;


    public function test_tours_list_by_travel_slug_returns_correct_tours(): void
    {
      $travel = Travel::factory()->create();
      $tour = Tour::factory()->create(['travel_id' => $travel->id]);

      $response = $this->get('api/v1/travels/'.$travel->slug.'/tours');

      $response->assertStatus(200);
      $response->assertJsonCount(1, 'data');
      $response->assertJsonFragment(['id'=> $tour->id]);
    }


    public function test_tour_price_is_shown_correctly(): void
    {
        $travel = Travel::factory()->create();
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 123.45,
        ]);
        $response = $this->get('api/v1/travels/'.$travel->slug.'/tours');

      $response->assertStatus(200);
      $response->assertJsonCount(1, 'data');
      $response->assertJsonFragment(['price'=> '123.45']);
    }
    public function test_tours_list_returns_pagination(): void
    {
        //$toursPerPAge = config('app.paginationPerPage.tours');

        $travel = Travel::factory()->create();
        Tour::factory(16)->create(['travel_id' => $travel->id]);

        $response = $this->get('api/v1/travels/'.$travel->slug.'/tours');

        $response->assertStatus(200);
        $response->assertJsonCount(15, 'data');
        $response->assertJsonPath('meta.last_page', 2);

    }


    // Test case part-4 video below

    public function test_tours_list_sorts_by_starting_date_correctly(): void
    {
        $travel = Travel::factory()->create();
        $laterTour = Tour::factory()->create(
            [
                'travel_id'=> $travel->id,
                'starting_date'=> now()->addDays(2),
                'endind_date'=> now()->addDays(3),
            ]
        );
        $earlierTour = Tour::factory()->create(
            [
                'travel_id'=> $travel->id,
                'starting_date'=> now(),
                'endind_date'=> now()->addDays(1),
            ]
        );

        $response = $this->get('api/v1/travels/'.$travel->slug.'/tours');
        $response->assertStatus(200);

        $response->assertJsonPath('data.0.id', $earlierTour->id);
        $response->assertJsonPath('data.1.id', $laterTour->id);
    }

    public function test_tours_list_sorts_by_price_correctly(): void
    {
        $travel = Travel::factory()->create();
        $expensiveTour = Tour::factory()->create(
            [
                'travel_id'=> $travel->id,
                'price'=> 200,

            ]
        );
        $cheapLaterTour = Tour::factory()->create(
            [
                'travel_id'=> $travel->id,
                'price'=> 100,
                'starting_date'=> now()->addDays(2),
                'endind_date'=> now()->addDays(3),

            ]
        );
        $cheapEarlierTour = Tour::factory()->create(
            [
                'travel_id'=> $travel->id,
                'price'=> 100,
                'starting_date'=> now(),
                'endind_date'=> now()->addDays(1),

            ]
        );
        $response = $this->get('api/v1/travels/'.$travel->slug.'/tours?sortBy=price&sortOrder=asc');
        $response->assertStatus(200);

        $response->assertJsonPath('data.0.id', $cheapEarlierTour->id);
        $response->assertJsonPath('data.1.id', $cheapLaterTour->id);
        $response->assertJsonPath('data.2.id', $expensiveTour->id);

    }


    public function test_tours_list_filters_by_price_correctly(): void
    {
        $travel = Travel::factory()->create();
        $expensiveTour = Tour::factory()->create(
            [
                'travel_id'=> $travel->id,
                'price'=> 200,

            ]
        );
        $cheapTour = Tour::factory()->create(
            [
                'travel_id'=> $travel->id,
                'price'=> 100,
            ]
        );
        $endpoint = 'api/v1/travels/'.$travel->slug.'/tours';
        $response = $this->get($endpoint.'?priceFrom=100');
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id'=>$cheapTour->id]);
        $response->assertJsonFragment(['id'=>$expensiveTour->id]);



        $response = $this->get($endpoint.'?priceFrom=150');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id'=>$cheapTour->id]);
        $response->assertJsonFragment(['id'=>$expensiveTour->id]);

        $response = $this->get($endpoint.'?priceFrom=250');
        $response->assertJsonCount(0, 'data');

        $response = $this->get($endpoint.'?priceTo=200');
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id'=>$cheapTour->id]);
        //changed the error
        //$response->assertJsonMissing(['id'=>$cheapTour->id]);
        $response->assertJsonFragment(['id'=>$expensiveTour->id]);

        $response = $this->get($endpoint.'?priceTo=150');
        $response->assertJsonCount(1, 'data');
         $response->assertJsonMissing(['id'=>$expensiveTour->id]);
         $response->assertJsonFragment(['id'=>$cheapTour->id]);
        //Changed the eroor below
        //$response->assertJsonFragment(['id'=>$expensiveTour->id]);
        //$response->assertJsonMissing(['id'=>$cheapTour->id]);

        $response = $this->get($endpoint.'?priceTo=50');
        $response->assertJsonCount(0, 'data');


        $response = $this->get($endpoint.'?priceFrom=150&priceTo=250');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id'=>$cheapTour->id]);
        $response->assertJsonFragment(['id'=>$expensiveTour->id]);
    }

    public function test_tours_list_filters_by_starting_date_correctly(): void
    {
        $travel = Travel::factory()->create();
        $laterTour = Tour::factory()->create(
            [
                'travel_id'=> $travel->id,
                'starting_date'=> now()->addDays(2),
                'endind_date'=> now()->addDays(3),
            ]
        );
        $earlierTour = Tour::factory()->create(
            [
                'travel_id'=> $travel->id,
                'starting_date'=> now(),
                'endind_date'=> now()->addDays(1),
            ]
        );

        $endpoint = 'api/v1/travels/'.$travel->slug.'/tours';
        $response = $this->get($endpoint.'?dateFrom='.now());
        // dd($response->json());
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id'=>$earlierTour->id]);
        //changed below error
       // $response->assertJsonMissing(['id'=>$earlierTour->id]);
        $response->assertJsonFragment(['id'=>$laterTour->id]);

        $response = $this->get($endpoint.'?dateFrom='.now()->addDay());
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id'=>$earlierTour->id]);
        $response->assertJsonFragment(['id'=>$laterTour->id]);

        $response = $this->get($endpoint.'?dateFrom='.now()->addDays(5));
        $response->assertJsonCount(0, 'data');


        $response = $this->get($endpoint.'?dateTo='.now()->addDays(5));
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id'=>$earlierTour->id]);
        //changed error below
       // $response->assertJsonMissing(['id'=>$earlierTour->id]);
        $response->assertJsonFragment(['id'=>$laterTour->id]);

        $response = $this->get($endpoint.'?dateTo='.now()->addDay());
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id'=>$laterTour->id]);
        $response->assertJsonFragment(['id'=>$earlierTour->id]);
        //changed here
        //$response->assertJsonMissing(['id'=>$earlierTour->id]);
        //$response->assertJsonFragment(['id'=>$laterTour->id]);

        $response = $this->get($endpoint.'?dateTo='.now()->subDay());
        //changehere error
        //$response = $this->get($endpoint.'?dateTo'.now()->subDay());
        $response->assertJsonCount(0, 'data');

        $response = $this->get($endpoint.'?dateFrom='.now()->addDay().'&dateTo'.now()->addDays(5));
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id'=>$earlierTour->id]);
        $response->assertJsonFragment(['id'=>$laterTour->id]);
    }

//I have typo error below and changed now ,so it will not call
//public function tets_tour_lists_returns_validation_errors(): void
    public function tests_tour_list_returns_validation_errors(): void
    {
        $travel = Travel::factory()->create();

        $response = $this->getJson(
             'api/v1/travels/'.$travel->slug.'/tours?dateFrom=abcde');
        $response->assertStatus(422);

        $response = $this->getJson( 'api/v1/travels/'.$travel->slug.'/tours?priceFrom=abcde');
        $response->assertStatus(422);
    }
}
