<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ClinicIcon from '@/components/ClinicIcon.vue';
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

withDefaults(
    defineProps<{
        title: string;
        eyebrow?: string;
        description?: string;
        publicPage?: boolean;
    }>(),
    { publicPage: false },
);
const page = usePage();
const user = computed(() => page.props.auth.user);
const open = ref(false);
const nav = computed(() =>
    user.value?.role === 'admin'
        ? [
              { title: 'Dashboard', icon: 'grid', route: dashboard() },
              { title: 'Meja antrean', icon: 'ticket', route: queue() },
              { title: 'Poli & dokter', icon: 'doctor', route: master() },
              {
                  title: 'Jadwal praktik',
                  icon: 'calendar',
                  route: management(),
              },
              { title: 'Database pasien', icon: 'users', route: patients() },
              { title: 'Kasir administrasi', icon: 'wallet', route: billing() },
              { title: 'Laporan', icon: 'chart', route: reports() },
          ]
        : [
              { title: 'Dashboard', icon: 'grid', route: dashboard() },
              { title: 'Cari dokter', icon: 'doctor', route: schedules() },
              { title: 'Riwayat kunjungan', icon: 'clock', route: history() },
              { title: 'Profil saya', icon: 'user', route: profile() },
          ],
);
const active = (url: string) => page.url.split('?')[0] === url;
const initials = computed(() =>
    user.value?.name
        .split(' ')
        .slice(0, 2)
        .map((part) => part[0])
        .join(''),
);
const todayLabel = new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    timeZone: 'Asia/Jakarta',
}).format(new Date());
</script>

<template>
    <div
        class="min-h-screen bg-[#f5f8fa] text-slate-800 dark:bg-slate-950 dark:text-slate-100"
    >
        <Head :title="title" />
        <button
            v-if="open && user && !publicPage"
            aria-label="Tutup navigasi"
            class="fixed inset-0 z-30 bg-slate-950/40 lg:hidden"
            @click="open = false"
        />
        <aside
            v-if="user && !publicPage"
            :class="[
                open ? 'translate-x-0' : '-translate-x-full',
                'fixed inset-y-0 left-0 z-40 flex w-64 flex-col overflow-y-auto bg-teal-900 p-5 text-white transition-transform lg:translate-x-0 print:hidden',
            ]"
        >
            <Link
                :href="home()"
                class="mb-10 flex items-center gap-3 px-2 pt-3"
            >
                <span
                    class="grid size-11 place-items-center rounded-2xl bg-white/15 ring-1 ring-white/20"
                    ><ClinicIcon name="plus" :size="28"
                /></span>
                <span class="text-2xl font-bold tracking-tight"
                    >Hospia<small
                        class="mt-0.5 block text-[9px] font-medium tracking-[0.18em] text-teal-200"
                        >RESERVASI & ANTREAN</small
                    ></span
                >
            </Link>
            <p
                class="mb-4 px-3 text-[10px] font-medium tracking-[0.18em] text-teal-200/60"
            >
                {{
                    user.role === 'admin'
                        ? 'RUANG ADMINISTRASI'
                        : 'RUANG PASIEN'
                }}
            </p>
            <nav class="flex flex-col gap-2" aria-label="Menu utama">
                <Link
                    v-for="item in nav"
                    :key="item.title"
                    :href="item.route"
                    :aria-current="active(item.route.url) ? 'page' : undefined"
                    :class="[
                        'flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm transition',
                        active(item.route.url)
                            ? 'bg-white font-semibold text-teal-900 shadow-sm'
                            : 'text-teal-50/80 hover:bg-white/10 hover:text-white',
                    ]"
                    @click="open = false"
                >
                    <ClinicIcon :name="item.icon" :size="19" />{{ item.title
                    }}<span
                        v-if="active(item.route.url)"
                        class="ml-auto size-1.5 rounded-full bg-teal-600"
                    />
                </Link>
            </nav>
            <div class="mt-auto pt-10">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <ClinicIcon name="heart" class="mb-3 text-teal-200" />
                    <p class="text-sm font-medium">
                        Kesehatan, lebih terencana.
                    </p>
                    <p class="mt-2 text-xs leading-6 text-teal-100/65">
                        {{
                            user.role === 'admin'
                                ? 'Bantu setiap pasien mendapatkan pengalaman kunjungan yang lebih nyaman.'
                                : 'Simpan tiket, datang sesuai jadwal, dan pantau antrean Anda.'
                        }}
                    </p>
                </div>
                <button
                    class="mt-4 flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm text-teal-100 transition hover:bg-white/10"
                    @click="router.post(logout().url)"
                >
                    <ClinicIcon name="logout" :size="18" />Keluar dari akun
                </button>
            </div>
        </aside>
        <div :class="user && !publicPage ? 'lg:pl-64' : ''">
            <header
                class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 print:hidden"
            >
                <div
                    :class="[
                        'mx-auto flex h-20 items-center justify-between gap-4 px-5 sm:px-8',
                        publicPage || !user ? 'max-w-7xl' : 'max-w-[1600px]',
                    ]"
                >
                    <Link
                        v-if="publicPage || !user"
                        :href="home()"
                        class="flex items-center gap-2.5"
                    >
                        <span
                            class="grid size-10 place-items-center rounded-xl bg-teal-700 text-white"
                            ><ClinicIcon name="plus" :size="27"
                        /></span>
                        <span
                            class="text-2xl font-bold tracking-tight text-teal-950 dark:text-white"
                            >Hospia<span class="text-teal-500">.</span></span
                        >
                    </Link>
                    <div v-else class="flex min-w-0 items-center gap-4">
                        <button
                            class="btn-secondary p-2 lg:hidden"
                            :aria-expanded="open"
                            aria-label="Buka navigasi"
                            @click="open = !open"
                        >
                            <ClinicIcon name="menu" />
                        </button>
                        <div>
                            <p class="text-sm font-semibold">
                                {{
                                    user.role === 'admin'
                                        ? 'Ruang administrasi'
                                        : 'Ruang pasien'
                                }}
                            </p>
                            <p
                                class="mt-1 hidden text-xs text-slate-400 sm:block"
                            >
                                {{ todayLabel }}
                            </p>
                        </div>
                    </div>
                    <nav
                        v-if="publicPage || !user"
                        class="hidden items-center gap-8 text-sm text-slate-500 md:flex"
                        aria-label="Navigasi informasi"
                    >
                        <Link
                            :href="home().url + '#layanan'"
                            class="hover:text-teal-700"
                            >Layanan</Link
                        >
                        <Link
                            :href="home().url + '#tentang'"
                            class="hover:text-teal-700"
                            >Tentang Hospia</Link
                        >
                        <Link
                            :href="home().url + '#cara-kerja'"
                            class="hover:text-teal-700"
                            >Cara reservasi</Link
                        >
                    </nav>
                    <div
                        v-if="user && !publicPage"
                        class="flex items-center gap-4"
                    >
                        <Link
                            :href="schedules()"
                            class="hidden items-center gap-2 border-r border-slate-200 pr-5 text-xs text-slate-500 hover:text-teal-700 sm:flex"
                            ><ClinicIcon name="calendar" :size="17" />Jadwal
                            dokter</Link
                        >
                        <span
                            class="grid size-10 place-items-center rounded-full bg-teal-100 text-xs font-bold text-teal-800"
                            >{{ initials }}</span
                        >
                        <div class="hidden sm:block">
                            <p class="text-sm font-semibold">{{ user.name }}</p>
                            <p class="mt-0.5 text-[11px] text-slate-400">
                                {{
                                    user.role === 'admin'
                                        ? 'Administrator'
                                        : 'Pasien'
                                }}
                            </p>
                        </div>
                    </div>
                    <Link
                        v-else
                        :href="user ? dashboard() : login()"
                        class="btn"
                        >{{ user ? 'Dashboard saya' : 'Masuk / Daftar'
                        }}<ClinicIcon name="arrow" :size="16"
                    /></Link>
                </div>
            </header>
            <main
                :class="[
                    'mx-auto min-w-0',
                    publicPage
                        ? 'max-w-7xl px-5 py-6 sm:px-8 sm:py-8'
                        : 'max-w-[1600px] px-5 py-8 sm:px-8',
                ]"
            >
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
                <div v-if="!publicPage" class="mb-7 print:hidden">
                    <p class="section-kicker mb-2">
                        {{
                            eyebrow ||
                            (user?.role === 'admin'
                                ? 'HOSPIA / ADMINISTRASI'
                                : 'HOSPIA / LAYANAN PASIEN')
                        }}
                    </p>
                    <h1
                        class="text-2xl font-semibold tracking-tight sm:text-3xl"
                    >
                        {{ title }}
                    </h1>
                    <p
                        v-if="description"
                        class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{ description }}
                    </p>
                </div>
                <slot />
                <footer
                    class="mt-12 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 py-6 text-xs text-slate-400 dark:border-slate-800 print:hidden"
                >
                    <span>© {{ new Date().getFullYear() }} Hospia.</span
                    ><span>Reservasi lebih mudah. Waktu lebih berharga.</span>
                </footer>
            </main>
        </div>
    </div>
</template>
