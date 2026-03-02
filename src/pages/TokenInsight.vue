<template>
    <div>
        <Header />
        <div class="bg-gradient-to-b from-[#f7f9fc] to-[#eef2f7] min-h-screen pt-[8rem] pb-20">
            <div class="max-w-6xl mx-auto px-6 space-y-10">
                <section class="relative overflow-hidden rounded-2xl p-6 border shadow-sm bg-white">
                    <!-- TOP RIGHT -->
                    <div
                        class="absolute -top-24 -right-24 w-[420px] h-[420px] bg-gradient-to-br from-blue-200/60 via-purple-200/40 to-transparent rounded-full blur-3xl pointer-events-none">
                    </div>

                    <!-- CONTENT -->
                    <div class="relative z-10">

                        <!-- TOP HEADER -->
                        <div class="flex items-center gap-4 mb-5">
                            <img v-if="token.image" :src="token.image"
                                class="w-16 h-16 rounded-full object-cover border" />
                            <div>
                                <h1 class="text-3xl font-bold flex items-center gap-2">
                                    {{ token.name }}
                                    <img :src="verified" class="w-4 h-4" />
                                </h1>

                                <p class="text-slate-500 text-sm mt-1">
                                    {{ token?.tagline || "Next generation AMM platform on Stellar" }}
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
                    </div>
                </section>

                <section
                    class="relative overflow-hidden rounded-2xl p-6 border shadow-sm bg-gradient-to-br from-white via-white to-blue-50/40">

                    <!-- TITLE -->
                    <h2 class="text-2xl font-bold text-slate-900 mb-4">
                        Project Info
                    </h2>

                    <div class="border-t pt-5 space-y-5">

                        <!-- METADATA + ACTIONS -->
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 pb-5 border-b">

                            <!-- LEFT INFO -->
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
                                        <span class="font-mono text-slate-700 bg-slate-50 px-3 py-1.5 rounded border">
                                            {{ shorten(token.issuer) }}
                                        </span>

                                        <button @click="navigator.clipboard.writeText(token.issuer)"
                                            class="px-3 py-1.5 text-xs border rounded-md hover:bg-slate-50 transition">
                                            Copy
                                        </button>
                                    </div>
                                </div>

                            </div>

                            <!-- RIGHT ACTIONS -->
                            <div class="flex items-center gap-2">

                                <a v-if="token?.website" :href="token.website" target="_blank" class="px-4 h-10 rounded-lg bg-slate-100 border
                           flex items-center gap-2 text-sm text-slate-700 hover:bg-slate-200 transition">
                                    <Globe class="w-4 h-4" />
                                    Website
                                </a>

                                <a v-if="token?.twitter" :href="token.twitter" target="_blank" class="w-10 h-10 rounded-lg bg-slate-100 border
                           flex items-center justify-center hover:bg-slate-200 transition">
                                    <Twitter class="w-4 h-4 text-slate-600" />
                                </a>

                                <a v-if="token?.email" :href="`mailto:${token.email}`" class="w-10 h-10 rounded-lg bg-slate-100 border
                           flex items-center justify-center hover:bg-slate-200 transition">
                                    <Mail class="w-4 h-4 text-slate-600" />
                                </a>

                            </div>
                        </div>

                        <!-- DESCRIPTION -->
                        <p class="text-slate-700 text-base leading-relaxed max-w-3xl">
                            {{ token.description || "No description available." }}
                        </p>

                    </div>

                </section>

                <section class="insight-card p-6">

                    <!-- TITLE -->
                    <h2 class="text-2xl font-bold text-slate-900 mb-5">
                        Security & Permissions
                    </h2>

                    <div class="border-t pt-5 grid grid-cols-1 md:grid-cols-2 gap-4">

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

                        <!-- WARNING ITEM -->
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
                </section>

                <!-- ========================= -->
                <!-- Network + Technical -->
                <!-- ========================= -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- LEFT SIDE -->
                    <section class="insight-card p-6 lg:col-span-2">

                        <h2 class="text-2xl font-bold text-slate-900 mb-6">
                            Network Exposure
                        </h2>

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

                        <!-- TRANSACTIONS TABLE -->
                        <div class="border rounded-xl overflow-hidden">

                            <!-- HEADER -->
                            <div class="grid grid-cols-5 bg-slate-50 text-sm text-slate-500 px-4 py-3 border-b">
                                <span>From</span>
                                <span>To</span>
                                <span>Type</span>
                                <span>Amount</span>
                                <span>Time</span>
                            </div>

                            <!-- ROWS -->
                            <div class="divide-y text-sm">
                                <div v-for="(tx, i) in token.transactions || []" :key="i"
                                    class="grid grid-cols-5 px-4 py-3 hover:bg-slate-50 transition">
                                    <span class="text-slate-700 font-medium">
                                        {{ shorten(tx.from) }}
                                    </span>

                                    <span class="text-slate-700 font-medium">
                                        → {{ shorten(tx.to) }}
                                    </span>

                                    <span class="capitalize text-slate-500">
                                        {{ tx.type }}
                                    </span>

                                    <span class="text-slate-700 font-medium">
                                        {{ formatNumber(tx.amount) }} {{ token.asset_code }}
                                    </span>

                                    <span class="text-slate-700 font-medium">
                                        {{ tx.time }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </section>

                    <!-- RIGHT SIDE -->
                    <section class="insight-card p-6">
                        <h2 class="text-2xl font-bold text-slate-900 mb-6">
                            Technical Details
                        </h2>

                        <div class="divide-y text-sm">

                            <div class="py-3 flex justify-between">
                                <span class="text-slate-500">Asset Code</span>
                                <span class="font-semibold">{{ token.asset_code }}</span>
                            </div>

                            <div class="py-3 flex justify-between">
                                <span class="text-slate-500">Issuer Account</span>
                                <span class="font-mono text-xs">
                                    {{ shorten(token.issuer) }}
                                </span>
                            </div>

                            <div class="py-3 flex justify-between">
                                <span class="text-slate-500">Decimals</span>
                                <span class="font-semibold">{{ token.decimals }}</span>
                            </div>

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
                                <span class="font-semibold">
                                    {{ formatNumber(token.total_supply) }}
                                </span>
                            </div>

                            <div class="py-3 flex justify-between">
                                <span class="text-slate-500">Auth Required</span>
                                <span class="font-semibold">
                                    {{ token.auth_required ? "Yes" : "No" }}
                                </span>
                            </div>

                            <div class="py-3 flex justify-between">
                                <span class="text-slate-500">Auth Revocable</span>
                                <span class="font-semibold">
                                    {{ token.auth_revocable ? "Yes" : "No" }}
                                </span>
                            </div>

                            <div class="py-3 flex justify-between">
                                <span class="text-slate-500">Clawback Enabled</span>
                                <span class="font-semibold">
                                    {{ token.auth_clawback_enabled ? "Yes" : "No" }}
                                </span>
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
import { reactive, onMounted } from "vue"
import axios from "axios"
import verified from "@/assets/verify.png";

import Header from "@/components/Header.vue"
import Footer from "@/components/Footer.vue"

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
})

onMounted(() => {
    fetchToken()
})

function shorten(str) {
    if (!str) return "-"
    return str.slice(0, 5) + "..." + str.slice(-4)
}

function formatNumber(value) {
    return new Intl.NumberFormat("en-US", {
        maximumFractionDigits: 0
    }).format(value || 0);
}

async function fetchToken() {
    try {
        const res = await axios.get("/api/token/show", {
            params: {
                // issuer: "GAM3PID2IOBTNCBMJXHIAS4EO3GQXAGRX4UB6HTQY2DUOVL3AQRB4UKQ"
                issuer: "GBNZILSTVQZ4R7IKQDGHYGY2QXL5QOFJYQMXPKWRRM5PAV7Y4M67AQUA"
            }
        })

        Object.assign(token, res.data)

    } catch (error) {
        console.error("Error fetching token data:", error)
    }
}
</script>