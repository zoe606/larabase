<?php

declare(strict_types=1);

namespace App\Contracts;

interface ActionInterface
{
    /**
     * Handle the action.
     *
     * @param  array<string, mixed>  $data  The data required to perform the action
     * @return mixed The result of the action
     *
     * @throws \App\Exceptions\DomainException When a business rule is violated
     * @throws \InvalidArgumentException When input data is invalid
     */
    public function handle(array $data): mixed;
}
