<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import ClinicLayout from '@/components/ClinicLayout.vue';
import { store } from '@/routes/login';
import { redirect } from '@/routes/google';
defineProps<{ googleEnabled: boolean; localMode: boolean }>();
const form = useForm({ email: '', password: '', remember: false });
</script>
<template>
    <ClinicLayout
        title="Senang melihat Anda kembali."
        eyebrow="AKSES KLINIK SEHAT"
        description="Masuk untuk mengelola kunjungan dan mendapatkan layanan yang lebih terencana."
    >
        <div
            class="mx-auto grid max-w-4xl overflow-hidden rounded-3xl border border-slate-200 bg-white md:grid-cols-2 dark:border-slate-800 dark:bg-slate-900"
        >
            <section
                class="flex flex-col justify-between gap-12 bg-teal-900 p-9 text-white"
            >
                <div>
                    <span class="text-xs tracking-[0.2em] text-teal-200"
                        >UNTUK PASIEN</span
                    >
                    <h2 class="mt-5 text-3xl leading-tight font-semibold">
                        Perawatan dimulai<br />dari satu langkah.
                    </h2>
                    <p class="mt-4 text-sm leading-7 text-teal-100/80">
                        Gunakan akun Google Anda. Setelah masuk, lengkapi
                        identitas untuk mendapatkan nomor rekam medis digital.
                    </p>
                </div>
                <div>
                    <a
                        v-if="googleEnabled"
                        :href="redirect.url()"
                        class="flex items-center justify-center gap-3 rounded-xl bg-white p-3 text-sm font-semibold text-teal-900"
                        ><span class="font-bold">G</span> Lanjutkan dengan
                        Google</a
                    >
                    <p
                        v-else
                        class="rounded-xl border border-white/20 p-4 text-sm leading-6 text-teal-100"
                    >
                        Login Google sedang disiapkan oleh pengelola.<span
                            v-if="localMode"
                            class="mt-2 block"
                            >Untuk pengujian lokal, gunakan akun pasien pada
                            formulir di samping.</span
                        >
                    </p>
                </div>
            </section>
            <form
                class="flex flex-col gap-5 p-9"
                @submit.prevent="
                    form.post(store().url, {
                        onFinish: () => form.reset('password'),
                    })
                "
            >
                <div>
                    <span class="section-kicker">AKSES INTERNAL</span>
                    <h2 class="mt-3 text-2xl font-semibold">Masuk ke akun</h2>
                    <p class="mt-2 text-sm text-slate-500">
                        {{
                            localMode
                                ? 'Admin dan akun pasien uji lokal.'
                                : 'Khusus admin / resepsionis klinik.'
                        }}
                    </p>
                </div>
                <label class="field"
                    >Email<input
                        v-model="form.email"
                        type="email"
                        autocomplete="username"
                        required
                        placeholder="nama@klinik.test" /></label
                ><label class="field"
                    >Kata sandi<input
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        required
                        placeholder="Masukkan kata sandi" /></label
                ><label class="flex items-center gap-2 text-sm text-slate-500"
                    ><input
                        v-model="form.remember"
                        type="checkbox"
                        class="size-4 accent-teal-700"
                    />
                    Ingat saya</label
                ><button class="btn justify-center" :disabled="form.processing">
                    {{ form.processing ? 'Memeriksa...' : 'Masuk ke akun →' }}
                </button>
                <p class="text-xs leading-6 text-slate-400">
                    Data identitas Anda hanya dapat diakses oleh petugas klinik
                    yang berwenang.
                </p>
            </form>
        </div>
    </ClinicLayout>
</template>
