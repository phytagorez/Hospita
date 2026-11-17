<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { create } from '@/routes/reservations';
import { login } from '@/routes';
import { money, type Schedule } from '@/types/clinic';
defineProps<{ schedules: Schedule[]; date: string }>();
const page = usePage();
</script>
<template>
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        <article
            v-for="schedule in schedules"
            :key="schedule.id"
            class="card flex flex-col gap-5"
        >
            <div class="flex items-start justify-between gap-3">
                <span
                    class="rounded-lg bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-800 dark:bg-teal-950 dark:text-teal-300"
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
                    class="grid size-14 shrink-0 place-items-center rounded-2xl bg-slate-100 text-lg font-semibold text-teal-700 dark:bg-slate-800"
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
                    >{{ schedule.start_time.slice(0, 5) }} –
                    {{ schedule.end_time.slice(0, 5) }} WIB</span
                ><span class="font-medium"
                    >{{ money(schedule.doctor.polyclinic.base_admin_fee)
                    }}<small
                        class="block text-right text-[10px] font-normal text-slate-400"
                        >administrasi</small
                    ></span
                >
            </div>
            <div class="flex items-center justify-between gap-3">
                <span
                    :class="[
                        'text-xs',
                        schedule.remaining && schedule.available
                            ? 'text-teal-700 dark:text-teal-400'
                            : 'text-slate-400',
                    ]"
                    >{{
                        !schedule.available
                            ? 'Sesi ditutup'
                            : schedule.remaining
                              ? schedule.remaining + ' kuota tersedia'
                              : 'Kuota penuh'
                    }}</span
                ><Link
                    v-if="
                        schedule.available &&
                        schedule.remaining &&
                        page.props.auth.user?.role !== 'admin'
                    "
                    :href="
                        page.props.auth.user
                            ? create({ query: { schedule: schedule.id, date } })
                            : login()
                    "
                    class="btn text-xs"
                    >Buat janji ↗</Link
                ><span v-else class="text-xs text-slate-400">{{
                    page.props.auth.user?.role === 'admin'
                        ? 'Sesi praktik'
                        : 'Tidak tersedia'
                }}</span>
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
