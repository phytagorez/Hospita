<script setup lang="ts">
import ClinicLayout from '@/components/ClinicLayout.vue';
import ReservationTable from '@/components/ReservationTable.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import {
    dateLabel,
    type Patient,
    type Pagination,
    type Reservation,
} from '@/types/clinic';
defineProps<{ patient: Patient; reservations: Pagination<Reservation> }>();
</script>
<template>
    <ClinicLayout
        :title="patient.user.name"
        description="Identitas dasar dan riwayat kedatangan pasien."
        ><div
            class="card mb-8 grid gap-6 text-sm @min-[27rem]/content:grid-cols-2 @min-[55rem]/content:grid-cols-3"
        >
            <div>
                <p class="section-kicker">NO. REKAM MEDIS</p>
                <p class="mt-2 font-mono break-all">{{ patient.no_rm }}</p>
            </div>
            <div>
                <p class="section-kicker">IDENTITAS</p>
                <p class="mt-2">
                    {{ patient.nik }}<br /><span class="text-xs text-slate-500"
                        >{{ dateLabel(patient.birth_date) }} ·
                        {{
                            patient.gender === 'male'
                                ? 'Laki-laki'
                                : 'Perempuan'
                        }}</span
                    >
                </p>
            </div>
            <div>
                <p class="section-kicker">KONTAK</p>
                <p class="mt-2">
                    {{ patient.phone }}<br /><span
                        class="text-xs text-slate-500"
                        >{{ patient.address }}</span
                    >
                </p>
            </div>
        </div>
        <h2 class="mb-5 text-lg font-semibold">Riwayat kunjungan</h2>
        <ReservationTable :reservations="reservations.data" /><PaginationLinks
            :links="reservations.links"
    /></ClinicLayout>
</template>
