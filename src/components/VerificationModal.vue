<template>
    <TransitionRoot as="template" :show="open">
        <Dialog as="div" class="relative z-50" @close="$emit('close')">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" />
            </TransitionChild>
            <div class="fixed inset-0 overflow-y-auto scrollbar-gutter-stable">
                <div class="flex min-h-full items-center justify-center p-4">
                    <DialogPanel
                        class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-white/40 bg-white shadow-xl">
                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient" />
                        <button @click="$emit('close')"
                            class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200">
                            ✕
                        </button>

                        <div class="p-5 sm:p-6">
                            <div class="flex items-start gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-cyan-100 bg-gradient-to-br from-cyan-50 to-fuchsia-50">
                                    <img :src="verified" class="h-9 w-9" />
                                </div>

                                <div class="flex-1 pr-6">
                                    <h2 class="text-xl font-bold tracking-tight text-slate-900">
                                        Token Verification
                                    </h2>

                                    <p class="mt-2 text-sm leading-6 text-slate-600">
                                        Your verification request will be
                                        reviewed manually to confirm project
                                        authenticity and ownership.
                                    </p>
                                </div>
                            </div>

                            <!-- PAYMENT ASSETS -->

                            <div class="mt-5">
                                <p class="mb-3 text-sm font-semibold text-slate-900">
                                    Select Payment Asset
                                </p>

                                <div class="space-y-2.5">

                                    <button
                                        v-for="asset in paymentAssets"
                                        :key="asset.id"
                                        @click="$emit('select-asset', asset)"
                                        class="w-full rounded-xl border px-4 py-3 text-left transition"
                                        :class="
                                            selectedAsset?.id === asset.id
                                                ? 'border-cyan-400 bg-cyan-50'
                                                : 'border-slate-200 bg-slate-50 hover:border-slate-300'
                                        "
                                    >

                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">
                                                    {{ asset.asset_code }}
                                                </p>

                                                <p class="mt-0.5 text-xs text-slate-500">

                                                    {{
                                                        asset.asset_issuer
                                                            ? shorten(asset.asset_issuer)
                                                            : 'Official Stellar network token'
                                                    }}

                                                </p>
                                            </div>

                                            <div class="text-right">
                                                <p class="text-xl font-bold text-slate-900">

                                                    {{
                                                        formatAmount(
                                                            asset.amount
                                                        )
                                                    }}

                                                    <span class="text-sm font-semibold">
                                                        {{ asset.asset_code }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- WARNING -->

                            <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-3.5">
                                <div class="text-sm leading-6 text-amber-800">

                                    Verification fees are refunded if the
                                    verification request is rejected.

                                    Approved projects are not eligible for
                                    refunds.

                                </div>

                            </div>

                            <div class="mt-5">
                                <button v-if="!connected" @click="$emit('connect-wallet')"
                                    class="flex w-full items-center justify-center gap-3 rounded-full bg-gradient py-3 text-sm font-semibold text-white shadow-lg transition hover:scale-[1.01]">
                                    Connect Wallet
                                </button>

                                <button v-else @click="$emit('pay')" :disabled="loading"
                                    class="flex w-full items-center justify-center gap-3 rounded-full bg-gradient py-3 text-sm font-semibold text-white shadow-lg transition hover:scale-[1.01] disabled:cursor-not-allowed disabled:opacity-50">

                                    <span v-if="loading">
                                        Processing...
                                    </span>

                                    <span v-else>
                                        Pay Now
                                    </span>

                                </button>
                            </div>

                        </div>
                    </DialogPanel>
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