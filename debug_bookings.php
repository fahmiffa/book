<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;

echo "Current server date: " . now()->format('Y-m-d') . "\n";
foreach(Booking::all() as $b) {
    echo "ID: $b->id | Name: $b->name | Date: $b->booking_date | Status: $b->status | Loc: $b->location_id\n";
}
