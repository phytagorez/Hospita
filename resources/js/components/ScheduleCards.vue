<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { create, show } from '@/routes/reservations';
import { money, type Schedule } from '@/types/clinic';

defineProps<{
    schedules: Schedule[];
    date: string;
    activeReservationId?: number | null;
}>();
const page = usePage();
</script>

<template>
    <div
        class="grid gap-5 @min-[35rem]/content:grid-cols-2 @min-[55rem]/content:grid-cols-3 @min-[76rem]/content:grid-cols-4"
    >
        <article
            v-for="schedule in schedules"
            :key="schedule.id"
            :class="[
                'card flex flex-col gap-5',
                schedule.available
                    ? 'border-blue-200 dark:border-blue-800'
                    : 'bg-slate-50/70 dark:bg-slate-900/70',
            ]"
        >
            <div class="flex items-start justify-between gap-3">
                <span
                    class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-800 dark:bg-blue-950 dark:text-blue-300"
                    >Poli {{ schedule.doctor.polyclinic.name }}</span
                ><span class="text-xs text-slate-400">{{
                    schedule.doctor.polyclinic.room_location
                }}</span>
            </div>
            <div class="flex items-center gap-4">
                <img
                    v-if="schedule.doctor.photo"
                    :src="schedule.doctor.photo"
                    alt="Foto dokter"
                    class="size-14 rounded-2xl object-cover"
                    referrerpolicy="no-referrer"
                />
                <div
                    v-else
                    class="grid size-14 shrink-0 place-items-center rounded-2xl bg-slate-100 text-lg font-semibold text-blue-700 dark:bg-slate-800"
                >
                    {{
                        schedule.doctor.name
                            .replace(/^dr[g]?\.?\s*/, '')
                            .slice(0, 2)
                            .toUpperCase()
                    }}
                </div>
                <div>
                    <h3 class="font-semibold">{{ schedule.doctor.name }}</h3>
                    <p class="mt-1 text-xs text-slate-400">
                        SIP {{ schedule.doctor.sip_number }}
                    </p>
                </div>
            </div>
            <div
                class="flex justify-between border-y border-slate-100 py-4 text-sm dark:border-slate-800"
            >
                <span
                    >{{ schedule.start_time.slice(0, 5) }} -
                    {{ schedule.end_time.slice(0, 5) }} WIB</span
                ><span class="font-medium"
                    >{{ money(schedule.doctor.polyclinic.base_admin_fee)
                    }}<small
                        class="block text-right text-[10px] font-normal text-slate-400"
                        >administrasi</small
                    ></span
                >
            </div>
            <div
                class="mt-auto flex flex-wrap items-center justify-between gap-3"
            >
                <div>
                    <span
                        :class="[
                            'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                            schedule.available
                                ? 'bg-blue-50 text-blue-800 dark:bg-blue-950 dark:text-blue-300'
                                : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                        ]"
                        >{{
                            schedule.available
                                ? 'Tersedia'
                                : schedule.unavailable_reason ||
                                  'Tidak tersedia'
                        }}</span
                    >
                    <p
                        v-if="schedule.available"
                        class="mt-2 text-xs text-blue-700 dark:text-blue-400"
                    >
                        {{ schedule.remaining }} kuota tersisa
                    </p>
                </div>
                <template v-if="page.props.auth.user?.role === 'patient'">
                    <Link
                        v-if="activeReservationId"
                        :href="show(activeReservationId)"
                        class="btn-secondary text-xs"
                        >Janji aktif saya</Link
                    >
                    <Link
                        v-else-if="schedule.available && schedule.remaining"
                        :href="
                            create({ query: { schedule: schedule.id, date } })
                        "
                        class="btn text-xs"
                        >Buat janji ↗</Link
                    >
                    <button
                        v-else
                        type="button"
                        disabled
                        class="cursor-not-allowed rounded-xl bg-slate-100 px-4 py-2.5 text-xs font-medium text-slate-400 dark:bg-slate-800"
                    >
                        Tidak dapat dipesan
                    </button>
                </template>
            </div>
        </article>
        <div
            v-if="!schedules.length"
            class="card col-span-full py-12 text-center text-sm text-slate-500"
        >
            Belum ada jadwal untuk pilihan ini. Silakan pilih tanggal atau
            poliklinik lain.
        </div>
    </div>
</template>
