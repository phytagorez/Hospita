<script setup lang="ts">
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { confirmAction } from '@/lib/alerts';
import ClinicLayout from '@/components/ClinicLayout.vue';
import { store, update, destroy } from '@/routes/admin/master';
import { money, type Polyclinic, type Doctor } from '@/types/clinic';
defineProps<{ polyclinics: Polyclinic[]; doctors: Doctor[] }>();
const poliId = ref<number | null>(null);
const doctorId = ref<number | null>(null);
const poli = useForm({
    name: '',
    code: '',
    room_location: '',
    base_admin_fee: 25000,
    is_active: true,
});
const doctor = useForm({
    name: '',
    sip_number: '',
    polyclinic_id: '' as number | string,
    photo: '',
    is_active: true,
});
function editPoli(item: Polyclinic) {
    poliId.value = item.id;
    Object.assign(poli, {
        name: item.name,
        code: item.code,
        room_location: item.room_location,
        base_admin_fee: item.base_admin_fee,
        is_active: item.is_active,
    });
}
function editDoctor(item: Doctor) {
    doctorId.value = item.id;
    Object.assign(doctor, {
        name: item.name,
        sip_number: item.sip_number,
        polyclinic_id: item.polyclinic_id,
        photo: item.photo ?? '',
        is_active: item.is_active,
    });
}
function savePoli() {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            poli.reset();
            poliId.value = null;
        },
    };
    if (poliId.value)
        poli.put(
            update({ entity: 'polyclinics', id: poliId.value }).url,
            options,
        );
    else poli.post(store('polyclinics').url, options);
}
function saveDoctor() {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            doctor.reset();
            doctorId.value = null;
        },
    };
    if (doctorId.value)
        doctor.put(
            update({ entity: 'doctors', id: doctorId.value }).url,
            options,
        );
    else doctor.post(store('doctors').url, options);
}
async function remove(entity: string, id: number) {
    if (
        await confirmAction(
            'Hapus data ini?',
            'Data yang masih digunakan harus dinonaktifkan.',
            'Hapus data',
        )
    )
        router.delete(destroy({ entity, id }).url, { preserveScroll: true });
}
</script>
<template>
    <ClinicLayout
        title="Poliklinik & dokter"
        description="Kelola layanan, tarif administrasi, dan tenaga dokter. Nonaktifkan data yang sudah memiliki riwayat kunjungan."
    >
        <section
            class="mb-10 grid items-start gap-6 @min-[62rem]/content:grid-cols-[minmax(17rem,1fr)_minmax(0,2fr)]"
        >
            <form class="card flex flex-col gap-4" @submit.prevent="savePoli">
                <h2 class="text-lg font-semibold">
                    {{ poliId ? 'Edit poliklinik' : 'Tambah poliklinik' }}
                </h2>
                <label class="field"
                    >Nama poli<input
                        v-model="poli.name"
                        required
                        maxlength="100"
                /></label>
                <div class="clinic-grid gap-4 [--clinic-column-min:7rem]">
                    <label class="field"
                        >Kode<input
                            v-model="poli.code"
                            required
                            maxlength="10" /></label
                    ><label class="field"
                        >Ruang<input
                            v-model="poli.room_location"
                            required
                            maxlength="100"
                    /></label>
                </div>
                <label class="field"
                    >Tarif administrasi (Rp)<input
                        v-model="poli.base_admin_fee"
                        type="number"
                        min="0"
                        max="10000000"
                        required /></label
                ><label class="flex items-center gap-2 text-sm"
                    ><input
                        v-model="poli.is_active"
                        type="checkbox"
                        class="size-4 accent-blue-700"
                    />
                    Aktif menerima pasien</label
                >
                <div class="flex gap-2">
                    <button class="btn" :disabled="poli.processing">
                        Simpan</button
                    ><button
                        v-if="poliId"
                        type="button"
                        class="btn-secondary"
                        @click="
                            poli.reset();
                            poliId = null;
                        "
                    >
                        Batal edit
                    </button>
                </div>
            </form>
            <div
                class="table-wrap h-fit"
                role="region"
                tabindex="0"
                aria-label="Daftar poliklinik"
            >
                <table class="w-full">
                    <thead>
                        <tr>
                            <th>Poliklinik</th>
                            <th>Ruang / tarif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in polyclinics" :key="item.id">
                            <td>
                                <strong class="font-medium">{{
                                    item.name
                                }}</strong>
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ item.code }} ·
                                    {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                                </p>
                            </td>
                            <td>
                                {{ item.room_location }}
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ money(item.base_admin_fee) }}
                                </p>
                            </td>
                            <td>
                                <div class="flex gap-3 text-xs">
                                    <button
                                        class="text-blue-700 dark:text-blue-400"
                                        @click="editPoli(item)"
                                    >
                                        Edit</button
                                    ><button
                                        class="text-rose-600"
                                        @click="remove('polyclinics', item.id)"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!polyclinics.length">
                            <td
                                colspan="3"
                                class="py-8 text-center text-slate-400"
                            >
                                Belum ada poliklinik.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        <section
            class="grid items-start gap-6 @min-[62rem]/content:grid-cols-[minmax(17rem,1fr)_minmax(0,2fr)]"
        >
            <form class="card flex flex-col gap-4" @submit.prevent="saveDoctor">
                <h2 class="text-lg font-semibold">
                    {{ doctorId ? 'Edit dokter' : 'Tambah dokter' }}
                </h2>
                <label class="field"
                    >Nama & gelar<input
                        v-model="doctor.name"
                        required
                        maxlength="150" /></label
                ><label class="field"
                    >Nomor SIP<input
                        v-model="doctor.sip_number"
                        required
                        maxlength="100" /></label
                ><label class="field"
                    >Poliklinik<select v-model="doctor.polyclinic_id" required>
                        <option value="" disabled>Pilih poli</option>
                        <option
                            v-for="item in polyclinics"
                            :key="item.id"
                            :value="item.id"
                        >
                            {{ item.name }}
                        </option>
                    </select></label
                ><label class="field"
                    >URL foto (HTTPS, opsional)<input
                        v-model="doctor.photo"
                        type="url"
                        maxlength="255"
                        placeholder="https://..." /></label
                ><label class="flex items-center gap-2 text-sm"
                    ><input
                        v-model="doctor.is_active"
                        type="checkbox"
                        class="size-4 accent-blue-700"
                    />
                    Dokter aktif</label
                >
                <div class="flex gap-2">
                    <button class="btn" :disabled="doctor.processing">
                        Simpan</button
                    ><button
                        v-if="doctorId"
                        type="button"
                        class="btn-secondary"
                        @click="
                            doctor.reset();
                            doctorId = null;
                        "
                    >
                        Batal edit
                    </button>
                </div>
            </form>
            <div
                class="table-wrap h-fit"
                role="region"
                tabindex="0"
                aria-label="Daftar dokter"
            >
                <table class="w-full">
                    <thead>
                        <tr>
                            <th>Dokter</th>
                            <th>Poli / status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in doctors" :key="item.id">
                            <td>
                                <strong class="font-medium">{{
                                    item.name
                                }}</strong>
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ item.sip_number }}
                                </p>
                            </td>
                            <td>
                                {{ item.polyclinic.name }}
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                                </p>
                            </td>
                            <td>
                                <div class="flex gap-3 text-xs">
                                    <button
                                        class="text-blue-700 dark:text-blue-400"
                                        @click="editDoctor(item)"
                                    >
                                        Edit</button
                                    ><button
                                        class="text-rose-600"
                                        @click="remove('doctors', item.id)"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!doctors.length">
                            <td
                                colspan="3"
                                class="py-8 text-center text-slate-400"
                            >
                                Belum ada dokter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </ClinicLayout>
</template>
