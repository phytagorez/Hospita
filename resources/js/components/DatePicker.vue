<script setup lang="ts">
import { computed, nextTick, ref, useId, watch } from 'vue';
import ClinicIcon from '@/components/ClinicIcon.vue';

const props = defineProps<{
    label: string;
    min: string;
    max?: string;
    clearable?: boolean;
    inline?: boolean;
}>();
const model = defineModel<string>({ required: true });
const dialog = ref<HTMLDialogElement>();
const trigger = ref<HTMLButtonElement>();
const month = ref(selectedMonth());
watch(
    () => [model.value, props.min, props.max],
    () => {
        if (props.inline) month.value = selectedMonth();
    },
);
const titleId = useId();
const weekdays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
const monthFormat = new Intl.DateTimeFormat('id-ID', {
    month: 'long',
    year: 'numeric',
});
const dayFormat = new Intl.DateTimeFormat('id-ID', { dateStyle: 'full' });

function date(value: string) {
    return new Date(value + 'T12:00:00');
}

function iso(year: number, monthIndex: number, day: number) {
    const value = new Date(year, monthIndex, day, 12);
    return [
        value.getFullYear(),
        String(value.getMonth() + 1).padStart(2, '0'),
        String(value.getDate()).padStart(2, '0'),
    ].join('-');
}

const displayed = computed(() => date(month.value + '-01'));
const cells = computed(() => {
    const year = displayed.value.getFullYear();
    const monthIndex = displayed.value.getMonth();
    const gap = (displayed.value.getDay() + 6) % 7;
    const length = new Date(year, monthIndex + 1, 0).getDate();
    return Array.from({ length: gap + length }, (_, index) =>
        index < gap ? '' : iso(year, monthIndex, index - gap + 1),
    );
});
const canGoBack = computed(() => month.value > props.min.slice(0, 7));
const canGoForward = computed(
    () => !props.max || month.value < props.max.slice(0, 7),
);

function moveMonth(offset: number) {
    month.value = iso(
        displayed.value.getFullYear(),
        displayed.value.getMonth() + offset,
        1,
    ).slice(0, 7);
}

function selectedMonth() {
    const selected = model.value;
    return (
        selected &&
        selected >= props.min &&
        (!props.max || selected <= props.max)
            ? selected
            : props.min
    ).slice(0, 7);
}

async function open() {
    month.value = selectedMonth();
    await nextTick();
    dialog.value?.showModal();
    const entry =
        dialog.value?.querySelector<HTMLButtonElement>(
            '[data-selected]:not(:disabled)',
        ) ??
        dialog.value?.querySelector<HTMLButtonElement>(
            '[data-today]:not(:disabled)',
        ) ??
        dialog.value?.querySelector<HTMLButtonElement>(
            '[data-date]:not(:disabled)',
        );
    entry?.focus();
}

function select(value: string) {
    model.value = value;
    if (!props.inline) dialog.value?.close();
}
</script>

<template>
    <div class="field">
        <span v-if="!inline">{{ label }}</span>
        <button
            v-if="!inline"
            ref="trigger"
            type="button"
            aria-haspopup="dialog"
            :aria-label="
                label + (model ? ', ' + dayFormat.format(date(model)) : '')
            "
            class="flex w-full items-center gap-3 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-left text-sm font-medium text-slate-800 hover:border-blue-400 focus-visible:outline-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            @click="open"
        >
            <ClinicIcon
                name="calendar"
                :size="19"
                class="shrink-0 text-blue-600"
            />
            <span>{{
                model ? dayFormat.format(date(model)) : 'Pilih tanggal'
            }}</span>
        </button>
        <component
            :is="inline ? 'div' : 'dialog'"
            ref="dialog"
            :role="inline ? 'group' : undefined"
            :aria-labelledby="titleId"
            class="rounded-2xl bg-[#101e36] p-4 text-slate-100 sm:p-5"
            :class="
                inline
                    ? ''
                    : 'm-auto w-[calc(100%-2rem)] max-w-sm shadow-xl backdrop:bg-slate-950/55'
            "
            @close="trigger?.focus()"
            @click.self="!inline && dialog?.close()"
        >
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between gap-2">
                    <h2 :id="titleId" class="text-sm font-bold text-white">
                        {{ label }}
                    </h2>
                    <button
                        type="button"
                        v-if="!inline"
                        class="rounded-lg px-2 py-1 text-xs text-blue-100 hover:bg-white/10"
                        @click="dialog?.close()"
                    >
                        Tutup
                    </button>
                </div>
                <div
                    class="flex items-center justify-between rounded-xl bg-white/10 p-1 text-sm"
                >
                    <button
                        type="button"
                        class="size-9 rounded-lg text-blue-100 hover:bg-white/15 disabled:cursor-not-allowed"
                        aria-label="Bulan sebelumnya"
                        :disabled="!canGoBack"
                        @click="moveMonth(-1)"
                    >
                        ‹
                    </button>
                    <p class="font-bold" aria-live="polite">
                        {{ monthFormat.format(displayed) }}
                    </p>
                    <button
                        type="button"
                        class="size-9 rounded-lg text-blue-100 hover:bg-white/15 disabled:cursor-not-allowed"
                        aria-label="Bulan berikutnya"
                        :disabled="!canGoForward"
                        @click="moveMonth(1)"
                    >
                        ›
                    </button>
                </div>
                <div class="grid grid-cols-7 gap-1 text-center">
                    <span
                        v-for="day in weekdays"
                        :key="day"
                        class="py-2 text-[11px] text-slate-300"
                        >{{ day }}</span
                    >
                    <template v-for="(value, index) in cells" :key="index">
                        <span v-if="!value" aria-hidden="true"></span>
                        <button
                            v-else
                            type="button"
                            data-date
                            :data-selected="model === value ? '' : undefined"
                            :data-today="min === value ? '' : undefined"
                            :aria-label="dayFormat.format(date(value))"
                            :aria-pressed="model === value"
                            :disabled="
                                value < min ||
                                (max !== undefined && value > max)
                            "
                            class="mx-auto grid aspect-square w-full max-w-10 place-items-center rounded-full text-xs transition focus-visible:outline-sky-300 disabled:cursor-not-allowed disabled:text-slate-400 sm:text-sm"
                            :class="
                                model === value
                                    ? 'bg-sky-300 font-bold text-slate-950'
                                    : 'enabled:hover:bg-white/15'
                            "
                            @click="select(value)"
                        >
                            {{ Number(value.slice(-2)) }}
                        </button>
                    </template>
                </div>
                <button
                    v-if="clearable && model"
                    type="button"
                    class="rounded-lg bg-white/10 px-3 py-2 text-sm text-sky-100 hover:bg-white/15"
                    @click="select('')"
                >
                    Hapus tanggal
                </button>
            </div>
        </component>
    </div>
</template>
