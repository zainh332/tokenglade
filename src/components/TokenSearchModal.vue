<template>
    <TransitionRoot appear :show="modelValue" as="template">
        <Dialog as="div" class="relative z-[9999]" @close="$emit('update:modelValue', false)">

            <!-- backdrop -->
            <TransitionChild enter="duration-200 ease-out" enter-from="opacity-0" enter-to="opacity-100"
                leave="duration-150 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" />
            </TransitionChild>

            <div class="fixed inset-0 flex items-center justify-center p-4">

                <TransitionChild enter="duration-200 ease-out" enter-from="opacity-0 scale-95"
                    enter-to="opacity-100 scale-100" leave="duration-150 ease-in" leave-from="opacity-100 scale-100"
                    leave-to="opacity-0 scale-95">

                    <DialogPanel class="w-[520px] max-w-[90vw] bg-white rounded-2xl shadow-xl p-6">

                        <DialogTitle class="text-lg font-semibold mb-4">
                            Search Stellar Token
                        </DialogTitle>

                        <p class="text-sm text-slate-500 mb-4">
                            Enter the issuer address of the token
                        </p>

                        <input v-model="issuerInput" @keyup.enter="openToken" type="text" placeholder="G..."
                            class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-cyan-400" />


                        <p class="text-red-500 text-sm mt-2 min-h-[20px]">
                            {{ error }}
                        </p>

                        <button :disabled="loading" @click="openToken" class="w-full mt-4 py-3 rounded-xl text-white font-medium
  bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))]
  hover:opacity-90 transition disabled:opacity-50">
                            {{ loading ? "Checking..." : "Open Token Insight" }}
                        </button>

                    </DialogPanel>

                </TransitionChild>

            </div>

        </Dialog>
    </TransitionRoot>
</template>

<script setup>

import { ref } from "vue"
import { useRouter } from "vue-router"
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild
} from "@headlessui/vue"

const props = defineProps({
    modelValue: Boolean
})

const emit = defineEmits(["update:modelValue"])

const router = useRouter()

const issuerInput = ref("")

const error = ref("")
const loading = ref(false)

function isValidStellarAddress(address) {
    return /^G[A-Z2-7]{55}$/.test(address)
}

async function openToken() {

  error.value = ""

  if (!issuerInput.value) {
    error.value = "Please enter an issuer address"
    return
  }

  if (!isValidStellarAddress(issuerInput.value)) {
    error.value = "Invalid Stellar address"
    return
  }

  loading.value = true

  try {

    const res = await fetch(
      `https://horizon.stellar.org/assets?asset_issuer=${issuerInput.value}`
    )

    const data = await res.json()

    if (!data._embedded.records.length) {
      error.value = "This address has not issued any tokens"
      loading.value = false
      return
    }

    router.push({
      path: "/token-insight",
      query: { issuer: issuerInput.value }
    })

    issuerInput.value = ""
    emit("update:modelValue", false)

  } catch (e) {
    error.value = "Issuer not found on Stellar network"
  }

  loading.value = false
}

</script>