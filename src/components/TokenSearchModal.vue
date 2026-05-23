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

                        <DialogTitle class="mb-4 text-lg font-semibold">
                            Search Stellar Token
                        </DialogTitle>

                        <p class="mb-4 text-sm text-slate-500">
                            Enter the asset code of the token
                        </p>

                        <!-- asset code input -->
                        <input v-model="assetCodeInput" @keyup.enter="searchAssets" type="text" placeholder="TKG"
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400" />

                        <!-- dropdown -->
                        <div v-if="assets.length" class="mt-4 border rounded-xl max-h-[250px] overflow-y-auto">
                            <div v-for="asset in assets" :key="`${asset.asset_code}_${asset.asset_issuer}`"
                                @click="selectAsset(asset)"
                                class="p-4 border-b cursor-pointer last:border-b-0 hover:bg-slate-50">
                                <div class="flex items-center gap-1.5 font-medium text-sm">
                                    {{ asset.asset_code }}
                                    <img v-if="asset.is_verified" :src="verified" alt="Verified"
                                        class="flex-shrink-0 w-4 h-4" title="Verified Token" />
                                </div>

                                <div class="mt-1 text-xs break-all text-slate-500">
                                    {{ asset.asset_issuer }}
                                </div>

                                <div class="mt-2 text-xs text-cyan-600">
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
import axios from "axios"
import verified from "@/assets/verify.png"
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
let searchRequestId = 0

async function enrichVerificationStatus(assetList) {
    const issuers = assetList.map((asset) => asset.asset_issuer)

    if (!issuers.length) {
        return
    }

    try {
        const { data } = await axios.post("/api/token/check-verification", {
            issuers,
        })

        for (const asset of assetList) {
            asset.is_verified = data.verified?.[asset.asset_issuer] === true
        }
    } catch {
        for (const asset of assetList) {
            asset.is_verified = false
        }
    }
}

async function searchAssets() {
    error.value = ""

    const rawInput = assetCodeInput.value.trim()

    if (!rawInput) {
        assets.value = []
        return
    }

    const requestId = ++searchRequestId
    loading.value = true

    try {
        const queries = [rawInput]

        if (rawInput !== rawInput.toUpperCase()) {
            queries.push(rawInput.toUpperCase())
        }

        let allRecords = []

        for (const code of queries) {
            const res = await fetch(
                `https://horizon.stellar.org/assets?asset_code=${encodeURIComponent(code)}&limit=200`
            )

            const data = await res.json()

            if (data._embedded?.records?.length) {
                allRecords = [...allRecords, ...data._embedded.records]
            }
        }

        if (requestId !== searchRequestId) {
            return
        }

        if (!allRecords.length) {
            assets.value = []
            error.value = "No token found"
            return
        }

        const uniqueAssets = Object.values(
            allRecords.reduce((acc, asset) => {
                const key = `${asset.asset_code}_${asset.asset_issuer}`
                acc[key] = asset
                return acc
            }, {})
        )

        const sortedAssets = uniqueAssets.sort((a, b) => {
            if (b.num_liquidity_pools !== a.num_liquidity_pools) {
                return b.num_liquidity_pools - a.num_liquidity_pools
            }

            return b.accounts.authorized - a.accounts.authorized
        })

        await enrichVerificationStatus(sortedAssets)

        if (requestId !== searchRequestId) {
            return
        }

        assets.value = sortedAssets
        error.value = ""

    } catch (e) {
        if (requestId !== searchRequestId) {
            return
        }

        error.value = "Failed to fetch assets"
        assets.value = []
    } finally {
        if (requestId === searchRequestId) {
            loading.value = false
        }
    }
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