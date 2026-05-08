<template>
    <TransitionRoot as="template" :show="open">
        <Dialog as="div" class="relative z-50" @close="$emit('close')">
            <div class="fixed inset-0 bg-black/50" />
            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <DialogPanel class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">

                        <h2 class="text-xl font-bold text-slate-900">
                            Token Verification
                        </h2>

                        <p class="mt-3 text-sm text-slate-600">
                            Your verification request will be reviewed manually to confirm project authenticity and
                            ownership.
                        </p>

                        <!-- FEE -->

                        <div class="mt-5 rounded-xl border bg-slate-50 p-4">
                            <div class="flex justify-between">
                                <span class="text-slate-500">
                                    Verification Fee
                                </span>

                                <span class="font-semibold">
                                    {{ fee }} XLM
                                </span>
                            </div>
                        </div>

                        <!-- WARNING -->

                        <div
                            class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700 leading-6">
                            Verification fees are refunded if the verification request is rejected.
                            Approved projects are not eligible for refunds.
                        </div>

                        <div class="mt-6">
                            <button v-if="!connected" @click="$emit('connect-wallet')"
                                class="w-full text-xs text-white rounded-full btn-padding sm:text-t14 bg-gradient">
                                Connect Wallet
                            </button>

                            <button v-else @click="$emit('pay')" :disabled="loading"
                                class="w-full text-xs text-white rounded-full btn-padding sm:text-t14 bg-gradient disabled:opacity-50">

                                <span v-if="loading">
                                    Processing...
                                </span>

                                <span v-else>
                                    Pay {{ fee }} XLM
                                </span>

                            </button>
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
    TransitionRoot
} from '@headlessui/vue'

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
        default: 100
    }
})

defineEmits([
    'close',
    'connect-wallet',
    'pay'
])

</script>