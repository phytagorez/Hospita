<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { PaginationLink } from '@/types/clinic';

defineProps<{ links: PaginationLink[] }>();

function paginationLabel(index: number, links: PaginationLink[]) {
    if (index === 0) return 'Sebelumnya';
    if (index === links.length - 1) return 'Berikutnya';

    return links[index]?.label;
}
</script>

<template>
    <nav v-if="links.length > 3" aria-label="Paginasi" class="mt-6">
        <ul class="flex list-none flex-wrap items-center gap-2">
            <li v-for="(link, index) in links" :key="index">
                <component
                    :is="link.url ? Link : 'span'"
                    :href="link.url ?? undefined"
                    :aria-current="link.active ? 'page' : undefined"
                    :class="[
                        'inline-flex min-h-8 min-w-8 items-center justify-center rounded-lg border px-3 py-2 text-xs',
                        link.active
                            ? 'border-blue-700 bg-blue-700 text-white'
                            : link.url
                              ? 'border-slate-200 hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800'
                              : 'border-transparent text-slate-500 dark:text-slate-400',
                    ]"
                >
                    {{ paginationLabel(index, links) }}
                </component>
            </li>
        </ul>
    </nav>
</template>
