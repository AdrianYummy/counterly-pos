<?php

use App\Models\User;
use App\Services\SaleService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Validation\ValidationException;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

try {
    $app->make(SaleService::class)->record((int) $argv[1], null, 4, User::findOrFail((int) $argv[2]));
    echo 'success';
} catch (ValidationException) {
    echo 'rejected';
}
