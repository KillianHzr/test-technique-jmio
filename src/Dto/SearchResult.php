<?php

namespace App\Dto;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

readonly class SearchResult implements IteratorAggregate, Countable, JsonSerializable
{
    public function __construct(
        public array $results,
        public int   $total,
        public int   $pages,
        public int   $currentPage,
        public int   $limit
    )
    {
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->results);
    }

    public function count(): int
    {
        return count($this->results);
    }

    public function jsonSerialize(): array
    {
        return [
            'results' => $this->results,
            'total'   => $this->total,
            'pages'   => $this->pages,
            'page'    => $this->currentPage,
            'limit'   => $this->limit,
        ];
    }
}
