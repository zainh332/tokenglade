<template>
    <div>
        <Header />
        <div class="bg-gradient-to-b from-[#f7f9fc] to-[#eef2f7] min-h-screen pt-[8rem] pb-10">
            <div class="max-w-6xl mx-auto px-6 space-y-10">
                <section class="relative overflow-hidden rounded-2xl p-6 border shadow-sm bg-white">
                    <!-- TOP RIGHT -->
                    <div
                        class="absolute -top-24 -right-24 w-[420px] h-[420px] bg-gradient-to-br from-blue-200/60 via-purple-200/40 to-transparent rounded-full blur-3xl pointer-events-none">
                    </div>

                    <!-- CONTENT -->
                    <div class="relative z-1">

                        <!-- LOADING STATE -->
                        <template v-if="loading">

                            <!-- HEADER skeleton -->
                            <div class="flex items-center gap-4 mb-5">
                                <div class="w-16 h-16 rounded-full bg-slate-200 animate-pulse"></div>

                                <div class="space-y-2">
                                    <div class="h-7 w-48 bg-slate-200 rounded animate-pulse"></div>
                                    <div class="h-4 w-64 bg-slate-200 rounded animate-pulse"></div>
                                </div>
                            </div>

                            <!-- STATS skeleton -->
                            <div class="grid grid-cols-2 lg:grid-cols-4 rounded-xl overflow-hidden border">
                                <div v-for="i in 4" :key="i" class="p-4 bg-slate-50 border-r border-b lg:border-b-0">
                                    <div class="h-3 w-20 bg-slate-200 rounded animate-pulse mb-3"></div>
                                    <div class="h-7 w-24 bg-slate-200 rounded animate-pulse"></div>
                                </div>
                            </div>

                        </template>

                        <!-- REAL CONTENT -->
                        <template v-else>

                            <!-- your existing header -->
                            <div class="flex items-center gap-4 mb-5">
                                <img v-if="token.image" :src="token.image"
                                    class="w-16 h-16 rounded-full object-cover border" />

                                <div>
                                    <h1 class="text-3xl font-bold flex items-center gap-2">
                                        {{ token.name }}

                                        <img v-if="isVerified" :src="verified" class="w-4 h-4" />

                                        <span v-else @click="contactVerification"
                                            class="text-xs bg-amber-50 text-amber-600 px-2 py-0.5 rounded border border-amber-200 cursor-pointer hover:bg-amber-100">
                                            Get Verified
                                        </span>
                                    </h1>

                                    <p v-if="token?.conditions" class="text-slate-500 text-sm mt-1">
                                        {{ token.conditions }}
                                    </p>
                                </div>
                            </div>

                            <!-- STATS -->
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-0 rounded-xl overflow-hidden border ">
                                <div class="p-4 bg-slate-50 border-r border-b lg:border-b-0">
                                    <p class="text-xs text-slate-500 flex items-center gap-1.5">
                                        <Users class="w-3.5 h-3.5 text-slate-400" />
                                        Holders
                                    </p>
                                    <p class="text-2xl font-semibold mt-1">{{ token.holders }}</p>
                                    <!-- <span class="text-xs text-blue-500 mt-2 inline-block">Stellar</span> -->
                                </div>

                                <div class="p-4 bg-slate-50 border-r border-b lg:border-b-0">
                                    <p class="text-xs text-slate-500 flex items-center gap-1.5">
                                        <Coins class="w-3.5 h-3.5 text-slate-400" />
                                        Total Supply
                                    </p>
                                    <p class="text-2xl font-semibold mt-1">
                                        {{ formatNumber(token.total_supply) }}
                                    </p>
                                    <!-- <span class="text-xs text-blue-500 mt-2 inline-block">{{ token.asset_code }}</span> -->
                                </div>

                                <div class="p-4 bg-slate-50 border-r border-b lg:border-b-0">
                                    <p class="text-xs text-slate-500 flex items-center gap-1.5">
                                        <Waves class="w-3.5 h-3.5 text-slate-400" />
                                        Liquidity Pools
                                    </p>
                                    <p class="text-2xl font-semibold mt-1">
                                        {{ token.liquidity_pools || 0 }}
                                    </p>
                                </div>

                                <div class="p-4 bg-slate-50">
                                    <p class="text-xs text-slate-500 flex items-center gap-1.5">
                                        <Clock3 class="w-3.5 h-3.5 text-slate-400" />
                                        Last Updated
                                    </p>
                                    <p class="text-2xl font-semibold mt-1">
                                        {{ token.updated_at || "-" }}
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>

                <section
                    class="relative overflow-hidden rounded-2xl p-6 border shadow-sm bg-gradient-to-br from-white via-white to-blue-50/40">

                    <!-- TITLE -->
                    <h2 class="text-2xl font-bold text-slate-900 mb-3">
                        Project Info
                    </h2>

                    <div class="border-t pt-5 space-y-5">

                        <!-- LOADING STATE -->
                        <template v-if="loading">

                            <!-- metadata skeleton -->
                            <div class="flex flex-col lg:flex-row lg:justify-between gap-6 pb-5 border-b">

                                <div class="space-y-3 w-full lg:w-1/2">

                                    <div class="flex items-center gap-3">
                                        <div class="h-4 w-24 bg-slate-200 rounded animate-pulse"></div>
                                        <div class="h-4 w-32 bg-slate-200 rounded animate-pulse"></div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <div class="h-4 w-24 bg-slate-200 rounded animate-pulse"></div>
                                        <div class="h-4 w-28 bg-slate-200 rounded animate-pulse"></div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <div class="h-4 w-24 bg-slate-200 rounded animate-pulse"></div>
                                        <div class="h-6 w-48 bg-slate-200 rounded animate-pulse"></div>
                                    </div>

                                </div>

                                <!-- actions skeleton -->
                                <div class="flex items-center gap-2">
                                    <div class="h-10 w-24 bg-slate-200 rounded-lg animate-pulse"></div>
                                    <div class="h-10 w-10 bg-slate-200 rounded-lg animate-pulse"></div>
                                    <div class="h-10 w-10 bg-slate-200 rounded-lg animate-pulse"></div>
                                </div>

                            </div>

                            <!-- description skeleton -->
                            <div class="space-y-2">
                                <div class="h-4 w-full bg-slate-200 rounded animate-pulse"></div>
                                <div class="h-4 w-11/12 bg-slate-200 rounded animate-pulse"></div>
                                <div class="h-4 w-9/12 bg-slate-200 rounded animate-pulse"></div>
                            </div>

                        </template>

                        <!-- REAL CONTENT -->
                        <template v-else>

                            <!-- METADATA + ACTIONS -->
                            <div
                                class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 pb-5 border-b">

                                <div class="space-y-3">

                                    <div class="flex items-center gap-3 text-sm">
                                        <span class="text-slate-500 w-28">Asset Code</span>
                                        <span class="font-semibold text-slate-800">{{ token.asset_code }}</span>
                                    </div>

                                    <div class="flex items-center gap-3 text-sm">
                                        <span class="text-slate-500 w-28">Blockchain</span>
                                        <span class="font-semibold text-slate-800">Stellar</span>
                                    </div>

                                    <div class="flex items-center gap-3 text-sm">
                                        <span class="text-slate-500 w-28">Issuer</span>

                                        <div class="flex items-center gap-2">
                                            <span
                                                class="font-mono text-slate-700 bg-slate-50 px-3 py-1.5 rounded border">
                                                {{ shorten(token.issuer) }}
                                            </span>

                                            <button @click="copyIssuer" :class="[
                                                'px-3 py-1.5 text-xs rounded-md transition',
                                                copied
                                                    ? 'bg-green-500 text-white border-green-500'
                                                    : 'border hover:bg-slate-50'
                                            ]">
                                                {{ copied ? 'Copied' : 'Copy' }}
                                            </button>
                                        </div>
                                    </div>

                                </div>

                                <!-- RIGHT ACTIONS -->
                                <div class="flex items-center gap-2">

                                    <a v-if="token?.website" :href="token.website" target="_blank"
                                        class="px-4 h-10 rounded-lg bg-slate-100 border flex items-center gap-2 text-sm text-slate-700 hover:bg-slate-200 transition">
                                        <Globe class="w-4 h-4" />
                                        Website
                                    </a>

                                    <a v-if="token?.twitter" :href="token.twitter" target="_blank"
                                        class="w-10 h-10 rounded-lg bg-slate-100 border flex items-center justify-center hover:bg-slate-200 transition">
                                        <Twitter class="w-4 h-4 text-slate-600" />
                                    </a>

                                    <a v-if="token?.email" :href="`mailto:${token.email}`"
                                        class="w-10 h-10 rounded-lg bg-slate-100 border flex items-center justify-center hover:bg-slate-200 transition">
                                        <Mail class="w-4 h-4 text-slate-600" />
                                    </a>

                                </div>
                            </div>

                            <p class="text-slate-700 text-base leading-relaxed max-w-3xl">
                                {{ token.description || "No description available." }}
                            </p>

                        </template>
                    </div>

                </section>

                <section class="insight-card p-6">

                    <!-- TITLE -->
                    <h2 class="text-2xl font-bold text-slate-900 mb-3">
                        Security & Permissions
                    </h2>

                    <div class="border-t pt-5 space-y-5">

                        <!-- LOADING STATE -->
                        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <div v-for="i in 4" :key="i" class="rounded-xl border bg-slate-50 p-5">
                                <div class="flex items-start gap-3">

                                    <!-- icon skeleton -->
                                    <div class="w-5 h-5 rounded-full bg-slate-200 animate-pulse mt-1"></div>

                                    <div class="flex-1 space-y-2">
                                        <div class="h-5 w-40 bg-slate-200 rounded animate-pulse"></div>
                                        <div class="h-4 w-full bg-slate-200 rounded animate-pulse"></div>
                                        <div class="h-4 w-3/4 bg-slate-200 rounded animate-pulse"></div>
                                    </div>

                                </div>
                            </div>

                        </div>

                        <!-- REAL CONTENT -->
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <!-- ITEM -->
                            <div class="rounded-xl border bg-slate-50 p-5">
                                <div class="flex items-start gap-3">

                                    <ShieldCheck v-if="token.issuer_locked" class="w-5 h-5 text-emerald-600 mt-0.5" />
                                    <ShieldX v-else class="w-5 h-5 text-red-500 mt-0.5" />

                                    <div>
                                        <h3 class="font-semibold text-slate-900 text-lg">
                                            Issuer Immutable
                                        </h3>

                                        <p class="text-sm text-slate-600 mt-1">
                                            Issuer cannot change token permissions anymore.
                                        </p>
                                    </div>

                                </div>
                            </div>

                            <!-- ITEM -->
                            <div class="rounded-xl border bg-slate-50 p-5">
                                <div class="flex items-start gap-3">
                                    <ShieldCheck v-if="!token.auth_clawback_enabled"
                                        class="w-5 h-5 text-emerald-600 mt-0.5" />
                                    <ShieldX v-else class="w-5 h-5 text-red-500 mt-0.5" />
                                    <div>
                                        <h3 class="font-semibold text-slate-900 text-lg">
                                            Clawback Disabled
                                        </h3>
                                        <p class="text-sm text-slate-600 mt-1">
                                            Issuer cannot force-remove tokens from holders.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- ITEM -->
                            <div class="rounded-xl border bg-slate-50 p-5">
                                <div class="flex items-start gap-3">
                                    <ShieldCheck v-if="!token.auth_revocable" class="w-5 h-5 text-emerald-600 mt-0.5" />
                                    <ShieldX v-else class="w-5 h-5 text-red-500 mt-0.5" />
                                    <div>
                                        <h3 class="font-semibold text-slate-900 text-lg">
                                            Revocation Disabled
                                        </h3>
                                        <p class="text-sm text-slate-600 mt-1">
                                            Issuer cannot freeze user balances.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- ITEM -->
                            <div class="rounded-xl border bg-slate-50 p-5">
                                <div class="flex items-start gap-3">
                                    <ShieldCheck v-if="token.auth_required" class="w-5 h-5 text-amber-500 mt-0.5" />
                                    <ShieldX v-else class="w-5 h-5 text-slate-400 mt-0.5" />
                                    <div>
                                        <h3 class="font-semibold text-slate-900 text-lg">
                                            Authorization Required
                                        </h3>
                                        <p class="text-sm text-slate-600 mt-1">
                                            Wallets must be approved before holding this token.
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </section>

                <!-- ========================= -->
                <!-- Network + Technical -->
                <!-- ========================= -->
                <!-- ========================= -->
                <!-- Network + Technical -->
                <!-- ========================= -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- ================= LEFT SIDE ================= -->
                    <section class="insight-card p-6 lg:col-span-2">

                        <h2 class="text-2xl font-bold text-slate-900 mb-3">
                            Network Exposure
                        </h2>

                        <div class="border-t pt-5 space-y-5">

                            <!-- LOADING -->
                            <template v-if="loading">

                                <!-- metric skeleton -->
                                <div class="grid grid-cols-2 lg:grid-cols-4 border rounded-xl overflow-hidden mb-6">
                                    <div v-for="i in 4" :key="i"
                                        class="p-4 bg-slate-50 border-r border-b lg:border-b-0">
                                        <div class="h-3 w-24 bg-slate-200 rounded animate-pulse mb-2"></div>
                                        <div class="h-6 w-20 bg-slate-200 rounded animate-pulse"></div>
                                    </div>
                                </div>

                                <!-- table skeleton -->
                                <div class="border rounded-xl overflow-hidden">
                                    <div class="grid grid-cols-5 bg-slate-50 px-4 py-3 border-b">
                                        <div class="h-4 bg-slate-200 rounded animate-pulse w-12"></div>
                                        <div class="h-4 bg-slate-200 rounded animate-pulse w-8"></div>
                                        <div class="h-4 bg-slate-200 rounded animate-pulse w-10"></div>
                                        <div class="h-4 bg-slate-200 rounded animate-pulse w-14"></div>
                                        <div class="h-4 bg-slate-200 rounded animate-pulse w-10"></div>
                                    </div>

                                    <div class="divide-y">
                                        <div v-for="i in 5" :key="i" class="grid grid-cols-5 px-4 py-3">
                                            <div class="h-4 bg-slate-200 rounded animate-pulse w-20"></div>
                                            <div class="h-4 bg-slate-200 rounded animate-pulse w-20"></div>
                                            <div class="h-4 bg-slate-200 rounded animate-pulse w-14"></div>
                                            <div class="h-4 bg-slate-200 rounded animate-pulse w-16"></div>
                                            <div class="h-4 bg-slate-200 rounded animate-pulse w-12"></div>
                                        </div>
                                    </div>
                                </div>

                            </template>

                            <!-- REAL DATA -->
                            <template v-else>

                                <!-- TOP METRICS -->
                                <div class="grid grid-cols-2 lg:grid-cols-4 border rounded-xl overflow-hidden mb-6">

                                    <div class="p-4 bg-slate-50 border-r border-b lg:border-b-0">
                                        <p class="text-xs text-slate-500">Claimable Balances</p>
                                        <p class="text-1xl font-semibold mt-1">
                                            {{ formatNumber(token.num_claimable_balances) }}
                                        </p>
                                    </div>

                                    <div class="p-4 bg-slate-50 border-r border-b lg:border-b-0">
                                        <p class="text-xs text-slate-500">Connected Contracts</p>
                                        <p class="text-1xl font-semibold mt-1">
                                            {{ formatNumber(token.num_contracts) }}
                                        </p>
                                    </div>

                                    <div class="p-4 bg-slate-50 border-r border-b lg:border-b-0">
                                        <p class="text-xs text-slate-500">Liquidity Pools Amount</p>
                                        <p class="text-1xl font-semibold mt-1">
                                            {{ formatNumber(token.liquidity_pools_amount || 0) }}
                                        </p>
                                    </div>

                                    <div class="p-4 bg-slate-50">
                                        <p class="text-xs text-slate-500">Contracts Amount</p>
                                        <p class="text-1xl font-semibold mt-1">
                                            {{ formatNumber(token.contracts_amount || 0) }}
                                        </p>
                                    </div>

                                </div>

                                <!-- TRANSACTIONS -->
                                <div class="border rounded-xl overflow-hidden">

                                    <div class="grid grid-cols-5 bg-slate-50 text-sm text-slate-500 px-4 py-3 border-b">
                                        <span>Side</span>
                                        <span>Amount</span>
                                        <span>Price</span>
                                        <span>Value</span>
                                        <span>Time</span>
                                    </div>

                                    <div class="divide-y text-sm">
                                        <div v-for="(tx, i) in token.transactions || []" :key="i"
                                            class="grid grid-cols-5 px-4 py-3 hover:bg-slate-50 transition">

                                            <span
                                                :class="tx.side === 'buy' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                                                {{ tx.side.toUpperCase() }}
                                            </span>

                                            <span class="text-slate-700 font-medium">
                                                {{ formatPrice(tx.amount) }} {{ token.asset_code }}
                                            </span>

                                            <span class="text-slate-700 font-medium">
                                                {{ formatPrice(tx.price) }} XLM
                                            </span>

                                            <span class="text-slate-700 font-medium">
                                                {{ formatPrice(tx.value) }} XLM
                                            </span>

                                            <span class="text-slate-700 font-medium">
                                                {{ tx.time }}
                                            </span>

                                        </div>
                                    </div>

                                </div>

                            </template>
                        </div>
                    </section>

                    <!-- ================= RIGHT SIDE ================= -->
                    <section class="insight-card p-6">

                        <h2 class="text-2xl font-bold text-slate-900 mb-3">
                            Technical Details
                        </h2>

                        <div class="border-t pt-5 space-y-5">
                            <!-- LOADING -->
                            <div v-if="loading" class="divide-y">
                                <div v-for="i in 9" :key="i" class="py-3 flex justify-between">
                                    <div class="h-4 w-24 bg-slate-200 rounded animate-pulse"></div>
                                    <div class="h-4 w-20 bg-slate-200 rounded animate-pulse"></div>
                                </div>
                            </div>

                            <!-- REAL -->
                            <div v-else class="divide-y text-sm">

                                <div class="py-3 flex justify-between">
                                    <span class="text-slate-500">Asset Code</span>
                                    <span class="font-semibold">{{ token.asset_code }}</span>
                                </div>

                                <div class="py-3 flex justify-between">
                                    <span class="text-slate-500">Issuer Account</span>
                                    <span class="font-mono text-xs">{{ shorten(token.issuer) }}</span>
                                </div>

                                <!-- <div class="py-3 flex justify-between">
                                    <span class="text-slate-500">Decimals</span>
                                    <span class="font-semibold">{{ token.decimals }}</span>
                                </div> -->

                                <div class="py-3 flex justify-between">
                                    <span class="text-slate-500">Trustlines</span>
                                    <span class="font-semibold">{{ token.holders }}</span>
                                </div>

                                <div class="py-3 flex justify-between">
                                    <span class="text-slate-500">Created</span>
                                    <span class="font-semibold">{{ token.mint_date_human }}</span>
                                </div>

                                <div class="py-3 flex justify-between">
                                    <span class="text-slate-500">Supply</span>
                                    <span class="font-semibold">{{ formatNumber(token.total_supply) }}</span>
                                </div>

                                <div class="py-3 flex justify-between">
                                    <span class="text-slate-500">1h Trading Volume</span>
                                    <span class="font-semibold">{{ formatPrice(token.volume_1h) }} {{(token.asset_code)}}</span>
                                </div>

                            </div>
                        </div>

                    </section>

                </div>
            </div>
        </div>
        <Footer />
    </div>
</template>

<script setup>
import { reactive, onMounted, watch, ref, computed } from "vue"
import { useRoute } from "vue-router"
import axios from "axios"
import verified from "@/assets/verify.png";

import Header from "@/components/Header.vue"
import Footer from "@/components/Footer.vue"
const loading = ref(true)
const copied = ref(false)
const issuerInput = ref("")
const route = useRoute()
const verified_issuer = "GAM3PID2IOBTNCBMJXHIAS4EO3GQXAGRX4UB6HTQY2DUOVL3AQRB4UKQ"

import {
    Users,
    Coins,
    Waves,
    Clock3,
    Globe,
    Mail,
    Twitter,
    ShieldCheck,
    ShieldX
} from "lucide-vue-next";

const token = reactive({
    name: "",
    asset_code: "",
    liquidity_pools: "",
    issuer: "",
    image: null,
    description: "",
    project: {},

    holders: 0,
    mint_date_human: "-",
    updated_at: "-",

    decimals: null,
    conditions: null,
})

onMounted(() => {
    if (route.query.issuer) {
        issuerInput.value = route.query.issuer
        fetchToken()
    }
})

watch(
    () => route.query,
    (query) => {
        if (query.issuer) {
            issuerInput.value = query.issuer
            fetchToken()
        }
    },
    { immediate: true }
)

function shorten(str) {
    if (!str) return "-"
    return str.slice(0, 5) + "..." + str.slice(-4)
}

function formatNumber(value) {
    return new Intl.NumberFormat("en-US", {
        maximumFractionDigits: 0
    }).format(value || 0);
}

function copyIssuer() {
    if (!token.issuer) return

    const success = () => {
        copied.value = true

        setTimeout(() => {
            copied.value = false
        }, 2000)
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(token.issuer)
            .then(success)
            .catch(() => fallbackCopy(success))
    } else {
        fallbackCopy(success)
    }
}

function fallbackCopy(onSuccess) {
    const textarea = document.createElement("textarea")
    textarea.value = token.issuer
    textarea.style.position = "fixed"
    textarea.style.left = "-9999px"

    document.body.appendChild(textarea)
    textarea.focus()
    textarea.select()

    document.execCommand("copy")
    document.body.removeChild(textarea)

    onSuccess()
}

async function fetchToken() {

    if (!issuerInput.value) return

    loading.value = true

    try {
        const res = await axios.get("/api/token/show", {
            params: {
                issuer: issuerInput.value
            }
        })

        Object.assign(token, res.data)

    } catch (error) {
        console.error("Error fetching token data:", error)
    } finally {
        loading.value = false
    }
}

const isVerified = computed(() => {
    return token.issuer === verified_issuer
})

function contactVerification() {
    window.open("https://x.com/TokenGlade", "_blank")
}

function formatPrice(num) {
    if (!num) return "0";

    const n = Number(num);

    if (n < 1) {
        return n.toFixed(6);
    }

    return n.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
</script>