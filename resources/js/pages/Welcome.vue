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
const benefits = [
    {
        icon: 'calendar',
        title: 'Kunjungan lebih terencana',
        description:
            'Pilih waktu pemeriksaan yang sesuai dengan aktivitas Anda.',
    },
    {
        icon: 'ticket',
        title: 'Tiket dalam satu tempat',
        description: 'Detail janji dan tiket kunjungan tersimpan di akun Anda.',
    },
    {
        icon: 'clock',
        title: 'Antrean lebih mudah dipantau',
        description: 'Ikuti perkembangan pelayanan setelah melakukan check-in.',
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
    <ClinicLayout
        title="Reservasi lebih mudah, kunjungan lebih nyaman"
        public-page
    >
        <section
            id="layanan"
            aria-labelledby="layanan-title"
            class="mb-6 scroll-mt-24 rounded-2xl border border-slate-200/80 bg-white p-4 sm:px-6 dark:border-slate-800 dark:bg-slate-900"
        >
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:gap-7"
            >
                <div class="shrink-0">
                    <p class="section-kicker">LAYANAN KAMI</p>
                    <h2 id="layanan-title" class="mt-1 text-sm font-semibold">
                        Poliklinik untuk Anda
                    </h2>
                </div>
                <div class="flex flex-1 flex-wrap gap-2">
                    <div
                        v-for="poli in polyclinics"
                        :key="poli.id"
                        class="flex items-center gap-2.5 rounded-xl bg-slate-50 px-3.5 py-2.5 dark:bg-slate-800"
                    >
                        <ClinicIcon
                            name="plus"
                            :size="17"
                            class="text-teal-600"
                        /><span class="text-xs font-medium"
                            >Poli {{ poli.name }}</span
                        ><span class="sr-only">, {{ poli.room_location }}</span>
                    </div>
                    <p
                        v-if="!polyclinics.length"
                        class="text-sm text-slate-500"
                    >
                        Informasi layanan sedang disiapkan.
                    </p>
                </div>
                <span
                    class="flex shrink-0 items-center gap-2 text-[11px] text-slate-400"
                    ><span class="size-1.5 rounded-full bg-teal-500" />Layanan
                    aktif</span
                >
            </div>
        </section>

        <section
            class="grid gap-5 lg:grid-cols-12"
            aria-labelledby="hero-title"
        >
            <div
                class="relative overflow-hidden rounded-[28px] bg-teal-900 lg:col-span-8"
            >
                <div
                    class="pointer-events-none absolute -top-20 -right-24 size-96 rounded-full border-[60px] border-white/5"
                />
                <div
                    class="relative z-10 flex min-h-[450px] flex-col items-start justify-center px-7 py-10 sm:px-11 sm:py-14 lg:min-h-[480px]"
                >
                    <span
                        class="mb-6 inline-flex items-center gap-2 rounded-full border border-teal-300/20 bg-white/5 px-3 py-2 text-[10px] font-medium tracking-[0.12em] text-teal-100"
                        ><span class="size-1.5 rounded-full bg-teal-300" />LEBIH
                        DEKAT DENGAN LAYANAN KESEHATAN</span
                    >
                    <h1
                        id="hero-title"
                        class="max-w-xl text-[40px] leading-[1.12] font-semibold tracking-tight text-white sm:text-5xl xl:text-[60px]"
                    >
                        Janji lebih mudah.<br />Kunjungan lebih<br /><span
                            class="text-teal-200"
                            >nyaman.</span
                        >
                    </h1>
                    <p class="mt-6 max-w-md text-sm leading-7 text-teal-50/75">
                        Mulai langkah sehat Anda bersama Hospia. Atur janji
                        dokter, simpan tiket, dan pantau antrean melalui satu
                        aplikasi yang mudah digunakan.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center gap-5">
                        <Link
                            :href="schedules()"
                            class="inline-flex items-center gap-4 rounded-xl bg-white px-5 py-3.5 text-sm font-semibold text-teal-900 transition hover:bg-teal-50"
                            >{{
                                $page.props.auth.user
                                    ? 'Temukan jadwal dokter'
                                    : 'Mulai reservasi'
                            }}<ClinicIcon name="arrow" :size="18" /></Link
                        ><a
                            href="#cara-kerja"
                            class="text-sm text-white underline decoration-white/30 underline-offset-4 hover:decoration-white"
                            >Cara menggunakan Hospia</a
                        >
                    </div>
                    <div
                        class="mt-8 flex items-center gap-2 text-xs text-teal-100/70"
                    >
                        <ClinicIcon name="shield" :size="16" />{{
                            $page.props.auth.user
                                ? 'Kelola kunjungan melalui akun Anda.'
                                : 'Masuk terlebih dahulu untuk melihat jadwal dan kuota.'
                        }}
                    </div>
                </div>
            </div>
            <div
                class="relative min-h-96 overflow-hidden rounded-[28px] bg-stone-200 lg:col-span-4"
            >
                <img
                    src="/images/doctor-portrait.jpg"
                    alt="Ilustrasi tenaga medis dengan jas putih dan stetoskop"
                    width="1000"
                    height="1498"
                    fetchpriority="high"
                    class="absolute inset-0 h-full w-full object-cover object-[center_25%]"
                />
                <div
                    class="absolute inset-0 bg-linear-to-t from-teal-950/85 via-transparent to-transparent"
                />
                <div
                    class="absolute inset-x-5 bottom-6 rounded-2xl border border-white/25 bg-white/95 p-4 shadow-lg dark:bg-slate-900/95"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="grid size-10 place-items-center rounded-xl bg-teal-50 text-teal-700"
                            ><ClinicIcon name="heart"
                        /></span>
                        <div>
                            <p class="text-sm font-semibold">
                                Ruang untuk kesehatan Anda
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                Persiapkan kunjungan dengan tenang.
                            </p>
                        </div>
                    </div>
                </div>
                <span
                    class="absolute top-4 right-4 rounded-full bg-white/90 px-3 py-1.5 text-[10px] text-slate-500"
                    >Foto ilustrasi</span
                >
            </div>
        </section>

        <section
            aria-label="Manfaat Hospia"
            class="grid gap-6 border-b border-slate-200 py-9 md:grid-cols-3 dark:border-slate-800"
        >
            <article
                v-for="benefit in benefits"
                :key="benefit.title"
                class="flex items-start gap-4"
            >
                <span
                    class="grid size-11 shrink-0 place-items-center rounded-2xl border border-teal-100 bg-teal-50 text-teal-700 dark:border-teal-900 dark:bg-teal-950 dark:text-teal-300"
                    ><ClinicIcon :name="benefit.icon"
                /></span>
                <div>
                    <h2 class="text-sm font-semibold">{{ benefit.title }}</h2>
                    <p
                        class="mt-2 max-w-xs text-xs leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{ benefit.description }}
                    </p>
                </div>
            </article>
        </section>

        <section
            id="tentang"
            class="grid scroll-mt-28 items-center gap-10 py-14 lg:grid-cols-2 lg:gap-16"
            aria-labelledby="tentang-title"
        >
            <div class="relative pb-6 pl-0 sm:pl-5">
                <img
                    src="/images/consultation.jpg"
                    alt="Ilustrasi dokter memeriksa pasien di ruang konsultasi"
                    width="1200"
                    height="800"
                    loading="lazy"
                    class="aspect-[5/4] w-full rounded-[28px] object-cover"
                />
                <div
                    class="absolute right-4 bottom-0 flex max-w-72 items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-lg sm:-right-3 dark:border-slate-800 dark:bg-slate-900"
                >
                    <span
                        class="grid size-12 shrink-0 place-items-center rounded-full bg-teal-50 text-teal-700"
                        ><ClinicIcon name="check" :size="24"
                    /></span>
                    <div>
                        <p class="text-sm font-semibold">
                            Satu janji. Satu tiket.
                        </p>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Detail kunjungan tersimpan rapi di akun Anda.
                        </p>
                    </div>
                </div>
            </div>
            <div>
                <p class="section-kicker">MENGENAL HOSPIA</p>
                <h2
                    id="tentang-title"
                    class="mt-4 max-w-lg text-3xl leading-tight font-semibold tracking-tight sm:text-4xl"
                >
                    Lebih sedikit urusan administrasi.<br /><span
                        class="text-teal-700 dark:text-teal-300"
                        >Lebih fokus pada kesehatan.</span
                    >
                </h2>
                <p
                    class="mt-5 text-sm leading-7 text-slate-500 dark:text-slate-400"
                >
                    Hospia adalah sistem ticketing untuk reservasi dan antrean
                    poliklinik. Dari memilih jadwal hingga memantau pelayanan,
                    semua informasi kunjungan Anda terhubung dalam satu tempat.
                </p>
                <ul class="mt-6 grid gap-4 text-sm">
                    <li
                        v-for="text in [
                            'Lihat jadwal dan kuota sebelum membuat janji',
                            'Akses kembali tiket serta riwayat kunjungan',
                            'Pantau antrean setelah check-in di klinik',
                        ]"
                        :key="text"
                        class="flex items-center gap-3"
                    >
                        <span
                            class="grid size-6 place-items-center rounded-full bg-teal-100 text-teal-700 dark:bg-teal-950 dark:text-teal-300"
                            ><ClinicIcon name="check" :size="14" /></span
                        >{{ text }}
                    </li>
                </ul>
                <a
                    href="#cara-kerja"
                    class="mt-7 inline-flex items-center gap-3 text-sm font-semibold text-teal-700 dark:text-teal-300"
                    >Kenali alur reservasi<ClinicIcon name="arrow" :size="17"
                /></a>
            </div>
        </section>

        <section
            id="cara-kerja"
            aria-labelledby="cara-title"
            class="scroll-mt-28 rounded-[28px] bg-white px-6 py-10 sm:px-9 dark:bg-slate-900"
        >
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="section-kicker">MUDAH DARI AWAL</p>
                    <h2
                        id="cara-title"
                        class="mt-3 text-3xl font-semibold tracking-tight"
                    >
                        Tiga langkah menuju kunjungan Anda.
                    </h2>
                </div>
                <p class="max-w-xs text-sm leading-6 text-slate-500">
                    Rencanakan dari mana saja, datang dengan persiapan yang
                    lebih baik.
                </p>
            </div>
            <div class="mt-9 grid gap-7 md:grid-cols-3">
                <article
                    v-for="(step, i) in steps"
                    :key="step.title"
                    class="rounded-2xl border border-slate-100 bg-slate-50/70 p-6 dark:border-slate-800 dark:bg-slate-950"
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="grid size-12 place-items-center rounded-xl bg-white text-teal-700 shadow-xs dark:bg-slate-800 dark:text-teal-300"
                            ><ClinicIcon :name="step.icon" :size="23" /></span
                        ><span
                            class="text-3xl font-medium text-slate-200 dark:text-slate-700"
                            >0{{ i + 1 }}</span
                        >
                    </div>
                    <h3 class="mt-6 font-semibold">{{ step.title }}</h3>
                    <p
                        class="mt-3 text-sm leading-7 text-slate-500 dark:text-slate-400"
                    >
                        {{ step.description }}
                    </p>
                </article>
            </div>
        </section>

        <section
            class="grid gap-8 py-14 lg:grid-cols-5"
            aria-labelledby="faq-title"
        >
            <div class="lg:col-span-2">
                <p class="section-kicker">SEBELUM MEMULAI</p>
                <h2
                    id="faq-title"
                    class="mt-3 text-3xl font-semibold tracking-tight"
                >
                    Ada yang ingin<br />Anda ketahui?
                </h2>
                <p class="mt-4 max-w-xs text-sm leading-7 text-slate-500">
                    Temukan informasi singkat tentang akun, reservasi, dan tiket
                    kunjungan.
                </p>
            </div>
            <div class="space-y-3 lg:col-span-3">
                <details
                    v-for="faq in faqs"
                    :key="faq.question"
                    class="group rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"
                >
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-semibold [&::-webkit-details-marker]:hidden"
                    >
                        {{ faq.question
                        }}<ClinicIcon
                            name="plus"
                            :size="17"
                            class="text-teal-600 transition group-open:rotate-45"
                        />
                    </summary>
                    <p
                        class="mt-4 text-sm leading-7 text-slate-500 dark:text-slate-400"
                    >
                        {{ faq.answer }}
                    </p>
                </details>
            </div>
        </section>

        <section
            class="flex flex-col items-start justify-between gap-6 rounded-[28px] bg-teal-900 p-8 text-white sm:flex-row sm:items-center sm:p-10"
        >
            <div>
                <p class="text-xs text-teal-200">
                    LANGKAH KECIL UNTUK KESEHATAN ANDA
                </p>
                <h2
                    class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl"
                >
                    Kunjungan berikutnya, lebih terencana.
                </h2>
                <p class="mt-3 text-sm text-teal-100/70">
                    Mulai dengan akun Anda, lalu temukan jadwal yang sesuai.
                </p>
            </div>
            <Link
                :href="schedules()"
                class="inline-flex shrink-0 items-center gap-4 rounded-xl bg-white px-5 py-3.5 text-sm font-semibold text-teal-900 transition hover:bg-teal-50"
                >Mulai sekarang<ClinicIcon name="arrow" :size="18"
            /></Link>
        </section>
        <p class="mt-5 text-center text-[10px] text-slate-400">
            Foto ilustrasi oleh
            <a
                href="https://www.pexels.com/photo/physician-in-white-coat-wearing-a-stethoscope-5998482/"
                target="_blank"
                rel="noopener noreferrer"
                class="underline underline-offset-2"
                >Pavel Danilyuk</a
            >
            dan
            <a
                href="https://www.pexels.com/photo/photo-of-doctor-checking-on-her-patient-5327584/"
                target="_blank"
                rel="noopener noreferrer"
                class="underline underline-offset-2"
                >Thirdman</a
            >
            di Pexels.
        </p>
    </ClinicLayout>
</template>
