<script setup lang="ts">
interface Props {
    title: string;
    color?: 'default' | 'gray' | 'green';
    padding?: number;
    textSize?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
}

const colorVariants = {
    default: {
        bg: 'bg-moovin-lime',
        text: 'text-moovin-dark-green',
        border: 'border-moovin-green'
    },
    gray: {
        bg: 'bg-neutral-900',
        text: 'text-neutral-50',
        border: 'border-neutral-800'
    },
    green: {
        bg: 'bg-moovin-green',
        text: 'text-moovin-dark-green',
        border: 'border-moovin-dark-green'
    }
}


const props = withDefaults(defineProps<Props>(), {
    padding: 4,
    textSize: 'md'
});

const { title, color, padding, textSize } = props;
</script>

<template>
    <div
        :class="`relative w-full ${colorVariants[color || 'default'].bg} rounded-t p-${padding} ${colorVariants[color || 'default'].text} border-b-2 border-dashed ${colorVariants[color || 'default'].border}
                        before:absolute before:bottom-0 before:left-0 before:h-5 before:w-5 before:-translate-x-1/2 before:translate-y-1/2 before:rounded-full before:bg-behind
                        after:absolute after:bottom-0 after:right-0 after:h-5 after:w-5 after:translate-x-1/2 after:translate-y-1/2 after:rounded-full after:bg-behind`">
        <span :class="`text-${textSize}`">{{ title }}</span>
    </div>
</template>