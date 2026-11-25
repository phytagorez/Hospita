<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { confirmAction } from '@/lib/alerts';
import ClinicLayout from '@/components/ClinicLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { destroy, qr, receipt } from '@/routes/reservations';
import { dateLabel, queueLabel, type Reservation } from '@/types/clinic';
defineProps<{
    reservation: Reservation;
    estimate: string;
    canCancel: boolean;
}>();
const form = useForm({});
async function cancel(id: number) {
    if (
        await confirmAction(
            'Batalkan reservasi?',
            'Kuota sesi akan tersedia bagi pasien lain.',
            'Batalkan reservasi',
        )
    ) {
        form.delete(destroy(id).url);
    }
}
function print() {
    window.print();
}
</script>
<template>
    <ClinicLayout
        title="Tiket reservasi Anda"
        description="Simpan tiket ini dan tunjukkan kode QR saat tiba di klinik."
    >
        <article
            class="mx-auto max-w-3xl overflow-hidden rounded-3xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
        >
            <header
                class="flex flex-wrap items-start justify-between gap-4 bg-blue-900 p-7 text-white"
            >
                <div>
                    <span class="text-[10px] tracking-[0.2em] text-blue-200"
                        >HOSPIA / TIKET KUNJUNGAN</span
                    >
                    <h2 class="mt-3 text-xl font-semibold">
                        {{ reservation.patient.user.name }}
                    </h2>
                    <p class="mt-2 text-xs break-all text-blue-100">
                        {{ reservation.patient.no_rm }}
                    </p>
                </div>
                <StatusBadge :status="reservation.status" />
            </header>
            <div
                class="grid gap-6 p-[clamp(1rem,3cqi,1.75rem)] @min-[36rem]/content:grid-cols-2"
            >
                <div>
                    <p class="section-kicker">NOMOR ANTREAN</p>
                    <p
                        class="mt-2 font-mono text-7xl font-semibold text-blue-800 dark:text-blue-300"
                    >
                        {{ queueLabel(reservation.queue_number) }}
                    </p>
                    <h3 class="mt-5 text-lg font-semibold">
                        {{ reservation.schedule.doctor.name }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-500">
                        Poli {{ reservation.schedule.doctor.polyclinic.name }} ·
                        {{
                            reservation.schedule.doctor.polyclinic.room_location
                        }}
                    </p>
                    <p class="mt-5 text-sm">
                        {{ dateLabel(reservation.reservation_date) }}<br /><span
                            class="mt-1 inline-block text-slate-500"
                            >{{ reservation.schedule.start_time.slice(0, 5) }} –
                            {{ reservation.schedule.end_time.slice(0, 5) }}
                            WIB</span
                        >
                    </p>
                    <p class="mt-5 text-xs leading-6 text-slate-500">
                        Estimasi panggil: <strong>{{ estimate }} WIB</strong
                        ><br />Waktu dapat berubah sesuai durasi pemeriksaan.
                    </p>
                </div>
                <div
                    class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 p-5 dark:border-slate-700"
                >
                    <img
                        :src="qr.url(reservation.id)"
                        alt="QR kode booking untuk check-in"
                        width="192"
                        height="192"
                        class="rounded-xl bg-white"
                    />
                    <p
                        class="mt-4 font-mono text-sm font-semibold tracking-widest"
                    >
                        {{ reservation.booking_code }}
                    </p>
                    <p
                        class="mt-2 text-center text-xs leading-5 text-slate-400"
                    >
                        Tunjukkan kepada petugas resepsionis.<br />QR ini tidak
                        melakukan check-in otomatis.
                    </p>
                </div>
            </div>
            <div
                class="border-t border-slate-100 px-7 py-5 text-sm dark:border-slate-800"
            >
                <p class="text-xs text-slate-400">Keluhan awal</p>
                <p class="mt-2 whitespace-pre-wrap">
                    {{ reservation.complaint }}
                </p>
            </div>
            <div
                class="flex flex-wrap gap-3 border-t border-slate-100 p-7 dark:border-slate-800 print:hidden"
            >
                <button class="btn-secondary" @click="print">
                    Cetak / simpan tiket PDF</button
                ><Link
                    v-if="reservation.transaction"
                    :href="receipt(reservation.id)"
                    class="btn"
                    >Lihat nota pembayaran</Link
                ><button
                    v-if="canCancel"
                    class="rounded-xl border border-rose-200 px-4 py-2.5 text-sm text-rose-700"
                    :disabled="form.processing"
                    @click="cancel(reservation.id)"
                >
                    Batalkan reservasi
                </button>
            </div>
        </article>
    </ClinicLayout>
</template>
