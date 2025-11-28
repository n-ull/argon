<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = defineProps<{
    class?: string;
    offset?: string;
}>();

const styles = computed(() => ({
    '--background': '#ffffff',
    '--color': 'rgba(0, 0, 0, 0.2)',
    '--height': '1px',
    '--width': '5px',
    '--fade-stop': '90%',
    '--offset': props.offset || '200px',
    '--color-dark': 'rgba(255, 255, 255, 0.2)',
    maskComposite: 'exclude',
}));
</script>

<template>
    <div :style="styles" :class="cn(
        'absolute left-[calc(var(--offset)/2*-1)] h-[var(--height)] w-[calc(100%+var(--offset))]',
        'bg-[linear-gradient(to_right,var(--color),var(--color)_50%,transparent_0,transparent)]',
        '[background-size:var(--width)_var(--height)]',
        '[mask:linear-gradient(to_left,var(--background)_var(--fade-stop),transparent),_linear-gradient(to_right,var(--background)_var(--fade-stop),transparent),_linear-gradient(black,black)]',
        '[mask-composite:exclude]',
        'z-30',
        'dark:bg-[linear-gradient(to_right,var(--color-dark),var(--color-dark)_50%,transparent_0,transparent)]',
        props.class
    )"></div>
</template>
