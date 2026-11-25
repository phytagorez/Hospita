<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import ClinicLayout from '@/components/ClinicLayout.vue';
import { store } from '@/routes/login';
import { redirect } from '@/routes/google';
defineProps<{ googleEnabled: boolean; localMode: boolean }>();
const form = useForm({ email: '', password: '', remember: false });
const showPassword = ref(false);
</script>
<template>
    <ClinicLayout
        title="Senang melihat Anda kembali."
        eyebrow="AKSES HOSPIA"
        description="Masuk untuk mengelola kunjungan dan mendapatkan layanan yang lebih terencana."
    >
        <div
            class="mx-auto grid max-w-4xl overflow-hidden rounded-3xl border border-slate-200 bg-white @min-[42rem]/content:grid-cols-2 dark:border-slate-800 dark:bg-slate-900"
        >
            <section
                class="flex min-w-0 flex-col justify-between gap-7 bg-blue-900 p-[clamp(1.25rem,4cqi,2.25rem)] text-white"
            >
                <div>
                    <span class="text-xs tracking-[0.2em] text-blue-200"
                        >UNTUK PASIEN</span
                    >
                    <h2 class="mt-5 text-3xl leading-tight font-semibold">
                        Perawatan dimulai<br />dari satu langkah.
                    </h2>
                    <p class="mt-4 text-sm leading-7 text-blue-100/80">
                        Gunakan akun Google Anda. Setelah masuk, lengkapi
                        identitas untuk mendapatkan nomor rekam medis digital.
                    </p>
                </div>
                <div>
                    <a
                        v-if="googleEnabled"
                        :href="redirect.url()"
                        class="flex items-center justify-center gap-3 rounded-xl bg-white p-3 text-sm font-semibold text-blue-900"
                        ><span class="font-bold">G</span> Lanjutkan dengan
                        Google</a
                    >
                    <p
                        v-else
                        class="rounded-xl border border-white/20 p-4 text-sm leading-6 text-blue-100"
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
                class="flex min-w-0 flex-col gap-5 p-[clamp(1.25rem,4cqi,2.25rem)]"
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
                        placeholder="nama@klinik.test"
                /></label>
                <div class="field">
                    <label for="password">Kata sandi</label>
                    <div class="relative">
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="pr-14"
                            autocomplete="current-password"
                            required
                            placeholder="Masukkan kata sandi"
                        />
                        <button
                            type="button"
                            class="absolute inset-y-0 right-1 flex w-12 items-center justify-center rounded-lg text-slate-500 hover:text-blue-700 focus-visible:outline-2 focus-visible:outline-blue-600 dark:hover:text-blue-300"
                            :aria-label="
                                showPassword
                                    ? 'Sembunyikan kata sandi'
                                    : 'Tampilkan kata sandi'
                            "
                            :aria-pressed="showPassword"
                            aria-controls="password"
                            @click="showPassword = !showPassword"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                                class="size-5"
                            >
                                <path
                                    d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                                />
                                <circle cx="12" cy="12" r="3" />
                                <path v-if="showPassword" d="m3 3 18 18" />
                            </svg>
                        </button>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-500"
                    ><input
                        v-model="form.remember"
                        type="checkbox"
                        class="size-4 accent-blue-700"
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
