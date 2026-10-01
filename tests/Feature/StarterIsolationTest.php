<?php

declare(strict_types=1);

it('keeps platform integration files free of product symbols', function () {
    $files = [
        'app/Providers/AppServiceProvider.php',
        'app/Providers/AuthServiceProvider.php',
        'app/Models/User.php',
        'routes/web.php',
        'routes/api.php',
        'routes/channels.php',
        'routes/console.php',
        'database/seeders/DatabaseSeeder.php',
        'database/seeders/RolePermissionSeeder.php',
        'database/seeders/MenuSeeder.php',
    ];

    $forbidden = file(base_path('.github/domain-symbols.txt'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($files as $file) {
        $source = file_get_contents(base_path($file));

        foreach ($forbidden as $symbol) {
            expect($source)->not->toContain($symbol, "$file contains $symbol");
        }
    }
});
