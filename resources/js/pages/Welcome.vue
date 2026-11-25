<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ClinicLayout from '@/components/ClinicLayout.vue';
import ClinicIcon from '@/components/ClinicIcon.vue';
import { schedules } from '@/routes';
import type { Polyclinic } from '@/types/clinic';

defineProps<{ polyclinics: Polyclinic[] }>();
const steps = [
    {
        title: 'Masuk dan lengkapi profil',
        description:
            'Daftar atau masuk, lalu isi data pasien agar kunjungan tercatat dengan tepat.',
        icon: 'user',
    },
    {
        title: 'Temukan jadwal yang sesuai',
        description:
            'Pilih poliklinik, dokter, dan waktu kunjungan. Lihat ketersediaan kuota sebelum membuat janji.',
        icon: 'calendar',
    },
    {
        title: 'Simpan tiket, pantau antrean',
        description:
            'Tunjukkan tiket saat tiba untuk check-in. Pantau nomor antrean melalui akun Anda.',
        icon: 'ticket',
    },
];
const faqs = [
    {
        question: 'Apakah harus punya akun untuk melihat jadwal?',
        answer: 'Ya. Informasi poliklinik dapat dilihat di halaman ini. Untuk melihat jadwal dokter, ketersediaan kuota, dan membuat janji, silakan masuk atau daftar terlebih dahulu.',
    },
    {
        question: 'Bolehkah saya membuat lebih dari satu janji?',
        answer: 'Setiap pasien hanya dapat memiliki satu janji aktif. Selesaikan atau batalkan janji sebelumnya sebelum membuat janji baru.',
    },
    {
        question: 'Apa yang perlu dilakukan setelah mendapat tiket?',
        answer: 'Simpan tiket Anda dan datang sesuai jadwal. Tunjukkan tiket kepada resepsionis untuk check-in, lalu pantau antrean melalui akun Anda.',
    },
    {
        question: 'Apakah Hospia melayani kondisi darurat?',
        answer: 'Hospia membantu reservasi kunjungan terjadwal. Untuk kondisi darurat, segera datangi IGD atau fasilitas kesehatan terdekat.',
    },
];
</script>

<template>
    <ClinicLayout title="Jadwal dokter dan reservasi online" public-page>
        <section
            class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
            aria-labelledby="hero-title"
        >
            <div
                class="grid @min-[48rem]/content:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]"
            >
                <div
                    class="flex flex-col items-start justify-center px-[clamp(1.25rem,4cqi,4rem)] py-[clamp(2rem,5cqi,5rem)] @min-[48rem]/content:min-h-[clamp(23rem,39cqi,34rem)]"
                >
                    <h1
                        id="hero-title"
                        class="max-w-xl text-[clamp(2.125rem,1.1rem+3.8cqi,4.5rem)] leading-[1.08] font-extrabold tracking-[-0.055em] text-slate-950 dark:text-white"
                    >
                        Atur kunjungan dokter
                        <span class="text-blue-700 dark:text-blue-300"
                            >dari rumah.</span
                        >
                    </h1>
                    <p
                        class="mt-5 max-w-md text-base leading-7 text-slate-600 dark:text-slate-300"
                    >
                        Pilih poliklinik dan jam praktik, simpan tiket
                        kunjungan, lalu pantau antrean di akun Hospia.
                    </p>
                    <div class="mt-7 flex flex-wrap items-center gap-3">
                        <Link :href="schedules()" class="btn rounded-full">
                            <ClinicIcon name="calendar" :size="18" /> Cari
                            jadwal dokter
                        </Link>
                        <a href="#cara-kerja" class="btn-secondary rounded-full"
                            >Cara reservasi</a
                        >
                    </div>
                    <p
                        v-if="!$page.props.auth.user"
                        class="mt-4 text-xs leading-5 text-slate-500 dark:text-slate-400"
                    >
                        Masuk untuk melihat jam praktik dan kuota terbaru.
                    </p>
                </div>
                <div
                    class="relative isolate min-h-[clamp(16rem,42cqi,24rem)] overflow-hidden bg-blue-700"
                >
                    <div
                        aria-hidden="true"
                        class="absolute top-6 right-6 hidden size-20 rounded-2xl border border-white/35 @min-[48rem]/content:block"
                    ></div>
                    <img
                        src="/images/doctor-portrait.jpg"
                        alt="Tenaga medis dengan stetoskop"
                        width="1000"
                        height="1498"
                        fetchpriority="high"
                        class="absolute inset-x-6 bottom-0 h-full w-[calc(100%-3rem)] rounded-t-3xl object-cover object-[center_22%] sm:inset-x-9 sm:w-[calc(100%-4.5rem)] @min-[48rem]/content:inset-x-auto @min-[48rem]/content:right-[10%] @min-[48rem]/content:h-[92%] @min-[48rem]/content:w-[80%]"
                    />
                </div>
            </div>
        </section>

        <section
            id="layanan"
            aria-labelledby="layanan-title"
            class="scroll-mt-24 py-11 sm:py-14"
        >
            <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2
                        id="layanan-title"
                        class="text-3xl font-extrabold tracking-tight"
                    >
                        Layanan poliklinik
                    </h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                        Pilih layanan untuk melihat jadwal dokter yang tersedia.
                    </p>
                </div>
                <Link
                    :href="schedules()"
                    class="inline-flex items-center gap-2 text-sm font-bold text-blue-700 dark:text-blue-300"
                >
                    Lihat semua jadwal <ClinicIcon name="arrow" :size="16" />
                </Link>
            </div>
            <div
                v-if="polyclinics.length"
                class="grid gap-3 @min-[27rem]/content:grid-cols-2 @min-[64rem]/content:grid-cols-4"
            >
                <Link
                    v-for="poli in polyclinics"
                    :key="poli.id"
                    :href="schedules({ query: { polyclinic: poli.id } })"
                    class="card flex items-center gap-3 p-4 transition-colors hover:border-blue-300 hover:bg-blue-50 dark:hover:border-blue-700 dark:hover:bg-blue-950"
                >
                    <span
                        class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200"
                    >
                        <ClinicIcon name="plus" :size="20" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-bold"
                            >Poli {{ poli.name }}</span
                        >
                        <span
                            class="mt-1 block text-xs text-slate-500 dark:text-slate-400"
                            >{{ poli.room_location }}</span
                        >
                    </span>
                    <ClinicIcon
                        name="arrow"
                        :size="16"
                        class="shrink-0 text-blue-700 dark:text-blue-300"
                    />
                </Link>
            </div>
            <p v-else class="card text-sm text-slate-500">
                Informasi layanan sedang disiapkan.
            </p>
        </section>

        <section
            id="tentang"
            aria-labelledby="tentang-title"
            class="grid scroll-mt-24 overflow-hidden rounded-[1.75rem] bg-blue-950 text-white @min-[45rem]/content:grid-cols-2"
        >
            <img
                src="/images/consultation.jpg"
                alt="Dokter memeriksa pasien di ruang konsultasi"
                width="1200"
                height="800"
                loading="lazy"
                class="h-full max-h-80 min-h-52 w-full object-cover"
            />
            <div class="flex flex-col justify-center px-6 py-8 sm:px-10">
                <h2
                    id="tentang-title"
                    class="text-3xl font-extrabold tracking-tight"
                >
                    Janji dan antrean dalam satu akun
                </h2>
                <p class="mt-4 max-w-md text-sm leading-7 text-blue-100">
                    Buka riwayat untuk melihat tiket kunjungan. Setelah check-in
                    di meja pendaftaran, pantau perkembangan antrean dari
                    dashboard pasien.
                </p>
            </div>
        </section>

        <section
            id="cara-kerja"
            aria-labelledby="cara-kerja-title"
            class="scroll-mt-24 py-11 sm:py-14"
        >
            <h2
                id="cara-kerja-title"
                class="mb-5 text-3xl font-extrabold tracking-tight"
            >
                Cara reservasi
            </h2>
            <ol class="grid gap-3 @min-[47rem]/content:grid-cols-3">
                <li v-for="step in steps" :key="step.title" class="card">
                    <ClinicIcon
                        :name="step.icon"
                        :size="23"
                        class="text-blue-700 dark:text-blue-300"
                    />
                    <h3 class="mt-3 text-sm font-bold">{{ step.title }}</h3>
                    <p
                        class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300"
                    >
                        {{ step.description }}
                    </p>
                </li>
            </ol>
        </section>

        <section
            id="faq"
            aria-labelledby="faq-title"
            class="grid scroll-mt-24 gap-5 rounded-[1.75rem] bg-blue-50 px-6 py-8 sm:px-8 @min-[43rem]/content:grid-cols-[minmax(0,1fr)_minmax(0,2fr)] dark:bg-slate-900"
        >
            <h2 id="faq-title" class="text-3xl font-extrabold tracking-tight">
                Pertanyaan umum
            </h2>
            <div class="flex flex-col gap-2">
                <details
                    v-for="faq in faqs"
                    :key="faq.question"
                    name="faq-hospia"
                    class="group rounded-xl border border-blue-100 bg-white p-4 dark:border-slate-800 dark:bg-slate-950"
                >
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between gap-4 rounded-sm text-sm font-bold [&::-webkit-details-marker]:hidden"
                    >
                        {{ faq.question }}
                        <span
                            aria-hidden="true"
                            class="shrink-0 text-xl leading-none text-blue-700 group-open:hidden dark:text-blue-300"
                            >+</span
                        >
                        <span
                            aria-hidden="true"
                            class="hidden shrink-0 text-xl leading-none text-blue-700 group-open:block dark:text-blue-300"
                            >−</span
                        >
                    </summary>
                    <p
                        class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300"
                    >
                        {{ faq.answer }}
                    </p>
                </details>
            </div>
        </section>
    </ClinicLayout>
</template>
