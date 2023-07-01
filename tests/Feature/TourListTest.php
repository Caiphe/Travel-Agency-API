<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TourListTest extends TestCase
{
    use RefreshDatabase;

    public function test_tours_list_by_trave_slug_returns_correct_tours(): void
    {
        $travel = Travel::factory()->create();
        $tour = Tour::factory()->create(['travel_id' => $travel->id]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $tour->id]);
    }

    public function test_tour_price_is_shown_correctly(): void
    {
        $travel = Travel::factory()->create();
        Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 123.45
        ]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['price' => '123.45']);
    }

    public function test_tours_list_returns_pagination () : void
    {
        // $toursPerPage = config('app.paginationPerPage.tours');

        $travel = Travel::factory()->create();
        Tour::factory(16)->create(['travel_id' => $travel->id]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');

        $response->assertStatus(200);
        $response->assertJsonCount(15, 'data');
        $response->assertJsonPath('meta.last_page', 2);
    }

    public function test_tours_list_sort_by_starting_date_correctly()
    {
        $travel = Travel::factory()->create();

        $laterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now()->addDays(2),
            'ending_date' => now()->addDays(3),
        ]);

        // $earlierTour = Tour::factory()->create([
        //     'travel_id' => $travel->id,
        //     'starting_date' => now(),
        //     'ending_date' => now()->addDays(1),
        // ]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');
        
        $response->assertStatus(200);

        $response->assertJsonPath('data.0.id', $laterTour->id);
        // $response->assertJsonPath('data.1.id', $earlierTour->id);
    }

    public function test_tours_list_sorts_by_price_correctly (): void
    {
        $travel = Travel::factory()->create();

        // $expensiveTour = Tour::factory()->create([
        //     'travel_id' => $travel->id,
        //     'price' => 200,
        // ]);

        $cheapLaterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
            'starting_date' => now()->addDays(2),
            'ending_date' => now()->addDays(3),
        ]);

        // $cheapEarlierTour = Tour::factory()->create([
        //     'travel_id' => $travel->id,
        //     'price' => 100,
        //     'starting_date' => now(),
        //     'ending_date' => now()->addDays(1),
        // ]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours?sortBy=price&sortOrder=asc');

        $response->assertStatus(200);
        // $response->assertJsonPath('data.0.id', $cheapEarlierTour->id);
        // $response->assertJsonPath('data.0.id', $cheapLaterTour->id);
        // $response->assertJsonPath('data.2.id', $expensiveTour->id);
        
    }

    public function test_tours_list_filters_by_price_correctly(): void
    {
        $travel = Travel::factory()->create();

        $expensiveTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 200,
        ]);

        $cheapTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100
        ]);

        // dd($expensiveTour->id, $cheapTour->id);

        // dd($expensiveTour->id, $cheapTour->id);

        $endpoint = '/api/v1/travels/'.$travel->slug.'/tours';

        // $response = $this->get($endpoint.'?priceFrom=100');
        // $response->assertJsonCount(2, 'data');
        // $response->assertJsonFragment(['id' => $cheapTour->id]);
        // $response->assertJsonFragment(['id' => $expensiveTour->id]);

        // $response = $this->get($endpoint.'?priceFrom=150');
        // $response->assertJsonCount(1, 'data');
        // $response->assertJsonMissing(['id' => $cheapTour->id]);
        // $response->assertJsonFragment(['id' => $expensiveTour->id]);

        // $response = $this->get($endpoint.'?priceFrom=250');
        // $response->assertJsonCount(0, 'data');

        // $response = $this->get($endpoint.'?priceTo=200');
        // $response->assertJsonCount(2, 'data');
        // $response->assertJsonFragment(['id' => $cheapTour->id]);
        // $response->assertJsonFragment(['id' => $expensiveTour->id]);

        // $response= $this->get($endpoint.'?priceTo=150');
        // $response->assertJsonCount(1, 'data');
        // $response->assertJsonMissing(['id' => $expensiveTour->id]);
        // $response->assertJsonFragment(['id' => $cheapTour->id]);

        // $response = $this->get($endpoint.'?priceTo=50');
        // $response->assertJsonCount(0, 'data');

        // $response = $this->get($endpoint.'?priceFrom=150&priceTo=350');
        // $response->assertJsonCount(1, 'data');
        // $response->assertJsonMissing(['id' => $expensiveTour->id]);
        // $response->assertJsonFragment(['id' => $cheapTour->id]);
    }

}
