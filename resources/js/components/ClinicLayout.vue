<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { dashboard, home, login, logout, schedules } from '@/routes';
import { profile, history } from '@/routes/patient';
import { index as queue } from '@/routes/admin/queue';
import {
    index as master,
    schedules as management,
} from '@/routes/admin/master';
import { index as patients } from '@/routes/admin/patients';
import { index as billing } from '@/routes/admin/billing';
import { reports } from '@/routes/admin';
defineProps<{ title: string; eyebrow?: string; description?: string }>();
const page = usePage();
const user = computed(() => page.props.auth.user);
const open = ref(false);
const nav = computed(() =>
    user.value?.role === 'admin'
        ? [
              { title: 'Ringkasan', route: dashboard() },
              { title: 'Meja antrean', route: queue() },
              { title: 'Poli & dokter', route: master() },
              { title: 'Jadwal praktik', route: management() },
              { title: 'Database pasien', route: patients() },
              { title: 'Kasir administrasi', route: billing() },
              { title: 'Laporan', route: reports() },
          ]
        : [
              { title: 'Dashboard', route: dashboard() },
              { title: 'Cari dokter', route: schedules() },
              { title: 'Riwayat kunjungan', route: history() },
              { title: 'Profil saya', route: profile() },
          ],
);
const active = (url: string) => page.url.split('?')[0] === url;
</script>

<template>
    <div
        class="min-h-screen bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100"
    >
        <Head :title="title" />
        <header
            class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 print:hidden"
        >
            <div
                class="mx-auto flex h-20 max-w-screen-2xl items-center justify-between gap-4 px-5 lg:px-10"
            >
                <Link :href="home()" class="flex items-center gap-3">
                    <span
                        class="grid size-10 place-items-center rounded-xl bg-teal-700 text-3xl font-semibold text-white"
                        >+</span
                    >
                    <span class="text-lg font-bold tracking-tight"
                        >klinik<span class="text-teal-600 dark:text-teal-400"
                            >sehat.</span
                        ><small
                            class="block text-[10px] font-medium tracking-[0.16em] text-slate-500"
                            >RESERVASI & ANTREAN</small
                        ></span
                    >
                </Link>
                <div class="flex items-center gap-3 text-sm">
                    <Link
                        :href="schedules()"
                        class="hidden text-slate-500 hover:text-teal-700 sm:block"
                        >Jadwal dokter</Link
                    >
                    <template v-if="user">
                        <span
                            class="hidden border-l border-slate-200 pl-5 sm:block"
                            >{{ user.name }}
                            <small class="block text-xs text-slate-500">{{
                                user.role === 'admin' ? 'Resepsionis' : 'Pasien'
                            }}</small></span
                        >
                        <button
                            class="btn-secondary px-3"
                            @click="router.post(logout().url)"
                        >
                            Keluar
                        </button>
                        <button
                            class="btn-secondary lg:hidden"
                            aria-label="Buka navigasi"
                            @click="open = !open"
                        >
                            ☰
                        </button>
                    </template>
                    <Link v-else :href="login()" class="btn"
                        >Masuk / Daftar <span aria-hidden="true">↗</span></Link
                    >
                </div>
            </div>
        </header>
        <div class="mx-auto flex max-w-screen-2xl">
            <aside
                v-if="user"
                :class="[
                    open
                        ? 'fixed inset-x-0 top-20 z-20 block shadow-xl'
                        : 'hidden',
                    'w-full shrink-0 border-r border-slate-200 bg-white p-5 lg:sticky lg:top-20 lg:block lg:h-[calc(100vh-5rem)] lg:w-60 dark:border-slate-800 dark:bg-slate-900 print:hidden',
                ]"
            >
                <p
                    class="px-3 pt-3 pb-4 text-[10px] font-semibold tracking-[0.18em] text-slate-400"
                >
                    {{
                        user.role === 'admin'
                            ? 'RUANG OPERASIONAL'
                            : 'RUANG PASIEN'
                    }}
                </p>
                <nav class="flex flex-col gap-1">
                    <Link
                        v-for="(item, i) in nav"
                        :key="item.title"
                        :href="item.route"
                        :class="[
                            'flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition',
                            active(item.route.url)
                                ? 'bg-teal-50 font-semibold text-teal-800 dark:bg-teal-950 dark:text-teal-300'
                                : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800',
                        ]"
                        @click="open = false"
                        ><span class="w-5 text-xs opacity-60">{{
                            String(i + 1).padStart(2, '0')
                        }}</span
                        >{{ item.title }}</Link
                    >
                </nav>
                <div
                    class="mt-12 rounded-xl bg-slate-50 p-4 text-xs leading-relaxed text-slate-500 dark:bg-slate-800"
                >
                    <span class="font-semibold text-teal-700 dark:text-teal-400"
                        >Kesehatan, lebih terencana.</span
                    >
                    <p class="mt-2">
                        Kelola kunjungan dalam satu tempat. Untuk keadaan
                        darurat, segera ke IGD terdekat.
                    </p>
                </div>
            </aside>
            <main class="min-w-0 flex-1 px-5 py-8 sm:px-8 lg:px-10 lg:py-10">
                <div
                    v-if="page.props.flash.success"
                    role="status"
                    class="mb-6 rounded-xl border border-teal-200 bg-teal-50 p-4 text-sm text-teal-900"
                >
                    {{ page.props.flash.success }}
                </div>
                <div
                    v-if="page.props.flash.error"
                    role="alert"
                    class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"
                >
                    {{ page.props.flash.error }}
                </div>
                <div
                    v-if="Object.keys(page.props.errors).length"
                    role="alert"
                    class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"
                >
                    <p v-for="(error, key) in page.props.errors" :key="key">
                        {{ error }}
                    </p>
                </div>
                <div class="mb-8 print:hidden">
                    <p
                        class="mb-2 text-[11px] font-semibold tracking-[0.2em] text-teal-700 uppercase dark:text-teal-400"
                    >
                        {{
                            eyebrow ||
                            (user?.role === 'admin'
                                ? 'KLINIK SEHAT / ADMINISTRASI'
                                : 'KLINIK SEHAT / LAYANAN PASIEN')
                        }}
                    </p>
                    <h1
                        class="text-3xl font-semibold tracking-tight sm:text-4xl"
                    >
                        {{ title }}
                    </h1>
                    <p
                        v-if="description"
                        class="mt-3 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{ description }}
                    </p>
                </div>
                <slot />
                <footer
                    class="mt-14 flex flex-wrap justify-between gap-3 border-t border-slate-200 pt-6 text-xs text-slate-400 dark:border-slate-800 print:hidden"
                >
                    <span>© {{ new Date().getFullYear() }} Klinik Sehat</span
                    ><span>Reservasi lebih mudah. Waktu lebih berharga.</span>
                </footer>
            </main>
        </div>
    </div>
</template>
