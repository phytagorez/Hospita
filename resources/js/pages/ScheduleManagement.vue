<script setup lang="ts">
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import DatePicker from '@/components/DatePicker.vue';
import { confirmAction } from '@/lib/alerts';
import ClinicLayout from '@/components/ClinicLayout.vue';
import { store, update, destroy } from '@/routes/admin/master';
import { days, type Doctor, type Schedule } from '@/types/clinic';
defineProps<{ doctors: Doctor[]; schedules: Schedule[]; today: string }>();
const editing = ref<number | null>(null);
const form = useForm({
    doctor_id: '' as number | string,
    day_of_week: 1,
    start_time: '08:00',
    end_time: '12:00',
    max_quota: 20,
    is_active: true,
    closed_date: '',
});
function edit(item: Schedule) {
    editing.value = item.id;
    Object.assign(form, {
        doctor_id: item.doctor_id,
        day_of_week: item.day_of_week,
        start_time: item.start_time.slice(0, 5),
        end_time: item.end_time.slice(0, 5),
        max_quota: item.max_quota,
        is_active: item.is_active,
        closed_date: item.closed_date ?? '',
    });
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
function submit() {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            editing.value = null;
        },
    };
    if (editing.value)
        form.put(
            update({ entity: 'schedules', id: editing.value }).url,
            options,
        );
    else form.post(store('schedules').url, options);
}
async function remove(id: number) {
    if (
        await confirmAction(
            'Hapus sesi praktik?',
            'Sesi dengan riwayat reservasi harus dinonaktifkan.',
            'Hapus sesi',
        )
    )
        router.delete(destroy({ entity: 'schedules', id }).url, {
            preserveScroll: true,
        });
}
</script>
<template>
    <ClinicLayout
        title="Jadwal praktik & kuota"
        description="Atur sesi mingguan dan batas pasien. Gunakan tanggal penutupan untuk dokter yang berhalangan pada satu hari tertentu."
    >
        <form class="card mb-8 flex flex-col gap-5" @submit.prevent="submit">
            <h2 class="text-lg font-semibold">
                {{ editing ? 'Edit sesi praktik' : 'Tambah sesi praktik' }}
            </h2>
            <div
                class="grid gap-5 @min-[29rem]/content:grid-cols-2 @min-[56rem]/content:grid-cols-4"
            >
                <label class="field"
                    >Dokter<select v-model="form.doctor_id" required>
                        <option value="" disabled>Pilih dokter</option>
                        <option
                            v-for="doctor in doctors"
                            :key="doctor.id"
                            :value="doctor.id"
                        >
                            {{ doctor.name }}
                        </option>
                    </select></label
                ><label class="field"
                    >Hari<select v-model="form.day_of_week">
                        <option v-for="(day, i) in days" :key="day" :value="i">
                            {{ day }}
                        </option>
                    </select></label
                ><label class="field"
                    >Jam mulai (WIB)<input
                        v-model="form.start_time"
                        type="time"
                        required /></label
                ><label class="field"
                    >Jam selesai (WIB)<input
                        v-model="form.end_time"
                        type="time"
                        required /></label
                ><label class="field"
                    >Kuota per sesi<input
                        v-model="form.max_quota"
                        type="number"
                        min="1"
                        max="200"
                        required
                /></label>
                <DatePicker
                    v-model="form.closed_date"
                    label="Tanggal tutup (opsional)"
                    :min="today"
                    clearable
                />
                <label class="flex items-center gap-2 self-end py-3 text-sm"
                    ><input
                        v-model="form.is_active"
                        type="checkbox"
                        class="size-4 accent-blue-700"
                    />
                    Jadwal aktif</label
                >
                <div class="flex items-end gap-2">
                    <button class="btn" :disabled="form.processing">
                        Simpan sesi</button
                    ><button
                        v-if="editing"
                        type="button"
                        class="btn-secondary"
                        @click="
                            form.reset();
                            editing = null;
                        "
                    >
                        Batal
                    </button>
                </div>
            </div>
            <p class="text-xs text-slate-400">
                Penutupan sesi tidak membatalkan reservasi yang sudah masuk.
                Hubungi pasien terdampak untuk penjadwalan ulang.
            </p>
        </form>
        <div
            class="table-wrap"
            role="region"
            tabindex="0"
            aria-label="Daftar sesi praktik"
        >
            <table class="w-full">
                <thead>
                    <tr>
                        <th>Dokter / poli</th>
                        <th>Hari & sesi</th>
                        <th>Kuota</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in schedules" :key="item.id">
                        <td>
                            {{ item.doctor.name }}
                            <p class="mt-1 text-xs text-slate-400">
                                {{ item.doctor.polyclinic.name }}
                            </p>
                        </td>
                        <td>
                            {{ days[item.day_of_week] }}
                            <p class="mt-1 text-xs text-slate-400">
                                {{ item.start_time.slice(0, 5) }} –
                                {{ item.end_time.slice(0, 5) }}
                            </p>
                        </td>
                        <td>{{ item.max_quota }} pasien</td>
                        <td>
                            <span
                                :class="
                                    item.is_active
                                        ? 'text-blue-700 dark:text-blue-400'
                                        : 'text-slate-400'
                                "
                                >{{
                                    item.is_active ? 'Aktif' : 'Nonaktif'
                                }}</span
                            >
                            <p
                                v-if="item.closed_date"
                                class="mt-1 text-xs text-rose-500"
                            >
                                Tutup {{ item.closed_date }}
                            </p>
                        </td>
                        <td>
                            <div class="flex gap-3 text-xs">
                                <button
                                    class="text-blue-700 dark:text-blue-400"
                                    @click="edit(item)"
                                >
                                    Edit</button
                                ><button
                                    class="text-rose-600"
                                    @click="remove(item.id)"
                                >
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!schedules.length">
                        <td
                            colspan="5"
                            class="py-12 text-center text-slate-400"
                        >
                            Belum ada sesi praktik.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </ClinicLayout>
</template>
