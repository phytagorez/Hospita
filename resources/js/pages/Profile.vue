<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import ClinicLayout from '@/components/ClinicLayout.vue';
import { update } from '@/routes/patient';
import type { Patient } from '@/types/clinic';
const props = defineProps<{ profile: Patient | null }>();
const form = useForm({
    nik: '',
    birth_date: '',
    gender: '',
    blood_type: 'unknown',
    phone: props.profile?.phone ?? '',
    address: props.profile?.address ?? '',
});
function submit() {
    form.transform((data) =>
        props.profile ? { phone: data.phone, address: data.address } : data,
    ).put(update().url);
}
</script>
<template>
    <ClinicLayout
        :title="profile ? 'Profil pasien' : 'Mari lengkapi profil Anda'"
        :description="
            profile
                ? 'Perbarui kontak agar klinik dapat menghubungi Anda.'
                : 'Identitas ini digunakan untuk penerbitan nomor rekam medis dan pendaftaran kunjungan.'
        "
    >
        <div class="grid gap-7 lg:grid-cols-3">
            <aside class="rounded-2xl bg-teal-900 p-7 text-white">
                <span class="text-xs tracking-[0.2em] text-teal-200"
                    >KARTU PASIEN DIGITAL</span
                >
                <h2 class="mt-9 text-2xl font-semibold">
                    {{ $page.props.auth.user?.name }}
                </h2>
                <p class="mt-3 text-xs text-teal-100">
                    {{ $page.props.auth.user?.email }}
                </p>
                <div class="mt-10 border-t border-white/20 pt-5">
                    <span class="text-[10px] tracking-widest text-teal-200"
                        >NOMOR REKAM MEDIS</span
                    >
                    <p class="mt-2 font-mono text-sm break-all">
                        {{
                            profile?.no_rm ||
                            'Diterbitkan setelah profil lengkap'
                        }}
                    </p>
                </div>
                <p v-if="profile" class="mt-5 text-xs leading-6 text-teal-100">
                    NIK: {{ profile.nik }}<br />Tanggal lahir:
                    {{ profile.birth_date }}<br />Golongan darah:
                    {{
                        profile.blood_type === 'unknown'
                            ? 'Belum diketahui'
                            : profile.blood_type || 'Belum diketahui'
                    }}
                </p>
            </aside>
            <form
                class="card flex flex-col gap-5 lg:col-span-2"
                @submit.prevent="submit"
            >
                <template v-if="!profile"
                    ><label class="field"
                        >NIK<input
                            v-model="form.nik"
                            inputmode="numeric"
                            pattern="[0-9]{16}"
                            maxlength="16"
                            required
                            placeholder="16 digit nomor identitas"
                    /></label>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <label class="field"
                            >Tanggal lahir<input
                                v-model="form.birth_date"
                                type="date"
                                required /></label
                        ><label class="field"
                            >Jenis kelamin<select
                                v-model="form.gender"
                                required
                            >
                                <option value="" disabled>
                                    Pilih jenis kelamin
                                </option>
                                <option value="male">Laki-laki</option>
                                <option value="female">Perempuan</option>
                            </select></label
                        >
                    </div>
                    <label class="field"
                        >Golongan darah<select v-model="form.blood_type">
                            <option value="unknown">Belum diketahui</option>
                            <option
                                v-for="blood in ['A', 'B', 'AB', 'O']"
                                :key="blood"
                                :value="blood"
                            >
                                {{ blood }}
                            </option>
                        </select></label
                    ></template
                ><label class="field"
                    >Nomor WhatsApp<input
                        v-model="form.phone"
                        type="tel"
                        required
                        placeholder="081234567890" /></label
                ><label class="field"
                    >Alamat lengkap<textarea
                        v-model="form.address"
                        rows="3"
                        required
                        minlength="5"
                        maxlength="1000"
                    ></textarea></label
                ><button class="btn self-start" :disabled="form.processing">
                    Simpan profil →
                </button>
                <p v-if="profile" class="text-xs text-slate-400">
                    Koreksi NIK dan tanggal lahir dapat dibantu oleh petugas
                    klinik setelah verifikasi identitas.
                </p>
            </form>
        </div>
    </ClinicLayout>
</template>
