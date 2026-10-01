<?php

declare(strict_types=1);

namespace App\Contracts;

interface QueryInterface
{
    /**
     * Execute the query.
     *
     * @param  array<string, mixed>  $filters  Optional filters for the query
     * @return mixed The result of the query
     *
     * @throws \InvalidArgumentException When filter parameters are invalid
     */
    public function handle(array $filters = []): mixed;
}
