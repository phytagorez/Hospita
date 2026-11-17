<script setup lang="ts">
import { computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import ClinicLayout from '@/components/ClinicLayout.vue';
import { create, store } from '@/routes/reservations';
import { money, type Schedule } from '@/types/clinic';
const props = defineProps<{
    scheduleId: number;
    date: string;
    schedules: Schedule[];
    today: string;
    maxDate: string;
}>();
const form = useForm({
    schedule_id: props.scheduleId || '',
    reservation_date: props.date,
    complaint: '',
});
const selected = computed(() =>
    props.schedules.find((s) => s.id === Number(form.schedule_id)),
);
function changeDate() {
    form.schedule_id = '';
    router.get(
        create().url,
        { date: form.reservation_date },
        { preserveState: true, preserveScroll: true },
    );
}
</script>
<template>
    <ClinicLayout
        title="Buat janji pemeriksaan"
        description="Pilih sesi praktik dan ceritakan keluhan awal Anda. Kuota akan diperiksa kembali saat reservasi dikirim."
    >
        <div class="grid gap-7 lg:grid-cols-3">
            <form
                class="card flex flex-col gap-6 lg:col-span-2"
                @submit.prevent="form.post(store().url)"
            >
                <label class="field"
                    >Tanggal kunjungan<input
                        v-model="form.reservation_date"
                        type="date"
                        :min="today"
                        :max="maxDate"
                        required
                        @change="changeDate"
                /></label>
                <label class="field"
                    >Dokter & sesi praktik<select
                        v-model="form.schedule_id"
                        required
                    >
                        <option value="" disabled>
                            Pilih dokter dan jadwal
                        </option>
                        <option
                            v-for="schedule in schedules"
                            :key="schedule.id"
                            :value="schedule.id"
                            :disabled="!schedule.remaining"
                        >
                            Poli {{ schedule.doctor.polyclinic.name }} ·
                            {{ schedule.doctor.name }} ·
                            {{ schedule.start_time.slice(0, 5) }}–{{
                                schedule.end_time.slice(0, 5)
                            }}
                            · Sisa {{ schedule.remaining }}
                        </option>
                    </select></label
                >
                <p
                    v-if="!schedules.length"
                    class="rounded-xl bg-amber-50 p-4 text-sm text-amber-800"
                >
                    Tidak ada sesi tersedia untuk tanggal ini. Silakan pilih
                    tanggal lain.
                </p>
                <label class="field"
                    >Keluhan awal<textarea
                        v-model="form.complaint"
                        rows="4"
                        minlength="5"
                        maxlength="2000"
                        required
                        placeholder="Contoh: Demam dan sakit kepala sejak dua hari yang lalu."
                    ></textarea
                    ><span class="text-right text-[10px] font-normal"
                        >{{ form.complaint.length }}/2000</span
                    ></label
                >
                <button
                    class="btn self-start"
                    :disabled="form.processing || !selected?.remaining"
                >
                    {{
                        form.processing
                            ? 'Memproses...'
                            : 'Konfirmasi reservasi →'
                    }}
                </button>
            </form>
            <aside class="card h-fit">
                <p class="section-kicker">RINGKASAN KUNJUNGAN</p>
                <template v-if="selected"
                    ><h2 class="mt-5 text-lg font-semibold">
                        {{ selected.doctor.name }}
                    </h2>
                    <p class="mt-2 text-sm text-slate-500">
                        Poli {{ selected.doctor.polyclinic.name }}<br />{{
                            selected.doctor.polyclinic.room_location
                        }}
                    </p>
                    <div
                        class="mt-6 border-y border-slate-100 py-4 text-sm dark:border-slate-800"
                    >
                        <p>
                            {{ form.reservation_date }} ·
                            {{ selected.start_time.slice(0, 5) }} WIB
                        </p>
                        <p class="mt-3 flex justify-between">
                            Administrasi
                            <strong>{{
                                money(selected.doctor.polyclinic.base_admin_fee)
                            }}</strong>
                        </p>
                    </div></template
                >
                <p class="mt-6 text-xs leading-6 text-slate-500">
                    Biaya konsultasi ditentukan di kasir setelah pemeriksaan.
                    Datang sebelum sesi dimulai dan tunjukkan QR tiket kepada
                    resepsionis.
                </p>
                <p class="mt-4 text-xs leading-6 text-slate-500">
                    Pembatalan mandiri tersedia sebelum sesi dimulai dan sebelum
                    check-in. Satu pasien hanya dapat memiliki satu reservasi
                    per poli per tanggal.
                </p>
            </aside>
        </div>
    </ClinicLayout>
</template>
