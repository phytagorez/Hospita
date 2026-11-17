<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ClinicLayout from '@/components/ClinicLayout.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { index, show } from '@/routes/admin/patients';
import type { Patient, Pagination } from '@/types/clinic';
const props = defineProps<{ patients: Pagination<Patient>; search: string }>();
const search = ref(props.search);
</script>
<template>
    <ClinicLayout
        title="Database pasien"
        description="Cari identitas pasien dan telusuri riwayat kunjungan berdasarkan nomor rekam medis, NIK, atau nama."
    >
        <form
            class="mb-6 flex gap-3"
            @submit.prevent="
                router.get(index().url, { search }, { preserveState: true })
            "
        >
            <input
                v-model="search"
                aria-label="Cari pasien"
                placeholder="Cari No. RM, NIK, atau nama..."
                maxlength="100"
            /><button class="btn">Cari pasien</button>
        </form>
        <p class="mb-4 text-xs text-slate-500">
            {{ patients.total }} pasien ditemukan
        </p>
        <div class="table-wrap">
            <table class="w-full whitespace-nowrap">
                <thead>
                    <tr>
                        <th>Pasien</th>
                        <th>No. RM</th>
                        <th>NIK</th>
                        <th>WhatsApp</th>
                        <th>Kunjungan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="patient in patients.data" :key="patient.id">
                        <td class="font-medium">{{ patient.user.name }}</td>
                        <td class="font-mono text-xs">{{ patient.no_rm }}</td>
                        <td class="text-xs">{{ patient.nik }}</td>
                        <td>{{ patient.phone }}</td>
                        <td>{{ patient.reservations_count }}</td>
                        <td>
                            <Link
                                :href="show(patient.id)"
                                class="text-xs font-semibold text-teal-700 dark:text-teal-400"
                                >Detail & riwayat →</Link
                            >
                        </td>
                    </tr>
                    <tr v-if="!patients.data.length">
                        <td
                            colspan="6"
                            class="py-12 text-center text-slate-400"
                        >
                            Tidak ada pasien yang cocok.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <PaginationLinks :links="patients.links" />
    </ClinicLayout>
</template>
