<?php

declare(strict_types=1);

// All Action classes implement ActionInterface
arch('actions implement ActionInterface')
    ->expect('App\Actions')
    ->toImplement('App\Contracts\ActionInterface');

// All Query classes implement QueryInterface
// Ignoring Concerns namespace which contains traits, not query classes
arch('queries implement QueryInterface')
    ->expect('App\Queries')
    ->toImplement('App\Contracts\QueryInterface')
    ->ignoring('App\Queries\Concerns');

// All PHP files use strict types
arch('all files use strict types')
    ->expect('App')
    ->toUseStrictTypes();

// No debugging functions left in code
arch('no debugging functions')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsed();

// Models should extend Eloquent Model
arch('models extend base model')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model');

// Controllers should not use DB facade directly (delegate to Actions/Queries)
arch('controllers are thin')
    ->expect('App\Http\Controllers')
    ->not->toUse('Illuminate\Support\Facades\DB')
    ->ignoring([
        'App\Http\Controllers\Api\HealthController',
    ]);

// Enums should be backed
arch('enums are backed')
    ->expect('App\Enums')
    ->toBeEnums();

// Form requests extend FormRequest
arch('form requests extend FormRequest')
    ->expect('App\Http\Requests')
    ->toExtend('Illuminate\Foundation\Http\FormRequest');

// API Resources extend JsonResource
arch('resources extend JsonResource')
    ->expect('App\Http\Resources')
    ->toExtend('Illuminate\Http\Resources\Json\JsonResource');

// Policies should have correct suffix
arch('policies have correct suffix')
    ->expect('App\Policies')
    ->toHaveSuffix('Policy');
