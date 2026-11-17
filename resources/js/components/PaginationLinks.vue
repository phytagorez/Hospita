<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
defineProps<{
    links: { url: string | null; label: string; active: boolean }[];
}>();
const label = (value: string) =>
    value
        .replace('&laquo; Previous', '← Sebelumnya')
        .replace('Next &raquo;', 'Berikutnya →')
        .replace(/<[^>]*>/g, '')
        .replace(/&laquo;/g, '←')
        .replace(/&raquo;/g, '→');
</script>
<template>
    <nav
        v-if="links.length > 3"
        aria-label="Paginasi"
        class="mt-6 flex flex-wrap gap-2"
    >
        <template v-for="(link, i) in links" :key="i"
            ><Link
                v-if="link.url"
                :href="link.url"
                :class="[
                    'rounded-lg border px-3 py-2 text-xs',
                    link.active
                        ? 'border-teal-700 bg-teal-700 text-white'
                        : 'border-slate-200 dark:border-slate-700',
                ]"
                >{{ label(link.label) }}</Link
            ><span v-else class="px-3 py-2 text-xs text-slate-400">{{
                label(link.label)
            }}</span></template
        >
    </nav>
</template>
