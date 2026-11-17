<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function index(Request $request): Response
    {
        $data = $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);
        $date = $data['date'] ?? today()->toDateString();

        return Inertia::render('Billing', [
            'reservations' => Reservation::with(['patient.user', 'schedule.doctor.polyclinic', 'transaction'])->whereDate('reservation_date', $date)->where('status', 'completed')->orderBy('id')->get(),
            'date' => $date, 'revenue' => (int) Transaction::whereDate('paid_at', $date)->sum('total_amount'),
        ]);
    }

    public function store(Request $request, Reservation $reservation): RedirectResponse
    {
        $data = $request->validate(['consultation_fee' => ['required', 'integer', 'min:0', 'max:10000000'], 'payment_method' => ['required', Rule::in(['cash', 'qris'])]]);
        DB::transaction(function () use ($reservation, $data) {
            $reservation = Reservation::lockForUpdate()->findOrFail($reservation->id);
            if ($reservation->status !== 'completed' || $reservation->transaction()->exists()) {
                throw ValidationException::withMessages(['payment' => 'Pasien belum selesai diperiksa atau pembayaran sudah tercatat.']);
            }
            Transaction::create(['reservation_id' => $reservation->id, 'admin_fee' => $reservation->admin_fee,
                'consultation_fee' => $data['consultation_fee'], 'total_amount' => $reservation->admin_fee + $data['consultation_fee'],
                'payment_method' => $data['payment_method'], 'payment_status' => 'paid', 'paid_at' => now()]);
        }, 5);

        return back()->with('success', 'Pelunasan berhasil dicatat.');
    }

    public function receipt(Request $request, Reservation $reservation): Response
    {
        abort_unless($request->user()->role === 'admin' || $reservation->patient_id === $request->user()->patientProfile?->id, 404);
        abort_unless($reservation->transaction()->exists(), 404);

        return Inertia::render('Receipt', ['reservation' => $reservation->load(['patient.user', 'schedule.doctor.polyclinic', 'transaction'])]);
    }
}
