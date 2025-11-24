<script setup lang="ts">
interface Props {
    title: string;
    color?: 'default' | 'gray' | 'green';
    padding?: number;
    textSize?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
    extras?: any[];
    extrasClass?: string;
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
    textSize: 'md',
    extrasClass: ''
});

const { title, color, padding, textSize, extrasClass } = props;
</script>

<template>
    <div
        :class="`relative w-full ${colorVariants[color || 'default'].bg} rounded-t p-${padding} ${colorVariants[color || 'default'].text} border-b-2 border-dashed ${colorVariants[color || 'default'].border} ticket-shape-bottom`">
        <span :class="`text-${textSize} font-bold`">{{ title }}</span>
        <div v-if="extras" class="flex items-center gap-2">
            <template v-for="(extra, index) in extras" :key="index" :class="extrasClass">
                <span v-if="typeof extra === 'string' || typeof extra === 'number'">{{ extra }}</span>
                <component :is="extra" v-else class="w-4 h-4" />
            </template>
        </div>
    </div>
</template>