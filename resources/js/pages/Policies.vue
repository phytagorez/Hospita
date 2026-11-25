<script setup lang="ts">
import ClinicLayout from '@/components/ClinicLayout.vue';

const sections = [
    {
        id: 'privasi',
        title: 'Privasi dan pelindungan data',
        entries: [
            [
                'Data yang diproses',
                'Hospia menggunakan nama dan email untuk akun. Profil pasien menyimpan NIK, tanggal lahir, jenis kelamin, nomor telepon, alamat, golongan darah jika diisi, dan nomor rekam medis. Saat Anda memesan jadwal, sistem menyimpan sesi, keluhan awal, status antrean, dan informasi administrasi pembayaran yang dicatat petugas.',
            ],
            [
                'Tujuan dan akses',
                'Data dipakai untuk verifikasi identitas, reservasi, check-in, pengelolaan antrean dan administrasi kunjungan. Pasien dapat mengakses reservasinya sendiri. Petugas berwenang mengakses informasi yang diperlukan untuk melayani pasien dan menjalankan administrasi.',
            ],
            [
                'Masuk dengan Google',
                'Jika opsi ini tersedia dan dipilih, Google menangani autentikasi. Hospia menerima informasi akun yang diizinkan untuk mengenali pengguna.',
            ],
            [
                'Akses, koreksi, dan permintaan lain',
                'Nomor telepon dan alamat dapat diperbarui melalui Profil saya. Untuk koreksi identitas, permintaan akses, penghapusan, atau pertanyaan tentang data dan masa penyimpanannya, hubungi petugas administrasi fasilitas kesehatan yang menyediakan portal ini. Petugas dapat meminta verifikasi identitas serta menilai permintaan sesuai ketentuan yang berlaku.',
            ],
        ],
    },
    {
        id: 'ketentuan',
        title: 'Syarat dan ketentuan layanan',
        entries: [
            [
                'Penggunaan portal',
                'Hospia menyediakan pendaftaran kunjungan, informasi jadwal, tiket, riwayat dan antrean. Masukkan informasi yang benar dan jaga kerahasiaan akun serta kode booking. Portal ini tidak menyediakan diagnosis atau pertolongan darurat. Untuk keadaan darurat, hubungi layanan darurat setempat atau segera ke IGD.',
            ],
            [
                'Informasi layanan dan biaya',
                'Jam, kuota dan status jadwal mengikuti informasi fasilitas kesehatan. Biaya administrasi ditampilkan saat memilih sesi. Biaya konsultasi ditetapkan petugas kasir setelah pemeriksaan. Simpan nota untuk rujukan administrasi. Untuk koreksi pembayaran, hubungi kasir fasilitas kesehatan.',
            ],
            [
                'Aksesibilitas dan bantuan',
                'Jika Anda kesulitan menggunakan portal, memerlukan bantuan aksesibilitas, atau menemukan kendala reservasi, hubungi meja pendaftaran fasilitas kesehatan.',
            ],
        ],
    },
    {
        id: 'cookie',
        title: 'Kebijakan cookie',
        entries: [
            [
                'Cookie yang digunakan',
                'Cookie sesi mempertahankan status masuk dan cookie keamanan membantu melindungi formulir. Jika opsi Ingat saya tersedia dan diaktifkan, cookie tambahan dapat mempertahankan status masuk lebih lama.',
            ],
            [
                'Aset eksternal',
                'Font dan komponen notifikasi dapat dimuat dari penyedia aset eksternal. Penyedia tersebut menerima informasi teknis yang dibutuhkan browser untuk meminta aset, seperti alamat IP. Isi formulir dan data reservasi tidak dikirim sebagai bagian dari permintaan aset tersebut.',
            ],
            [
                'Pilihan Anda',
                'Anda dapat menghapus atau memblokir cookie melalui pengaturan browser. Memblokir cookie penting dapat membuat login atau pengiriman formulir gagal. Keluar dari akun setelah memakai perangkat bersama. Google mengelola cookie sendiri saat Anda menggunakan layanan login Google.',
            ],
        ],
    },
    {
        id: 'reservasi',
        title: 'Reservasi, check-in, dan pembatalan',
        entries: [
            [
                'Pemesanan',
                'Jadwal tersedia hingga 30 hari ke depan. Kuota diperiksa ulang ketika reservasi dikirim. Setiap pasien dapat memiliki satu janji aktif.',
            ],
            [
                'Kedatangan dan tiket',
                'Datang sebelum sesi dimulai dan tunjukkan tiket atau kode booking Anda kepada petugas. Jangan membagikan kode di ruang publik. Urutan pemeriksaan mengikuti antrean dan perkiraan waktu dapat berubah.',
            ],
            [
                'Pembatalan dan perubahan jadwal',
                'Pembatalan mandiri dapat dilakukan sebelum sesi dimulai dan sebelum check-in. Untuk memilih jam lain, batalkan janji lama selagi pembatalan tersedia, lalu buat janji baru sesuai kuota. Hubungi meja pendaftaran jika sesi diubah oleh fasilitas atau pembatalan mandiri sudah tidak tersedia.',
            ],
        ],
    },
];
</script>

<template>
    <ClinicLayout title="Kebijakan layanan" public-page>
        <div class="mx-auto max-w-4xl">
            <header
                class="mb-6 rounded-3xl bg-blue-950 px-6 py-8 text-white sm:px-9"
            >
                <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">
                    Kebijakan layanan Hospia
                </h1>
                <p class="mt-3 text-sm text-blue-100">
                    Informasi penggunaan portal, data pasien, dan aturan
                    kunjungan.
                </p>
            </header>
            <nav aria-label="Daftar isi" class="card mb-5 flex flex-wrap gap-3">
                <a
                    v-for="section in sections"
                    :key="section.id"
                    :href="'#' + section.id"
                    class="rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-800 hover:bg-blue-100 dark:bg-slate-800 dark:text-blue-300"
                    >{{ section.title }}</a
                >
            </nav>
            <section
                v-for="section in sections"
                :key="section.id"
                :id="section.id"
                class="card mb-5 scroll-mt-28"
            >
                <h2 class="text-xl font-bold">{{ section.title }}</h2>
                <div
                    v-for="[title, body] in section.entries"
                    :key="title"
                    class="mt-5"
                >
                    <h3 class="text-sm font-bold">{{ title }}</h3>
                    <p
                        class="mt-1 text-sm leading-7 text-slate-600 dark:text-slate-300"
                    >
                        {{ body }}
                    </p>
                </div>
            </section>
            <h2 class="mb-2 text-sm font-bold">Referensi kebijakan</h2>
            <div class="flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-500">
                <a
                    href="https://peraturan.bpk.go.id/Details/229798/uu-no-27-tahun-2022"
                    class="underline underline-offset-4"
                    >UU Pelindungan Data Pribadi</a
                >
                <a
                    href="https://peraturan.bpk.go.id/Details/245544/permenkes-no-24-tahun-2022"
                    class="underline underline-offset-4"
                    >Permenkes tentang Rekam Medis</a
                >
                <a
                    href="https://www.halodoc.com/kebijakan-privasi/"
                    class="underline underline-offset-4"
                    >Privasi Halodoc</a
                >
                <a
                    href="https://www.halodoc.com/syarat-dan-ketentuan"
                    class="underline underline-offset-4"
                    >Ketentuan Halodoc</a
                >
                <a
                    href="https://www.alodokter.com/syarat-dan-ketentuan"
                    class="underline underline-offset-4"
                    >Ketentuan Alodokter</a
                >
            </div>
        </div>
    </ClinicLayout>
</template>
