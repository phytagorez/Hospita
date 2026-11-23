<script setup lang="ts">
import ClinicLayout from '@/components/ClinicLayout.vue';
import { money, dateLabel, type Reservation } from '@/types/clinic';
defineProps<{ reservation: Reservation }>();
function print() {
    window.print();
}
</script>
<template>
    <ClinicLayout
        title="Nota administrasi"
        description="Bukti pembayaran administrasi dan konsultasi klinik."
    >
        <article
            v-if="reservation.transaction"
            class="card mx-auto max-w-2xl p-8 sm:p-12"
        >
            <div
                class="flex justify-between gap-5 border-b border-slate-200 pb-7 dark:border-slate-700"
            >
                <div>
                    <h2
                        class="text-2xl font-bold text-teal-800 dark:text-teal-300"
                    >
                        Hospia
                    </h2>
                    <p class="mt-2 text-xs text-slate-500">
                        Administrasi & konsultasi
                    </p>
                </div>
                <div class="text-right">
                    <span
                        class="rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-800"
                        >LUNAS</span
                    >
                    <p class="mt-4 font-mono text-xs">
                        INV-{{
                            String(reservation.transaction.id).padStart(6, '0')
                        }}
                    </p>
                </div>
            </div>
            <div class="grid gap-6 py-7 text-sm sm:grid-cols-2">
                <div>
                    <p class="text-xs text-slate-400">DITERBITKAN UNTUK</p>
                    <p class="mt-2 font-semibold">
                        {{ reservation.patient.user.name }}
                    </p>
                    <p class="mt-1 text-xs break-all text-slate-500">
                        {{ reservation.patient.no_rm }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">DETAIL KUNJUNGAN</p>
                    <p class="mt-2">
                        {{ dateLabel(reservation.reservation_date) }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ reservation.schedule.doctor.name }}<br />Poli
                        {{ reservation.schedule.doctor.polyclinic.name }}
                    </p>
                </div>
            </div>
            <div
                class="flex flex-col gap-4 border-y border-slate-200 py-6 text-sm dark:border-slate-700"
            >
                <p class="flex justify-between gap-3">
                    <span>Administrasi klinik</span
                    ><span>{{ money(reservation.transaction.admin_fee) }}</span>
                </p>
                <p class="flex justify-between gap-3">
                    <span>Konsultasi dokter</span
                    ><span>{{
                        money(reservation.transaction.consultation_fee)
                    }}</span>
                </p>
                <p
                    class="mt-3 flex justify-between gap-3 border-t border-slate-100 pt-5 text-lg font-semibold dark:border-slate-800"
                >
                    <span>Total pembayaran</span
                    ><span>{{
                        money(reservation.transaction.total_amount)
                    }}</span>
                </p>
            </div>
            <p class="mt-6 text-xs leading-6 text-slate-500">
                Metode pembayaran:
                {{
                    reservation.transaction.payment_method === 'cash'
                        ? 'Tunai'
                        : 'QRIS (diverifikasi petugas)'
                }}<br />Dibayarkan:
                {{
                    new Date(reservation.transaction.paid_at).toLocaleString(
                        'id-ID',
                        { timeZone: 'Asia/Jakarta' },
                    )
                }}
                WIB<br />Kode booking: {{ reservation.booking_code }}
            </p>
            <p class="mt-8 text-center text-xs text-slate-400">
                Terima kasih telah mempercayakan kesehatan Anda kepada kami.
            </p>
            <button class="btn mt-8 w-full print:hidden" @click="print">
                Cetak / simpan nota PDF
            </button>
        </article>
    </ClinicLayout>
</template>
