<script setup lang="ts">
import { statuses } from '@/types/clinic';
defineProps<{ status: string }>();
</script>
<template>
    <span
        :class="[
            'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
            status === 'completed'
                ? 'bg-teal-50 text-teal-800 dark:bg-teal-950 dark:text-teal-300'
                : status === 'cancelled'
                  ? 'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300'
                  : status === 'examining'
                    ? 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300'
                    : 'bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
        ]"
        >{{ statuses[status] || status }}</span
    >
</template>
