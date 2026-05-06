<?php

namespace App\Tests\Unit;

use App\Dto\SearchResult;
use PHPUnit\Framework\TestCase;

class SearchResultTest extends TestCase
{
    public function testSearchResultBehavior(): void
    {
        $items = ['a', 'b', 'c'];
        $result = new SearchResult(
            results: $items,
            total: 10,
            pages: 4,
            currentPage: 1,
            limit: 3
        );

        $iterated = [];
        foreach ($result as $item) {
            $iterated[] = $item;
        }
        $this->assertEquals($items, $iterated);

        $this->assertCount(3, $result);

        $json = json_encode($result);
        $decoded = json_decode($json, true);

        $this->assertArrayHasKey('results', $decoded);
        $this->assertArrayHasKey('total', $decoded);
        $this->assertEquals(10, $decoded['total']);
        $this->assertEquals(['a', 'b', 'c'], $decoded['results']);
    }
}
