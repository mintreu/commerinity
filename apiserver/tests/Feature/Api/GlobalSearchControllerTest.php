<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns empty payload for too short query', function () {
    $response = $this->getJson('/api/search/global?q=a');

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'query' => 'a',
                'totals' => [
                    'products' => 0,
                    'blogs' => 0,
                    'news' => 0,
                    'all' => 0,
                ],
            ],
        ]);
});

it('returns grouped search structure', function () {
    $response = $this->getJson('/api/search/global?q=phone');

    $response
        ->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'query',
                'results' => ['products', 'blogs', 'news'],
                'totals' => ['products', 'blogs', 'news', 'all'],
            ],
        ]);
});
