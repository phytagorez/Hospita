<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import ClinicLayout from '@/components/ClinicLayout.vue';
import { index, store } from '@/routes/admin/billing';
import { receipt } from '@/routes/reservations';
import { money, type Reservation } from '@/types/clinic';
const props = defineProps<{
    reservations: Reservation[];
    date: string;
    revenue: number;
}>();
const date = ref(props.date);
const selected = ref<Reservation | null>(null);
const verified = ref(false);
const form = useForm({ consultation_fee: 75000, payment_method: 'cash' });
const total = computed(
    () => (selected.value?.admin_fee ?? 0) + Number(form.consultation_fee || 0),
);
function submit() {
    if (!selected.value || !verified.value) return;
    form.post(store(selected.value.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            selected.value = null;
            form.reset();
        },
    });
}
</script>
<template>
    <ClinicLayout
        title="Kasir administrasi"
        description="Catat pembayaran setelah pemeriksaan selesai. Verifikasi pembayaran tunai / QRIS sebelum mencatat pelunasan."
    >
        <div class="clinic-grid mb-7 gap-5 [--clinic-column-min:12rem]">
            <div class="card">
                <p class="text-xs text-slate-500">
                    Pendapatan diterima pada tanggal ini
                </p>
                <p
                    class="mt-4 text-2xl font-semibold text-blue-700 dark:text-blue-400"
                >
                    {{ money(revenue) }}
                </p>
            </div>
            <div class="card">
                <p class="text-xs text-slate-500">Selesai, belum dibayar</p>
                <p class="mt-4 text-3xl font-semibold">
                    {{ reservations.filter((r) => !r.transaction).length }}
                </p>
            </div>
            <form
                class="card flex items-end gap-3"
                @submit.prevent="router.get(index().url, { date })"
            >
                <label class="field flex-1"
                    >Tanggal kunjungan<input
                        v-model="date"
                        type="date"
                        required /></label
                ><button class="btn-secondary">Lihat</button>
            </form>
        </div>
        <form
            v-if="selected"
            class="card mb-7 flex flex-col gap-5"
            @submit.prevent="submit"
        >
            <div class="flex justify-between gap-3">
                <h2 class="text-lg font-semibold">
                    Pembayaran · {{ selected.patient.user.name }}
                </h2>
                <button
                    type="button"
                    class="text-xs text-slate-500"
                    @click="selected = null"
                >
                    Tutup ✕
                </button>
            </div>
            <div
                class="grid gap-5 @min-[28rem]/content:grid-cols-2 @min-[57rem]/content:grid-cols-4"
            >
                <div>
                    <p class="field">Administrasi</p>
                    <p class="mt-3 text-lg font-semibold">
                        {{ money(selected.admin_fee) }}
                    </p>
                </div>
                <label class="field"
                    >Biaya konsultasi (Rp)<input
                        v-model="form.consultation_fee"
                        type="number"
                        min="0"
                        max="10000000"
                        required /></label
                ><label class="field"
                    >Metode pembayaran<select v-model="form.payment_method">
                        <option value="cash">Tunai</option>
                        <option value="qris">QRIS (verifikasi manual)</option>
                    </select></label
                >
                <div>
                    <p class="field">Total dibayar</p>
                    <p
                        class="mt-3 text-xl font-semibold text-blue-700 dark:text-blue-400"
                    >
                        {{ money(total) }}
                    </p>
                </div>
            </div>
            <p class="text-xs text-slate-400">
                QRIS dicatat setelah dana diverifikasi petugas. Aplikasi ini
                tidak terhubung dengan payment gateway.
            </p>
            <label
                class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-300"
            >
                <input
                    v-model="verified"
                    type="checkbox"
                    required
                    class="mt-0.5 size-4 shrink-0 accent-blue-700"
                />
                Saya telah memverifikasi penerimaan pembayaran sebesar
                {{ money(total) }}.
            </label>
            <button
                class="btn self-start"
                :disabled="form.processing || !verified"
            >
                Konfirmasi pelunasan →
            </button>
        </form>
        <div
            class="table-wrap"
            role="region"
            tabindex="0"
            aria-label="Transaksi administrasi"
        >
            <table class="w-full">
                <thead>
                    <tr>
                        <th>Pasien / booking</th>
                        <th>Dokter</th>
                        <th>Administrasi</th>
                        <th>Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in reservations" :key="item.id">
                        <td>
                            {{ item.patient.user.name }}
                            <p
                                class="mt-1 font-mono text-[10px] text-slate-400"
                            >
                                {{ item.booking_code }}
                            </p>
                        </td>
                        <td>{{ item.schedule.doctor.name }}</td>
                        <td>{{ money(item.admin_fee) }}</td>
                        <td>
                            <span
                                v-if="item.transaction"
                                class="text-blue-700 dark:text-blue-400"
                                >Lunas ·
                                {{ money(item.transaction.total_amount) }}</span
                            ><span
                                v-else
                                class="text-amber-700 dark:text-amber-400"
                                >Belum dibayar</span
                            >
                        </td>
                        <td>
                            <Link
                                v-if="item.transaction"
                                :href="receipt(item.id)"
                                class="text-xs font-semibold text-blue-700 dark:text-blue-400"
                                >Cetak nota ↗</Link
                            ><button
                                v-else
                                class="btn-secondary text-xs"
                                @click="
                                    selected = item;
                                    verified = false;
                                "
                            >
                                Input pembayaran
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!reservations.length">
                        <td
                            colspan="5"
                            class="py-12 text-center text-slate-400"
                        >
                            Belum ada pemeriksaan selesai pada tanggal ini.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </ClinicLayout>
</template>
