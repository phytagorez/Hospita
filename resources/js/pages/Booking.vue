<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import DatePicker from '@/components/DatePicker.vue';
import ClinicIcon from '@/components/ClinicIcon.vue';
import ClinicLayout from '@/components/ClinicLayout.vue';
import { create, store } from '@/routes/reservations';
import { dateLabel, money, type Schedule } from '@/types/clinic';
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
const loading = ref(false);
const doctors = computed(() => {
    const groups = new Map<
        number,
        { doctor: Schedule['doctor']; sessions: Schedule[] }
    >();
    for (const session of props.schedules) {
        const group = groups.get(session.doctor_id);
        if (group) group.sessions.push(session);
        else
            groups.set(session.doctor_id, {
                doctor: session.doctor,
                sessions: [session],
            });
    }
    return [...groups.values()];
});
const selected = computed(() =>
    props.schedules.find((session) => session.id === Number(form.schedule_id)),
);
const doctorId = ref<number | ''>(selected.value?.doctor_id ?? '');
const sessions = computed(
    () =>
        doctors.value.find(({ doctor }) => doctor.id === doctorId.value)
            ?.sessions ?? [],
);

watch(
    () => [props.date, props.scheduleId] as const,
    ([date, id]) => {
        form.reservation_date = date;
        form.schedule_id = id || '';
        doctorId.value = selected.value?.doctor_id ?? '';
    },
);
watch(
    () => form.reservation_date,
    (date) => {
        if (date === props.date) return;
        form.schedule_id = '';
        doctorId.value = '';
        form.clearErrors('schedule_id', 'reservation_date');
        router.get(
            create.url(),
            { date },
            {
                preserveState: true,
                preserveScroll: true,
                onStart: () => {
                    loading.value = true;
                },
                onError: () => {
                    form.reservation_date = props.date;
                },
                onFinish: () => {
                    loading.value = false;
                },
            },
        );
    },
);

function submit() {
    if (
        loading.value ||
        props.date !== form.reservation_date ||
        !selected.value
    )
        return;
    form.post(store().url, {
        onError: (errors) => {
            if (errors.schedule_id) {
                form.schedule_id = '';
                router.reload({ only: ['schedules'] });
            }
        },
    });
}
</script>
<template>
    <ClinicLayout
        title="Buat janji pemeriksaan"
        description="Pilih dokter dan jam praktik sesuai tanggal kunjungan Anda."
    >
        <div
            class="grid items-start gap-5 @min-[58rem]/content:grid-cols-[minmax(0,1fr)_minmax(16rem,0.42fr)]"
        >
            <form
                class="card @container/booking flex flex-col gap-5"
                @submit.prevent="submit"
            >
                <label
                    v-if="
                        doctors.length &&
                        !loading &&
                        form.reservation_date === date
                    "
                    class="field"
                >
                    Dokter
                    <select
                        v-model="doctorId"
                        required
                        @change="form.schedule_id = ''"
                    >
                        <option value="" disabled>Pilih dokter</option>
                        <option
                            v-for="{ doctor } in doctors"
                            :key="doctor.id"
                            :value="doctor.id"
                        >
                            {{ doctor.name }} · Poli
                            {{ doctor.polyclinic.name }}
                        </option>
                    </select>
                </label>
                <div
                    class="grid items-stretch gap-3 @min-[32rem]/booking:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]"
                >
                    <DatePicker
                        v-model="form.reservation_date"
                        label="Tanggal kunjungan"
                        :min="today"
                        :max="maxDate"
                        inline
                    />
                    <div
                        class="min-w-0 rounded-2xl bg-[#101e36] p-4 text-slate-100 sm:p-5"
                    >
                        <fieldset class="min-w-0">
                            <legend class="mb-3 text-sm font-bold">
                                Jam praktik (WIB)
                            </legend>
                            <p
                                v-if="loading"
                                role="status"
                                class="text-sm leading-6 text-slate-300"
                            >
                                Memuat sesi praktik…
                            </p>
                            <p
                                v-else-if="
                                    !schedules.length ||
                                    form.reservation_date !== date
                                "
                                class="text-sm leading-6 text-slate-300"
                            >
                                Belum ada sesi tersedia. Pilih tanggal lain.
                            </p>
                            <p
                                v-else-if="!sessions.length"
                                class="text-sm leading-6 text-slate-300"
                            >
                                Pilih dokter untuk melihat jam praktiknya.
                            </p>
                            <div
                                v-else
                                class="grid max-h-72 gap-2 overflow-y-auto @min-[24rem]/booking:grid-cols-2 @min-[32rem]/booking:grid-cols-1"
                            >
                                <label
                                    v-for="session in sessions"
                                    :key="session.id"
                                    class="flex cursor-pointer items-center justify-between gap-2 rounded-xl border border-white/15 px-3 py-2.5 text-sm has-[:checked]:border-sky-300 has-[:checked]:bg-sky-300 has-[:checked]:text-slate-950 has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-sky-300"
                                >
                                    <input
                                        v-model="form.schedule_id"
                                        :value="session.id"
                                        name="schedule"
                                        type="radio"
                                        required
                                        class="sr-only"
                                    />
                                    <strong
                                        >{{ session.start_time.slice(0, 5) }}–{{
                                            session.end_time.slice(0, 5)
                                        }}</strong
                                    >
                                    <span class="text-xs"
                                        >{{ session.remaining }} tersisa</span
                                    >
                                </label>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <label class="field">
                    Keluhan awal
                    <textarea
                        v-model="form.complaint"
                        rows="4"
                        minlength="5"
                        maxlength="2000"
                        required
                        placeholder="Contoh: Demam dan sakit kepala sejak dua hari yang lalu."
                    ></textarea>
                    <span class="text-right text-xs font-normal"
                        >{{ form.complaint.length }}/2000</span
                    >
                </label>
                <button
                    class="btn self-start"
                    :disabled="
                        form.processing ||
                        loading ||
                        form.reservation_date !== date ||
                        !selected
                    "
                >
                    {{
                        form.processing ? 'Memproses…' : 'Konfirmasi reservasi'
                    }}
                    <ClinicIcon name="arrow" :size="17" />
                </button>
            </form>
            <aside class="card">
                <div class="mb-4 flex items-center gap-3">
                    <span
                        class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200"
                    >
                        <ClinicIcon name="ticket" :size="20" />
                    </span>
                    <h2 class="text-base font-bold">Ringkasan kunjungan</h2>
                </div>
                <template v-if="selected">
                    <p class="font-bold">{{ selected.doctor.name }}</p>
                    <p class="mt-1 text-sm text-slate-500">
                        Poli {{ selected.doctor.polyclinic.name }} ·
                        {{ selected.doctor.polyclinic.room_location }}
                    </p>
                    <div
                        class="mt-4 space-y-2 border-y border-slate-200 py-4 text-sm dark:border-slate-700"
                    >
                        <p>{{ dateLabel(form.reservation_date) }}</p>
                        <p>
                            {{ selected.start_time.slice(0, 5) }}–{{
                                selected.end_time.slice(0, 5)
                            }}
                            WIB
                        </p>
                        <p class="flex justify-between gap-2">
                            Administrasi
                            <strong>{{
                                money(selected.doctor.polyclinic.base_admin_fee)
                            }}</strong>
                        </p>
                    </div>
                </template>
                <p v-else class="text-sm leading-6 text-slate-500">
                    Rincian dan biaya administrasi muncul setelah Anda memilih
                    jam.
                </p>
                <p
                    class="mt-4 text-xs leading-6 text-slate-500 dark:text-slate-400"
                >
                    Biaya konsultasi ditetapkan di kasir setelah pemeriksaan.
                    Pembatalan mandiri tersedia sebelum sesi dimulai dan sebelum
                    check-in.
                </p>
            </aside>
        </div>
    </ClinicLayout>
</template>
