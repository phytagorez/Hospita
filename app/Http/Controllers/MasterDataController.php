<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Polyclinic;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class MasterDataController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('MasterData', ['polyclinics' => Polyclinic::orderBy('name')->get(), 'doctors' => Doctor::with('polyclinic')->orderBy('name')->get()]);
    }

    public function schedules(): Response
    {
        return Inertia::render('ScheduleManagement', ['schedules' => DoctorSchedule::with('doctor.polyclinic')->orderBy('day_of_week')->orderBy('start_time')->get(), 'doctors' => Doctor::with('polyclinic')->orderBy('name')->get(), 'today' => today()->toDateString()]);
    }

    public function store(Request $request, string $entity): RedirectResponse
    {
        $model = $this->model($entity);
        $data = $request->validate($this->rules($entity, $model));
        DB::transaction(function () use ($model, $data) {
            if ($model instanceof DoctorSchedule) {
                $this->validateSchedule($data);
            }
            $model->fill($data)->save();
        }, 5);

        return back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, string $entity, int $id): RedirectResponse
    {
        $model = $this->model($entity)->findOrFail($id);
        $data = $request->validate($this->rules($entity, $model));
        DB::transaction(function () use ($model, $data) {
            if ($model instanceof DoctorSchedule) {
                $model = DoctorSchedule::lockForUpdate()->findOrFail($model->id);
                $future = Reservation::where('schedule_id', $model->id)->whereDate('reservation_date', '>=', today())->where('status', '!=', 'cancelled');
                $structural = (int) $data['doctor_id'] !== $model->doctor_id || (int) $data['day_of_week'] !== $model->day_of_week
                    || $data['start_time'] !== substr($model->start_time, 0, 5) || $data['end_time'] !== substr($model->end_time, 0, 5);
                if ($structural && (clone $future)->exists()) {
                    throw ValidationException::withMessages(['schedule' => 'Jadwal memiliki reservasi mendatang. Buat sesi baru untuk mengubah dokter/hari/jam.']);
                }
                $largest = (clone $future)->select('reservation_date')->selectRaw('COUNT(*) as total')->groupBy('reservation_date')->get()->max('total') ?? 0;
                if ($data['max_quota'] < $largest) {
                    throw ValidationException::withMessages(['max_quota' => 'Kuota tidak boleh kurang dari jumlah reservasi yang sudah masuk.']);
                }
                $this->validateSchedule($data, $model->id);
            }
            if ($model instanceof Doctor && (int) $data['polyclinic_id'] !== $model->polyclinic_id
                && Reservation::whereHas('schedule', fn ($query) => $query->where('doctor_id', $model->id))->exists()) {
                throw ValidationException::withMessages(['polyclinic_id' => 'Dokter sudah memiliki riwayat reservasi. Tambahkan dokter baru untuk pindah poli.']);
            }
            $model->update($data);
        }, 5);

        return back()->with('success', 'Perubahan berhasil disimpan.');
    }

    public function destroy(string $entity, int $id): RedirectResponse
    {
        $model = $this->model($entity)->findOrFail($id);
        $related = match (true) {
            $model instanceof Polyclinic => $model->doctors()->exists(), $model instanceof Doctor => $model->schedules()->exists(), $model instanceof DoctorSchedule => $model->reservations()->exists()
        };
        if ($related) {
            throw ValidationException::withMessages(['master' => 'Data masih digunakan. Nonaktifkan data untuk mempertahankan riwayat.']);
        }
        try {
            DB::transaction(fn () => $model->delete());
        } catch (QueryException $exception) {
            if (! str_starts_with((string) $exception->getCode(), '23')) {
                throw $exception;
            }
            throw ValidationException::withMessages(['master' => 'Data masih digunakan dan tidak dapat dihapus.']);
        }

        return back()->with('success', 'Data berhasil dihapus.');
    }

    private function model(string $entity): Polyclinic|Doctor|DoctorSchedule
    {
        return match ($entity) {
            'polyclinics' => new Polyclinic, 'doctors' => new Doctor, 'schedules' => new DoctorSchedule, default => abort(404)
        };
    }

    /** @return array<string, mixed> */
    private function rules(string $entity, Model $model): array
    {
        return match ($entity) {
            'polyclinics' => ['name' => ['required', 'string', 'max:100'], 'code' => ['required', 'alpha_num', 'max:10', Rule::unique('polyclinics')->ignore($model)],
                'room_location' => ['required', 'string', 'max:100'], 'base_admin_fee' => ['required', 'integer', 'min:0', 'max:10000000'], 'is_active' => ['required', 'boolean']],
            'doctors' => ['name' => ['required', 'string', 'max:150'], 'polyclinic_id' => ['required', 'integer', 'exists:polyclinics,id'],
                'sip_number' => ['required', 'string', 'max:100', Rule::unique('doctors')->ignore($model)], 'photo' => ['nullable', 'url:https', 'max:255'], 'is_active' => ['required', 'boolean']],
            'schedules' => ['doctor_id' => ['required', 'integer', 'exists:doctors,id'], 'day_of_week' => ['required', 'integer', 'between:0,6'],
                'start_time' => ['required', 'date_format:H:i'], 'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
                'max_quota' => ['required', 'integer', 'between:1,200'], 'is_active' => ['required', 'boolean'],
                'closed_date' => [
                    'nullable', 'date_format:Y-m-d',
                    Rule::when(
                        fn (Fluent $input): bool => ! ($model instanceof DoctorSchedule
                            && $model->closed_date?->toDateString() === $input->get('closed_date')),
                        'after_or_equal:today',
                    ),
                ]],
            default => abort(404),
        };
    }

    /** @param array<string, mixed> $data */
    private function validateSchedule(array $data, ?int $id = null): void
    {
        Doctor::whereKey($data['doctor_id'])->lockForUpdate()->firstOrFail();
        $overlap = DoctorSchedule::where('doctor_id', $data['doctor_id'])->where('day_of_week', $data['day_of_week'])->where('is_active', true)
            ->when($id, fn ($query) => $query->whereKeyNot($id))->where('start_time', '<', $data['end_time'])->where('end_time', '>', $data['start_time'])->exists();
        if ($data['is_active'] && $overlap) {
            throw ValidationException::withMessages(['start_time' => 'Sesi bertumpang tindih dengan jadwal dokter yang sudah ada.']);
        }
    }
}
