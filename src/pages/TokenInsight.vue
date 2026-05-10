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

                                <div class="flex-1">
                                    <h1 class="text-2xl sm:text-3xl font-bold flex items-center gap-2 flex-wrap">
                                        {{ token.name }}

                                        <img v-if="isVerified" :src="verified" class="w-4 h-4" />

                                        <span v-else-if="isVerificationPending"
                                            class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded border border-blue-200">
                                            Verification Pending
                                        </span>

                                        <span v-else @click="verificationModal = true"
                                            class="text-xs bg-amber-50 text-amber-600 px-2 py-0.5 rounded border border-amber-200 cursor-pointer hover:bg-amber-100">
                                            Get Verified
                                        </span>
                                    </h1>

                                    <!-- voting section -->
                                    <div class="grid grid-cols-1 sm:flex gap-2 sm:gap-3 mt-3">

                                        <button @click="submitVote('trusted')" class="flex items-center justify-center sm:justify-start gap-2
                                        px-3 sm:px-4 py-2
                                        rounded-xl border
                                        bg-green-50 text-green-700
                                        text-sm sm:text-base
                                        hover:bg-green-100 transition">
                                            <span class="text-base sm:text-lg">✅</span>
                                            <span>Trusted</span>
                                            <span class="font-semibold">{{ votes.trusted }}</span>
                                        </button>

                                        <button @click="submitVote('suspicious')" class="flex items-center justify-center sm:justify-start gap-2
                                        px-3 sm:px-4 py-2
                                        rounded-xl border
                                        bg-yellow-50 text-yellow-700
                                        text-sm sm:text-base
                                        hover:bg-yellow-100 transition">
                                            <span class="text-base sm:text-lg">⚠️</span>
                                            <span>Suspicious</span>
                                            <span class="font-semibold">{{ votes.suspicious }}</span>
                                        </button>

                                        <button @click="submitVote('scam')" class="flex items-center justify-center sm:justify-start gap-2
                                        px-3 sm:px-4 py-2
                                        rounded-xl border
                                        bg-red-50 text-red-700
                                        text-sm sm:text-base
                                        hover:bg-red-100 transition">
                                            <span class="text-base sm:text-lg">❌</span>
                                            <span>Scam</span>
                                            <span class="font-semibold">{{ votes.scam }}</span>
                                        </button>

                                    </div>
                                </div>
                            </div>

                            <!-- STATS -->
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-0 rounded-xl overflow-hidden border ">
                                <div class="p-4 bg-slate-50 border-r border-b lg:border-b-0">
                                    <p class="text-xs text-slate-500 flex items-center gap-1.5">
                                        <Coins class="w-3.5 h-3.5 text-slate-400" />
                                        Price (USD)
                                    </p>
                                    <p class="text-sm sm:text-lg font-semibold mt-1 break-all">
                                        {{ formatPrice(token.usd_price) }}
                                    </p>
                                </div>

                                <div class="p-4 bg-slate-50 border-r border-b lg:border-b-0">
                                    <p class="text-xs text-slate-500 flex items-center gap-1.5">
                                        <TrendingUp class="w-3.5 h-3.5 text-slate-400" />
                                        XLM Price
                                    </p>
                                    <p class="text-sm sm:text-lg font-semibold mt-1 break-all">
                                        {{ token.xlm_price }}</p>
                                    <!-- <span class="text-xs text-blue-500 mt-2 inline-block">Stellar</span> -->
                                </div>

                                <div class="p-4 bg-slate-50 border-r border-b lg:border-b-0">
                                    <p class="text-xs text-slate-500 flex items-center gap-1.5">
                                        <Users class="w-3.5 h-3.5 text-slate-400" />
                                        Holders
                                    </p>
                                    <p class="text-sm sm:text-lg font-semibold mt-1 break-all">
                                        {{ token.holders || 0 }}
                                    </p>
                                </div>

                                <div class="p-4 bg-slate-50">
                                    <p class="text-xs text-slate-500 flex items-center gap-1.5">
                                        <Clock3 class="w-3.5 h-3.5 text-slate-400" />
                                        Last Updated
                                    </p>
                                    <p class="text-sm sm:text-lg font-semibold mt-1 break-all">
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

                <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- ================= LEFT: LIQUIDITY ================= -->
                    <div class="bg-white rounded-2xl border p-6 shadow-sm">

                        <!-- ALWAYS VISIBLE -->
                        <h2 class="text-2xl font-bold text-slate-900 mb-3">
                            Liquidity & Flow
                        </h2>

                        <div class="border-t pt-5 space-y-5">

                            <!-- LOADING -->
                            <template v-if="loading">
                                <div v-for="i in 3" :key="i">
                                    <div class="h-3 w-28 bg-slate-200 rounded animate-pulse mb-2"></div>
                                    <div class="h-5 w-40 bg-slate-200 rounded animate-pulse"></div>
                                </div>
                            </template>

                            <!-- REAL -->
                            <template v-else>
                                <div>
                                    <p class="text-xs text-slate-500 flex items-center gap-1.5 mb-1">
                                        <BarChart3 class="w-3.5 h-3.5 text-slate-400" />
                                        Total Trades Count
                                    </p>
                                    <p class="text-sm sm:text-lg font-semibold">
                                        {{ formatNumber(token.activity?.total_trades) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500 flex items-center gap-1.5 mb-1">
                                        <ArrowRightLeft class="w-3.5 h-3.5 text-slate-400" />
                                        Overall Payments Volume
                                    </p>
                                    <p class="text-sm sm:text-lg font-semibold">
                                        {{ formatNumber(token.activity?.payments_volume) }} {{ token.asset_code }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500 flex items-center gap-1.5 mb-1">
                                        <waves class="w-3.5 h-3.5 text-slate-400" />
                                        Liquidity Pools
                                    </p>
                                    <p class="text-sm sm:text-lg font-semibold">
                                        {{ formatNumber(token.liquidity_pools) }}
                                    </p>
                                </div>
                            </template>

                        </div>

                    </div>


                    <!-- ================= RIGHT: HEALTH ================= -->
                    <div class="bg-white rounded-2xl border p-6 shadow-sm lg:col-span-2">

                        <!-- ALWAYS VISIBLE -->
                        <h2 class="text-2xl font-bold text-slate-900 mb-3">
                            Token Health Score
                        </h2>

                        <div class="border-t pt-5"></div>

                        <div class="grid grid-cols-1 md:grid-cols-[1fr_160px] gap-8 items-center mt-5">

                            <!-- LEFT: BARS -->
                            <div class="space-y-5">

                                <!-- LOADING -->
                                <template v-if="loading">
                                    <div v-for="i in 6" :key="i">
                                        <div class="flex justify-between mb-2">
                                            <div class="h-3 w-20 bg-slate-200 rounded animate-pulse"></div>
                                            <div class="h-3 w-6 bg-slate-200 rounded animate-pulse"></div>
                                        </div>
                                        <div class="h-[6px] w-full bg-slate-200 rounded animate-pulse"></div>
                                    </div>
                                </template>

                                <!-- REAL -->
                                <template v-else>
                                    <div v-for="(value, key) in ratingBars" :key="key">

                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-sm text-slate-600 font-medium">
                                                {{ key }}
                                            </span>

                                            <span class="text-sm font-semibold text-slate-800">
                                                {{ value }}
                                            </span>
                                        </div>

                                        <div class="w-full h-[6px] bg-slate-200 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full bg-gradient-to-r from-cyan-400 to-blue-500 transition-all duration-500"
                                                :style="{ width: Math.max(value * 10, 4) + '%' }">
                                            </div>
                                        </div>

                                    </div>
                                </template>

                            </div>


                            <!-- RIGHT: SCORE -->
                            <div class="flex flex-col items-center justify-center">

                                <!-- LOADING -->
                                <template v-if="loading">
                                    <div class="w-24 h-24 rounded-full bg-slate-200 animate-pulse"></div>
                                    <div class="h-3 w-20 bg-slate-200 rounded animate-pulse mt-4"></div>
                                    <div class="h-3 w-16 bg-slate-200 rounded animate-pulse mt-2"></div>
                                </template>

                                <!-- REAL -->
                                <template v-else>
                                    <div class="relative w-28 h-28 flex items-center justify-center">

                                        <div class="absolute inset-0 rounded-full bg-slate-100"></div>

                                        <div
                                            class="relative w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center shadow">

                                            <span class="text-white text-2xl font-bold">
                                                {{ token.rating?.average?.toFixed(1) || 0 }}
                                            </span>

                                        </div>

                                    </div>

                                    <p class="text-sm text-slate-500 mt-3">
                                        Overall Score
                                    </p>

                                    <p class="text-sm font-semibold mt-1" :class="healthLabel.color">
                                        {{ healthLabel.text }}
                                    </p>
                                </template>

                            </div>

                        </div>

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

                                    <div
                                        class="hidden sm:grid grid-cols-5 bg-slate-50 text-sm text-slate-500 px-4 py-3 border-b">
                                        <span>Side</span>
                                        <span>Amount</span>
                                        <span>Price</span>
                                        <span>Value</span>
                                        <span>Time</span>
                                    </div>

                                    <div class="divide-y text-sm">
                                        <div v-for="(tx, i) in token.transactions || []" :key="i"
                                            class="flex flex-col gap-2 sm:grid sm:grid-cols-5 sm:gap-x-4 px-4 py-3 hover:bg-slate-50 transition">

                                            <!-- SIDE -->
                                            <div class="flex justify-between sm:block">
                                                <span class="text-xs text-slate-400 sm:hidden">Side</span>
                                                <span class="font-semibold"
                                                    :class="tx.side === 'buy' ? 'text-green-600' : 'text-red-600'">
                                                    {{ tx.side.toUpperCase() }}
                                                </span>
                                            </div>

                                            <!-- AMOUNT -->
                                            <div class="flex justify-between sm:block">
                                                <span class="text-xs text-slate-400 sm:hidden">Amount</span>
                                                <span class="text-slate-700 font-medium">
                                                    {{ formatPrice2Deci(tx.amount) }} {{ token.asset_code }}
                                                </span>
                                            </div>

                                            <!-- PRICE -->
                                            <div class="flex justify-between sm:block">
                                                <span class="text-xs text-slate-400 sm:hidden">Price</span>
                                                <span class="text-slate-700 font-medium">
                                                    {{ formatPrice(tx.price) }} XLM
                                                </span>
                                            </div>

                                            <!-- VALUE -->
                                            <div class="flex justify-between sm:block">
                                                <span class="text-xs text-slate-400 sm:hidden">Value</span>
                                                <span class="text-slate-700 font-medium">
                                                    {{ formatPrice2Deci(tx.value) }} XLM
                                                </span>
                                            </div>

                                            <!-- TIME -->
                                            <div class="flex justify-between sm:block">
                                                <span class="text-xs text-slate-400 sm:hidden">Time</span>
                                                <span class="text-slate-700 font-medium">
                                                    {{ tx.time }}
                                                </span>
                                            </div>

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

                                <div class="py-3 flex justify-between">
                                    <span class="text-slate-500">Trustlines</span>
                                    <span class="font-semibold">{{ token.trustlines }}</span>
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
                                    <span class="text-slate-500">1hr Trading Volume</span>
                                    <span class="font-semibold">{{ formatPrice(token.volume_1h) }}
                                        {{ (token.asset_code) }}</span>
                                </div>

                            </div>
                        </div>

                    </section>

                </div>
            </div>
        </div>
        <ConnectWalletModal v-model="ConnectWalletModals" :connected="isWalletConnected" :walletKey="walletKey" />
        <VerificationModal :open="verificationModal" :connected="isWalletConnected" :loading="verificationLoading"
            :payment-assets="verificationPaymentAssets" :selected-asset="selectedVerificationAsset" @select-asset="
                selectedVerificationAsset = $event
                " @close="verificationModal = false" @connect-wallet="
                    ConnectWalletModals = true" @pay="contactVerification" />
        <Footer />
    </div>
</template>

<script setup>
import { reactive, onMounted, watch, ref, computed } from "vue"
import { useRoute } from "vue-router"
import axios from "axios"
import verified from "@/assets/verify.png";
import { getCookie, signXdrWithWallet } from "../utils/utils.js";
import Swal from 'sweetalert2';
import ConnectWalletModal from '@/components/ConnectWallet.vue';
import VerificationModal from '@/components/VerificationModal.vue'

import Header from "@/components/Header.vue"
import Footer from "@/components/Footer.vue"
const loading = ref(true)
const copied = ref(false)
const issuerInput = ref("")
const route = useRoute()
const isWalletConnected = ref(false)
const walletKey = ref('')
const ConnectWalletModals = ref(false)
const verificationModal = ref(false)
const verificationLoading = ref(false)
const verificationFee = ref(185)
const isVerificationPending = computed(
    () => token.is_verification_pending === true
)

const verificationPaymentAssets = ref([])
const selectedVerificationAsset = ref(null)

import {
    Users,
    Coins,
    Waves,
    Clock3,
    Globe,
    Mail,
    Twitter,
    ShieldCheck,
    ShieldX,
    TrendingUp,
    ArrowRightLeft,
    BarChart3,
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

    conditions: null,
})

onMounted(async () => {

    walletKey.value =
        getCookie('public_key') || ''

    isWalletConnected.value = !!walletKey.value

    if (route.query.issuer) {
        issuerInput.value = route.query.issuer
        fetchToken()
    }

    await fetchVerificationAssets()
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

const votes = ref({
    trusted: 0,
    suspicious: 0,
    scam: 0
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

        votes.value = res.data.votes || {
            trusted: 0,
            suspicious: 0,
            scam: 0
        }

    } catch (error) {
        console.error("Error fetching token data:", error)
    } finally {
        loading.value = false
    }
}

const isVerified = computed(() => token.is_verified === true)

async function contactVerification() {

    try {
        verificationLoading.value = true
        const publicKey = getCookie('public_key')

        if (!publicKey) {
            verificationLoading.value = false
            ConnectWalletModals.value = true
            return
        }

        const res = await axios.post('/api/token/verification', {
            identifier: token.issuer,
            asset_code: token.asset_code,
            public_key: publicKey,
            verification_payment_asset_id: selectedVerificationAsset.value.id
        })

        if (res.data.status !== 'success') {
            verificationLoading.value = false
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: res.data.message || 'Failed to generate verification transaction'
            })

            return
        }

        const signedXdr = await signXdrWithWallet(
            localStorage.getItem("wallet_key"),
            res.data.xdr,
            'public'
        )


        const submitRes = await axios.post('/api/token/submit_verification_xdr', {
            signedXdr,
            verification_transaction_id:
                res.data.verification_transaction_id
        })

        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Verification submitted successfully'
        })

        verificationLoading.value = false
        verificationModal.value = false
        window.location.reload();

    } catch (e) {
        verificationLoading.value = false
        console.error(e)

        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: e?.response?.data?.message || 'Verification failed'
        })
    }
}

function formatPrice(num) {
    if (!num) return "0";

    const n = Number(num);

    return n.toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 6
    });
}

function formatPrice2Deci(num) {
    if (!num) return "0.00";

    const n = Number(num);

    return n.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

const healthLabel = computed(() => {
    const score = token.rating?.average || 0

    if (score >= 8) return { text: "Strong", color: "text-green-600" }
    if (score >= 5) return { text: "Moderate", color: "text-yellow-500" }
    return { text: "Weak", color: "text-red-500" }
})

const ratingBars = computed(() => {
    if (!token.rating) return {}

    return {
        Age: token.rating.age || 0,
        Activity: token.rating.activity || 0,
        Trustlines: token.rating.trustlines || 0,
        Liquidity: token.rating.liquidity || 0,
        Volume: token.rating.volume7d || 0,
        Interop: token.rating.interop || 0,
    }
})

async function submitVote(type) {
    try {

        const publicKey = getCookie('public_key')

        if (!publicKey) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Wallet not connected",
            });
            return
        }

        /*
        First check if wallet is active and has at least 4 XLM
        */

        const walletCheck = await axios.post("/api/wallet/check", {
            public_key: publicKey
        })

        if (!walletCheck.data.status) {
            Swal.fire({
                icon: "error",
                title: "Wallet Error",
                text: walletCheck.data.message || "Wallet is not active",
            });
            return
        }

        /*
        send vote to backend
        */

        const res = await axios.post("/api/token/vote", {
            asset_code: token.asset_code,
            issuer: token.issuer,
            vote_type: type,
            public_key: publicKey
        })

        votes.value = res.data.votes

    } catch (error) {
        console.error(error)

        Swal.fire({
            icon: "error",
            title: "Error",
            text: error?.response?.data?.message || "Failed to submit vote",
        });
    }
}

async function fetchVerificationAssets() {

    try {

        const res = await axios.get(
            '/api/token/verification-payment-assets'
        )

        verificationPaymentAssets.value =
            res.data.assets || []

        /*
        Default select first asset
        */

        if (
            verificationPaymentAssets.value.length > 0
        ) {

            selectedVerificationAsset.value =
                verificationPaymentAssets.value[0]
        }

    } catch (error) {

        console.error(error)

        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load verification assets'
        })
    }
}
</script>