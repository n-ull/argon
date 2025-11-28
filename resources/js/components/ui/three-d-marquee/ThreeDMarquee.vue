<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed } from 'vue';
import GridLineVertical from './GridLineVertical.vue';
import GridLineHorizontal from './GridLineHorizontal.vue';

const props = withDefaults(defineProps<{
    images: string[];
    class?: string;
    speed?: number;
}>(), {
    speed: 1
});

// Ensure we have enough images to fill the view and scroll smoothly
const processedImages = computed(() => {
    const minImages = 40; // Minimum number of images to ensure smooth scrolling and full grid
    let currentImages = [...props.images];

    if (currentImages.length === 0) return [];

    while (currentImages.length < minImages) {
        currentImages = [...currentImages, ...props.images];
    }

    return currentImages;
});

const chunkSize = computed(() => Math.ceil(processedImages.value.length / 4));
const chunks = computed(() => {
    return Array.from({ length: 4 }, (_, colIndex) => {
        const start = colIndex * chunkSize.value;
        return processedImages.value.slice(start, start + chunkSize.value);
    });
});
</script>

<template>
    <div :class="cn(
        'w-full h-full overflow-hidden rounded-2xl',
        props.class
    )">
        <div class="flex size-full items-center justify-center">
            <div class="h-[1720px] w-[1720px] min-h-[1720px] min-w-[1720px] shrink-0 scale-50 sm:scale-75 lg:scale-100">
                <div style="transform: rotateX(55deg) rotateY(0deg) rotateZ(-45deg);"
                    class="relative top-96 right-[50%] grid size-full origin-top-left grid-cols-4 gap-8 [transform-style:preserve-3d]">
                    <div v-for="(subarray, colIndex) in chunks" :key="colIndex + 'marquee'"
                        class="flex flex-col items-start marquee-column relative" :style="{
                            '--duration': (colIndex % 2 === 0 ? 40 : 50) / props.speed + 's',
                            '--direction': colIndex % 2 === 0 ? 'normal' : 'reverse'
                        }">
                        <!-- Duplicate the subarray to create seamless infinite scroll -->
                        <div v-for="i in 2" :key="i" class="flex flex-col w-full relative">
                            <GridLineVertical class="-left-4" offset="0px" :remove-mask="true" />
                            <div v-for="(image, imageIndex) in subarray" :key="imageIndex + image + i"
                                class="relative mb-8">
                                <GridLineHorizontal class="-top-4" offset="20px" />
                                <img :src="image" :alt="`Image ${imageIndex + 1}`"
                                    class="aspect-[970/700] rounded-lg object-cover ring ring-gray-950/5 transition-transform duration-300 ease-in-out"
                                    width="970" height="700" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.marquee-column {
    animation: marquee var(--duration) linear infinite var(--direction);
}

@keyframes marquee {
    0% {
        transform: translateY(0);
    }

    100% {
        transform: translateY(-50%);
    }
}
</style>
