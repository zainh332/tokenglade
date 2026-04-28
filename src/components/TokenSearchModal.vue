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
                            Enter the asset code of the token
                        </p>

                        <!-- asset code input -->
                        <input v-model="assetCodeInput" @keyup.enter="searchAssets" type="text" placeholder="TKG"
                            class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-cyan-400" />

                        <!-- dropdown -->
                        <div v-if="assets.length" class="mt-4 border rounded-xl max-h-[250px] overflow-y-auto">
                            <div v-for="asset in assets" :key="asset.asset_issuer" @click="selectAsset(asset)"
                                class="p-4 border-b last:border-b-0 cursor-pointer hover:bg-slate-50">
                                <div class="font-medium text-sm">
                                    {{ asset.asset_code }}
                                </div>

                                <div class="text-xs text-slate-500 break-all mt-1">
                                    {{ asset.asset_issuer }}
                                </div>

                                <div class="text-xs text-cyan-600 mt-2">
                                    Holders: {{ asset.accounts.authorized }}
                                </div>
                            </div>
                        </div>

                        <p class="text-red-500 text-sm mt-2 min-h-[20px]">
                            {{ error }}
                        </p>

                        <button :disabled="loading" @click="searchAssets" class="w-full mt-4 py-3 rounded-xl text-white font-medium
                            bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))]
                            hover:opacity-90 transition disabled:opacity-50">
                            {{ loading ? "Checking..." : "Search Token" }}
                        </button>

                    </DialogPanel>

                </TransitionChild>

            </div>

        </Dialog>
    </TransitionRoot>
</template>

<script setup>

import { ref, watch } from "vue"
import { useRouter } from "vue-router"
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild
} from "@headlessui/vue"

defineProps({
    modelValue: Boolean
})

const emit = defineEmits(["update:modelValue"])

const router = useRouter()

const assetCodeInput = ref("")

const error = ref("")
const loading = ref(false)
const assets = ref([])

let debounceTimeout = null

async function searchAssets() {

    error.value = ""

    const rawInput = assetCodeInput.value.trim()

    if (!rawInput) {
        assets.value = []
        return
    }

    loading.value = true

    try {
        const queries = [rawInput]

        if (rawInput !== rawInput.toUpperCase()) {
            queries.push(rawInput.toUpperCase())
        }

        let allRecords = []

        for (const code of queries) {
            const res = await fetch(
                `https://horizon.stellar.org/assets?asset_code=${code}&limit=200`
            )

            const data = await res.json()

            if (data._embedded?.records?.length) {
                allRecords = [...allRecords, ...data._embedded.records]
            }
        }

        if (!allRecords.length) {
            assets.value = []
            error.value = "No token found"
            loading.value = false
            return
        }

        /*
        remove duplicates using issuer + asset code
        because same asset may appear from both searches
        */

        const uniqueAssets = Object.values(
            allRecords.reduce((acc, asset) => {
                const key = `${asset.asset_code}_${asset.asset_issuer}`
                acc[key] = asset
                return acc
            }, {})
        )

        assets.value = uniqueAssets.sort((a, b) => {
            if (b.num_liquidity_pools !== a.num_liquidity_pools) {
                return b.num_liquidity_pools - a.num_liquidity_pools
            }

            return b.accounts.authorized - a.accounts.authorized
        })

        error.value = ""

    } catch (e) {
        error.value = "Failed to fetch assets"
        assets.value = []
    }

    loading.value = false
}

watch(assetCodeInput, (newValue) => {
    clearTimeout(debounceTimeout)

    if (!newValue.trim()) {
        assets.value = []
        error.value = ""
        return
    }

    debounceTimeout = setTimeout(() => {
        searchAssets()
    }, 500)
})

function selectAsset(asset) {
    router.push({
        path: "/token-insight",
        query: {
            asset_code: asset.asset_code,
            issuer: asset.asset_issuer
        }
    })

    assetCodeInput.value = ""
    assets.value = []
    emit("update:modelValue", false)
}
</script>