<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from 'vue';
import { Link, router, useForm, usePoll } from '@inertiajs/vue3';
import { show as viewReservation } from '@/routes/reservations';
import ClinicIcon from '@/components/ClinicIcon.vue';
import ClinicLayout from '@/components/ClinicLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { index, checkIn as checkInRoute, update } from '@/routes/admin/queue';
import { walkIn } from '@/routes/admin';
import { queueLabel, type Reservation, type Schedule } from '@/types/clinic';
const props = defineProps<{
    reservations: Reservation[];
    schedules: Schedule[];
    selectedSchedule: string | number;
    today: string;
}>();
const selected = ref(props.selectedSchedule);
const checkIn = useForm({ booking_code: '' });
const transition = useForm({ status: '' });
const walk = useForm({
    patient_identifier: '',
    name: '',
    nik: '',
    birth_date: '',
    gender: '',
    phone: '',
    address: '',
    schedule_id: '',
    complaint: '',
});
const registered = ref(true);
const showWalk = ref(false);
const scannerMessage = ref('');
const scanning = ref(false);
const video = ref<HTMLVideoElement | null>(null);
let stream: MediaStream | null = null;
let frame: number | null = null;
const search = ref('');
const state = ref('');
const visibleReservations = computed(() => {
    const query = search.value.trim().toLocaleLowerCase('id-ID');

    return props.reservations.filter(
        (item) =>
            (!state.value || item.status === state.value) &&
            (!query ||
                [
                    item.patient.user.name,
                    item.schedule.doctor.name,
                    item.schedule.doctor.polyclinic.name,
                ].some((name) =>
                    name.toLocaleLowerCase('id-ID').includes(query),
                )),
    );
});
const counts = computed(() => ({
    total: props.reservations.length,
    examining: props.reservations.filter((r) => r.status === 'examining')
        .length,
    booked: props.reservations.filter((r) => r.status === 'booked').length,
    waiting: props.reservations.filter((r) => r.status === 'waiting').length,
    completed: props.reservations.filter((r) => r.status === 'completed')
        .length,
}));
const summary = computed(() => [
    { title: 'Total antrean', value: counts.value.total, icon: 'users' },
    { title: 'Menunggu check-in', value: counts.value.booked, icon: 'ticket' },
    { title: 'Menunggu giliran', value: counts.value.waiting, icon: 'clock' },
    {
        title: 'Sedang diperiksa',
        value: counts.value.examining,
        icon: 'doctor',
    },
    { title: 'Selesai', value: counts.value.completed, icon: 'check' },
]);
function stopScan() {
    scanning.value = false;
    stream?.getTracks().forEach((track) => track.stop());
    stream = null;
    if (frame !== null) cancelAnimationFrame(frame);
}
async function scan() {
    scannerMessage.value = '';
    const Detector = (
        window as unknown as {
            BarcodeDetector?: new (options: { formats: string[] }) => {
                detect: (
                    video: HTMLVideoElement,
                ) => Promise<{ rawValue: string }[]>;
            };
        }
    ).BarcodeDetector;
    if (!Detector) {
        scannerMessage.value =
            'Browser ini belum mendukung pemindai kamera. Gunakan pemindai QR USB atau ketik kode booking.';
        return;
    }
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment' },
        });
        scanning.value = true;
        const detector = new Detector({ formats: ['qr_code'] });
        if (video.value) {
            video.value.srcObject = stream;
            await video.value.play();
        }
        async function detect() {
            if (!scanning.value || !video.value) return;
            try {
                const codes = await detector.detect(video.value);
                if (codes[0]) {
                    checkIn.booking_code = codes[0].rawValue;
                    stopScan();
                    scannerMessage.value =
                        'Kode terbaca. Cocokkan identitas pasien, lalu tekan Verifikasi check-in.';
                    return;
                }
                frame = requestAnimationFrame(() => {
                    void detect();
                });
            } catch {
                stopScan();
                scannerMessage.value =
                    'Pemindaian gagal. Silakan masukkan kode booking secara manual.';
            }
        }
        frame = requestAnimationFrame(() => {
            void detect();
        });
    } catch {
        stopScan();
        scannerMessage.value =
            'Kamera tidak tersedia atau izin ditolak. Anda tetap dapat mengetik kode booking.';
    }
}
function changeStatus(id: number, status: string) {
    transition.status = status;
    transition.patch(update(id).url, { preserveScroll: true });
}
function submitWalk() {
    walk.transform((data) =>
        registered.value
            ? {
                  patient_identifier: data.patient_identifier,
                  schedule_id: data.schedule_id,
                  complaint: data.complaint,
              }
            : {
                  name: data.name,
                  nik: data.nik,
                  birth_date: data.birth_date,
                  gender: data.gender,
                  phone: data.phone,
                  address: data.address,
                  schedule_id: data.schedule_id,
                  complaint: data.complaint,
              },
    ).post(walkIn().url, {
        preserveScroll: true,
        onSuccess: () => {
            walk.reset();
            showWalk.value = false;
        },
    });
}
onBeforeUnmount(stopScan);
usePoll(10000, { only: ['reservations', 'schedules'] });
</script>
<template>
    <ClinicLayout
        title="Meja antrean & check-in"
        description="Verifikasi kedatangan, panggil pasien, dan kelola antrean online serta walk-in hari ini."
    >
        <div
            class="mb-5 grid gap-3 @min-[24rem]/content:grid-cols-2 @min-[42rem]/content:grid-cols-3 @min-[54rem]/content:grid-cols-5"
            aria-label="Ringkasan antrean"
        >
            <article
                v-for="item in summary"
                :key="item.title"
                class="card flex items-center gap-3 p-4"
            >
                <span
                    class="grid size-9 shrink-0 place-items-center rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                >
                    <ClinicIcon :name="item.icon" :size="18" />
                </span>
                <div>
                    <p
                        class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                    >
                        {{ item.title }}
                    </p>
                    <p class="mt-1 text-xl font-extrabold tabular-nums">
                        {{ item.value }}
                    </p>
                </div>
            </article>
        </div>
        <div
            class="mb-7 grid items-start gap-5 @min-[54rem]/content:grid-cols-[minmax(0,2fr)_minmax(16rem,1fr)]"
        >
            <form
                class="card"
                @submit.prevent="
                    checkIn.post(checkInRoute().url, {
                        preserveScroll: true,
                        onSuccess: () => checkIn.reset(),
                    })
                "
            >
                <p class="section-kicker">VERIFIKASI KEDATANGAN</p>
                <div class="mt-4 flex flex-wrap items-end gap-3">
                    <label class="field flex-[1_1_12rem]"
                        >Kode booking / pemindai QR USB<input
                            v-model="checkIn.booking_code"
                            maxlength="16"
                            required
                            placeholder="KSXXXXXXXXXXXX"
                            autocomplete="off" /></label
                    ><button class="btn" :disabled="checkIn.processing">
                        Verifikasi check-in</button
                    ><button
                        class="btn-secondary"
                        type="button"
                        @click="scanning ? stopScan() : scan()"
                    >
                        {{ scanning ? 'Tutup kamera' : 'Scan kamera' }}
                    </button>
                </div>
                <video
                    v-show="scanning"
                    ref="video"
                    muted
                    playsinline
                    class="mt-4 max-h-56 w-full rounded-xl bg-black"
                ></video>
                <p
                    v-if="scannerMessage"
                    role="status"
                    class="mt-3 text-xs leading-6 text-slate-500"
                >
                    {{ scannerMessage }}
                </p>
                <p class="mt-3 text-xs text-slate-400">
                    Cocokkan identitas pada tiket pasien sebelum melakukan
                    check-in.
                </p>
            </form>
            <div class="card">
                <p class="section-kicker">PASIEN DATANG LANGSUNG</p>
                <h2 class="mt-4 text-lg font-semibold">Pendaftaran walk-in</h2>
                <p class="mt-2 text-xs leading-6 text-slate-500">
                    Nomor urut tersambung dengan booking online pada sesi yang
                    sama.
                </p>
                <button
                    class="btn-secondary mt-4 w-full"
                    @click="showWalk = !showWalk"
                >
                    {{
                        showWalk ? 'Tutup formulir' : '+ Tambah pasien walk-in'
                    }}
                </button>
            </div>
        </div>
        <form
            v-if="showWalk"
            class="card mb-8 flex flex-col gap-5"
            @submit.prevent="submitWalk"
        >
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold">Pendaftaran walk-in</h2>
                <label class="flex items-center gap-2 text-sm"
                    ><input
                        v-model="registered"
                        type="checkbox"
                        class="size-4 accent-blue-700"
                    />
                    Pasien sudah terdaftar</label
                >
            </div>
            <label v-if="registered" class="field"
                >Nomor RM atau NIK<input
                    v-model="walk.patient_identifier"
                    required
                    placeholder="Masukkan No. RM / 16 digit NIK" /></label
            ><template v-else
                ><div class="clinic-grid gap-5 [--clinic-column-min:17rem]">
                    <label class="field"
                        >Nama lengkap<input
                            v-model="walk.name"
                            required
                            maxlength="150" /></label
                    ><label class="field"
                        >NIK<input
                            v-model="walk.nik"
                            inputmode="numeric"
                            pattern="[0-9]{16}"
                            maxlength="16"
                            required /></label
                    ><label class="field"
                        >Tanggal lahir<input
                            v-model="walk.birth_date"
                            type="date"
                            :max="today"
                            required /></label
                    ><label class="field"
                        >Jenis kelamin<select v-model="walk.gender" required>
                            <option value="" disabled>Pilih</option>
                            <option value="male">Laki-laki</option>
                            <option value="female">Perempuan</option>
                        </select></label
                    ><label class="field"
                        >Nomor WhatsApp<input
                            v-model="walk.phone"
                            type="tel"
                            required /></label
                    ><label class="field"
                        >Alamat<input
                            v-model="walk.address"
                            minlength="5"
                            maxlength="1000"
                            required
                    /></label></div></template
            ><label class="field"
                >Sesi praktik hari ini<select
                    v-model="walk.schedule_id"
                    required
                >
                    <option value="" disabled>Pilih dokter</option>
                    <option
                        v-for="schedule in schedules.filter((s) => s.is_active)"
                        :key="schedule.id"
                        :value="schedule.id"
                    >
                        {{ schedule.doctor.name }} ·
                        {{ schedule.doctor.polyclinic.name }} ·
                        {{ schedule.start_time.slice(0, 5) }}–{{
                            schedule.end_time.slice(0, 5)
                        }}
                    </option>
                </select></label
            ><label class="field"
                >Keluhan awal<textarea
                    v-model="walk.complaint"
                    required
                    minlength="5"
                    maxlength="2000"
                    rows="2"
                ></textarea></label
            ><button class="btn self-start" :disabled="walk.processing">
                Daftarkan & check-in pasien →
            </button>
        </form>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold">Daftar antrean hari ini</h2>
                <p class="mt-1 text-xs text-slate-500">
                    {{ visibleReservations.length }} dari
                    {{ reservations.length }} antrean
                </p>
            </div>
            <div
                class="clinic-grid w-full max-w-3xl flex-[1_1_28rem] gap-2 [--clinic-column-min:10rem]"
            >
                <input
                    v-model="search"
                    type="search"
                    aria-label="Cari pasien, dokter, atau poli"
                    placeholder="Cari pasien, dokter, atau poli"
                    class="min-w-0"
                />
                <select v-model="state" aria-label="Filter status antrean">
                    <option value="">Semua status</option>
                    <option value="booked">Dipesan</option>
                    <option value="waiting">Menunggu</option>
                    <option value="examining">Diperiksa</option>
                    <option value="skipped">Dilewati</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
                <select
                    v-model="selected"
                    class="min-w-0"
                    aria-label="Filter sesi antrean"
                    @change="
                        router.get(
                            index().url,
                            selected ? { schedule: selected } : {},
                            { preserveState: true, preserveScroll: true },
                        )
                    "
                >
                    <option value="">Semua sesi hari ini</option>
                    <option
                        v-for="schedule in schedules"
                        :key="schedule.id"
                        :value="schedule.id"
                    >
                        {{ schedule.doctor.name }} ·
                        {{ schedule.start_time.slice(0, 5) }}
                    </option>
                </select>
            </div>
        </div>
        <div
            class="table-wrap queue-table"
            role="region"
            tabindex="0"
            aria-label="Daftar antrean"
        >
            <table class="w-full">
                <thead>
                    <tr>
                        <th>Nomor antrean</th>
                        <th>Pasien</th>
                        <th>Sesi dokter</th>
                        <th>Jenis / status</th>
                        <th>Aksi antrean</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in visibleReservations" :key="item.id">
                        <td
                            class="font-mono text-xl font-semibold whitespace-nowrap text-blue-700 dark:text-blue-400"
                        >
                            {{ queueLabel(item.queue_number) }}
                        </td>
                        <td>
                            <strong class="font-medium">{{
                                item.patient.user.name
                            }}</strong>
                            <Link
                                :href="viewReservation(item.id)"
                                class="mt-1 block text-xs font-semibold text-blue-700 hover:underline dark:text-blue-300"
                            >
                                Lihat arsip
                            </Link>
                        </td>
                        <td>
                            {{ item.schedule.doctor.name }}
                            <p class="mt-1 text-xs text-slate-400">
                                {{ item.schedule.doctor.polyclinic.name }} ·
                                {{ item.schedule.start_time.slice(0, 5) }}
                            </p>
                        </td>
                        <td>
                            <StatusBadge :status="item.status" />
                            <p class="mt-2 text-xs text-slate-400">
                                {{
                                    item.booking_type === 'online'
                                        ? 'Online'
                                        : 'Walk-in'
                                }}
                            </p>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-if="item.status === 'booked'"
                                    class="btn-secondary text-xs"
                                    :disabled="transition.processing"
                                    @click="changeStatus(item.id, 'waiting')"
                                >
                                    Check-in</button
                                ><button
                                    v-if="item.status === 'waiting'"
                                    class="btn text-xs"
                                    :disabled="transition.processing"
                                    @click="changeStatus(item.id, 'examining')"
                                >
                                    Panggil</button
                                ><button
                                    v-if="item.status === 'waiting'"
                                    class="btn-secondary text-xs"
                                    :disabled="transition.processing"
                                    @click="changeStatus(item.id, 'skipped')"
                                >
                                    Lewati</button
                                ><button
                                    v-if="item.status === 'examining'"
                                    class="btn text-xs"
                                    :disabled="transition.processing"
                                    @click="changeStatus(item.id, 'completed')"
                                >
                                    Selesai</button
                                ><button
                                    v-if="item.status === 'skipped'"
                                    class="btn-secondary text-xs"
                                    :disabled="transition.processing"
                                    @click="changeStatus(item.id, 'waiting')"
                                >
                                    Antrekan kembali
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!visibleReservations.length">
                        <td
                            colspan="5"
                            class="py-12 text-center text-slate-400"
                        >
                            {{
                                search.trim() || state
                                    ? 'Tidak ada antrean yang cocok dengan filter.'
                                    : 'Belum ada antrean hari ini untuk sesi ini.'
                            }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </ClinicLayout>
</template>
