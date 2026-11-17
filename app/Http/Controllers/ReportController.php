<?php

namespace App\Http\Controllers;

use App\ClinicReport;
use App\Models\Polyclinic;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request, ClinicReport $report): Response|HttpResponse
    {
        $data = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'polyclinic' => ['nullable', 'integer', 'exists:polyclinics,id'], 'format' => ['nullable', Rule::in(['pdf', 'xlsx'])],
        ]);
        $from = $data['from'] ?? today()->startOfMonth()->toDateString();
        $to = $data['to'] ?? today()->toDateString();
        if ($from > $to) {
            throw ValidationException::withMessages(['to' => 'Tanggal akhir tidak boleh sebelum tanggal awal.']);
        }
        $query = Reservation::with(['patient.user', 'schedule.doctor.polyclinic', 'transaction'])
            ->whereBetween('reservation_date', [$from, $to])
            ->when($data['polyclinic'] ?? null, fn ($q, $id) => $q->whereHas('schedule.doctor', fn ($d) => $d->where('polyclinic_id', $id)))
            ->orderBy('reservation_date')->orderBy('id');
        $visits = (clone $query)->get();
        $summary = $visits->groupBy(fn ($item) => $item->schedule->doctor->polyclinic->name)->map(fn ($items, $name) => [
            'name' => $name, 'visits' => $items->count(), 'completed' => $items->where('status', 'completed')->count(),
            'revenue' => $items->sum(fn ($item) => $item->transaction->total_amount ?? 0),
        ])->values();
        $doctorSummary = $visits->groupBy(fn ($item) => $item->schedule->doctor_id)->map(fn ($items) => [
            'name' => $items->first()->schedule->doctor->name,
            'polyclinic' => $items->first()->schedule->doctor->polyclinic->name,
            'visits' => $items->count(), 'completed' => $items->where('status', 'completed')->count(),
            'revenue' => $items->sum(fn ($item) => $item->transaction->total_amount ?? 0),
        ])->values();
        if (! empty($data['format'])) {
            $rows = [['Tanggal', 'Booking', 'No RM', 'Pasien', 'Poli', 'Dokter', 'Status', 'Metode', 'Total (Rp)']];
            $details = [];
            foreach ($query->lazy(500) as $item) {
                $rows[] = [$item->reservation_date->toDateString(), $item->booking_code, $item->patient->no_rm, $item->patient->user->name,
                    $item->schedule->doctor->polyclinic->name, $item->schedule->doctor->name, $item->status, $item->transaction->payment_method ?? '-', $item->transaction->total_amount ?? 0];
                $details[] = $item->reservation_date->toDateString().' | '.$item->booking_code.' | '.$item->patient->user->name;
                $details[] = $item->schedule->doctor->polyclinic->name.' / '.$item->schedule->doctor->name.' | '.$item->status.' | Rp '.number_format($item->transaction->total_amount ?? 0, 0, ',', '.');
                $details[] = '';
            }
            if ($data['format'] === 'xlsx') {
                return response($report->spreadsheet($rows), 200, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Content-Disposition' => 'attachment; filename="laporan-klinik.xlsx"', 'Cache-Control' => 'private, no-store']);
            }
            $lines = ['KLINIK SEHAT - LAPORAN KUNJUNGAN', $from.' s/d '.$to, 'Total kunjungan: '.(count($rows) - 1).' | Pendapatan: Rp '.number_format($summary->sum('revenue'), 0, ',', '.'), ''];
            array_push($lines, ...$details);

            return response($report->pdf($lines), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="laporan-klinik.pdf"', 'Cache-Control' => 'private, no-store']);
        }

        return Inertia::render('Reports', ['reservations' => $query->paginate(20)->withQueryString(), 'summary' => $summary, 'doctorSummary' => $doctorSummary, 'polyclinics' => Polyclinic::orderBy('name')->get(), 'filters' => ['from' => $from, 'to' => $to, 'polyclinic' => $data['polyclinic'] ?? '']]);
    }
}
