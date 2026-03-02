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
                                <span class="text-xs text-blue-500 mt-2 inline-block">Stellar</span>
                            </div>

                            <div class="p-4 bg-slate-50 border-r border-b lg:border-b-0">
                                <p class="text-xs text-slate-500 flex items-center gap-1.5">
                                    <Coins class="w-3.5 h-3.5 text-slate-400" />
                                    Total Supply
                                </p>
                                <p class="text-2xl font-semibold mt-1">
                                    {{ formatNumber(token.total_supply) }}
                                </p>
                                <span class="text-xs text-blue-500 mt-2 inline-block">{{ token.asset_code }}</span>
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

                <!-- ========================= -->
                <!-- Risk Overview -->
                <!-- ========================= -->
                <section class="insight-card p-7">
                    <h2 class="text-2xl font-bold text-slate-900 mb-6">
                        Token Control & Risk Overview
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <RiskCard title="Issuer Locked" value="Yes" type="green" />
                        <RiskCard title="Minting Possible" value="No" type="green" />
                        <RiskCard title="Total Holders" :value="String(token.holders)" type="green" />
                    </div>
                </section>

                <!-- ========================= -->
                <!-- Activity -->
                <!-- ========================= -->
                <section class="insight-card p-7">
                    <h2 class="text-2xl font-bold text-slate-900 mb-6">
                        Recent Activity
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <StatCard title="Transfers (24h)" value="124" />
                        <StatCard title="Transfers (7d)" value="982" />
                        <StatCard title="Active Wallets" value="78" />
                    </div>

                    <div class="divide-y divide-slate-100">
                        <div v-for="i in 10" :key="i"
                            class="py-3 flex justify-between text-sm hover:bg-slate-50 px-3 rounded-lg transition">

                            <span class="text-slate-700">GABC...XYZ → GFFF...AAA</span>
                            <span class="font-semibold text-slate-900">12,000 TOKEN</span>
                            <span class="text-slate-500">2m ago</span>
                        </div>
                    </div>
                </section>

                <!-- ========================= -->
                <!-- Details -->
                <!-- ========================= -->
                <section class="insight-card p-7">
                    <h2 class="text-2xl font-bold text-slate-900 mb-6">
                        Token Details
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-3 text-sm">
                        <DetailRow label="Asset Code" :value="token.asset_code" />
                        <DetailRow label="Issuer Account" :value="token.issuer" />
                        <DetailRow label="Decimals" :value="token.decimals" />
                        <DetailRow label="Trustlines" value="1245" />
                        <DetailRow label="Creation Date" :value="token.mint_date_human" />
                        <DetailRow label="Burned Supply" value="0" />
                    </div>
                </section>
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

import StatCard from "@/components/insight/StatCard.vue"
import RiskCard from "@/components/insight/RiskCard.vue"
import DetailRow from "@/components/insight/DetailRow.vue"

import {
    Users,
    Coins,
    Waves,
    Clock3,
    Globe,
    Mail,
    Twitter
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