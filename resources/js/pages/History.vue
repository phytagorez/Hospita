<script setup lang="ts">
import ClinicLayout from '@/components/ClinicLayout.vue';
import ReservationTable from '@/components/ReservationTable.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import type { Pagination, Reservation } from '@/types/clinic';
defineProps<{ reservations: Pagination<Reservation> }>();
</script>
<template>
    <ClinicLayout
        title="Riwayat kunjungan"
        description="Lihat seluruh reservasi Anda dan nota administrasi yang sudah dibayarkan."
        ><ReservationTable :reservations="reservations.data" /><PaginationLinks
            :links="reservations.links"
    /></ClinicLayout>
</template>
