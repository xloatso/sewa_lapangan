<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Court;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    public function ensureAvailable(int $courtId, string $date, string $start, int $duration, ?int $except = null): void
    {
        $startHour = (int) substr($start, 0, 2);
        $details = BookingDetail::where('court_id', $courtId)
            ->whereHas('booking', function ($query) use ($date, $except) {
                $query->whereDate('booking_date', $date)->where('status', '!=', 'Batal');
                if ($except) {
                    $query->where('id', '!=', $except);
                }
            })->get();
        foreach ($details as $detail) {
            $existingStart = (int) substr($detail->start_time, 0, 2);
            if ($startHour < $existingStart + $detail->duration_hours && $startHour + $duration > $existingStart) {
                throw ValidationException::withMessages(['schedule' => 'Jadwal lapangan sudah dipesan. Silakan pilih jam atau lapangan lain.']);
            }
        }
    }

    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            // Serialize changes per court, including booking creation and reactivation.
            $court = Court::lockForUpdate()->findOrFail($data['court_id']);
            $this->ensureAvailable($court->id, $data['booking_date'], $data['start_time'], $data['duration_hours']);
            $total = $court->price_per_hour * $data['duration_hours'];
            $booking = Booking::create([
                'customer_name' => $data['customer_name'], 'customer_phone' => $data['customer_phone'],
                'booking_date' => $data['booking_date'], 'total_price' => $total, 'status' => 'Pending',
            ]);
            $booking->details()->create([
                'court_id' => $court->id, 'start_time' => $data['start_time'],
                'duration_hours' => $data['duration_hours'], 'subtotal' => $total,
            ]);

            return $booking;
        }, 3);
    }

    public function changeStatus(Booking $booking, string $status): void
    {
        DB::transaction(function () use ($booking, $status) {
            $details = $booking->details()->orderBy('court_id')->get();
            Court::whereIn('id', $details->pluck('court_id'))->orderBy('id')->lockForUpdate()->get();
            $booking = Booking::lockForUpdate()->findOrFail($booking->id);
            if ($status !== 'Batal') {
                foreach ($details as $detail) {
                    $this->ensureAvailable($detail->court_id, $booking->booking_date->format('Y-m-d'), $detail->start_time, $detail->duration_hours, $booking->id);
                }
            }
            $booking->update(['status' => $status]);
        }, 3);
    }
}
