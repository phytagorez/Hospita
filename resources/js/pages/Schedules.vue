<script setup lang="ts">
import { reactive } from 'vue';
import { router, usePoll } from '@inertiajs/vue3';
import ClinicLayout from '@/components/ClinicLayout.vue';
import ScheduleCards from '@/components/ScheduleCards.vue';
import { schedules as explore } from '@/routes';
import type { Polyclinic, Schedule } from '@/types/clinic';
const props = defineProps<{
    polyclinics: Polyclinic[];
    schedules: Schedule[];
    filters: { date: string; polyclinic: number | string };
    today: string;
    maxDate: string;
}>();
const filters = reactive({ ...props.filters });
function search() {
    router.get(explore().url, filters, {
        preserveState: true,
        preserveScroll: true,
    });
}
usePoll(15000, { only: ['schedules'] });
</script>
<template>
    <ClinicLayout
        title="Dokter & jadwal praktik"
        description="Temukan dokter dan waktu kunjungan yang tepat. Reservasi tersedia hingga 30 hari ke depan."
    >
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
        <ScheduleCards :schedules="schedules" :date="props.filters.date" />
    </ClinicLayout>
</template>
