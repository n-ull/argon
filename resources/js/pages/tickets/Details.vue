<script setup lang="ts">
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import AppLogo from '@/components/AppLogo.vue';
import { TotpUtils } from '@/lib/TotpUtils';
import { formatDateTime } from '@/lib/utils';
import { show } from '@/routes/orders';
import { Ticket } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Calendar, Gift, Printer } from 'lucide-vue-next';
import { NQrCode } from 'naive-ui';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { trans as t, trans, wTrans } from 'laravel-vue-i18n';
import { useDialog } from '@/composables/useDialog';
import TransferTicketDialog from './dialogs/TransferTicketDialog.vue';

const { ticket } = defineProps<{ ticket: Ticket }>();

const totp = ref('');
const timeLeft = ref(0);
const qrCode = ref('');
let timer: number | null = null;

const updateTotp = async () => {
    const result = await TotpUtils.generate(ticket.token);
    totp.value = result.otp;
    qrCode.value = `dyn-${ticket.token}-${result.otp}`;
};

onMounted(async () => {
    // Initial calculation
    timeLeft.value = TotpUtils.getRemainingSeconds();
    await updateTotp();

    timer = window.setInterval(async () => {
        if (timeLeft.value <= 1) {
            await updateTotp();
            timeLeft.value = 30;
        } else {
            timeLeft.value -= 1;
        }
    }, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

const printTicket = () => {
    window.print();
};

const isInactive = computed(() => ticket.status === 'used' || ticket.status === 'expired' || ticket.status === 'cancelled');

const canTransfer = computed(() => {
    return ticket.transfers_left > 0 && !isInactive.value;
});

const { open } = useDialog();

const transferTicket = () => {
    open({
        component: TransferTicketDialog,
        props: {
            ticket: ticket,
        },
    });
}



</script>

<template>
    <SimpleLayout>
        <div class="flex items-center justify-center min-h-screen bg-zinc-950 p-6">
            <AppLogo class="print-only mb-4 h-6 fill-neutral-900" />
            <div :class="{ 'opacity-20 grayscale-[0.5]': isInactive }"
                class="max-w-md w-full bg-zinc-900 rounded-2xl shadow-xl overflow-hidden border border-zinc-800">
                <div
                    :class="[ticket.type === 'static' ? 'bg-moovin-lila' : 'bg-moovin-lime', 'p-6 text-neutral-900 relative']">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold leading-tight">{{ ticket.event.title }}</h2>
                            <p class="text-neutral-600 flex items-center gap-2 mt-1">
                                <Calendar class="inline" :size="16" />
                                {{ formatDateTime(ticket.event.start_date) }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span
                                class="px-3 py-1 bg-white/20 rounded-full text-xs font-semibold uppercase tracking-wider">
                                {{ $t('tickets.' + ticket.type) }}
                            </span>
                            <button v-if="ticket.type === 'static'" @click="printTicket"
                                class="no-print p-2 bg-white/20 hover:bg-white/40 rounded-lg transition-colors flex items-center gap-2 px-3 border border-white/30"
                                :title="t('tickets.print')">
                                <Printer :size="16" />
                                <span class="text-[10px] font-bold uppercase tracking-wider">{{ $t('tickets.print')
                                }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="ticket-card" class="p-6 space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-wider">{{
                                $t('tickets.venue') }}</label>
                            <p class="text-zinc-100 font-medium">{{ ticket.event.location_info.address }}</p>
                        </div>
                        <div class="text-right">
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-wider">{{ t('tickets.seat')
                            }} /
                                {{ $t('tickets.section') }}</label>
                            <p class="text-zinc-100 font-medium">{{ ticket.product.name }}</p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center py-4 border-y border-dashed border-zinc-800">
                        <div v-if="ticket.order">
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-wider">{{
                                $t('order.reference') }}</label>
                            <Link :href="show(ticket.order.id)">
                                <p class="text-zinc-300 font-mono underline">#{{ ticket.order.reference_id }}</p>
                            </Link>
                        </div>

                        <div class="flex gap-2 items-center mx-auto" v-if="ticket.is_courtesy">
                            <Gift />
                            <span>
                                {{ $t('tickets.this_is_courtesy_ticket') }}
                            </span>
                        </div>
                    </div>

                    <div v-if="ticket.type === 'dynamic'" class="flex flex-col items-center pt-2">
                        <p class="text-[10px] text-zinc-500 uppercase tracking-[0.2em] mb-4 text-center font-bold">
                            {{ $t('tickets.dynamic_ticket_info') }}</p>

                        <div class="qr-wrapper p-4 bg-white rounded-xl border border-zinc-700 mb-3">
                            <n-qr-code :value="qrCode" :size="256" type="svg" :padding="0" color="hsl(165, 40%, 30%)" />
                        </div>
                        <p class="text-[10px] text-zinc-500 uppercase tracking-[0.2em] font-bold">Scan for Entry</p>

                        <p class="text-[10px] text-zinc-500 uppercase tracking-[0.2em] font-bold mt-2 font-mono">{{ totp
                            }}</p>

                        <!-- Refresh Indicator -->
                        <div class="mt-4 flex flex-col items-center w-full">
                            <div class="w-32 h-1 bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full bg-moovin-lime transition-all duration-1000 ease-linear"
                                    :style="{ width: `${(timeLeft / 30) * 100}%` }"></div>
                            </div>
                            <p class="text-[9px] text-zinc-600 mt-2 uppercase tracking-widest">{{
                                $t('tickets.refresh_in') }} {{ timeLeft
                                }}s</p>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center pt-2">
                        <p class="text-md mb-4 text-zinc-500 uppercase tracking-[0.2em] font-bold">{{ ticket.token }}
                        </p>
                        <div class="qr-wrapper p-4 bg-white rounded-xl border border-zinc-700 mb-3">
                            <n-qr-code :value="`st-${ticket.token}`" :size="256" type="svg" :padding="0"
                                color="hsl(242, 32%, 15%)" />
                        </div>
                        <p class="text-[10px] text-zinc-500 uppercase tracking-[0.2em] font-bold">{{
                            $t('tickets.scan_for_entry') }}</p>
                    </div>
                </div>

                <div class="p-6">
                    <button @click="transferTicket" :disabled="!canTransfer"
                        class="w-full font-bold py-3 px-4 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        :class="ticket.type === 'static' ? 'bg-moovin-lila text-moovin-dark-purple hover:bg-moovin-lila/80' : 'bg-moovin-lime text-moovin-dark-green hover:bg-moovin-lime/80'">
                        <span v-if="canTransfer">{{ $t('tickets.transfer') }}</span>
                        <span v-else>{{ $t('tickets.cannot_transfer') }}</span>
                    </button>
                    <div v-if="canTransfer" class="mt-2 text-center">
                        <span class="text-xs text-gray-400">{{ trans('tickets.transfers_left', {
                            count:
                                ticket.transfers_left.toString()
                        })
                        }}</span>
                    </div>
                </div>
            </div>
        </div>

    </SimpleLayout>
</template>

<style scoped>
@media print {

    /* Hide ALL UI elements except the ticket content */
    :deep(nav),
    :deep(footer),
    :deep(.no-print),
    .no-print {
        display: none !important;
    }

    .print-only {
        display: block !important;
    }

    /* Reset layout for print */
    :root,
    body {
        background: white !important;
        overflow: visible !important;
    }

    .bg-zinc-950 {
        background: white !important;
        padding: 0 !important;
        min-height: auto !important;
        display: block !important;
    }

    /* Make the ticket card use full width and no shadow */
    .max-w-md {
        max-width: 100% !important;
        width: 100% !important;
        box-shadow: none !important;
        border: 1px solid #e5e7eb !important;
        margin: 0 !important;
        border-radius: 12px !important;
        overflow: visible !important;
    }

    /* Ensure background colors are printed */
    .bg-moovin-lila {
        background-color: #f3f4f6 !important;
        /* Elegant light gray for print */
        color: #111827 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .bg-zinc-900 {
        background: white !important;
    }

    /* Standardize text colors for legibility */
    .text-zinc-500,
    .text-neutral-600 {
        color: #6b7280 !important;
    }

    .text-zinc-100,
    .text-zinc-300,
    .text-neutral-900 {
        color: #111827 !important;
    }

    /* Borders for structure */
    .border-zinc-800,
    .border-zinc-700,
    .border-y {
        border-color: #e5e7eb !important;
    }

    /* Ensure QR code container stays white */
    .bg-white {
        background: white !important;
        border: 1px solid #e5e7eb !important;
    }

    #ticket-card {
        color: black !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        text-align: center !important;
    }

    #ticket-card>div {
        width: 100% !important;
    }

    .qr-wrapper {
        width: 100% !important;
        margin: 2rem auto !important;
        padding: 1rem !important;
        background: white !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
    }

    .qr-wrapper :deep(svg) {
        width: 100% !important;
        height: auto !important;
        max-width: 100% !important;
    }

    /* Adjust text sizes for print with large QR */
    .text-2xl {
        font-size: 2.5rem !important;
    }

    .text-md {
        font-size: 1.5rem !important;
    }

    .text-\[10px\] {
        font-size: 1rem !important;
    }

    /* Center layout elements for print */
    .grid-cols-2 {
        display: flex !important;
        flex-direction: column !important;
        gap: 1.5rem !important;
    }

    .text-right {
        text-align: center !important;
    }

    .flex.justify-between.items-center {
        justify-content: center !important;
        text-align: center !important;
    }

    .border-y.border-dashed {
        border-style: solid !important;
        width: 100% !important;
    }

    /* Global print adjustment */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}

.print-only {
    display: none;
}
</style>