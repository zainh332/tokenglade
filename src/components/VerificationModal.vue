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
                        class="relative w-full max-w-xl overflow-hidden rounded-[32px] border border-white/40 bg-white shadow-2xl">
                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient" />
                        <button @click="$emit('close')"
                            class="absolute right-5 top-5 flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200">
                            ✕
                        </button>

                        <div class="p-7 sm:p-8">
                            <div class="flex items-start gap-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-cyan-100 bg-gradient-to-br from-cyan-50 to-fuchsia-50">
                                    <img :src="verified" class="w-12 h-12" />
                                </div>

                                <div class="flex-1 pr-8">
                                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                                        Token Verification
                                    </h2>

                                    <p class="mt-3 text-sm leading-7 text-slate-600">
                                        Your verification request will be
                                        reviewed manually to confirm project
                                        authenticity and ownership.
                                    </p>
                                </div>
                            </div>

                            <!-- FEE -->

                            <div class="mt-7 rounded-2xl border border-slate-200 bg-slate-50/80 p-5">

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div>
                                            <p class="text-base font-semibold text-slate-900">
                                                Verification Fee
                                            </p>

                                            <p class="mt-1 text-sm text-slate-500">
                                                One-time review deposit
                                            </p>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-3xl font-bold tracking-tight text-slate-900">
                                            {{ fee }}
                                            <span class="text-xl">
                                                XLM
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- WARNING -->

                            <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-5">
                                <div class="flex gap-4">
                                    <div class="text-sm leading-7 text-amber-800">

                                        Verification fees are refunded if the
                                        verification request is rejected.

                                        Approved projects are not eligible for
                                        refunds.

                                    </div>
                                </div>
                            </div>

                            <div class="mt-7">
                                <button v-if="!connected" @click="$emit('connect-wallet')"
                                    class="flex w-full items-center justify-center gap-3 rounded-full bg-gradient py-4 text-sm font-semibold text-white shadow-lg transition hover:scale-[1.01]">
                                    Connect Wallet
                                </button>

                                <button v-else @click="$emit('pay')" :disabled="loading"
                                    class="flex w-full items-center justify-center gap-3 rounded-full bg-gradient py-4 text-sm font-semibold text-white shadow-lg transition hover:scale-[1.01] disabled:cursor-not-allowed disabled:opacity-50">

                                    <span v-if="loading">
                                        Processing...
                                    </span>

                                    <span v-else>
                                        Pay {{ fee }} XLM
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

    fee: {
        type: Number,
        default: 185
    }
})

defineEmits([
    'close',
    'connect-wallet',
    'pay'
])

</script>