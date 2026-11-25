<script setup lang="ts">
import ClinicLayout from '@/components/ClinicLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { dateLabel, queueLabel } from '@/types/clinic';

defineProps<{
    record: {
        id: number;
        name: string;
        medicalNumber: string;
        date: string;
        doctor: string;
        polyclinic: string;
        startTime: string;
        endTime: string;
        queueNumber: number;
        status: string;
        complaint: string;
    };
}>();

function printRecord() {
    window.print();
}
</script>

<template>
    <ClinicLayout
        title="Arsip reservasi pasien"
        description="Catatan kunjungan untuk petugas administrasi."
    >
        <article
            class="card mx-auto flex max-w-3xl flex-col gap-6 print:border-slate-300 print:bg-white print:text-black"
        >
            <header
                class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-200 pb-5 dark:border-slate-700"
            >
                <div>
                    <p class="section-kicker mb-2">
                        DOKUMEN INTERNAL · TIDAK BERLAKU UNTUK CHECK-IN
                    </p>
                    <h2 class="text-xl font-bold">
                        Reservasi #{{ record.id }}
                    </h2>
                </div>
                <StatusBadge :status="record.status" />
            </header>
            <dl
                class="grid gap-x-6 gap-y-5 text-sm @min-[36rem]/content:grid-cols-2"
            >
                <div>
                    <dt class="text-slate-500">Pasien</dt>
                    <dd class="mt-1 font-semibold">{{ record.name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Nomor rekam medis</dt>
                    <dd class="mt-1 font-semibold">
                        {{ record.medicalNumber }}
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Tanggal kunjungan</dt>
                    <dd class="mt-1 font-semibold">
                        {{ dateLabel(record.date) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Jam praktik</dt>
                    <dd class="mt-1 font-semibold">
                        {{ record.startTime.slice(0, 5) }}–{{
                            record.endTime.slice(0, 5)
                        }}
                        WIB
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Dokter</dt>
                    <dd class="mt-1 font-semibold">{{ record.doctor }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Poliklinik</dt>
                    <dd class="mt-1 font-semibold">{{ record.polyclinic }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Nomor antrean</dt>
                    <dd class="mt-1 font-semibold">
                        {{ queueLabel(record.queueNumber) }}
                    </dd>
                </div>
                <div class="@min-[36rem]/content:col-span-2">
                    <dt class="text-slate-500">Keluhan awal</dt>
                    <dd class="mt-1 whitespace-pre-wrap">
                        {{ record.complaint }}
                    </dd>
                </div>
            </dl>
            <div
                class="border-t border-slate-200 pt-5 dark:border-slate-700 print:hidden"
            >
                <button
                    type="button"
                    class="btn-secondary"
                    @click="printRecord"
                >
                    Cetak / simpan arsip PDF
                </button>
            </div>
        </article>
    </ClinicLayout>
</template>
