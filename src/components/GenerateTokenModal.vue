    <template>
        <TransitionRoot as="template" :show="open">
            <Dialog as="div" class="relative z-10" @close="closeModal">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0"
                    enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        <TransitionChild as="template" enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <DialogPanel
                                class="relative overflow-hidden rounded-lg bg-white px-6 pt-6 pb-4 shadow-xl transition-all w-full max-w-md text-left">

                                <!-- Close -->
                                <button @click="closeModal"
                                    class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>

                                <!-- Logo + Title -->
                                <div class="mb-4 text-center">
                                    <img class="h-12 mx-auto" :src="Logo" alt="Stellar Logo" />
                                    <h2 class="text-xl font-semibold mt-2">Create Your Token on Stellar</h2>
                                    <p class="text-sm text-gray-500">Easily mint tokens on Stellar using your connected
                                        wallet.</p>
                                </div>

                                <!-- Form -->
                                <form @submit.prevent="generateToken">
                                    <div class="space-y-4">

                                        <!-- Distributor Wallet (read-only) -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Distributor Wallet
                                                <span class="text-gray-400 cursor-help"
                                                    title="This is your connected wallet.">?</span>
                                            </label>
                                            <div
                                                class=" bg-gradient text-white rounded-full px-3 py-2 text-sm font-mono truncate w-[120px]">
                                                123456789 Dummy Token
                                            </div>
                                        </div>

                                        <!-- Asset Code -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Asset Code
                                                <span class="text-gray-400 cursor-help"
                                                    title="Token symbol (e.g., USDT, COIN)">?</span>
                                            </label>
                                            <input type="text" required
                                                class="block w-full rounded-md border border-gray-300 px-3 py-2 sm:text-sm focus:ring-indigo-500 focus:border-indigo-500" />
                                        </div>

                                        <!-- Total Supply -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Total Supply
                                                <span class="text-gray-400 cursor-help"
                                                    title="Initial amount of tokens to mint">?</span>
                                            </label>
                                            <input type="number" required
                                                class="block w-full rounded-md border border-gray-300 px-3 py-2 sm:text-sm focus:ring-indigo-500 focus:border-indigo-500" />
                                        </div>

                                        <!-- Lock Issuer Wallet -->
                                        <div class="">
                                            <label class="block text-sm font-medium text-gray-700">
                                                Lock Issuer Wallet
                                                <span class="text-gray-400 cursor-help"
                                                    title="Disables future minting. Irreversible.">?</span>
                                            </label>
                                            <button type="button" role="switch" :aria-checked="form.lockIssuer"
                                                @click="form.lockIssuer = !form.lockIssuer"
                                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-300"
                                                :class="form.lockIssuer ? 'bg-gradient' : 'bg-gray-300'">
                                                <span
                                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition duration-300"
                                                    :class="form.lockIssuer ? 'translate-x-6' : 'translate-x-1'" />
                                            </button>
                                        </div>

                                        <!-- Warning -->
                                        <div
                                            class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-2 rounded-md text-sm">
                                            ⚠ Ensure your wallet has at least 6 XLM to proceed.
                                        </div>

                                        <!-- Submit -->
                                        <div class="pt-2">
                                            <button type="submit" :disabled="isLoading"
                                                class="w-full rounded-full bg-gradient px-4 py-2 text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                <span v-if="isLoading">Generating...</span>
                                                <span v-else>Generate Token</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue'
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue'
import Logo from '@/assets/token-glade-logo.png'

// Props and Emit
const props = defineProps({
    open: Boolean,
    distributorWallet: String
})
const emit = defineEmits(['close'])

// State
const isLoading = ref(false)
const form = ref({
    assetCode: '',
    totalSupply: '',
    lockIssuer: false
})


// Close modal
function closeModal() {
    emit('close')
}

</script>
