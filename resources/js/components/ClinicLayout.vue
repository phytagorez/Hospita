<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { showError, showSuccess } from '@/lib/alerts';
import ClinicIcon from '@/components/ClinicIcon.vue';
import { dashboard, home, login, logout, policies, schedules } from '@/routes';
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
const compactOverride = ref<boolean | null>(null);
const roomySidebar = ref(false);
const compact = computed(() => compactOverride.value ?? !roomySidebar.value);
const compactPreference = 'hospia-sidebar-compact';
let sidebarMedia: MediaQueryList | undefined;
function updateSidebarWidth() {
    roomySidebar.value = sidebarMedia?.matches ?? false;
}
onMounted(() => {
    const saved = window.localStorage.getItem(compactPreference);
    compactOverride.value =
        saved === 'true' ? true : saved === 'false' ? false : null;
    sidebarMedia = window.matchMedia('(min-width: 80rem)');
    updateSidebarWidth();
    sidebarMedia.addEventListener('change', updateSidebarWidth);
});
onUnmounted(() =>
    sidebarMedia?.removeEventListener('change', updateSidebarWidth),
);
function toggleSidebar() {
    const next = !compact.value;
    compactOverride.value = next === !roomySidebar.value ? null : next;
    if (compactOverride.value === null) {
        window.localStorage.removeItem(compactPreference);
    } else {
        window.localStorage.setItem(compactPreference, String(next));
    }
}
watch(
    () => page.props.flash,
    (flash) => {
        if (typeof window === 'undefined') return;
        if (flash?.error) showError(flash.error);
        else if (flash?.success) showSuccess(flash.success);
    },
    { immediate: true },
);
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
        class="clinic-shell min-h-screen bg-[#f4f7fd] text-slate-800 dark:bg-slate-950 dark:text-slate-100"
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
            id="clinic-sidebar"
            :class="[
                open ? 'visible translate-x-0' : 'invisible -translate-x-full',
                compact ? 'lg:w-20' : 'lg:w-60',
                'fixed inset-y-0 left-0 z-40 flex w-60 max-w-[calc(100vw-2rem)] flex-col overflow-y-auto border-r border-slate-200 bg-white px-3 pb-4 text-slate-700 transition-[translate] motion-reduce:transition-none lg:visible lg:translate-x-0 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 print:hidden',
            ]"
            @keydown.esc="open = false"
        >
            <div
                class="mb-6 flex h-[72px] shrink-0 items-center gap-2 border-b border-slate-100 px-1 dark:border-slate-800"
            >
                <Link
                    :href="home()"
                    aria-label="Beranda Hospia"
                    class="flex min-w-0 items-center gap-2.5"
                >
                    <span
                        class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-700 text-white"
                    >
                        <ClinicIcon name="plus" :size="24" />
                    </span>
                    <span
                        class="text-xl font-extrabold tracking-tight text-slate-950 dark:text-white"
                        :class="{ 'lg:sr-only': compact }"
                    >
                        Hospia<span class="text-blue-600">.</span>
                    </span>
                </Link>
                <button
                    type="button"
                    class="ml-auto rounded-lg px-2 text-xl lg:hidden"
                    aria-label="Tutup navigasi"
                    @click="open = false"
                >
                    ×
                </button>
            </div>
            <p
                class="mb-3 px-2 text-xs font-semibold text-slate-500"
                :class="{ 'lg:sr-only': compact }"
            >
                {{ user.role === 'admin' ? 'Administrasi' : 'Akun pasien' }}
            </p>
            <nav class="flex flex-col gap-1" aria-label="Menu utama">
                <Link
                    v-for="item in nav"
                    :key="item.title"
                    :href="item.route"
                    :title="compact ? item.title : undefined"
                    :aria-current="active(item.route.url) ? 'page' : undefined"
                    :class="[
                        'flex h-11 items-center gap-3 rounded-xl px-3 text-sm font-medium transition-colors',
                        compact ? 'lg:justify-center lg:px-0' : '',
                        active(item.route.url)
                            ? 'bg-blue-50 font-bold text-blue-800 dark:bg-blue-950 dark:text-blue-200'
                            : 'hover:bg-slate-100 hover:text-blue-800 dark:hover:bg-slate-800 dark:hover:text-blue-200',
                    ]"
                    @click="open = false"
                >
                    <ClinicIcon :name="item.icon" :size="19" class="shrink-0" />
                    <span :class="{ 'lg:sr-only': compact }">{{
                        item.title
                    }}</span>
                </Link>
            </nav>
            <div
                class="mt-auto border-t border-slate-100 pt-3 dark:border-slate-800"
            >
                <button
                    type="button"
                    class="flex h-11 w-full items-center gap-3 rounded-xl px-3 text-sm font-medium transition-colors hover:bg-slate-100 dark:hover:bg-slate-800"
                    :class="{ 'lg:justify-center lg:px-0': compact }"
                    :title="compact ? 'Keluar dari akun' : undefined"
                    @click="router.post(logout().url)"
                >
                    <ClinicIcon name="logout" :size="18" class="shrink-0" />
                    <span :class="{ 'lg:sr-only': compact }"
                        >Keluar dari akun</span
                    >
                </button>
            </div>
        </aside>
        <div
            :class="
                user && !publicPage
                    ? [compact ? 'lg:ml-20' : 'lg:ml-60', 'print:ml-0']
                    : ''
            "
        >
            <header
                class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 print:hidden"
            >
                <div
                    :class="[
                        'clinic-frame mx-auto flex min-h-16 items-center justify-between gap-2 py-2 sm:min-h-[72px] sm:gap-4',
                        publicPage || !user
                            ? 'max-w-[88rem]'
                            : 'max-w-[112rem]',
                    ]"
                >
                    <Link
                        v-if="publicPage || !user"
                        :href="home()"
                        class="flex shrink-0 items-center gap-2.5"
                    >
                        <span
                            class="grid size-10 place-items-center rounded-xl bg-blue-700 text-white"
                            ><ClinicIcon name="plus" :size="27"
                        /></span>
                        <span
                            class="text-2xl font-bold tracking-tight text-blue-950 dark:text-white"
                            >Hospia<span class="text-blue-500">.</span></span
                        >
                    </Link>
                    <div
                        v-else
                        class="flex min-w-0 items-center gap-2 sm:gap-4"
                    >
                        <button
                            class="btn-secondary p-2 lg:hidden"
                            :aria-expanded="open"
                            aria-label="Buka navigasi"
                            aria-controls="clinic-sidebar"
                            @click="open = !open"
                        >
                            <ClinicIcon name="menu" />
                        </button>
                        <button
                            type="button"
                            class="hidden size-9 items-center justify-center rounded-lg border border-slate-200 text-lg text-slate-600 hover:bg-blue-50 hover:text-blue-800 lg:flex dark:border-slate-700 dark:text-slate-300"
                            :aria-label="
                                compact ? 'Perluas sidebar' : 'Ringkas sidebar'
                            "
                            :title="
                                compact ? 'Perluas sidebar' : 'Ringkas sidebar'
                            "
                            :aria-expanded="!compact"
                            aria-controls="clinic-sidebar"
                            @click="toggleSidebar"
                        >
                            <span aria-hidden="true">{{
                                compact ? '›' : '‹'
                            }}</span>
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
                        class="hidden items-center gap-4 text-sm text-slate-500 md:flex lg:gap-8"
                        aria-label="Navigasi informasi"
                    >
                        <Link
                            :href="home().url + '#layanan'"
                            class="hover:text-blue-700"
                            >Layanan</Link
                        >
                        <Link
                            :href="home().url + '#tentang'"
                            class="hover:text-blue-700"
                            >Tentang Hospia</Link
                        >
                        <Link
                            :href="home().url + '#cara-kerja'"
                            class="hover:text-blue-700"
                            >Cara reservasi</Link
                        >
                    </nav>
                    <div
                        v-if="user && !publicPage"
                        class="flex min-w-0 shrink-0 items-center gap-2 sm:gap-4"
                    >
                        <Link
                            :href="schedules()"
                            class="hidden items-center gap-2 border-r border-slate-200 pr-5 text-xs text-slate-500 hover:text-blue-700 sm:flex"
                            ><ClinicIcon name="calendar" :size="17" />Jadwal
                            dokter</Link
                        >
                        <span
                            class="grid size-10 shrink-0 place-items-center rounded-full bg-blue-100 text-xs font-bold text-blue-800"
                            >{{ initials }}</span
                        >
                        <div
                            class="hidden max-w-40 min-w-0 sm:block xl:max-w-56"
                        >
                            <p
                                class="truncate text-sm font-semibold"
                                :title="user.name"
                            >
                                {{ user.name }}
                            </p>
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
                        class="btn shrink-0 px-3 sm:px-5"
                        >{{ user ? 'Dashboard saya' : 'Masuk / Daftar'
                        }}<ClinicIcon name="arrow" :size="16"
                    /></Link>
                </div>
            </header>
            <main
                :class="[
                    'clinic-frame @container/content mx-auto min-w-0 py-[clamp(1.25rem,2.5vw,2.75rem)] print:px-0',
                    publicPage ? 'max-w-[88rem]' : 'max-w-[112rem]',
                ]"
            >
                <div v-if="!publicPage" class="mb-7 print:hidden">
                    <p v-if="eyebrow" class="section-kicker mb-2">
                        {{ eyebrow }}
                    </p>
                    <h1
                        class="text-[clamp(1.5rem,1.1rem+1.25cqi,2.25rem)] font-semibold tracking-tight"
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
                    <span>© {{ new Date().getFullYear() }} Hospia</span>
                    <nav
                        aria-label="Kebijakan layanan"
                        class="flex flex-wrap gap-x-4 gap-y-2 text-slate-600 dark:text-slate-400"
                    >
                        <Link
                            :href="policies().url + '#privasi'"
                            class="hover:text-blue-700"
                            >Privasi</Link
                        >
                        <Link
                            :href="policies().url + '#ketentuan'"
                            class="hover:text-blue-700"
                            >Ketentuan layanan</Link
                        >
                        <Link
                            :href="policies().url + '#cookie'"
                            class="hover:text-blue-700"
                            >Cookie</Link
                        >
                        <Link
                            :href="policies().url + '#reservasi'"
                            class="hover:text-blue-700"
                            >Reservasi & pembatalan</Link
                        >
                    </nav>
                </footer>
            </main>
        </div>
    </div>
</template>
