<script setup lang="ts">
import { reactive } from 'vue';
import { Link, router, usePoll } from '@inertiajs/vue3';
import ClinicLayout from '@/components/ClinicLayout.vue';
import ScheduleCards from '@/components/ScheduleCards.vue';
import { schedules as explore } from '@/routes';
import type { Polyclinic, Schedule } from '@/types/clinic';
import { show } from '@/routes/reservations';
const props = defineProps<{
    polyclinics: Polyclinic[];
    schedules: Schedule[];
    filters: { date: string; polyclinic: number | string };
    today: string;
    maxDate: string;
    activeReservation: {
        id: number;
        booking_code: string;
        reservation_date: string;
    } | null;
}>();
const filters = reactive({ ...props.filters });
function search() {
    router.get(explore().url, filters, {
        preserveState: true,
        preserveScroll: true,
    });
}
usePoll(15000, { only: ['schedules', 'activeReservation'] });
</script>
<template>
    <ClinicLayout
        title="Dokter & jadwal praktik"
        description="Temukan dokter dan waktu kunjungan yang tepat. Reservasi tersedia hingga 30 hari ke depan."
    >
        <aside
            v-if="activeReservation"
            role="status"
            class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-950 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-100"
        >
            <div>
                <h2 class="font-semibold">
                    Anda masih memiliki satu janji aktif
                </h2>
                <p class="mt-1 text-sm">
                    Selesaikan atau batalkan janji
                    {{ activeReservation.booking_code }} sebelum membuat janji
                    baru. Anda tetap dapat melihat ketersediaan jadwal.
                </p>
            </div>
            <Link
                :href="show(activeReservation.id)"
                class="btn-secondary shrink-0"
                >Lihat janji saya</Link
            >
        </aside>
        <form
            class="card mb-7 flex flex-wrap items-end gap-4"
            @submit.prevent="search"
        >
            <label class="field min-w-48 flex-1"
                >Tanggal kunjungan<input
                    v-model="filters.date"
                    type="date"
                    :min="today"
                    :max="maxDate"
                    required /></label
            ><label class="field min-w-48 flex-1"
                >Poliklinik<select v-model="filters.polyclinic">
                    <option value="">Semua poliklinik</option>
                    <option
                        v-for="poli in polyclinics"
                        :key="poli.id"
                        :value="poli.id"
                    >
                        {{ poli.name }}
                    </option>
                </select></label
            ><button class="btn">Cari jadwal →</button>
        </form>
        <p class="mb-5 text-xs text-slate-500">
            {{ schedules.length }} sesi ditemukan · Waktu Indonesia Barat ·
            Kuota diperbarui setiap 15 detik
        </p>
        <p class="mb-5 text-sm text-slate-500">
            Sesi yang masih tersedia ditampilkan lebih dahulu. Sesi penuh atau
            sudah dimulai tetap ditampilkan sebagai informasi.
        </p>
        <ScheduleCards
            :schedules="schedules"
            :date="props.filters.date"
            :active-reservation-id="activeReservation?.id ?? null"
        />
    </ClinicLayout>
</template>
