<script setup lang="ts">
import { SwitchCameraIcon } from 'lucide-vue-next';
import { ref } from 'vue';
import { QrcodeStream } from 'vue-qrcode-reader';
import axios from 'axios';
import { scan } from '@/routes/manage/event';

const props = defineProps<{
    event: {
        title: string;
        slug: string;
    }
}>();

const paused = ref<boolean>(false)
const ticket = ref<string | null>(null)
const scanResult = ref<{ status: string, user: { name: string, email: string }, used_at: string, doorman: { name: string, email: string } } | null>(null)
const error = ref<string | null>(null)
const loading = ref<boolean>(false)

const playScanSound = () => {
    const audioContext = new (window.AudioContext || (window as any).webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.type = 'sine';
    oscillator.frequency.setValueAtTime(800, audioContext.currentTime); // Beep frequency
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    oscillator.start();
    gainNode.gain.exponentialRampToValueAtTime(0.00001, audioContext.currentTime + 0.1);
    oscillator.stop(audioContext.currentTime + 0.1);
};

const triggerHapticFeedback = (type: 'success' | 'warning' | 'error') => {
    if (!navigator.vibrate) return;

    switch (type) {
        case 'success':
            // Two short vibrations
            navigator.vibrate([100, 50, 100]);
            break;
        case 'warning':
            // Two medium long vibrations
            navigator.vibrate([300, 100, 300]);
            break;
        case 'error':
            // One big vibration
            navigator.vibrate(500);
            break;
    }
};

async function onDetect(detectedCodes: any) {
    const rawValue = detectedCodes[0].rawValue
    ticket.value = rawValue
    paused.value = true
    loading.value = true
    error.value = null
    scanResult.value = null

    // Play beep on detection
    playScanSound();

    let type = 'static';
    let token = rawValue;
    let totp = null;

    if (rawValue.startsWith('dyn-')) {
        type = 'dynamic';
        const content = rawValue.substring(4);
        const lastHyphenIndex = content.lastIndexOf('-');

        if (lastHyphenIndex !== -1) {
            token = content.substring(0, lastHyphenIndex);
            totp = content.substring(lastHyphenIndex + 1);
        } else {
            token = content;
        }
    } else if (rawValue.startsWith('st-')) {
        type = 'static';
        token = rawValue.substring(3);
    }

    try {
        const response = await axios.post(scan({ event: props.event.slug }).url, {
            type,
            token,
            totp
        });

        scanResult.value = response.data;

        // Vibration feedback based on status
        if (scanResult.value?.status === 'valid' || scanResult.value?.status === 'active') {
            triggerHapticFeedback('success');
        } else if (scanResult.value?.status === 'used') {
            triggerHapticFeedback('warning');
        } else {
            // Treat other statuses as warnings or potential issues
            triggerHapticFeedback('warning');
        }

    } catch (e: any) {
        console.error(e);
        error.value = e.response?.data?.message || 'Error al procesar la entrada';
        triggerHapticFeedback('error');
    } finally {
        loading.value = false;
    }
}

function onError(err: any) {
    console.log(err)
    error.value = "Error de cámara: " + err.message
}

function resetScanner() {
    paused.value = false
    ticket.value = null
    scanResult.value = null
    error.value = null
}

const facingMode = ref('environment')

function switchCamera() {
    facingMode.value = facingMode.value === 'environment' ? 'user' : 'environment'
}
</script>

<template>
    <div class="flex h-screen items-center justify-center bg-black">
        <qrcode-stream :paused="paused" :constraints="{ facingMode }" @detect="onDetect" @error="onError">
            <button @click="switchCamera"
                class="absolute top-4 right-4 z-10 rounded-full bg-black/50 p-2 text-white backdrop-blur-sm">
                <SwitchCameraIcon class="h-6 w-6" />
            </button>

            <!-- Frame -->
            <div class="absolute inset-0 bg-linear-to-b from-transparent via-black/40 to-black">
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="h-64 w-64 border-8 border-moovin-lime/50 rounded-xl" />
                </div>

                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="h-32 w-32 border-8 border-moovin-lila/50 rounded-full" />
                </div>

                <div class="absolute bottom-12 left-0 right-0 text-center px-4">
                    <p class="text-moovin-lime/80 text-lg font-black">Escanea el código QR para entrar al evento</p>
                    <p class="text-white/60 text-sm mt-2">{{ props.event.title }}</p>
                </div>
            </div>
        </qrcode-stream>
    </div>

    <!-- Status Window Overlay -->
    <div v-if="paused"
        class="fixed inset-0 z-50 flex items-end justify-center sm:items-center bg-black/60 backdrop-blur-sm p-4 animate-in fade-in duration-200">

        <div
            class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl animate-in slide-in-from-bottom-10 zoom-in-95 duration-300">

            <div v-if="loading" class="flex flex-col items-center justify-center py-8">
                <div class="h-10 w-10 animate-spin rounded-full border-4 border-moovin-lime border-t-transparent"></div>
                <p class="mt-4 text-gray-600 font-medium">Validando entrada...</p>
            </div>

            <div v-else-if="error" class="flex flex-col items-center text-center">
                <div class="mb-4 rounded-full bg-red-100 p-4 text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-red-600 mb-2">Error</h3>
                <p class="text-gray-600 mb-6">{{ error }}</p>
            </div>

            <div v-else-if="scanResult" class="flex flex-col items-center text-center">
                <!-- Success State (Valid) -->
                <template v-if="scanResult.status === 'valid' || scanResult.status === 'active'">
                    <div class="mb-4 rounded-full bg-green-100 p-4 text-green-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-green-600 mb-1">¡Entrada Válida!</h3>
                    <p class="text-gray-500 mb-4 font-medium">Puedes ingresar</p>

                    <div class="text-gray-800 bg-gray-100 w-full space-y-4 p-4 rounded-lg text-left mb-6">
                        <div>
                            <p class="font-bold">Usuario</p>
                            <p>{{ scanResult.user.name }}</p>
                            <p>{{ scanResult.user.email }}</p>
                        </div>
                        <div>
                            <p class="font-bold">Fecha y hora</p>
                            <p>{{ scanResult.used_at }}</p>
                        </div>
                        <div>
                            <p class="font-bold">Portero</p>
                            <p>{{ scanResult.doorman.name }}</p>
                        </div>
                    </div>
                </template>

                <!-- Used State -->
                <template v-else-if="scanResult.status === 'used'">
                    <div class="mb-4 rounded-full bg-yellow-100 p-4 text-yellow-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-yellow-600 mb-1">Ticket Ya Usado</h3>
                    <p class="text-gray-500 mb-4 font-medium">Esta entrada ya fue escaneada previamente</p>

                    <div class="text-gray-800 bg-gray-100 w-full space-y-4 p-4 rounded-lg text-left mb-6">
                        <div>
                            <p class="font-bold">Usuario</p>
                            <p>{{ scanResult.user.name }}</p>
                            <p>{{ scanResult.user.email }}</p>
                        </div>
                        <div>
                            <p class="font-bold">Fecha y hora</p>
                            <p>{{ scanResult.used_at }}</p>
                        </div>
                        <div>
                            <p class="font-bold">Portero</p>
                            <p>{{ scanResult.doorman.name }}</p>
                        </div>
                    </div>
                </template>

                <!-- Fallback for other statuses -->
                <template v-else>
                    <div class="mb-4 rounded-full bg-gray-100 p-4 text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Estado: {{ scanResult.status }}</h3>
                    <p class="text-gray-500 mb-6">Consulte con un supervisor</p>
                </template>
            </div>

            <button @click="resetScanner"
                class="w-full rounded-xl bg-black py-4 text-white font-bold text-lg active:scale-95 transition-transform">
                Escanear Otro
            </button>
        </div>
    </div>
</template>