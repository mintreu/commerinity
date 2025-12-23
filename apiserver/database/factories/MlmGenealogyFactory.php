<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Membership\Level;
use App\Models\Membership\Stage;
use App\Models\Mlm\MlmGenealogy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MlmGenealogy>
 */
class MlmGenealogyFactory extends Factory
{
    protected $model = MlmGenealogy::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'placement_parent_id' => null,
            'placement_position' => 1,
            'depth' => 0,
            'direct_count' => 0,
            'active_direct_count' => 0,
            'level_1_count' => 0,
            'level_2_count' => 0,
            'level_3_count' => 0,
            'level_4_count' => 0,
            'total_team_count' => 0,
            'active_team_count' => 0,
            'personal_sales' => 0,
            'level_1_sales' => 0,
            'level_2_sales' => 0,
            'level_3_sales' => 0,
            'level_4_sales' => 0,
            'total_team_sales' => 0,
            'personal_pv' => 0,
            'team_pv' => 0,
            'current_stage_id' => null,
            'current_level_id' => null,
            'highest_level_id' => null,
            'is_active' => true,
            'activated_at' => now(),
            'last_activity_at' => now(),
        ];
    }

    /**
     * Set a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Set the placement parent
     */
    public function withPlacementParent(User $parent, int $position = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'placement_parent_id' => $parent->id,
            'placement_position' => $position,
        ]);
    }

    /**
     * Set the depth in the tree
     */
    public function atDepth(int $depth): static
    {
        return $this->state(fn (array $attributes) => [
            'depth' => $depth,
        ]);
    }

    /**
     * Set as inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'activated_at' => null,
        ]);
    }

    /**
     * With team counts
     */
    public function withTeamCounts(int $level1 = 5, int $level2 = 25, int $level3 = 0, int $level4 = 0): static
    {
        $total = $level1 + $level2 + $level3 + $level4;

        return $this->state(fn (array $attributes) => [
            'direct_count' => $level1,
            'active_direct_count' => $level1,
            'level_1_count' => $level1,
            'level_2_count' => $level2,
            'level_3_count' => $level3,
            'level_4_count' => $level4,
            'total_team_count' => $total,
            'active_team_count' => $total,
        ]);
    }

    /**
     * With sales volumes (in paisa)
     */
    public function withSales(int $personal = 0, int $level1 = 0, int $level2 = 0, int $level3 = 0, int $level4 = 0): static
    {
        $totalTeam = $level1 + $level2 + $level3 + $level4;

        return $this->state(fn (array $attributes) => [
            'personal_sales' => $personal,
            'level_1_sales' => $level1,
            'level_2_sales' => $level2,
            'level_3_sales' => $level3,
            'level_4_sales' => $level4,
            'total_team_sales' => $totalTeam,
        ]);
    }

    /**
     * With point values
     */
    public function withPv(int $personal = 0, int $team = 0): static
    {
        return $this->state(fn (array $attributes) => [
            'personal_pv' => $personal,
            'team_pv' => $team,
        ]);
    }

    /**
     * With stage and level
     */
    public function withStageLevel(int $stageId, int $levelId): static
    {
        return $this->state(fn (array $attributes) => [
            'current_stage_id' => $stageId,
            'current_level_id' => $levelId,
            'highest_level_id' => $levelId,
        ]);
    }

    /**
     * Create a complete tree with user
     */
    public function withUser(): static
    {
        return $this->state(function (array $attributes) {
            $user = User::factory()->create();

            return [
                'user_id' => $user->id,
            ];
        });
    }
}
