<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ClinicIcon from '@/components/ClinicIcon.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import ReservationTable from '@/components/ReservationTable.vue';
import { index as queue } from '@/routes/admin/queue';
import { schedules as management } from '@/routes/admin/master';
import { reports } from '@/routes/admin';
import {
    queueLabel,
    type Reservation,
    type Monitor,
    type Schedule,
} from '@/types/clinic';

const props = defineProps<{
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
    overview: {
        schedules: Schedule[];
        services: { name: string; count: number }[];
        date: string;
    };
}>();
const cards = computed(() => [
    {
        label: 'Kunjungan hari ini',
        value: props.stats.total,
        detail: 'Seluruh reservasi hari ini',
        icon: 'users',
        color: 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
    },
    {
        label: 'Sedang dilayani',
        value: props.stats.waiting,
        detail: 'Menunggu & dalam pemeriksaan',
        icon: 'clock',
        color: 'bg-sky-50 text-sky-600 dark:bg-sky-950 dark:text-sky-300',
    },
    {
        label: 'Kunjungan selesai',
        value: props.stats.completed,
        detail: 'Pemeriksaan telah selesai',
        icon: 'check',
        color: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-300',
    },
    {
        label: 'Pasien walk-in',
        value: props.stats.walk_in,
        detail: 'Pendaftaran langsung di klinik',
        icon: 'ticket',
        color: 'bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-300',
    },
]);
const distribution = computed(() => [
    { label: 'Selesai', count: props.stats.completed, color: '#1d4ed8' },
    {
        label: 'Menunggu / diperiksa',
        count: props.stats.waiting,
        color: '#38bdf8',
    },
    {
        label: 'Status lainnya',
        count: Math.max(
            0,
            props.stats.total - props.stats.completed - props.stats.waiting,
        ),
        color: '#cbd5e1',
    },
]);
const weekTotal = computed(() =>
    props.week.reduce((sum, day) => sum + day.count, 0),
);
const chartMax = computed(() =>
    Math.max(1, ...props.week.map((day) => day.count)),
);
const todayIso = () =>
    new Intl.DateTimeFormat('en-CA', {
        timeZone: 'Asia/Jakarta',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(new Date());
const isClosed = (schedule: Schedule) =>
    schedule.closed_date?.slice(0, 10) === todayIso();
</script>

<template>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <span class="relative flex size-2"
                ><span
                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-blue-400 opacity-40 motion-reduce:animate-none" /><span
                    class="relative size-2 rounded-full bg-blue-500" /></span
            >Diperbarui otomatis setiap 10 detik<span
                class="mx-1 hidden text-slate-300 sm:inline"
                >|</span
            ><span class="hidden sm:inline">{{ overview.date }} · WIB</span>
        </div>
        <Link :href="queue()" class="btn py-2.5"
            ><ClinicIcon name="plus" :size="16" />Buka meja antrean</Link
        >
    </div>
    <div
        class="mb-6 grid gap-4 @min-[26rem]/content:grid-cols-2 @min-[53rem]/content:grid-cols-4"
    >
        <article v-for="card in cards" :key="card.label" class="card !p-5">
            <div class="flex items-center gap-4">
                <span
                    :class="[
                        'grid size-12 shrink-0 place-items-center rounded-2xl',
                        card.color,
                    ]"
                    ><ClinicIcon :name="card.icon" :size="23"
                /></span>
                <div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        {{ card.label }}
                    </p>
                    <p class="mt-1 text-3xl font-semibold tracking-tight">
                        {{ card.value.toLocaleString('id-ID') }}
                    </p>
                </div>
            </div>
            <p
                class="mt-4 border-t border-slate-100 pt-3 text-[10px] text-slate-400 dark:border-slate-800"
            >
                {{ card.detail }}
            </p>
        </article>
    </div>

    <div
        class="grid items-start gap-5 @min-[43rem]/content:grid-cols-2 @min-[64rem]/content:grid-cols-12"
    >
        <section
            class="card @min-[64rem]/content:col-span-6"
            aria-labelledby="overview-title"
        >
            <div class="flex items-center justify-between">
                <h2 id="overview-title" class="text-sm font-semibold">
                    Status kunjungan
                </h2>
                <span
                    class="rounded-md bg-slate-50 px-2 py-1 text-[9px] text-slate-400 dark:bg-slate-800"
                    >Hari ini</span
                >
            </div>
            <ul class="mt-5 space-y-4">
                <li v-for="item in distribution" :key="item.label">
                    <div class="mb-1.5 flex justify-between gap-3 text-xs">
                        <span>{{ item.label }}</span>
                        <strong>{{ item.count }}</strong>
                    </div>
                    <div
                        aria-hidden="true"
                        class="h-2 rounded-full bg-slate-100 dark:bg-slate-800"
                    >
                        <div
                            class="h-2 rounded-full"
                            :style="{
                                width:
                                    (stats.total
                                        ? (item.count / stats.total) * 100
                                        : 0) + '%',
                                backgroundColor: item.color,
                            }"
                        ></div>
                    </div>
                </li>
            </ul>
            <p class="mt-4 text-[9px] leading-5 text-slate-400">
                Status lainnya mencakup dipesan, dilewati, dan dibatalkan.
            </p>
        </section>

        <section
            class="card @min-[64rem]/content:col-span-6"
            aria-labelledby="chart-title"
        >
            <div class="flex items-start justify-between">
                <div>
                    <h2 id="chart-title" class="text-sm font-semibold">
                        Aktivitas kunjungan
                    </h2>
                    <p class="mt-1 text-[10px] text-slate-400">
                        7 hari terakhir, termasuk hari ini
                    </p>
                </div>
                <Link
                    :href="reports()"
                    class="text-[11px] font-medium text-blue-700 dark:text-blue-300"
                    >Laporan →</Link
                >
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <strong class="text-3xl font-semibold tracking-tight">{{
                    weekTotal
                }}</strong
                ><span class="text-xs text-slate-400">total kunjungan</span>
            </div>
            <svg
                viewBox="0 0 420 180"
                class="mt-3 h-44 w-full overflow-visible"
                role="img"
                :aria-label="
                    week
                        .map((day) => `${day.date}: ${day.count} kunjungan`)
                        .join(', ')
                "
            >
                <line
                    v-for="y in [35, 90, 145]"
                    :key="y"
                    x1="30"
                    :y1="y"
                    x2="390"
                    :y2="y"
                    stroke="currentColor"
                    class="text-slate-100 dark:text-slate-800"
                    stroke-dasharray="4 4"
                />
                <g v-for="(day, i) in week" :key="i">
                    <rect
                        :x="17 + i * 60"
                        :y="145 - (day.count / chartMax) * 110"
                        width="26"
                        :height="(day.count / chartMax) * 110"
                        rx="4"
                        fill="currentColor"
                        :class="
                            i === week.length - 1
                                ? 'text-blue-700 dark:text-blue-400'
                                : 'text-sky-400 dark:text-sky-600'
                        "
                    >
                        <title>{{ day.date }}: {{ day.count }} kunjungan</title>
                    </rect>
                    <text
                        :x="30 + i * 60"
                        :y="131 - (day.count / chartMax) * 110"
                        text-anchor="middle"
                        font-size="10"
                        fill="currentColor"
                        class="text-slate-400"
                    >
                        {{ day.count }}
                    </text>
                    <text
                        :x="30 + i * 60"
                        y="173"
                        text-anchor="middle"
                        font-size="10"
                        fill="currentColor"
                        class="text-slate-400"
                    >
                        {{ day.date }}
                    </text>
                </g>
            </svg>
        </section>

        <section
            class="card !p-0 @min-[64rem]/content:col-span-5"
            aria-labelledby="schedule-title"
        >
            <div
                class="flex items-center justify-between gap-2 border-b border-slate-100 px-5 py-4 dark:border-slate-800"
            >
                <div>
                    <h2 id="schedule-title" class="text-sm font-semibold">
                        Jadwal dokter hari ini
                    </h2>
                    <p class="mt-1 text-[10px] text-slate-400">
                        Sesi praktik yang tercatat
                    </p>
                </div>
                <Link
                    :href="management()"
                    aria-label="Kelola seluruh jadwal dokter"
                    class="text-blue-700 dark:text-blue-300"
                    ><ClinicIcon name="arrow" :size="17"
                /></Link>
            </div>
            <div class="px-5">
                <div
                    v-for="schedule in overview.schedules.slice(0, 5)"
                    :key="schedule.id"
                    class="flex items-center gap-3 border-b border-slate-100 py-4 last:border-0 dark:border-slate-800"
                >
                    <span
                        class="grid size-9 shrink-0 place-items-center rounded-xl bg-slate-50 text-blue-600 dark:bg-slate-800 dark:text-blue-300"
                        ><ClinicIcon name="doctor" :size="18"
                    /></span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-semibold">
                            {{ schedule.doctor.name }}
                        </p>
                        <p class="mt-1 truncate text-[10px] text-slate-400">
                            Poli {{ schedule.doctor.polyclinic.name }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p
                            class="text-[10px] font-medium text-blue-700 dark:text-blue-300"
                        >
                            {{ schedule.start_time.slice(0, 5) }}–{{
                                schedule.end_time.slice(0, 5)
                            }}
                        </p>
                        <p
                            :class="[
                                'mt-1 text-[9px]',
                                isClosed(schedule)
                                    ? 'text-rose-500'
                                    : 'text-slate-400',
                            ]"
                        >
                            {{
                                isClosed(schedule)
                                    ? 'Jadwal ditutup'
                                    : schedule.max_quota + ' kuota / sesi'
                            }}
                        </p>
                    </div>
                </div>
                <p
                    v-if="!overview.schedules.length"
                    class="flex min-h-72 items-center justify-center px-3 py-12 text-center text-xs leading-6 text-slate-400"
                >
                    Belum ada jadwal praktik aktif untuk hari ini.
                </p>
            </div>
            <Link
                :href="management()"
                class="flex items-center justify-center gap-2 border-t border-slate-100 py-3 text-[11px] font-medium text-blue-700 hover:bg-blue-50 dark:border-slate-800 dark:text-blue-300 dark:hover:bg-blue-950"
                ><ClinicIcon name="calendar" :size="14" />Lihat seluruh
                jadwal</Link
            >
        </section>

        <section
            class="card @min-[64rem]/content:col-span-4"
            aria-labelledby="services-title"
        >
            <h2 id="services-title" class="text-sm font-semibold">
                Kunjungan per poli
            </h2>
            <p class="mt-1 text-[10px] text-slate-400">
                Berdasarkan reservasi hari ini
            </p>
            <div class="mt-7 space-y-5">
                <div
                    v-for="service in overview.services.slice(0, 5)"
                    :key="service.name"
                >
                    <div class="mb-2 flex justify-between gap-3 text-[11px]">
                        <span class="text-slate-500 dark:text-slate-400">{{
                            service.name
                        }}</span
                        ><span class="font-semibold">{{ service.count }}</span>
                    </div>
                    <div
                        class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                    >
                        <div
                            class="h-full rounded-full bg-blue-500"
                            :style="{
                                width: `${(service.count / Math.max(1, stats.total)) * 100}%`,
                            }"
                        />
                    </div>
                </div>
                <p
                    v-if="!overview.services.length"
                    class="py-10 text-center text-xs leading-6 text-slate-400"
                >
                    Belum ada kunjungan hari ini.
                </p>
            </div>
        </section>

        <section
            class="rounded-2xl bg-blue-900 p-5 text-white @min-[64rem]/content:col-span-3"
            aria-labelledby="monitor-title"
        >
            <div class="flex items-center justify-between">
                <h2 id="monitor-title" class="text-sm font-semibold">
                    Sedang diperiksa
                </h2>
                <span
                    class="flex items-center gap-1.5 rounded-full bg-white/10 px-2 py-1 text-[8px] tracking-widest text-blue-100"
                    ><span class="size-1 rounded-full bg-blue-300" />LIVE</span
                >
            </div>
            <div
                v-if="monitor.length"
                class="mt-5 max-h-56 space-y-3 overflow-auto"
            >
                <div
                    v-for="item in monitor"
                    :key="item.id"
                    class="rounded-xl border border-white/10 bg-white/5 p-3"
                >
                    <div class="flex items-center justify-between gap-3">
                        <strong class="font-mono text-3xl">{{
                            queueLabel(item.number)
                        }}</strong
                        ><span class="text-right text-[11px] text-blue-100"
                            >Poli {{ item.polyclinic }}</span
                        >
                    </div>
                    <p class="mt-2 text-[10px] text-blue-100/60">
                        {{ item.doctor }} · {{ item.room }}
                    </p>
                </div>
            </div>
            <div
                v-else
                class="flex min-h-48 flex-col items-center justify-center text-center"
            >
                <ClinicIcon name="doctor" :size="30" class="text-blue-200/40" />
                <p class="mt-4 text-xs text-blue-100">
                    Belum ada pemeriksaan aktif
                </p>
                <p class="mt-2 text-[10px] leading-6 text-blue-100/50">
                    Nomor tampil saat petugas memanggil pasien.
                </p>
            </div>
            <Link
                :href="queue()"
                class="mt-4 flex items-center justify-between border-t border-white/10 pt-4 text-[11px] text-blue-100"
                >Kelola antrean<ClinicIcon name="arrow" :size="15"
            /></Link>
        </section>
    </div>

    <section class="mt-7">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-base font-semibold">Detail reservasi aktif</h2>
            <span class="text-[10px] text-slate-400"
                >Maksimal 20 reservasi terdekat</span
            >
        </div>
        <ReservationTable :reservations="reservations" show-patient />
    </section>
</template>
