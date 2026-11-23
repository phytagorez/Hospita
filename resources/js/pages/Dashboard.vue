<script setup lang="ts">
import { Link, usePoll } from '@inertiajs/vue3';
import ClinicLayout from '@/components/ClinicLayout.vue';
import ReservationTable from '@/components/ReservationTable.vue';
import AdminDashboard from '@/components/AdminDashboard.vue';
import { schedules } from '@/routes';
import { index as queue } from '@/routes/admin/queue';
import {
    queueLabel,
    type Reservation,
    type Monitor,
    type Schedule,
} from '@/types/clinic';
defineProps<{
    stats: {
        total: number;
        online: number;
        waiting: number;
        walk_in: number;
        completed: number;
    };
    reservations: Reservation[];
    monitor: Monitor[];
    week: { date: string; count: number }[];
    adminOverview: {
        schedules: Schedule[];
        services: { name: string; count: number }[];
        date: string;
    } | null;
}>();
usePoll(10000, {
    only: ['stats', 'reservations', 'monitor', 'week', 'adminOverview'],
});
</script>
<template>
    <ClinicLayout
        :title="
            $page.props.auth.user?.role === 'admin'
                ? 'Ringkasan operasional'
                : 'Halo, ' + $page.props.auth.user?.name.split(' ')[0] + '.'
        "
        :description="
            $page.props.auth.user?.role === 'admin'
                ? 'Pantau kunjungan dan aktivitas klinik hari ini dalam satu tampilan.'
                : 'Rencanakan kunjungan Anda dan pantau antrean tanpa khawatir terlewat.'
        "
    >
        <AdminDashboard
            v-if="$page.props.auth.user?.role === 'admin' && adminOverview"
            :stats="stats"
            :reservations="reservations"
            :monitor="monitor"
            :week="week"
            :overview="adminOverview"
        />
        <template v-else>
            <div class="mb-7 flex flex-wrap items-center justify-between gap-4">
                <span class="flex items-center gap-2 text-xs text-slate-500"
                    ><span class="size-2 rounded-full bg-teal-500"></span> Data
                    diperbarui setiap 10 detik · WIB</span
                ><Link
                    :href="
                        $page.props.auth.user?.role === 'admin'
                            ? queue()
                            : schedules()
                    "
                    class="btn"
                    >{{
                        $page.props.auth.user?.role === 'admin'
                            ? 'Buka meja antrean →'
                            : '+ Buat janji baru'
                    }}</Link
                >
            </div>
            <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="item in [
                        {
                            label: 'Kunjungan hari ini',
                            value: stats.total,
                            detail: 'Seluruh status reservasi',
                        },
                        {
                            label: 'Sudah check-in',
                            value: stats.waiting,
                            detail: 'Menunggu & diperiksa',
                        },
                        {
                            label: 'Kunjungan selesai',
                            value: stats.completed,
                            detail: 'Pemeriksaan telah selesai',
                        },
                        {
                            label:
                                $page.props.auth.user?.role === 'admin'
                                    ? 'Pasien walk-in'
                                    : 'Booking online',
                            value:
                                $page.props.auth.user?.role === 'admin'
                                    ? stats.walk_in
                                    : stats.online,
                            detail: 'Tercatat pada hari ini',
                        },
                    ]"
                    :key="item.label"
                    class="card"
                >
                    <p class="text-xs text-slate-500">{{ item.label }}</p>
                    <p class="mt-4 text-4xl font-semibold tracking-tight">
                        {{ item.value }}
                    </p>
                    <p class="mt-3 text-[10px] text-slate-400">
                        {{ item.detail }}
                    </p>
                </div>
            </div>
            <section class="mb-9 rounded-2xl bg-teal-900 p-6 text-white">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold">Sedang diperiksa</h2>
                    <span
                        class="rounded-full border border-white/20 px-2 py-1 text-[10px] tracking-widest"
                        >LIVE QUEUE</span
                    >
                </div>
                <div
                    v-if="monitor.length"
                    class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
                >
                    <div
                        v-for="item in monitor"
                        :key="item.id"
                        class="flex items-center gap-5 rounded-xl bg-white/10 p-4"
                    >
                        <strong class="font-mono text-4xl">{{
                            queueLabel(item.number)
                        }}</strong>
                        <div class="text-sm">
                            Poli {{ item.polyclinic
                            }}<small class="mt-1 block text-teal-200"
                                >{{ item.doctor }} · {{ item.room }}</small
                            >
                        </div>
                    </div>
                </div>
                <p v-else class="mt-4 text-sm text-teal-100/70">
                    Belum ada pasien yang sedang dipanggil. Nomor akan muncul
                    setelah petugas memanggil pasien.
                </p>
            </section>
            <section v-if="week.length" class="card mb-9">
                <p class="section-kicker">7 HARI TERAKHIR</p>
                <h2 class="mt-2 text-lg font-semibold">Aktivitas kunjungan</h2>
                <div class="mt-7 flex h-40 items-end gap-3">
                    <div
                        v-for="day in week"
                        :key="day.date"
                        class="flex h-full flex-1 flex-col items-center justify-end gap-2"
                    >
                        <span class="text-xs text-slate-500">{{
                            day.count
                        }}</span>
                        <div
                            class="w-full max-w-16 rounded-t-lg bg-teal-600/80"
                            :style="{
                                height:
                                    Math.max(
                                        3,
                                        (day.count /
                                            Math.max(
                                                1,
                                                ...week.map((d) => d.count),
                                            )) *
                                            100,
                                    ) + 'px',
                            }"
                        ></div>
                        <span class="text-[10px] text-slate-400">{{
                            day.date
                        }}</span>
                    </div>
                </div>
            </section>
            <h2 class="mb-5 text-xl font-semibold">
                {{
                    $page.props.auth.user?.role === 'admin'
                        ? 'Antrean aktif & reservasi mendatang'
                        : 'Janji temu Anda'
                }}
            </h2>
            <ReservationTable
                :reservations="reservations"
                :show-patient="$page.props.auth.user?.role === 'admin'"
            />
        </template>
    </ClinicLayout>
</template>
