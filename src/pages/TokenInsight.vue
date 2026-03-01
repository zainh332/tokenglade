<template>
    <div>
        <Header />
        <div class="bg-gradient-to-b from-[#f7f9fc] to-[#eef2f7] min-h-screen pt-[8rem] pb-20">
            <div class="max-w-6xl mx-auto px-6 space-y-10">
                <section class="insight-card insight-highlight p-7">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- LEFT -->
                        <div class="flex items-start gap-4">

                            <div
                                class="w-16 h-16 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-500 font-semibold">
                                <img v-if="token.image" :src="token.image"
                                    class="w-16 h-16 rounded-full object-cover" />
                            </div>

                            <div>
                                <h1 class="text-3xl font-bold text-slate-900 flex items-center gap-2">
                                    {{ token.name }}
                                    <span class="text-blue-500 text-sm">✔</span>
                                </h1>
                                <div class="h-px bg-    slate-100 my-3 w-40"></div>

                                <p class="text-sm text-slate-500 mt-1">{{ token.asset_code }}</p>

                                <div class="mt-4 flex items-center gap-2 text-sm">
                                    <span class="text-slate-500">Issuer:</span>
                                    <span class="font-semibold text-slate-800">{{ shorten(token.issuer) }}</span>
                                    <button class="px-2 py-1 text-xs bg-slate-100 rounded hover:bg-slate-200">
                                        Copy
                                    </button>
                                </div>

                                <span
                                    class="inline-block mt-4 text-xs px-3 py-1 rounded-full bg-cyan-50 text-cyan-700 font-medium">
                                    Stellar
                                </span>
                            </div>

                        </div>

                        <!-- RIGHT -->
                        <div class="grid grid-cols-2 gap-4">
                            <StatCard title="Holders" :value="token.holders" />
                            <StatCard title="Total Supply" :value="formatNumber(token.total_supply)" />
                            <StatCard title="Mint Date" :value="token.mint_date" />
                            <StatCard title="Last Updated" :value="token.updated_at" />
                        </div>
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
                        <RiskCard title="Top 10 Holders" :value="token.top10_percentage !== null
                            ? token.top10_percentage + '%'
                            : 'Loading...'" type="yellow" />
                        <RiskCard title="Largest Wallet" value="34%" type="red" />
                        <RiskCard title="Total Holders" :value="String(token.holders)" type="green" />
                    </div>
                </section>

                <!-- ========================= -->
                <!-- Distribution -->
                <!-- ========================= -->
                <section class="insight-card p-7">
                    <h2 class="text-2xl font-bold text-slate-900 mb-6">
                        Holder Distribution
                    </h2>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-6">
                            <div class="h-56 flex items-center justify-center text-slate-400">
                                Chart Area (Pie / Bars)
                            </div>
                        </div>

                        <div class="space-y-3 text-sm text-slate-700">
                            <p>Top 1 Wallet: <span class="font-bold text-slate-900">34%</span></p>
                            <p>Top 5 Wallets: <span class="font-bold text-slate-900">56%</span></p>
                            <p>Top 10 Wallets: <span class="font-bold text-slate-900">68%</span></p>
                            <p>Others: <span class="font-bold text-slate-900">32%</span></p>

                            <p class="pt-4 text-slate-600">
                                Top 10 wallets control
                                <span class="font-bold text-slate-900">68%</span>
                                of total supply.
                            </p>
                        </div>
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
                        <DetailRow label="Creation Date" :value="token.mint_date" />
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

import Header from "@/components/Header.vue"
import Footer from "@/components/Footer.vue"

import StatCard from "@/components/insight/StatCard.vue"
import RiskCard from "@/components/insight/RiskCard.vue"
import DetailRow from "@/components/insight/DetailRow.vue"

const token = reactive({
    name: "",
    asset_code: "",
    issuer: "",
    image: null,
    description: "",
    project: {},

    holders: 0,
    supply: 0,
    mint_date: "-",
    updated_at: "-",

    largest_holder: null,
    top10_percentage: null,
    top10_holders: [],
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
    return new Intl.NumberFormat().format(value || 0)
}

async function fetchToken() {
    try {
        const res = await axios.get("/api/token/show", {
            params: {
                issuer: "GAM3PID2IOBTNCBMJXHIAS4EO3GQXAGRX4UB6HTQY2DUOVL3AQRB4UKQ"
            }
        })

        const data = res.data

        Object.assign(token, res.data)

        fetchAnalytics()

    } catch (error) {
        console.error("Error fetching token data:", error)
    }
}

async function fetchAnalytics() {
    const res = await axios.get("/api/token/holders", {
        params: {
            issuer: token.issuer,
            code: token.asset_code
        }
    })

    token.largest_holder = res.data.largest_holder
    token.top10_percentage = res.data.top10_percentage
    token.top10_holders = res.data.top10_holders
}
</script>

<style scoped>
.insight-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
}

.insight-highlight {
    border-top: 4px solid #22d3ee;
}
</style>