<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Location;
use App\Models\User;

echo "--- LOCATIONS ---\n";
foreach(Location::all() as $l) {
    echo "ID: $l->id | Name: $l->name\n";
}

echo "\n--- CURRENT USER ---\n";
if (auth()->check()) {
    $u = auth()->user();
    echo "ID: $u->id | Name: $u->name | Role: $u->role | Loc: $u->location_id\n";
} else {
    echo "No user logged in (via CLI)\n";
}
