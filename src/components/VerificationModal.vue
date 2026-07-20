<template>
    <TransitionRoot as="template" :show="open">
        <Dialog as="div" class="relative z-50" @close="$emit('close')">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 transition-opacity bg-slate-950/80 backdrop-blur-sm" />
            </TransitionChild>
            <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild as="template" enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel
                            class="relative w-full max-w-lg overflow-hidden text-left transition-all transform bg-[#111827] border border-[rgba(148,163,184,0.16)] rounded-[25px] shadow-2xl">
                            
                            <!-- Close Button (X) -->
                            <button @click="$emit('close')"
                                class="absolute top-4 right-4 text-slate-400 hover:text-white transition focus:outline-none"
                                aria-label="Close Modal">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <div class="p-6">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-cyan-500/20 bg-[#182235] shadow-inner flex-shrink-0">
                                        <img :src="verified" class="h-8 w-8" />
                                    </div>

                                    <div class="flex-1 pr-6">
                                        <h2 class="text-xl font-bold tracking-tight text-white">
                                            Token Verification
                                        </h2>

                                        <p class="mt-1.5 text-xs leading-relaxed text-slate-400">
                                            Your verification request will be reviewed manually on-chain to confirm project authenticity and ownership.
                                        </p>
                                    </div>
                                </div>

                                <!-- PAYMENT ASSETS -->
                                <div class="mt-6">
                                    <p class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">
                                        Select Payment Asset
                                    </p>

                                    <div class="space-y-2.5">
                                        <button
                                            v-for="asset in paymentAssets"
                                            :key="asset.id"
                                            @click="$emit('select-asset', asset)"
                                            class="w-full rounded-2xl border px-4 py-3.5 text-left transition-all duration-200"
                                            :class="
                                                selectedAsset?.id === asset.id
                                                    ? 'border-cyan-400 bg-[#182738] shadow-[0_0_20px_rgba(18,203,238,0.15)] ring-1 ring-cyan-400/50'
                                                    : 'border-[rgba(148,163,184,0.16)] bg-[#182235] hover:border-slate-600 hover:bg-[#1f2c44]'
                                            "
                                        >
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-bold text-white flex items-center gap-2">
                                                        <span>{{ asset.asset_code }}</span>
                                                        <span v-if="selectedAsset?.id === asset.id" class="text-cyan-400 text-xs font-semibold">✓ Selected</span>
                                                    </p>

                                                    <p class="mt-0.5 text-xs font-mono text-slate-400">
                                                        {{
                                                            asset.asset_issuer
                                                                ? shorten(asset.asset_issuer)
                                                                : 'Official Stellar network token'
                                                        }}
                                                    </p>
                                                </div>

                                                <div class="text-right">
                                                    <p class="text-lg font-mono font-black text-white">
                                                        {{
                                                            formatAmount(
                                                                asset.amount
                                                            )
                                                        }}
                                                        <span class="text-xs font-bold text-slate-400">
                                                            {{ asset.asset_code }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- WARNING BOX -->
                                <div class="mt-5 rounded-2xl border border-amber-500/20 bg-amber-950/20 p-4">
                                    <div class="text-xs leading-relaxed text-amber-300/90 font-medium">
                                        Verification fees are fully refunded if your verification request is rejected. Approved projects are non-refundable.
                                    </div>
                                </div>

                                <!-- ACTION BUTTON -->
                                <div class="mt-6">
                                    <button v-if="!connected" @click="$emit('connect-wallet')"
                                        class="w-full text-white py-3.5 rounded-xl font-extrabold uppercase tracking-wider hover:opacity-95 hover:scale-[1.01] active:scale-[0.99] transition duration-300 bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] bg-[length:200%_200%] animate-gradientMove shadow-lg">
                                        Connect Wallet
                                    </button>

                                    <button v-else @click="$emit('pay')" :disabled="loading"
                                        class="w-full text-white py-3.5 rounded-xl font-extrabold uppercase tracking-wider hover:opacity-95 hover:scale-[1.01] active:scale-[0.99] transition duration-300 bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] bg-[length:200%_200%] animate-gradientMove shadow-lg disabled:cursor-not-allowed disabled:opacity-50">
                                        <span v-if="loading">
                                            Processing Payment...
                                        </span>
                                        <span v-else>
                                            Pay {{ selectedAsset ? `${formatAmount(selectedAsset.amount)} ${selectedAsset.asset_code}` : 'Now' }}
                                        </span>
                                    </button>
                                </div>

                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import {
    Dialog,
    DialogPanel,
    TransitionRoot,
    TransitionChild
} from '@headlessui/vue'
import verified from "@/assets/verify.png";

defineProps({
    open: {
        type: Boolean,
        default: false
    },
    connected: {
        type: Boolean,
        default: false
    },
    loading: {
        type: Boolean,
        default: false
    },
    paymentAssets: {
        type: Array,
        default: () => []
    },
    selectedAsset: {
        type: Object,
        default: null
    }
})

defineEmits([
    'close',
    'connect-wallet',
    'pay',
    'select-asset'
])

function shorten(str, head = 4, tail = 4) {
    if (str == null) return ''
    return str.length > head + tail
        ? `${str.slice(0, head)}...${str.slice(-tail)}`
        : str
}

function formatAmount(amount) {
    return Number(amount).toLocaleString(
        undefined,
        {
            maximumFractionDigits: 2
        }
    )
}
</script>