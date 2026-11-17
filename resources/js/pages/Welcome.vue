<script setup lang="ts">
import { Link, usePoll } from '@inertiajs/vue3';
import ClinicLayout from '@/components/ClinicLayout.vue';
import ScheduleCards from '@/components/ScheduleCards.vue';
import { schedules as explore, login, dashboard } from '@/routes';
import { dateLabel, type Polyclinic, type Schedule } from '@/types/clinic';
defineProps<{
    polyclinics: Polyclinic[];
    schedules: Schedule[];
    filters: { date: string };
    today: string;
}>();
usePoll(15000, { only: ['schedules'] });
</script>
<template>
    <ClinicLayout
        title="Selamat datang di Klinik Sehat"
        eyebrow="PEDULI SEHAT, SETIAP HARI"
        description="Layanan kesehatan yang dekat dengan Anda. Pilih dokter, atur kunjungan, dan pantau antrean dari mana saja."
    >
        <section
            class="relative mb-10 grid overflow-hidden rounded-3xl bg-teal-900 text-white lg:grid-cols-5"
        >
            <div
                class="relative z-10 flex flex-col items-start gap-6 px-7 py-12 sm:px-12 lg:col-span-3"
            >
                <span
                    class="rounded-full border border-white/20 px-3 py-1.5 text-[10px] tracking-[0.2em] text-teal-100"
                    >WAKTU ANDA BERHARGA</span
                >
                <h2
                    class="max-w-xl text-4xl leading-tight font-semibold tracking-tight sm:text-5xl"
                >
                    Langkah kecil untuk<br /><span class="text-teal-200"
                        >hidup lebih sehat.</span
                    >
                </h2>
                <p class="max-w-md text-sm leading-7 text-teal-100/80">
                    Jadwalkan pemeriksaan tanpa harus datang lebih awal untuk
                    mengambil nomor antrean. Kami membantu Anda merencanakan
                    perawatan.
                </p>
                <div class="flex flex-wrap gap-3">
                    <Link
                        :href="explore()"
                        class="rounded-xl bg-white px-5 py-3 text-sm font-semibold text-teal-900"
                        >Cari dokter & jadwal ↗</Link
                    ><Link
                        :href="$page.props.auth.user ? dashboard() : login()"
                        class="rounded-xl border border-white/30 px-5 py-3 text-sm"
                        >Lihat kunjungan saya</Link
                    >
                </div>
            </div>
            <div
                class="relative hidden min-h-80 items-center justify-center lg:col-span-2 lg:flex"
                aria-hidden="true"
            >
                <div
                    class="absolute size-96 rounded-full border border-white/10"
                ></div>
                <div
                    class="absolute size-72 rounded-full border border-white/15 bg-white/5"
                ></div>
                <div
                    class="grid size-44 rotate-12 place-items-center rounded-[2.5rem] bg-teal-100 text-[130px] leading-none font-light text-teal-900 shadow-2xl"
                >
                    +
                </div>
                <div
                    class="absolute right-8 bottom-10 rounded-2xl bg-white/10 p-5 text-sm backdrop-blur"
                >
                    Satu tempat.<br /><strong
                        >Perawatan lebih terencana.</strong
                    >
                </div>
            </div>
        </section>
        <div class="mb-10 grid gap-4 sm:grid-cols-3">
            <div
                v-for="(text, i) in [
                    'Pilih dokter & sesi praktik',
                    'Simpan tiket reservasi Anda',
                    'Check-in di resepsionis',
                ]"
                :key="text"
                class="flex items-center gap-4 rounded-2xl border border-slate-200 p-5 dark:border-slate-800"
            >
                <span class="text-2xl font-light text-teal-600"
                    >0{{ i + 1 }}</span
                ><span class="text-sm">{{ text }}</span>
            </div>
        </div>
        <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="section-kicker">JADWAL HARI INI</p>
                <h2 class="mt-2 text-2xl font-semibold">
                    Temukan perawatan Anda
                </h2>
                <p class="mt-2 text-sm text-slate-500">
                    {{ dateLabel(today) }} · Kuota diperbarui setiap 15 detik
                </p>
            </div>
            <Link
                :href="explore()"
                class="text-sm font-semibold text-teal-700 dark:text-teal-400"
                >Semua jadwal →</Link
            >
        </div>
        <ScheduleCards :schedules="schedules" :date="filters.date" />
        <section class="mt-12">
            <p class="section-kicker">LAYANAN KAMI</p>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Link
                    v-for="poli in polyclinics"
                    :key="poli.id"
                    :href="explore({ query: { polyclinic: poli.id } })"
                    class="card group"
                    ><span class="text-xs text-slate-400"
                        >{{ poli.code }} / {{ poli.room_location }}</span
                    >
                    <h3 class="mt-4 text-lg font-semibold">
                        Poli {{ poli.name }}
                        <span
                            class="float-right text-teal-600 transition group-hover:translate-x-1"
                            >↗</span
                        >
                    </h3></Link
                >
            </div>
        </section>
    </ClinicLayout>
</template>
