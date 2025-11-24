<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = defineProps<{
    class?: string;
    offset?: string;
    removeMask?: boolean;
}>();

const styles = computed(() => {
    const baseStyles: Record<string, string> = {
        '--background': '#ffffff',
        '--color': 'rgba(0, 0, 0, 0.2)',
        '--height': '5px',
        '--width': '1px',
        '--fade-stop': '90%',
        '--offset': props.offset || '150px',
        '--color-dark': 'rgba(255, 255, 255, 0.2)',
    };

    return baseStyles;
});
</script>

<template>
    <div :style="styles" :class="cn(
        'absolute top-[calc(var(--offset)/2*-1)] h-[calc(100%+var(--offset))] w-[var(--width)]',
        'bg-[linear-gradient(to_bottom,var(--color),var(--color)_50%,transparent_0,transparent)]',
        '[background-size:var(--width)_var(--height)]',
        !props.removeMask && '[mask:linear-gradient(to_top,var(--background)_var(--fade-stop),transparent),_linear-gradient(to_bottom,var(--background)_var(--fade-stop),transparent),_linear-gradient(black,black)]',
        '[mask-composite:exclude]',
        'z-30',
        'dark:bg-[linear-gradient(to_bottom,var(--color-dark),var(--color-dark)_50%,transparent_0,transparent)]',
        props.class
    )"></div>
</template>
