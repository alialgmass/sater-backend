<?php

namespace Tests\Feature\Search;

use App\DTOs\ProductSearchDTO;
use App\Models\User;
use App\Services\Search\SearchHistoryService;
use Modules\Product\Models\Product;
use Modules\Category\Models\Category;
use Modules\Vendor\Models\Vendor;
use Tests\TestCase;

class SearchHistoryTest extends TestCase
{
    protected SearchHistoryService $historyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->historyService = app(SearchHistoryService::class);
    }

    /** @test */
    public function can_record_search_history_for_authenticated_user()
    {
        $user = User::factory()->create();

        $history = $this->historyService->record(
            $user,
            'hijab',
            ['category_id' => 1],
            25
        );

        $this->assertNotNull($history);
        $this->assertEquals('hijab', $history->query);
        $this->assertEquals(25, $history->results_count);
    }

    /** @test */
    public function does_not_record_for_unauthenticated_users()
    {
        $history = $this->historyService->record(
            null,
            'hijab',
            [],
            10
        );

        $this->assertNull($history);
    }

    /** @test */
    public function does_not_record_empty_queries()
    {
        $user = User::factory()->create();

        $history = $this->historyService->record(
            $user,
            '   ',
            [],
            0
        );

        $this->assertNull($history);
    }

    /** @test */
    public function can_retrieve_user_search_history()
    {
        $user = User::factory()->create();

        $this->historyService->record($user, 'hijab', [], 10);
        $this->historyService->record($user, 'abaya', [], 5);
        $this->historyService->record($user, 'scarf', [], 3);

        $history = $this->historyService->getHistory($user);

        $this->assertEquals(3, $history->count());
    }

    /** @test */
    public function can_clear_user_search_history()
    {
        $user = User::factory()->create();

        $this->historyService->record($user, 'hijab', [], 10);
        $this->historyService->record($user, 'abaya', [], 5);

        $this->historyService->clearHistory($user);

        $history = $this->historyService->getHistory($user);

        $this->assertEquals(0, $history->count());
    }

    /** @test */
    public function limits_history_per_user()
    {
        $user = User::factory()->create();

        // Record more than the limit
        for ($i = 0; $i < 60; $i++) {
            $this->historyService->record($user, "query-{$i}", [], $i);
        }

        $history = $this->historyService->getHistory($user, 100);

        // Should be limited to 50
        $this->assertLessThanOrEqual(50, $history->count());
    }
}
