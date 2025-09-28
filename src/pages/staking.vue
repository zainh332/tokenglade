<template>
    <div>
        <Header />
        <div
            class="container-fluid mx-auto pt-[8rem] pb-[6rem] relative top-0 z-0 bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] bg-[length:200%_200%] animate-gradientMove">
            <div class="flex flex-col lg:flex-row items-center justify-center gap-12">
                <!-- Text Content -->
                <div class="text-center lg:text-left max-w-3xl">
                    <h1 class="text-[32px] sm:text-[48px] lg:text-[64px] font-normal leading-tight text-white">
                        Stake Your
                        <span class="block font-semibold">
                            TKG, Earn More
                        </span>
                    </h1>
                    <p class="text-[18px] sm:text-[20px] mt-4 text-white max-w-xl mx-auto lg:mx-0">
                        Put your tokens to work with TokenGlade’s staking module.
                        Secure, seamless, and built for long-term rewards — up to 18% APY.
                        The stronger you hold, the more you earn
                    </p>
                </div>

                <!-- Form -->
                <div class="px-[4vw] sm:px-6">
                    <div class="flex-shrink-0 w-full max-w-md lg:max-w-lg bg-white rounded-[25px] shadow-lg mx-auto">
                        <div class="bg-[#3A3A3A] text-white text-center py-5 rounded-t-[25px]">
                            <h2 class="card-header">
                                Stake with <span>TokenGlade</span>
                            </h2>
                        </div>

                        <form class="flex flex-col gap-4 p-6" @submit.prevent="onSubmit">
                            <!-- Current Balance (always visible) -->
                            <div>
                                <label for="current_balance" class="block text-sm font-medium text-gray-700">
                                    Current balance
                                </label>

                                <!-- Input with skeleton while loading -->
                                <div class="mt-1 relative">
                                    <input v-if="!loadingBalance" type="text" id="current_balance"
                                        name="current_balance" :value="tkgBalance"
                                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#3A3A3A]"
                                        placeholder="Current balance" readonly required />
                                    <div v-else class="w-full h-10 rounded-md relative overflow-hidden bg-gray-200"
                                        aria-busy="true">
                                        <div
                                            class="absolute inset-0 animate-pulse bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200">
                                        </div>
                                    </div>
                                </div>
                                <p v-if="loadingBalance" class="mt-2 text-xs text-gray-500">Fetching your TKG balance…
                                </p>
                            </div>

                            <!-- Range -->
                            <div class="relative w-full">
                                <div class="flex items-center justify-between mb-1">
                                    <label for="range_value" class="block text-sm font-medium text-gray-700">

                                    </label>
                                    <span class="text-xs text-gray-500">
                                        Min: <strong>1,500</strong> • Max: <strong>{{ formattedMaxStake }}</strong>
                                    </span>
                                </div>

                                <!-- Tooltip -->
                                <div class="absolute -top-[0] transform -translate-x-1/2 text-xs bg-[#43CDFF] text-white px-2 py-1 rounded-[5px] transition-all duration-200"
                                    :style="{ left: `calc(${percentage}% - 1px)` }">
                                    {{ rangeValue }}%
                                </div>

                                <!-- Range Input (0 → 100) -->
                                <input type="range" id="range_value" name="range_value" min="0" max="100"
                                    v-model="rangeValue" class="w-full h-2 rounded-lg appearance-none cursor-pointer"
                                    :style="{
                                        background:
                                            'linear-gradient(90deg, rgba(220,25,224,1), rgba(67,205,255,1), rgba(0,254,254,1))'
                                    }" />

                                <!-- Scale helper -->
                                <div class="flex justify-between text-[11px] text-gray-500 mt-1">
                                    <span>0%</span>
                                    <span>100%</span>
                                </div>
                            </div>

                            <!-- Selected stake amount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Stake amount
                                </label>
                                <input type="text" :value="selectedTokensFormatted"
                                    class="mt-1 w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#3A3A3A]"
                                    readonly />
                            </div>

                            <div class="mt-2 rounded-xl border bg-gray-50 p-3">
                                <!-- Projected tier / APY -->
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                    <div class="text-sm text-gray-600">
                                        Projected after stake:
                                        <strong>{{ fmtTKG(projectedTotal) }} TKG</strong>
                                    </div>
                                    <div class="text-sm">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :class="projected.tier === 4 ? 'bg-emerald-100 text-emerald-800'
                                                : projected.tier === 3 ? 'bg-teal-100 text-teal-800'
                                                    : projected.tier === 2 ? 'bg-blue-100 text-blue-800'
                                                        : projected.tier === 1 ? 'bg-violet-100 text-violet-800'
                                                            : 'bg-gray-100 text-gray-600'">
                                            Tier {{ projected.tier || '—' }} • {{ projected.apy.toFixed(2) }}% APY
                                        </span>
                                    </div>
                                </div>

                                <!-- Reward chips -->
                                <div class="grid grid-cols-3 gap-2 text-center">
                                    <div class="rounded-lg bg-white border p-2">
                                        <div class="text-[11px] text-gray-500">Est. Daily</div>
                                        <div class="text-sm font-semibold">{{ fmtTKG(estDaily) }} TKG</div>
                                    </div>
                                    <div class="rounded-lg bg-white border p-2">
                                        <div class="text-[11px] text-gray-500">Est. Monthly</div>
                                        <div class="text-sm font-semibold">{{ fmtTKG(estMonthly) }} TKG</div>
                                    </div>
                                    <div class="rounded-lg bg-white border p-2">
                                        <div class="text-[11px] text-gray-500">Est. Yearly</div>
                                        <div class="text-sm font-semibold">{{ fmtTKG(estYearly) }} TKG</div>
                                    </div>
                                </div>

                                <!-- Hint when below threshold -->
                                <p v-if="projected.tier === 0" class="mt-2 text-xs text-amber-700">
                                    Stake at least <strong>1,500 TKG</strong> to start earning rewards.
                                </p>
                            </div>

                            <button type="submit"
                                class="w-full text-white py-2 rounded-[20px] hover:opacity-90 transition duration-300 bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] bg-[length:200%_200%] animate-gradientMove"
                                :disabled="stakeLoading || !hasMinBalance" :aria-busy="stakeLoading">
                                <span v-if="stakeLoading">Staking…</span>
                                <span v-else>Stake</span>
                            </button>
                            <!-- </template> -->

                            <!-- <template v-else-if="!loadingBalance">
                            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4">
                                <p class="text-sm text-amber-800">
                                    Your TKG balance is less than <strong>1,500</strong>. You need at least 1,500 TKG
                                    to start staking.
                                </p>
                                <a href="https://lobstr.co/" target="_blank" rel="noopener"
                                    class="mt-3 inline-block underline text-blue-600 hover:text-blue-800">
                                    Buy TKG on LOBSTR
                                </a>
                            </div>
                        </template> -->
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <section class="container mx-auto mt-12">
            <div class="max-w-[80%] mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">

                <!-- ===== Live Stats (Light + Gradient Accents) ===== -->
                <div
                    class="rounded-2xl bg-white/80 backdrop-blur border border-slate-200 shadow-[0_12px_40px_rgba(2,6,23,0.06)]">
                    <!-- gradient hairline -->
                    <div class="h-1.5 w-full rounded-t-2xl bg-gradient-to-r from-fuchsia-500 via-sky-400 to-cyan-400">
                    </div>

                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-[18px] font-semibold text-slate-800">Live Staking Stats</h3>
                            <span class="text-xs text-slate-500">Updated {{ lastUpdatedAgo }}</span>
                        </div>

                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <!-- TILE -->
                            <div
                                class="rounded-2xl p-[1px] bg-gradient-to-r from-fuchsia-400/60 via-sky-400/60 to-cyan-400/60">
                                <div class="rounded-2xl bg-white/95 p-4">
                                    <div class="flex items-center gap-3">
                                        <!-- icon -->
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full
                         bg-gradient-to-br from-fuchsia-500 to-sky-400 text-white">
                                            <!-- coin -->
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M12 6a9 4 0 100 12 9 4 0 000-12zM3 12a9 4 0 0018 0" />
                                            </svg>
                                        </span>

                                        <div class="min-w-0">
                                            <div class="text-[12px] uppercase tracking-wide text-slate-500">Total TKG
                                                Staked
                                            </div>
                                            <div class="mt-0.5 font-semibold text-slate-900 text-xl sm:text-2xl"
                                                style="font-variant-numeric: tabular-nums;">
                                                <span v-if="totalStakedTKG === null">Loading...</span>
                                                <span v-else>{{ totalStakedFormatted }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TILE -->
                            <div
                                class="rounded-2xl p-[1px] bg-gradient-to-r from-fuchsia-400/60 via-sky-400/60 to-cyan-400/60">
                                <div class="rounded-2xl bg-white/95 p-4">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full
                         bg-gradient-to-br from-sky-500 to-cyan-400 text-white">
                                            <!-- users -->
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M16 14a4 4 0 10-8 0m12 4a6 6 0 00-12 0M12 8a4 4 0 110-8 4 4 0 010 8z" />
                                            </svg>
                                        </span>

                                        <div class="min-w-0">
                                            <div class="text-[12px] uppercase tracking-wide text-slate-500">Active
                                                Stakers</div>
                                            <div class="mt-0.5 font-semibold text-slate-900 text-xl sm:text-2xl"
                                                style="font-variant-numeric: tabular-nums;">
                                                <span v-if="activeStakers === null">Loading...</span>
                                                <span v-else>{{ activeStakersFormatted }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Rewards Paid 24h TKG -->
                            <div
                                class="rounded-2xl p-[1px] bg-gradient-to-r from-fuchsia-400/60 via-sky-400/60 to-cyan-400/60">
                                <div class="rounded-2xl bg-white/95 p-4">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full
                   bg-gradient-to-br from-cyan-500 to-fuchsia-500 text-white">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M20 12H4m0 0v7a2 2 0 002 2h12a2 2 0 002-2v-7M4 12l1.5-6H18.5L20 12M9 6V4a2 2 0 114 0v2" />
                                            </svg>
                                        </span>

                                        <div class="min-w-0 w-full">
                                            <div class="text-[12px] uppercase tracking-wide text-slate-500">
                                                Rewards Paid (24h)
                                            </div>

                                            <!-- value row: number, unit never wraps -->
                                            <div class="mt-0.5 flex items-baseline gap-1 sm:gap-1.5 text-slate-900">
                                                <div class="mt-0.5 font-semibold text-slate-900 text-xl sm:text-2xl"
                                                    style="font-variant-numeric: tabular-nums;">
                                                    <span v-if="rewards24hFormatted === null">Loading...</span>
                                                    <span v-else>{{ rewards24hFormatted }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Total Payouts -->
                            <div
                                class="rounded-2xl p-[1px] bg-gradient-to-r from-fuchsia-400/60 via-sky-400/60 to-cyan-400/60">
                                <div class="rounded-2xl bg-white/95 p-4">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full
                   bg-gradient-to-br from-fuchsia-500 to-cyan-500 text-white">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M9 7h6M9 11h6M5 3h14v18l-3-2-4 2-4-2-3 2V3z" />
                                            </svg>
                                        </span>

                                        <div class="min-w-0 w-full">
                                            <div class="text-[12px] uppercase tracking-wide text-slate-500">Total
                                                Payouts</div>

                                            <div class="mt-0.5 flex items-baseline gap-1 sm:gap-1.5 text-slate-900">
                                                <div class="mt-0.5 font-semibold text-slate-900 text-xl sm:text-2xl"
                                                    style="font-variant-numeric: tabular-nums;">
                                                    <span v-if="totalPayoutsFormatted === null">Loading...</span>
                                                    <span v-else>{{ totalPayoutsFormatted }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /Total Payouts -->
                        </div>
                    </div>
                </div>

                <!-- ===== APY Tiers (Light Table, Soft Borders) ===== -->
                <div
                    class="rounded-2xl bg-white/95 backdrop-blur border border-slate-200 shadow-[0_8px_30px_rgba(0,0,0,0.06)] overflow-hidden">
                    <div class="h-1.5 w-full rounded-t-2xl bg-gradient-to-r from-fuchsia-500 via-sky-400 to-cyan-400">
                    </div>

                    <div class="px-6 py-5">
                        <h3 class="text-[18px] font-semibold text-slate-800">APY Tiers</h3>
                        <p class="text-sm text-slate-500 mt-1">
                            Rewards are calculated daily based on your tier.
                        </p>
                    </div>

                    <div class="px-6 pb-6">
                        <div class="overflow-hidden rounded-xl border border-slate-200">
                            <table class="min-w-full text-left">
                                <thead class="bg-slate-50/70 text-slate-600">
                                    <tr class="text-sm">
                                        <th class="py-3 px-4 font-medium">Tier</th>
                                        <th class="py-3 px-4 font-medium">Amount Staked (TKG)</th>
                                        <th class="py-3 px-4 font-medium">APY</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-800">
                                    <tr v-for="tier in apyTiers" :key="tier.tier" class="bg-white hover:bg-slate-50/60">
                                        <td class="py-3 px-4 font-semibold">Tier {{ tier.tier }}</td>
                                        <td class="py-3 px-4">{{ tier.range }}</td>
                                        <td class="py-3 px-4">
                                            <span
                                                class="inline-flex items-center rounded-full bg-gradient-to-r from-fuchsia-50 via-sky-50 to-cyan-50 px-2.5 py-0.5 text-sm font-medium text-slate-800 border border-slate-200">
                                                {{ tier.apy }}%
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </section>


        <!-- Your Staking History -->
        <section v-if="hasPositions" id="your-stakes" class="container mx-auto mt-16 mb-10">
            <div class="container mx-auto pt-20">
                <h2 class="text-2xl font-semibold text-center mb-6">Your Staking History</h2>

                <div class="w-full max-w-[80%] mx-auto bg-white rounded-xl shadow-md overflow-hidden">
                    <table class="min-w-full border-collapse">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-3 px-4 text-right">Amount</th>
                                <th class="py-3 px-4 text-center">APY</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center">Rewards</th>
                                <th class="py-3 px-4 text-center">Next Reward</th>
                                <th class="py-3 px-4 text-center">Transaction</th>
                                <th class="py-3 px-4 text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="pos in positions" :key="pos.id" class="border-b">

                                <td class="py-3 px-4 text-right">
                                    {{ formatAmount(pos.amount) }} TKG
                                </td>

                                <td class="py-3 px-4 text-center">
                                    {{ Number(pos.apy).toFixed(2) }}%
                                </td>

                                <td class="py-3 px-4 text-center">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="statusClass(pos.status_id)" :title="pos.status || '—'">
                                        {{ pos.status || '—' }}
                                    </span>
                                </td>

                                <td class="py-3 px-4 text-center">
                                    <div class="text-sm">
                                        <div class="font-medium">{{ formatAmount(pos.total_reward || 0) }} TKG</div>
                                        <div class="text-gray-500 text-xs">{{ pos.rewards_count || 0 }} payout(s)</div>
                                    </div>
                                </td>

                                <td class="py-3 px-4 text-center">
                                    <div v-if="Number(pos.apy) > 0 && !isEnded(pos)" class="text-sm">
                                        <div class="font-medium">{{ formatAmount(dailyReward(pos)) }} TKG</div>
                                        <div class="text-gray-500 text-xs">{{ etaString(nextRewardAt(pos)) }}</div>
                                    </div>
                                    <span v-else>—</span>
                                </td>

                                <td class="py-3 px-4 text-center">
                                    <a v-if="pos.transaction" :href="txUrl(pos.transaction)" target="_blank"
                                        rel="noopener"
                                        class="inline-block px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 font-mono text-xs hover:bg-gray-200 transition"
                                        :title="pos.transaction">
                                        {{ shortMiddle(pos.transaction, 6, 6) }}
                                    </a>
                                    <span v-else>—</span>
                                </td>

                                <td class="py-3 px-4 text-center">
                                    <button
                                        class="inline-flex items-center rounded-xl px-3 py-1.5 text-sm font-medium text-white bg-rose-500 hover:bg-rose-600 disabled:opacity-40 disabled:cursor-not-allowed"
                                        :disabled="!canUnstake(pos) || !!unstaking[pos.id]"
                                        :title="canUnstake(pos) ? 'Unstake' : ('Unlocks on ' + formatDate(pos.unlock_at))"
                                        @click="unstake(pos)">
                                        <svg v-if="unstaking[pos.id]" class="animate-spin h-4 w-4 mr-2"
                                            viewBox="0 0 24 24" fill="none">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                        </svg>
                                        Unstake
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <div class="container mx-auto pt-20">
            <div class="mx-auto">
                <div class="table-section pb-10">
                    <h1>
                        Latest Staking Reward <br /> Transactions
                    </h1>
                </div>

                <div class="w-full max-w-[80%] mx-auto bg-white rounded-xl shadow-md overflow-hidden">
                    <!-- Table -->
                    <div class="overflow-x-auto overscroll-x-contain touch-pan-x -mx-4 sm:mx-0">
                        <table class="min-w-max w-[720px] sm:w-full border-collapse">
                            <thead class="bg-[#43CDFF] text-white">
                                <tr>
                                    <th class="py-4 px-4 text-center">Wallet Address</th>
                                    <th class="py-4 px-4 text-center">Reward</th>
                                    <th class="py-4 px-4 text-center">Transactions</th>
                                    <th class="py-4 px-4 text-center">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="pageRows.length === 0">
                                    <td colspan="4" class="py-6 text-center text-gray-500">Loading rewards</td>
                                </tr>

                                <tr v-for="(row, index) in pageRows" :key="index"
                                    class="bg-white border-b border-[#EBEBEB]">
                                    <td class="py-4 px-4 text-dark text-center">
                                        <span :title="row.wallet_address">{{ shortMiddle(row.wallet_address, 6, 6)
                                        }}</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span
                                            class="inline-block w-full px-4 py-1 text-center text-sm font-medium text-dark bg-[#DBFEF0] rounded-full">
                                            {{ row.reward }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-dark text-center">
                                        <a v-if="row.transaction" :href="txUrl(row.transaction)" target="_blank"
                                            rel="noopener noreferrer" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5
           rounded-full border border-gray-200 bg-gray-100 text-gray-700
           hover:bg-gray-200 hover:border-gray-300
           focus:outline-none focus:ring-2 focus:ring-gray-300/60 transition"
                                            :title="`View transaction ${row.transaction} on the explorer`"
                                            aria-label="View on Explorer">
                                            <!-- icon -->
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                                fill="currentColor" aria-hidden="true">
                                                <path
                                                    d="M12.5 3H17v4.5a1 1 0 11-2 0V6.414l-6.793 6.793a1 1 0 01-1.414-1.414L13.586 5H12.5a1 1 0 110-2z" />
                                                <path
                                                    d="M5 5h4a1 1 0 110 2H6v7h7v-3a1 1 0 112 0v4a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                                            </svg>
                                            <span class="text-sm font-medium">View on Explorer</span>
                                            <span class="sr-only"> Tx: {{ row.transaction }}</span>
                                        </a>
                                        <span v-else>—</span>
                                    </td>

                                    <td class="py-4 px-4 text-dark text-center">
                                        {{ fmtDate(row.at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-between items-center px-4 py-3">
                        <div class="text-sm text-gray-600">
                            Showing {{ paginatedData.length ? (startIndex + 1) : 0 }}–{{ endIndex }} of {{
                                paginatedData.length
                            }}
                        </div>
                        <div class="flex gap-2">
                            <button @click="prevPage" :disabled="currentPage === 1"
                                class="px-3 py-1 text-sm border rounded-lg disabled:opacity-50">Prev</button>
                            <button @click="nextPage" :disabled="currentPage === totalPages"
                                class="px-3 py-1 text-sm border rounded-lg disabled:opacity-50">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="container mx-auto mt-16 py-10 ">
            <div class="w-full max-w-[80%] mx-auto bg-white rounded-2xl shadow-md overflow-hidden text-center">
                <h2 class="text-3xl font-bold mb-2 mt-8">How Staking Works</h2>
                <p class="text-sm text-gray-600 mb-10">Earn rewards every 24 hours by staking $TKG</p>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-10 max-w-6xl mx-auto px-6 md:px-0 pb-10">
                    <!-- Step 1: Connect Wallet -->
                    <div class="flex flex-col items-center">
                        <div class="bg-green-100 p-4 rounded-full mb-4 animate-pulseSoft">
                            <img :src="Wallet" alt="Connect Wallet" class="w-12 h-12" />
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Connect Wallet</h3>
                        <p class="text-dark text-sm">
                            Connect your Stellar wallet to TokenGlade<br />
                        </p>
                    </div>

                    <!-- Step 2: Start Staking -->
                    <div class="flex flex-col items-center">
                        <div class="bg-blue-100 p-4 rounded-full mb-4 animate-pulseSoft">
                            <img :src="Coin" alt="Start Staking" class="w-12 h-12" />
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Start Staking</h3>
                        <p class="text-dark text-sm">
                            Choose the amount of <strong>TKG</strong> to stake and confirm<br />
                        </p>
                    </div>

                    <!-- Step 3: Rewards Every 24h -->
                    <div class="flex flex-col items-center">
                        <div class="bg-yellow-100 p-4 rounded-full mb-4 animate-pulseSoft">
                            <img :src="reward" alt="Rewards" class="w-12 h-12" />
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Earn Rewards (24h)</h3>
                        <p class="text-dark text-sm">
                            Rewards will be distributed every <strong>24 hours</strong>
                        </p>
                    </div>

                    <!-- Step 4: Unstake Anytime -->
                    <div class="flex flex-col items-center">
                        <div class="bg-rose-100 p-4 rounded-full mb-4 animate-pulseSoft">
                            <img :src="stop" alt="Unstake Anytime" class="w-12 h-12" />
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Unstake Anytime</h3>
                        <p class="text-dark text-sm">
                            Stop staking whenever you like<br />
                        </p>
                    </div>
                </div>
            </div>
        </section>


        <section class="container mx-auto mt-16">
            <div class="w-full max-w-[80%] mx-auto bg-white rounded-2xl shadow-md overflow-hidden">
                <div class="px-6 py-5 border-b">
                    <h3 class="text-xl font-semibold">Frequently Asked Questions</h3>
                </div>

                <div class="divide-y">
                    <div v-for="(q, idx) in faq" :key="idx" class="group">
                        <summary
                            class="flex items-center justify-between cursor-pointer px-6 py-4 text-dark select-none"
                            @click="toggleFaq(idx)">
                            <span class="font-medium">{{ q.q }}</span>
                            <span class="ml-4 h-4 w-4 text-sky-500 transition-transform duration-300"
                                :class="{ 'rotate-180': openIndex === idx }">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2" class="h-full w-full">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </summary>

                        <div v-show="openIndex === idx" class="px-6 pb-5 text-gray-700 leading-relaxed">
                            <p v-html="q.a"></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <Footer />
    </div>
</template>

<script setup lang="ts">
import Header from "@/components/Header.vue";
import Footer from "@/components/Footer.vue";
import { ref, computed, onMounted, watch } from "vue";
import { checkTkgBalance, getCookie, updateLoader, apiHeaders, shortMiddle, statusClass } from "../utils/utils.js";
import Swal from "sweetalert2";
import axios from "axios";
import { signTransaction } from "@stellar/freighter-api";
import Coin from "@/assets/coin.png";
import reward from "@/assets/reward.png";
import Wallet from "@/assets/wallet.jpg";
import stop from "@/assets/stop.png";

// ---------------------
// Slider state (0 → 100)
// ---------------------
const rangeValue = ref(0); // start at 0%
const min = 0;
const max = 100;

// Tooltip percentage for slider
const percentage = computed(() => {
    return ((rangeValue.value - min) / (max - min)) * 100;
});

// --- User Staking tab state ---
const positions = ref([]);
const hasPositions = computed(() => positions.value.length > 0);

// ---------------------
// TKG Balance & staking bounds
// ---------------------
const tkgBalance = ref(0);
const loadingBalance = ref(true);
const publicKey = getCookie("public_key");
const hasMinBalance = computed(() => Number(tkgBalance.value) >= 1500);
const stakeLoading = ref(false);

// Staking min/max amounts
const stakeMin = 1500;
const stakeMax = computed(() => publicKey ? Number(tkgBalance.value) : 1_000_000);

// Map slider percent → token amount [1500 → tkgBalance]
const selectedTokens = computed(() => {
    const max = Math.max(stakeMin, stakeMax.value || 0);
    const fraction = Number(rangeValue.value) / 100; // 0..1
    const mapped = Math.round(stakeMin + (max - stakeMin) * fraction);
    return isNaN(mapped) ? stakeMin : mapped;
});

// Formatting helpers
const formattedMaxStake = computed(() => Number(stakeMax.value || 0).toLocaleString());
const selectedTokensFormatted = computed(() => `${selectedTokens.value.toLocaleString()} TKG`);

// ---------------------
// Table & Pagination
// ---------------------
const currentPage = ref(1);
const itemsPerPage = 5;
const paginatedData = ref([]);

const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage);
const endIndex = computed(() => Math.min(startIndex.value + itemsPerPage, paginatedData.value.length));
const totalPages = computed(() => Math.max(1, Math.ceil(paginatedData.value.length / itemsPerPage)));

const pageRows = computed(() => paginatedData.value.slice(startIndex.value, endIndex.value));

const prevPage = () => {
    if (currentPage.value > 1) currentPage.value--;
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++;
};

const network = 'public';
const isTestnet = network === 'testnet';
const explorerBase = `https://stellar.expert/explorer/${isTestnet ? 'testnet' : 'public'}`;

const txUrl = (tx) => `${explorerBase}/tx/${encodeURIComponent(tx)}`;

// ---------------------
// Initialize
// ---------------------
const sleep = (ms) => new Promise(r => setTimeout(r, ms));
onMounted(async () => {
    try {
        const pk = publicKey;
        if (pk) {
            let bal = Number(await checkTkgBalance(pk)) || 0;
            if (bal === 0) {
                await sleep(800);
                bal = Number(await checkTkgBalance(pk)) || 0;
            }
            tkgBalance.value = bal;
        }
    } catch {
        tkgBalance.value = 0;
    } finally {
        loadingBalance.value = false;
    }

    await fetchPositions();
    await fetchrewards();
    await refreshStats();
});

watch(paginatedData, () => {
    currentPage.value = 1;
});

const existingTkgStaked = computed(() =>
    (positions.value || [])
        .filter(p => p.asset_code === 'TKG' && (p.status === 'Active' || p.status === 'Topped Up'))
        .reduce((s, p) => s + Number(p.amount || 0), 0)
);

// --- Tier & APY rules (mirrors backend tkgTierAndApy) ---
function tierAndApy(total) {
    if (total >= 100000) return { tier: 4, apy: 18.00 };
    if (total >= 50000) return { tier: 3, apy: 16.00 };
    if (total >= 10000) return { tier: 2, apy: 15.00 };
    if (total >= 1500) return { tier: 1, apy: 12.00 };
    return { tier: 0, apy: 0.00 };
}

function isEnded(pos) {
    // Adjust if your backend already provides a boolean
    return Number(pos.status_id) === 4 || Boolean(pos.is_withdrawn);
}

// Daily reward = amount × (APY / 100) / 365 (no compounding; matches backend logic)
function dailyReward(pos) {
    const apy = Number(pos.apy) || 0;
    const amt = Number(pos.amount) || 0;
    if (apy <= 0 || amt <= 0) return 0;
    return +(amt * (apy / 100) / 365).toFixed(7); // 7dp to match on-chain precision
}

// Next reward time = (last_reward_at || start_at) + 24h
function nextRewardAt(pos) {
    const baseStr = pos.last_reward_at || pos.start_at;
    const base = baseStr ? new Date(baseStr) : null;
    if (!base || isNaN(+base)) return null;
    return new Date(base.getTime() + 24 * 60 * 60 * 1000);
}

// Human ETA like "in 5h 12m" / "in 2d 3h" / "due now"
function etaString(dt) {
    if (!dt) return '—';
    const now = new Date();
    const ms = dt - now;
    if (ms <= 0) return 'due now';
    const mins = Math.floor(ms / 60000);
    const days = Math.floor(mins / (60 * 24));
    const hours = Math.floor((mins - days * 24 * 60) / 60);
    const m = mins % 60;
    if (days > 0) return `in ${days}d ${hours}h`;
    if (hours > 0) return `in ${hours}h ${m}m`;
    return `in ${m}m`;
}


// --- Totals after this stake selection ---
const projectedTotal = computed(() => existingTkgStaked.value + Number(selectedTokens.value || 0));
const projected = computed(() => tierAndApy(projectedTotal.value)); // { tier, apy }

// --- Reward estimates for the *selected* amount at projected APY ---
const estDaily = computed(() => Number(selectedTokens.value) * (projected.value.apy / 100) / 365);
const estMonthly = computed(() => estDaily.value * 30);   // simple 30-day month
const estYearly = computed(() => Number(selectedTokens.value) * (projected.value.apy / 100));

async function onSubmit() {

    const stakingAssetId = isTestnet ? 2 : 1;
    if (!publicKey) {
        Swal.fire({ icon: "info", title: "Connect Wallet", text: "Please connect your wallet to stake." });
        return;
    }

    if (!hasMinBalance.value) {
        Swal.fire({ icon: "warning", title: "Insufficient Balance", text: "You need at least 1,500 TKG to stake." });
        return;
    }

    const amount = Number(selectedTokens.value);
    const max = Number(stakeMax.value || 0);

    if (isNaN(amount) || amount < stakeMin || amount > max) {
        Swal.fire({
            icon: "error",
            title: "Invalid Amount",
            text: `Stake amount must be between 1,500 and ${max.toLocaleString()} TKG.`,
        });
        return;
    }

    stakeLoading.value = true;

    try {
        updateLoader("Preparing Staking", "Creating staking payment XDR…");

        const res = await axios.post(
            "/api/staking/start",
            {
                public_key: publicKey,
                amount,
                staking_asset_id: stakingAssetId,
            },
            {
                headers: apiHeaders(),
                withCredentials: true,
            }
        );

        if (res.data.status === "success") {

            updateLoader(
                "Sign in Wallet",
                `Please approve the staking transaction for <b>${amount.toLocaleString()}</b> TKG…`
            );

            signXdr(res.data.xdr, res.data.staking_id, isTestnet);
        } else {
            const msg = res?.data?.message || res?.data?.error || "Failed to stake tokens.";
            Swal.close();
            Swal.fire({ icon: "error", title: "Error!", text: msg });
        }
    } catch (error) {
        const msg =
            error?.response?.data?.message ||
            (error?.response?.status === 422
                ? "Validation error. Please check your input."
                : "An error occurred while staking tokens.");
        Swal.close();
        Swal.fire({ icon: "error", title: "Error!", text: msg });
        console.error("Error staking tokens:", error);
    } finally {
        stakeLoading.value = false;
    }
}

async function signXdr(xdr, staking_id, testnet) {
  if (typeof xdr !== 'string' || !/^[A-Za-z0-9+/=]+$/.test(xdr)) {
    Swal.fire({ icon:'error', title:'Invalid XDR', text:'Unsigned XDR is not a base64 envelope.' });
    throw new Error('unsigned XDR not base64');
  }

  const net = {
    freighter: testnet ? 'TESTNET' : 'PUBLIC',
    albedo:    testnet ? 'testnet' : 'public',
    rabet:     testnet ? 'testnet' : 'mainnet',
    xbull:     testnet ? 'testnet' : 'public'
  };

  const active = (localStorage.getItem('wallet_key') || '').toLowerCase();

  switch (active) {
    case 'rabet': {
      try {
        const result = await rabet.sign(xdr, net.rabet);
        await submitStakingXdr(result.xdr, staking_id);
      } catch (e) {
        Swal.fire({ icon:'error', title:'Rabet', text:'Signing was rejected or failed.' });
        throw e;
      }
      return;
    }

    case 'freighter': {
      try {
        const signed = await signTransaction(xdr, net.freighter);
        await submitStakingXdr(signed, staking_id);
      } catch (e) {
        Swal.fire({ icon:'error', title:'Freighter', text:'Signing was rejected or failed.' });
        throw e;
      }
      return;
    }

    case 'albedo': {
      try {
        const res = await window.albedo.tx({
          xdr,                 
          network: net.albedo
        });
        
        const signedXdr = res.signed_envelope_xdr;
        
        if (!signedXdr) {
          Swal.fire({ icon:'error', title:'Albedo', text:'No signed XDR returned.' });
          return;
        }
        await submitStakingXdr(signedXdr, staking_id);
      } catch (err) {
        const msg = (err && (err.message || err.error || err.code)) || '';
        const hint = /cancel|denied|not selected/i.test(msg)
          ? 'Request cancelled. Open Albedo, select an account, and try again.'
          : 'Could not sign the transaction.';
        Swal.fire({ icon:'error', title:'Albedo', text:hint });
        throw err;
      }
      return;
    }

    case 'xbull': {
        const xbull = window.xBullSDK || window.xBull;
        if (!xbull) throw new Error('xBull not installed');

        try {
            await xbull.connect({
            canRequestPublicKey: true,
            canRequestSign: true,
            });

            const pubkey = await xbull.getPublicKey();
            if (!pubkey) throw new Error('No public key from xBull');

            const signedXdr = await xbull.signXDR(xdr, {
            network: net.xbull,
            });

            await submitStakingXdr(signedXdr, staking_id);
            return;
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'xBull', text: e?.message || 'Signing was rejected or failed.' });
            throw e;
        }
    }
        default:
            throw new Error("No wallet selected. Connect a wallet first.");
    }
}

async function submitStakingXdr(signedXdr, staking_id) {

    if (typeof updateLoader === "function") {
        updateLoader("Submitting", "Broadcasting signed transaction to Stellar…");
    }

    try {
        const { data } = await axios.post(
            '/api/staking/submit_xdr',
            { signedXdr, staking_id },
            {
                headers: apiHeaders(),
                withCredentials: true,
            }
        );

        if (data?.status === "success" || data?.status === 1) {
            if (typeof updateLoader === "function") {
                updateLoader("Finalizing", "Saving staking record…");
            }
            if (Swal.isVisible()) Swal.close();

            Swal.fire({ icon: 'success', title: 'Staked!', text: 'Your TKG were staked successfully.' });

            setTimeout(() => location.reload(), 1000);
        } else {
            if (Swal.isVisible()) Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Staking failed',
                text: data?.message || data?.error || 'Something went wrong while submitting your transaction.',
            });
        }
    } catch (err) {
        if (Swal.isVisible()) Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Network error',
            text: err?.response?.data?.message || err?.message || 'Failed to submit the transaction.',
        });
        console.error('submitStakingXdr error:', err);
    }
}

const unstaking = ref({});

// Helpers (used by the "Your Active Stakes" table)
function formatAmount(v) { return Number(v || 0).toLocaleString(); }
function formatDate(d) {
    if (!d) return '-';
    const dt = d instanceof Date ? d : new Date(d);
    return Number.isNaN(dt.getTime()) ? '-' : dt.toLocaleDateString();
}

async function fetchPositions() {
    if (!publicKey) return;
    try {
        const { data } = await axios.get('/api/staking/user', {
            params: { public_key: publicKey },
            headers: apiHeaders(),
            withCredentials: true,
        });
        positions.value = Array.isArray(data?.positions) ? data.positions : [];
    } catch (e) {
        console.warn('Failed to load positions', e);
        positions.value = [];
    }
}

function canUnstake(pos) {
    if (Number(pos.status_id) === 4) return false;
    const now = Date.now();
    const unlock = pos.unlock_at ? Date.parse(pos.unlock_at) : 0;
    return unlock === 0 || unlock <= now;
}

async function unstake(pos) {
    if (!pos?.id) return;

    const ok = await Swal.fire({
        icon: "warning",
        title: "Unstake?",
        text: "This will stop rewards and return your staked tokens.",
        showCancelButton: true,
        confirmButtonText: "Unstake",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#ef4444",
    });
    if (!ok.isConfirmed) return;

    try {
        unstaking.value[pos.id] = true;

        const { data } = await axios.post(
            "/api/staking/unstake",
            { staking_id: pos.id },
            { headers: apiHeaders(), withCredentials: true }
        );

        if (data?.status === "success") {
            await fetchPositions(); // refresh table
            Swal.fire({ icon: "success", title: "Unstaked", timer: 1200, showConfirmButton: false });
        } else {
            Swal.fire({ icon: "error", title: "Failed", text: data?.message || "Could not unstake." });
        }
    } catch (e) {
        Swal.fire({ icon: "error", title: "Network error", text: e?.response?.data?.message || e.message });
    } finally {
        unstaking.value[pos.id] = false;
    }
}

function fmtDate(d) {
    if (!d) return '—';
    const dt = new Date(d);
    if (Number.isNaN(dt.getTime())) return '—';
    return dt.toLocaleString(undefined, {
        year: 'numeric', month: 'short', day: '2-digit',
        // hour: '2-digit', minute: '2-digit'
    });
}

async function fetchrewards() {
    try {
        const response = await axios.get('/api/global/staking_reward', {
            headers: apiHeaders(),
        });

        if (response.data.status === "success") {
            paginatedData.value = response.data.stakingreward;
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: response.data.message || "An unexpected error occurred.",
            });
        }
    } catch (error) {
        console.error("Error:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: error.response?.data?.message || "Failed to fetch wallet types. Please try again later.",
        });
    }
}

const apyTiers = [
    { tier: 1, range: '1,500 – 9,999', apy: 12 },
    { tier: 2, range: '10,000 – 49,999', apy: 15 },
    { tier: 3, range: '50,000 – 99,999', apy: 16 },
    { tier: 4, range: '100,000+', apy: 18 },
]

const faq = [
    {
        q: "What’s the minimum stake?",
        a: "You need at least <strong>1,500 TKG</strong> to start earning rewards."
    },
    {
        q: "How often are rewards paid?",
        a: "Rewards are <strong>distributed every 24 hours</strong>, calculated by your tier at the time of payout."
    },
    {
        q: "Can I unstake anytime?",
        a: "Yes — you can unstake at any time, and your <strong>TKG tokens will be sent back to your wallet</strong> immediately."
    },
    {
        q: "Is there a fee for unstaking?",
        a: "There’s <strong>no platform fee</strong> for unstaking. Standard network fees may apply."
    },
    {
        q: "Where do rewards come from?",
        a: "Rewards are funded from the <strong>35M TKG reserve</strong> allocated for staking sustainability."
    }
]

const openIndex = ref<number | null>(null)

function toggleFaq(idx: number) {
    openIndex.value = openIndex.value === idx ? null : idx
}

// --- Helpers ---
function fmtNum(n: number | string | null | undefined, digits = 0) {
  if (n === null || n === undefined || Number.isNaN(Number(n))) return null; // signal "no value"
  return Number(n).toLocaleString(undefined, {
    minimumFractionDigits: digits,
    maximumFractionDigits: digits,
  });
}

function fmtInt(n: number | string | null | undefined, digits = 0) {
  return fmtNum(n, digits);
}


function fmtTKG(n: number | string | null | undefined, digits = 2) {
  const val = fmtNum(n, digits);
  return val === null ? null : `${val}`;
}

// ---------- State ----------
const statsLoading = ref(false)
const lastUpdated = ref<Date | null>(null)
const totalStakedTKG = ref<number | null>(null)
const activeStakers = ref<number | null>(null)
const rewardsPaid24hTKG = ref<number | null>(null)
const totalPayouts = ref<number | null>(null)

// ---------- Formatted values ----------
const totalStakedFormatted = computed(() => fmtInt(totalStakedTKG.value));
const activeStakersFormatted = computed(() => fmtInt(activeStakers.value));
const rewards24hFormatted    = computed(() => fmtTKG(rewardsPaid24hTKG.value, 2));
const totalPayoutsFormatted  = computed(() => fmtTKG(totalPayouts.value, 2));

const lastUpdatedAgo = computed(() => {
    if (!lastUpdated.value) return 'just now'
    const diffMs = Date.now() - lastUpdated.value.getTime()
    const mins = Math.floor(diffMs / 60000)
    if (mins < 1) return 'just now'
    if (mins < 60) return `${mins} minute${mins === 1 ? '' : 's'} ago`
    const hours = Math.floor(mins / 60)
    return `${hours} hour${hours === 1 ? '' : 's'} ago`
})

// ---------- Fetch / refresh ----------
const statsError = ref(false);

async function refreshStats() {
  statsLoading.value = true;
  statsError.value = false;
  try {
    const { data } = await axios.get('/api/global/stats');
    const s = data?.stats ?? data
    totalStakedTKG.value   = Number(s?.total_staked   ?? 0);
    activeStakers.value    = Number(s?.active_stakers ?? 0);
    rewardsPaid24hTKG.value= Number(s?.rewards_paid   ?? 0);
    totalPayouts.value     = Number(s?.total_payouts  ?? 0);
  } catch (err) {
    console.error('Failed to load staking stats', err)
    totalStakedTKG.value    = null
    activeStakers.value     = null
    rewardsPaid24hTKG.value = null
    totalPayouts.value      = null
    statsError.value = true;
    } finally {
        lastUpdated.value = new Date()
        statsLoading.value = false
    }
}

</script>

<style lang="scss" scoped>
.responsive-container {
    max-width: 100%;
    margin-left: auto;
    margin-right: auto;
    padding: 2rem;

    @media screen and (min-width: 640px) {
        padding: 4rem;
    }

    @media screen and (min-width: 1024px) {
        padding: 6rem;
    }
}

.card-header {
    font-family: 'DM Sans', sans-serif;
    font-weight: 700;
    font-style: normal;
    font-size: 30px;
    line-height: 100%;
    text-align: center;
}

.card-header span {
    color: #43CDFF;
}

.table-section {
    font-family: 'DM Sans', sans-serif;
    font-weight: 700;
    font-style: Bold;
    font-size: 35px;
    line-height: 50px;
    text-align: center;
    text-transform: capitalize;
}
</style>
