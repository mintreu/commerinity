<?php

namespace Tests\Unit;

use App\Services\RewardPointService;
use Mintreu\LaravelProductCatalogue\Models\ProductTier;
use Tests\TestCase;

class RewardPointServiceTest extends TestCase
{
    /** @test */
    public function it_calculates_reward_points_correctly()
    {
        // Arrange
        config(['laravel-product-catalogue.reward.profit_margin_percentage' => 30]);
        config(['laravel-product-catalogue.reward.conversion' => 100]);

        $tier = new ProductTier(['profit_margin' => 1000]); // 10.00
        $service = new RewardPointService();

        // Act
        $rewardPoints = $service->calculate($tier);

        // Assert
        $this->assertEquals(3, $rewardPoints); // (1000 * 30 / 100) / 100 = 3
    }

    /** @test */
    public function it_returns_zero_if_tier_is_null()
    {
        // Arrange
        $service = new RewardPointService();

        // Act
        $rewardPoints = $service->calculate(null);

        // Assert
        $this->assertEquals(0, $rewardPoints);
    }

    /** @test */
    public function it_returns_zero_if_conversion_is_zero()
    {
        // Arrange
        config(['laravel-product-catalogue.reward.profit_margin_percentage' => 30]);
        config(['laravel-product-catalogue.reward.conversion' => 0]);

        $tier = new ProductTier(['profit_margin' => 1000]);
        $service = new RewardPointService();

        // Act
        $rewardPoints = $service->calculate($tier);

        // Assert
        $this->assertEquals(0, $rewardPoints);
    }
}
