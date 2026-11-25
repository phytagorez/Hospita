<script setup lang="ts">
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import ClinicLayout from '@/components/ClinicLayout.vue';
import ReservationTable from '@/components/ReservationTable.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { reports } from '@/routes/admin';
import {
    money,
    type Polyclinic,
    type Pagination,
    type Reservation,
} from '@/types/clinic';
const props = defineProps<{
    reservations: Pagination<Reservation>;
    summary: {
        name: string;
        visits: number;
        completed: number;
        revenue: number;
    }[];
    polyclinics: Polyclinic[];
    doctorSummary: {
        name: string;
        polyclinic: string;
        visits: number;
        completed: number;
        revenue: number;
    }[];
    filters: { from: string; to: string; polyclinic: number | string };
}>();
const filters = reactive({ ...props.filters });
</script>
<template>
    <ClinicLayout
        title="Laporan kunjungan & pendapatan"
        description="Rekapitulasi berdasarkan tanggal kunjungan. Pendapatan mencakup pembayaran yang tercatat untuk kunjungan dalam periode pilihan."
    >
        <form
            class="card mb-7 flex flex-wrap items-end gap-4"
            @submit.prevent="
                router.get(reports().url, filters, { preserveState: true })
            "
        >
            <label class="field flex-[1_1_10rem]"
                >Dari tanggal<input
                    v-model="filters.from"
                    type="date"
                    required /></label
            ><label class="field flex-[1_1_10rem]"
                >Sampai tanggal<input
                    v-model="filters.to"
                    type="date"
                    :min="filters.from"
                    required /></label
            ><label class="field flex-[1_1_10rem]"
                >Poliklinik<select v-model="filters.polyclinic">
                    <option value="">Semua poli</option>
                    <option
                        v-for="poli in polyclinics"
                        :key="poli.id"
                        :value="poli.id"
                    >
                        {{ poli.name }}
                    </option>
                </select></label
            ><button class="btn">Tampilkan</button>
        </form>
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <p class="text-sm text-slate-500">
                {{ reservations.total }} kunjungan · Total
                <strong class="text-slate-800 dark:text-white">{{
                    money(summary.reduce((sum, item) => sum + item.revenue, 0))
                }}</strong>
            </p>
            <div class="flex gap-3">
                <a
                    :href="
                        reports.url({
                            query: { ...props.filters, format: 'pdf' },
                        })
                    "
                    class="btn-secondary text-xs"
                    >↓ Unduh PDF</a
                ><a
                    :href="
                        reports.url({
                            query: { ...props.filters, format: 'xlsx' },
                        })
                    "
                    class="btn-secondary text-xs"
                    >↓ Unduh Excel</a
                >
            </div>
        </div>
        <div
            v-if="summary.length"
            class="mb-8 grid gap-4 @min-[26rem]/content:grid-cols-2 @min-[53rem]/content:grid-cols-4"
        >
            <article v-for="item in summary" :key="item.name" class="card">
                <p class="section-kicker">POLI {{ item.name.toUpperCase() }}</p>
                <p class="mt-4 text-3xl font-semibold">
                    {{ item.visits }}
                    <span class="text-xs font-normal text-slate-400"
                        >kunjungan</span
                    >
                </p>
                <p class="mt-3 text-xs text-slate-500">
                    {{ item.completed }} selesai · {{ money(item.revenue) }}
                </p>
            </article>
        </div>
        <section v-if="doctorSummary.length" class="mb-8">
            <h2 class="mb-4 text-lg font-semibold">Rekapitulasi per dokter</h2>
            <div
                class="table-wrap"
                role="region"
                tabindex="0"
                aria-label="Tabel laporan"
            >
                <table class="w-full">
                    <thead>
                        <tr>
                            <th>Dokter / poli</th>
                            <th>Kunjungan</th>
                            <th>Selesai</th>
                            <th>Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="doctor in doctorSummary" :key="doctor.name">
                            <td>
                                {{ doctor.name }}
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ doctor.polyclinic }}
                                </p>
                            </td>
                            <td>{{ doctor.visits }}</td>
                            <td>{{ doctor.completed }}</td>
                            <td>{{ money(doctor.revenue) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        <ReservationTable
            :reservations="reservations.data"
            show-patient
        /><PaginationLinks :links="reservations.links" />
    </ClinicLayout>
</template>
