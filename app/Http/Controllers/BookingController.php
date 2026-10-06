<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Court;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:'.today()->addMonths(3)->format('Y-m-d')]]);
        $date = $request->input('date', old('booking_date', today()->format('Y-m-d')));
        $occupied = BookingDetail::whereHas('booking', fn ($q) => $q->whereDate('booking_date', $date)->where('status', '!=', 'Batal'))
            ->get(['court_id', 'start_time', 'duration_hours'])->groupBy('court_id');

        return view('customer.index', ['courts' => Court::orderBy('id')->get(), 'date' => $date, 'occupied' => $occupied]);
    }

    public function store(Request $request, ReservationService $service)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^\+?[0-9][0-9\s-]{7,23}$/'],
            'court_id' => ['required', 'integer', 'exists:courts,id'],
            'booking_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:'.today()->addMonths(3)->format('Y-m-d')],
            'start_time' => ['required', Rule::in(array_map(fn ($h) => sprintf('%02d:00', $h), range(8, 21)))],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:4'],
        ]);
        if ((int) substr($data['start_time'], 0, 2) + $data['duration_hours'] > 22 || now()->greaterThanOrEqualTo(Carbon::parse($data['booking_date'].' '.$data['start_time']))) {
            throw ValidationException::withMessages(['start_time' => 'Pilih jadwal mendatang antara pukul 08.00–22.00.']);
        }
        $booking = $service->create($data);

        return redirect()->route('customer.index', ['date' => $data['booking_date']])->with('receipt', [
            'id' => $booking->id, 'name' => $booking->customer_name, 'date' => $data['booking_date'],
            'court' => $booking->details()->first()->court->name, 'start' => $data['start_time'],
            'duration' => $data['duration_hours'], 'total' => $booking->total_price,
        ])->with('success', 'Reservasi berhasil diajukan! Status pembayaran: Pending. Hubungi petugas lapangan untuk pembayaran.');
    }

    public function dashboard(Request $request)
    {
        $request->validate(['status' => ['nullable', Rule::in(['Pending', 'Lunas', 'Batal'])]]);

        return view('admin.dashboard', [
            'bookings' => Booking::with('details.court')->when($request->status, fn ($q, $status) => $q->where('status', $status))->latest()->paginate(12)->withQueryString(),
            'total' => Booking::count(), 'pending' => Booking::where('status', 'Pending')->count(),
            'revenue' => Booking::where('status', 'Lunas')->sum('total_price'), 'courtCount' => Court::count(),
        ]);
    }

    public function status(Request $request, Booking $booking, ReservationService $service)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['Pending', 'Lunas', 'Batal'])]]);
        $service->changeStatus($booking, $data['status']);

        return back()->with('success', 'Status reservasi diperbarui.');
    }
}
