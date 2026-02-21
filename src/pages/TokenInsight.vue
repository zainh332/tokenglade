<template>
  <div>
    <Header />

    <div class="bg-[#f4f6fa] min-h-screen pt-[8rem] pb-16">
      <div class="max-w-6xl mx-auto px-6 space-y-10">

        <!-- ========================= -->
        <!-- Identity + Snapshot -->
        <!-- ========================= -->
        <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- LEFT -->
            <div class="flex items-start gap-4">
              <div class="w-14 h-14 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center">
                <span class="text-sm font-semibold text-gray-500">
                  {{ token.asset_code.slice(0,2) }}
                </span>
              </div>

              <div>
                <h1 class="text-2xl font-semibold text-gray-900 flex items-center gap-2">
                  {{ token.name }}
                  <span class="text-blue-500 text-sm">✔</span>
                </h1>

                <p class="text-sm text-gray-500">{{ token.asset_code }}</p>

                <div class="mt-3 flex items-center gap-2 text-sm text-gray-600">
                  <span>Issuer:</span>
                  <span class="font-medium">{{ shorten(token.issuer) }}</span>
                  <button class="text-xs px-2 py-1 bg-gray-100 rounded-md hover:bg-gray-200">
                    Copy
                  </button>
                </div>

                <span class="inline-block mt-3 text-xs px-3 py-1 rounded-full bg-cyan-50 text-cyan-700">
                  Stellar
                </span>
              </div>
            </div>

            <!-- RIGHT -->
            <div class="grid grid-cols-2 gap-3">
              <StatCard title="Holders" :value="token.holders" />
              <StatCard title="Total Supply" :value="formatNumber(token.supply)" />
              <StatCard title="Mint Date" :value="token.mint_date" />
              <StatCard title="Last Updated" :value="token.updated_at" />
            </div>

          </div>
        </section>

        <!-- ========================= -->
        <!-- Risk Overview -->
        <!-- ========================= -->
        <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-5">
            Token Control & Risk Overview
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <RiskCard title="Issuer Locked" value="Yes" type="green" />
            <RiskCard title="Minting Possible" value="No" type="green" />
            <RiskCard title="Top 10 Holders" value="68%" type="yellow" />
            <RiskCard title="Largest Wallet" value="34%" type="red" />
            <RiskCard title="Total Holders" :value="String(token.holders)" type="green" />
          </div>
        </section>

        <!-- ========================= -->
        <!-- Distribution -->
        <!-- ========================= -->
        <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-5">
            Holder Distribution
          </h2>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="bg-gray-50 rounded-xl p-6">
              <div class="h-48 flex items-center justify-center text-gray-400">
                Chart Area (Pie / Bars)
              </div>
            </div>

            <div class="space-y-3 text-sm">
              <p>Top 1 Wallet: <span class="font-semibold">34%</span></p>
              <p>Top 5 Wallets: <span class="font-semibold">56%</span></p>
              <p>Top 10 Wallets: <span class="font-semibold">68%</span></p>
              <p>Others: <span class="font-semibold">32%</span></p>

              <p class="text-gray-600 pt-3">
                Top 10 wallets control
                <span class="font-semibold text-gray-900">68%</span>
                of total supply.
              </p>
            </div>

          </div>
        </section>

        <!-- ========================= -->
        <!-- Activity -->
        <!-- ========================= -->
        <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-5">
            Recent Activity
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
            <StatCard title="Transfers (24h)" value="124" />
            <StatCard title="Transfers (7d)" value="982" />
            <StatCard title="Active Wallets" value="78" />
          </div>

          <div class="space-y-2">
            <div v-for="i in 10" :key="i"
              class="bg-gray-50 rounded-lg px-4 py-3 flex justify-between text-sm">
              <span class="text-gray-700">GABC...XYZ → GFFF...AAA</span>
              <span class="font-medium">12,000 TOKEN</span>
              <span class="text-gray-500">2m ago</span>
            </div>
          </div>
        </section>

        <!-- ========================= -->
        <!-- Details -->
        <!-- ========================= -->
        <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-5">
            Token Details
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-y-3 text-sm">
            <DetailRow label="Asset Code" :value="token.asset_code" />
            <DetailRow label="Issuer Account" :value="token.issuer" />
            <DetailRow label="Decimals" value="7" />
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
import { reactive } from "vue"

import Header from "@/components/Header.vue"
import Footer from "@/components/Footer.vue"

import StatCard from "@/components/insight/StatCard.vue"
import RiskCard from "@/components/insight/RiskCard.vue"
import DetailRow from "@/components/insight/DetailRow.vue"

const token = reactive({
  name: "Testing Token",
  asset_code: "TES",
  issuer: "GABCD12345XYZ987654321",
  holders: 1245,
  supply: 1000000000,
  mint_date: "2026-01-20",
  updated_at: "2 mins ago"
})

function shorten(str) {
  return str.slice(0,5) + "..." + str.slice(-4)
}

function formatNumber(value) {
  return new Intl.NumberFormat().format(value)
}
</script>