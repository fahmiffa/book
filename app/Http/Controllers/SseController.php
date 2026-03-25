<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SseController extends Controller
{
    public function stream($locationId)
    {
        return new StreamedResponse(function () use ($locationId) {
            $lastUpdate = null;

            while (true) {
                // Check if there is a new call or status change
                $latest = Booking::where('location_id', $locationId)
                    ->whereDate('booking_date', now()->format('Y-m-d'))
                    ->latest('updated_at')
                    ->first();

                if ($latest) {
                    $currentUpdate = $latest->updated_at->toDateTimeString() . '_' . $latest->id . '_' . $latest->status;
                    
                    if ($lastUpdate !== $currentUpdate) {
                        echo "data: " . json_encode([
                            'id' => $latest->id,
                            'updated_at' => $latest->updated_at->toDateTimeString(),
                            'status' => $latest->status
                        ]) . "\n\n";
                        
                        $lastUpdate = $currentUpdate;
                    }
                }

                if (connection_aborted()) {
                    break;
                }

                // Push buffer to client
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();

                // Wait for 1 second before next check to reduce DB load
                sleep(1);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Specific for Nginx
        ]);
    }
}
