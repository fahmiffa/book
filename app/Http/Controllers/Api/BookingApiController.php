<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
 
class BookingApiController extends Controller
{
    /**
     * Update booking status from 4 to 3 based on UUID.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'uuid' => 'required|uuid|exists:bookings,uuid',
        ]);
 
        $booking = Booking::where('uuid', $request->uuid)->first();
 
        // Logic: Ubah status dari 4 ke 3
        if ($booking->status == 4) {
            $booking->status = 3;
            $booking->save();
 
            return response()->json([
                'success' => true,
                'message' => 'Status antrian berhasil diupdate dari 4 ke 3.',
                'data' => [
                    'id' => $booking->id,
                    'uuid' => $booking->uuid,
                    'status' => $booking->status
                ]
            ]);
        }
 
        return response()->json([
            'success' => false,
            'message' => 'Booking ditemukan, namun status saat ini adalah ' . $booking->status . ' (bukan 4). Tidak ada perubahan dilakukan.',
            'current_status' => $booking->status
        ], 400);
    }
}
