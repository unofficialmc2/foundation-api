<?php
declare(strict_types=1);

namespace Test\Fake;

use FoundationApi\Query as BaseQuery;

/**
 * Fake Query : expose cleanerFetch
 */
class Query extends BaseQuery
{
    /**
     * @inheritDoc
     */
    public function __invoke(): void
    {
    }

    /**
     * @param array<string,string[]> $properties
     * @param array<string,mixed> $fetch
     * @return array<string,mixed>
     */
    public function clean(array $properties, array $fetch): array
    {
        return ($this->cleanerFetch($properties))($fetch);
    }
}
