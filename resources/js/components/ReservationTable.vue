<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import StatusBadge from '@/components/StatusBadge.vue';
import { show, receipt } from '@/routes/reservations';
import { dateLabel, queueLabel, type Reservation } from '@/types/clinic';
defineProps<{ reservations: Reservation[]; showPatient?: boolean }>();
</script>
<template>
    <div class="table-wrap">
        <table class="w-full whitespace-nowrap">
            <thead>
                <tr>
                    <th>Tiket / tanggal</th>
                    <th v-if="showPatient">Pasien</th>
                    <th>Dokter & poli</th>
                    <th>Antrean</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in reservations" :key="item.id">
                    <td>
                        <Link
                            :href="show(item.id)"
                            class="font-mono text-xs font-semibold text-teal-700 dark:text-teal-400"
                            >{{ item.booking_code }}</Link
                        ><small class="mt-1 block text-xs text-slate-400">{{
                            dateLabel(item.reservation_date)
                        }}</small>
                    </td>
                    <td v-if="showPatient">{{ item.patient.user.name }}</td>
                    <td>
                        {{ item.schedule.doctor.name
                        }}<small class="mt-1 block text-xs text-slate-400"
                            >Poli {{ item.schedule.doctor.polyclinic.name }} ·
                            {{ item.schedule.start_time.slice(0, 5) }}</small
                        >
                    </td>
                    <td class="font-mono text-lg">
                        {{ queueLabel(item.queue_number) }}
                    </td>
                    <td><StatusBadge :status="item.status" /></td>
                    <td>
                        <Link
                            v-if="item.transaction"
                            :href="receipt(item.id)"
                            class="text-xs font-semibold text-teal-700 dark:text-teal-400"
                            >Lihat nota ↗</Link
                        ><Link
                            v-else
                            :href="show(item.id)"
                            class="text-xs text-slate-500"
                            >Detail →</Link
                        >
                    </td>
                </tr>
                <tr v-if="!reservations.length">
                    <td
                        :colspan="showPatient ? 6 : 5"
                        class="py-12 text-center text-slate-400"
                    >
                        Belum ada kunjungan untuk ditampilkan.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
