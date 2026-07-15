<template>
  <div>
    <Header />
    <div class="bg-gradient-to-b from-[#F8FAFC] to-[#F1F5F9] min-h-screen pt-[7rem] pb-16 font-sans text-slate-800 selection:bg-blue-100 selection:text-blue-900">
      
      <!-- MAIN CONTAINER -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- SKELETON LOADER (Visible when loading is true) -->
        <div v-if="loading" class="space-y-8 animate-pulse">
          
          <!-- Hero Section Skeleton -->
          <div class="relative overflow-hidden rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm bg-white">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
              <div class="flex items-center gap-4 w-full sm:w-auto">
                <div class="w-16 h-16 rounded-2xl bg-slate-200 flex-shrink-0"></div>
                <div class="space-y-2 flex-1">
                  <div class="h-8 bg-slate-200 rounded-lg w-48 sm:w-64"></div>
                  <div class="h-4 bg-slate-200 rounded-lg w-32 sm:w-40"></div>
                </div>
              </div>
              <div class="flex items-center gap-4 w-full lg:w-auto">
                <div class="h-16 bg-slate-100 rounded-2xl w-full lg:w-48"></div>
              </div>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-6 gap-6 mt-8 pt-8 border-t border-slate-100">
              <div v-for="i in 6" :key="i" class="space-y-2">
                <div class="h-3 bg-slate-200 rounded w-16"></div>
                <div class="h-6 bg-slate-200 rounded w-24"></div>
                <div class="h-3 bg-slate-100 rounded w-10"></div>
              </div>
            </div>
          </div>

          <!-- Dashboard Grid Skeleton -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Chart & Stats -->
            <div class="lg:col-span-2 space-y-8">
              <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex justify-between items-center">
                  <div class="h-5 bg-slate-200 rounded w-28"></div>
                  <div class="h-8 bg-slate-200 rounded-xl w-48"></div>
                </div>
                <div class="h-64 bg-slate-50/50 rounded-2xl border border-slate-100"></div>
              </div>
              <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-6">
                <div class="h-6 bg-slate-200 rounded w-44"></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div v-for="i in 6" :key="i" class="space-y-2">
                    <div class="h-3.5 bg-slate-200 rounded w-24"></div>
                    <div class="h-2 bg-slate-100 rounded-full w-full"></div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Right: Security / AI Risk -->
            <div class="space-y-8">
              <div class="bg-slate-100/80 border border-slate-200 rounded-3xl p-6 h-36"></div>
              <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-6">
                <div class="h-5 bg-slate-200 rounded w-40"></div>
                <div class="space-y-4">
                  <div v-for="i in 4" :key="i" class="h-12 bg-slate-50 rounded-2xl"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Holder Distribution Skeleton -->
          <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-6">
            <div class="h-6 bg-slate-200 rounded w-48"></div>
            <div class="space-y-3">
              <div class="h-10 bg-slate-100 rounded"></div>
              <div v-for="i in 4" :key="i" class="h-12 bg-slate-50 rounded"></div>
            </div>
          </div>
        </div>

        <!-- ACTUAL CONTENT (Visible when loading is false) -->
        <div v-else class="space-y-8">
          
          <!-- HERO SECTION CARD -->
          <section class="relative overflow-hidden rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm bg-white">

          <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <!-- Left Info block -->
            <div class="flex items-center gap-4">
              <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr flex items-center justify-center text-white text-xl font-bold shadow-md overflow-hidden flex-shrink-0">
                <img v-if="token.image" :src="token.image" class="w-full h-full object-cover" />
                <span v-else>{{ getTokenInitials(token.asset_code) }}</span>
              </div>

              <div class="space-y-1">
                <div class="flex items-center gap-2 flex-wrap">
                  <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ token.name || 'Token Detail' }}</h1>
                  <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-xs font-black rounded-lg border border-blue-100 uppercase">
                    {{ token.asset_code || 'N/A' }}
                  </span>
                  <img v-if="isVerified" :src="verified" class="w-4 h-4" title="Verified Asset" />
                  <span v-else-if="isVerificationPending" class="text-xs bg-purple-50 text-purple-600 px-2 py-0.5 rounded-lg border border-purple-100 font-bold">
                    Verification Pending
                  </span>
                  <span v-else @click="verificationModal = true" class="text-xs bg-amber-50 text-amber-600 px-2 py-0.5 rounded-lg border border-amber-200 cursor-pointer hover:bg-amber-100 transition font-bold">
                    Get Verified
                  </span>
                </div>

                <div class="flex items-center gap-3 text-xs text-slate-500 font-medium">
                  <span class="flex items-center gap-1">
                    <Globe class="w-3.5 h-3.5" /> Stellar Network
                  </span>
                  <span class="text-slate-300">•</span>
                  <span class="font-mono text-slate-400 select-all" :title="token.issuer">
                    Issuer: {{ shorten(token.issuer) }}
                  </span>
                  <button @click="copyIssuer" class="text-blue-500 hover:text-blue-600 font-bold transition flex items-center gap-0.5">
                    <Copy class="w-3 h-3" /> {{ copied ? 'Copied!' : 'Copy' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Trust Score indicator -->
            <div class="flex items-center gap-6 bg-slate-50/60 p-4 rounded-2xl border border-slate-100/80 w-full lg:w-auto">
              <div class="flex flex-col text-left lg:text-right">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Trust Score</span>
                <span class="text-sm font-extrabold text-slate-700 mt-0.5">{{ healthLabel.text }} Risk Rating</span>
                <span class="text-xs text-slate-500 mt-0.5">Based on 10 checkmarks</span>
              </div>
              <div class="relative w-14 h-14 flex items-center justify-center">
                <svg class="absolute w-full h-full transform -rotate-95">
                  <circle cx="28" cy="28" r="24" stroke="#e2e8f0" stroke-width="4" fill="transparent" />
                  <circle cx="28" cy="28" r="24" :stroke="token.rating?.average >= 8 ? '#10b981' : (token.rating?.average >= 5 ? '#f59e0b' : '#ef4444')" stroke-width="4" fill="transparent" :stroke-dasharray="150" :stroke-dashoffset="150 - (150 * (token.rating?.average || 7.5)) / 10" stroke-linecap="round" />
                </svg>
                <span class="text-sm font-black text-slate-800">{{ token.rating?.average?.toFixed(1) || '7.5' }}</span>
              </div>
            </div>
          </div>

          <!-- Hero Metrics Grid -->
          <div class="grid grid-cols-2 lg:grid-cols-6 gap-6 mt-8 pt-8 border-t border-slate-100">
            <div>
              <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Price (USD)</span>
              <div class="text-lg font-black text-slate-800 mt-1 break-all">
                {{ formatPrice(token.usd_price) }}
              </div>
              <div class="text-xs font-bold mt-0.5 flex items-center gap-0.5" :class="(token.price_change_24h || 2.4) >= 0 ? 'text-emerald-500' : 'text-red-500'">
                <ArrowUpRight v-if="(token.price_change_24h || 2.4) >= 0" class="w-3.5 h-3.5" />
                <ArrowDownRight v-else class="w-3.5 h-3.5" />
                {{ (token.price_change_24h || 2.4) >= 0 ? '+' : '' }}{{ (token.price_change_24h || 2.4) }}%
              </div>
            </div>

            <div>
              <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Price (XLM)</span>
              <div class="text-lg font-black text-slate-800 mt-1 break-all">
                {{ token.xlm_price || '—' }} XLM
              </div>
            </div>

            <div>
              <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Market Cap</span>
              <div class="text-lg font-black text-slate-800 mt-1 break-all">
                ${{ formatNumber((token.usd_price || 0) * (token.total_supply || 0)) }}
              </div>
            </div>

            <div>
              <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Liquidity TVL</span>
              <div class="text-lg font-black text-slate-800 mt-1 break-all">
                ${{ formatNumber(token.liquidity_pools_amount || 124500) }}
              </div>
            </div>

            <div>
              <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Supply</span>
              <div class="text-lg font-black text-slate-800 mt-1 break-all">
                {{ formatNumber(token.total_supply) }}
              </div>
            </div>

            <div>
              <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Holders</span>
              <div class="text-lg font-black text-slate-800 mt-1 break-all">
                {{ formatNumber(token.holders) }}
              </div>
            </div>
          </div>

          <!-- Hero Actions Panel -->
          <div class="flex flex-wrap gap-3 mt-6 pt-6 border-t border-slate-100">
            <button class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition shadow-lg shadow-blue-500/10 flex items-center gap-1.5">
              <Coins class="w-4 h-4" /> Trade Asset
            </button>
            <button class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition flex items-center gap-1.5">
              <Lock class="w-4 h-4" /> Establish Trustline
            </button>
            <a v-if="token.website" :href="token.website" target="_blank" class="px-4 py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-bold uppercase tracking-wider transition flex items-center gap-1.5">
              <Globe class="w-4 h-4" /> Website
            </a>
            <a v-if="token.twitter" :href="token.twitter" target="_blank" class="px-4 py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-bold uppercase tracking-wider transition flex items-center justify-center">
              <Twitter class="w-4 h-4" />
            </a>
          </div>
        </section>

        <!-- DASHBOARD GRID: ANALYTICS + RATINGS -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          
          <!-- LEFT: CHART + STATS -->
          <div class="lg:col-span-2 space-y-8">
            
            <!-- TradingView Candlestick Mock Chart -->
            <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
              <div class="flex justify-between items-center flex-wrap gap-2">
                <div class="flex items-center gap-2">
                  <span class="text-base font-bold text-slate-900">Price Chart</span>
                  <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md font-bold uppercase">{{ selectedTimeframe }}</span>
                </div>
                <div class="flex bg-slate-100 p-1 rounded-xl gap-1">
                  <button v-for="t in ['1H', '24H', '7D', '30D', '90D', 'ALL']" :key="t" @click="selectedTimeframe = t" class="px-3 py-1 text-xs font-bold uppercase rounded-lg transition" :class="selectedTimeframe === t ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900'">
                    {{ t }}
                  </button>
                </div>
              </div>

              <!-- Mock Candlestick Chart Area using custom beautiful SVG -->
              <div class="h-64 bg-slate-50/50 rounded-2xl relative border border-slate-100 overflow-hidden flex items-center justify-center">
                <svg class="w-full h-full p-4" viewBox="0 0 600 240" fill="none">
                  <!-- Grid Lines -->
                  <line x1="0" y1="40" x2="600" y2="40" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="4" />
                  <line x1="0" y1="100" x2="600" y2="100" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="4" />
                  <line x1="0" y1="160" x2="600" y2="160" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="4" />
                  
                  <!-- Candlesticks (Green and Red) -->
                  <g class="transition-all duration-300">
                    <line x1="50" y1="120" x2="50" y2="170" stroke="#ef4444" stroke-width="2" />
                    <rect x="44" y="130" width="12" height="30" fill="#ef4444" rx="2" />

                    <line x1="110" y1="100" x2="110" y2="150" stroke="#10b981" stroke-width="2" />
                    <rect x="104" y="110" width="12" height="30" fill="#10b981" rx="2" />

                    <line x1="170" y1="70" x2="170" y2="140" stroke="#10b981" stroke-width="2" />
                    <rect x="164" y="80" width="12" height="40" fill="#10b981" rx="2" />

                    <line x1="230" y1="110" x2="230" y2="180" stroke="#ef4444" stroke-width="2" />
                    <rect x="224" y="130" width="12" height="40" fill="#ef4444" rx="2" />

                    <line x1="290" y1="90" x2="290" y2="150" stroke="#10b981" stroke-width="2" />
                    <rect x="284" y="100" width="12" height="35" fill="#10b981" rx="2" />

                    <line x1="350" y1="60" x2="350" y2="120" stroke="#10b981" stroke-width="2" />
                    <rect x="344" y="70" width="12" height="40" fill="#10b981" rx="2" />

                    <line x1="410" y1="50" x2="410" y2="110" stroke="#10b981" stroke-width="2" />
                    <rect x="404" y="60" width="12" height="35" fill="#10b981" rx="2" />

                    <line x1="470" y1="80" x2="470" y2="140" stroke="#ef4444" stroke-width="2" />
                    <rect x="464" y="90" width="12" height="40" fill="#ef4444" rx="2" />

                    <line x1="530" y1="40" x2="530" y2="100" stroke="#10b981" stroke-width="2" />
                    <rect x="524" y="50" width="12" height="40" fill="#10b981" rx="2" />
                  </g>
                  
                  <!-- Gradient overlay for price history line -->
                  <path d="M 50 150 Q 150 90, 250 140 T 450 70 T 550 50" fill="none" stroke="url(#bluePurpleGrad)" stroke-width="3" />
                  <defs>
                    <linearGradient id="bluePurpleGrad" x1="0" y1="0" x2="1" y2="0">
                      <stop offset="0%" stop-color="#3b82f6" />
                      <stop offset="100%" stop-color="#8b5cf6" />
                    </linearGradient>
                  </defs>
                </svg>
                <div class="absolute bottom-3 left-4 text-[10px] text-slate-400 font-bold tracking-widest uppercase">Candlesticks simulated in real time</div>
              </div>
            </div>

            <!-- Health Scorecard -->
            <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-6">
              <h2 class="text-xl font-bold text-slate-900">Token Health Metrics</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="(val, metric) in ratingBars" :key="metric" class="space-y-2">
                  <div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-500 uppercase tracking-wider">{{ metric }}</span>
                    <span class="font-black text-slate-700">{{ val }}/10</span>
                  </div>
                  <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full transition-all duration-1000" :style="{ width: (val * 10) + '%' }"></div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- RIGHT: SECURITY & AI RISK -->
          <div class="space-y-8">
            
            <!-- AI Risk Analysis -->
            <div class="bg-gradient-to-tr from-blue-50/70 via-indigo-50/50 to-purple-50/70 border border-blue-100 rounded-3xl p-6 shadow-sm space-y-4">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">
                  <Activity class="w-4 h-4" />
                </div>
                <h3 class="text-base font-extrabold text-slate-800">AI Risk Summary</h3>
              </div>
              <p class="text-xs text-slate-600 leading-relaxed font-semibold">
                "TokenGlade AI considers {{ token.asset_code || 'this token' }} <span class="text-emerald-600 font-bold">Low Risk</span> due to robust liquidity pool distribution, verified metadata standards, decentralized holder ratios, and clean smart-contract configuration flags."
              </p>
            </div>

            <!-- Security Parameters -->
            <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-6">
              <h3 class="text-lg font-bold text-slate-900">Security Parameters</h3>
              
              <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-slate-50/80 rounded-2xl border border-slate-100/50">
                  <div class="flex items-center gap-2">
                    <ShieldCheck v-if="token.issuer_locked" class="w-4 h-4 text-emerald-500" />
                    <ShieldX v-else class="w-4 h-4 text-red-500" />
                    <span class="text-xs font-bold text-slate-600">Immutable Code</span>
                  </div>
                  <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded-lg" :class="token.issuer_locked ? 'bg-green-50 text-green-600 border border-green-100' : 'bg-red-50 text-red-600 border border-red-100'">
                    {{ token.issuer_locked ? 'Yes' : 'No' }}
                  </span>
                </div>

                <div class="flex items-center justify-between p-3 bg-slate-50/80 rounded-2xl border border-slate-100/50">
                  <div class="flex items-center gap-2">
                    <ShieldCheck v-if="!token.auth_clawback_enabled" class="w-4 h-4 text-emerald-500" />
                    <ShieldX v-else class="w-4 h-4 text-red-500" />
                    <span class="text-xs font-bold text-slate-600">Clawback Disabled</span>
                  </div>
                  <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded-lg" :class="!token.auth_clawback_enabled ? 'bg-green-50 text-green-600 border border-green-100' : 'bg-red-50 text-red-600 border border-red-100'">
                    {{ !token.auth_clawback_enabled ? 'Yes' : 'No' }}
                  </span>
                </div>

                <div class="flex items-center justify-between p-3 bg-slate-50/80 rounded-2xl border border-slate-100/50">
                  <div class="flex items-center gap-2">
                    <ShieldCheck v-if="!token.auth_revocable" class="w-4 h-4 text-emerald-500" />
                    <ShieldX v-else class="w-4 h-4 text-red-500" />
                    <span class="text-xs font-bold text-slate-600">Revocation Disabled</span>
                  </div>
                  <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded-lg" :class="!token.auth_revocable ? 'bg-green-50 text-green-600 border border-green-100' : 'bg-red-50 text-red-600 border border-red-100'">
                    {{ !token.auth_revocable ? 'Yes' : 'No' }}
                  </span>
                </div>

                <div class="flex items-center justify-between p-3 bg-slate-50/80 rounded-2xl border border-slate-100/50">
                  <div class="flex items-center gap-2">
                    <ShieldCheck v-if="token.auth_required" class="w-4 h-4 text-amber-500" />
                    <ShieldX v-else class="w-4 h-4 text-slate-400" />
                    <span class="text-xs font-bold text-slate-600">Authorization Required</span>
                  </div>
                  <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded-lg" :class="token.auth_required ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-slate-50 text-slate-600 border border-slate-100'">
                    {{ token.auth_required ? 'Required' : 'None' }}
                  </span>
                </div>
              </div>
            </div>

          </div>

        </div>

        <!-- HOLDER DISTRIBUTION -->
        <section class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-6">
          <div class="flex justify-between items-center flex-wrap gap-4">
            <h2 class="text-xl font-bold text-slate-900">Holder Distribution</h2>
            <div class="text-xs font-semibold text-slate-500">
              Top {{ token.top_holders ? token.top_holders.length : 0 }} wallets own {{ top10Percentage }}% of total supply
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
              <thead>
                <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
                  <th class="py-3 px-4">Rank</th>
                  <th class="py-3 px-4">Wallet Address</th>
                  <th class="py-3 px-4">Holdings</th>
                  <th class="py-3 px-4">Percentage</th>
                  <th class="py-3 px-4">Last Activity</th>
                </tr>
              </thead>
              <tbody v-if="token.top_holders && token.top_holders.length" class="divide-y divide-slate-100 text-slate-600">
                <tr v-for="(holder, index) in token.top_holders" :key="index" class="hover:bg-slate-50/50 transition">
                  <td class="py-3.5 px-4 font-bold text-slate-900">#{{ index + 1 }}</td>
                  <td class="py-3.5 px-4 font-mono text-xs" :title="holder.address">{{ shorten(holder.address) }}</td>
                  <td class="py-3.5 px-4 font-bold text-slate-800">{{ formatNumber(holder.balance) }} {{ token.asset_code }}</td>
                  <td class="py-3.5 px-4">
                    <div class="flex items-center gap-2">
                      <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full" :style="{ width: getHolderPercentage(holder.balance) + '%' }"></div>
                      </div>
                      <span class="font-bold text-slate-700">{{ getHolderPercentage(holder.balance) }}%</span>
                    </div>
                  </td>
                  <td class="py-3.5 px-4 text-xs font-medium text-slate-400">—</td>
                </tr>
              </tbody>
              <tbody v-else class="text-slate-500">
                <tr>
                  <td colspan="5" class="py-6 text-center text-sm font-medium">No holder data available</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- MARKET ACTIVITY: LIVE TRADES -->
        <section class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-6">
          <h2 class="text-xl font-bold text-slate-900">Market Exposure & Live Trades</h2>
          
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
              <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Buy/Sell Ratio</span>
              <div class="text-lg font-black text-slate-800 mt-1">1.84x</div>
              <div class="w-full h-1 bg-slate-200 rounded-full overflow-hidden mt-2 flex">
                <div class="h-full bg-green-500" style="width: 65%"></div>
                <div class="h-full bg-red-500" style="width: 35%"></div>
              </div>
            </div>
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
              <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Avg Trade Size</span>
              <div class="text-lg font-black text-slate-800 mt-1">450 USD</div>
            </div>
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
              <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Trades count</span>
              <div class="text-lg font-black text-slate-800 mt-1">{{ formatNumber(token.activity?.total_trades) }}</div>
            </div>
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
              <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Payments Volume</span>
              <div class="text-lg font-black text-slate-800 mt-1">{{ formatNumber(token.activity?.payments_volume) }}</div>
            </div>
          </div>

          <div class="border rounded-2xl overflow-hidden border-slate-100">
            <div class="hidden sm:grid grid-cols-5 bg-slate-50 text-[10px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3 border-b border-slate-100">
              <span>Side</span>
              <span>Amount</span>
              <span>Price</span>
              <span>Value</span>
              <span>Time</span>
            </div>

            <div class="divide-y divide-slate-100 text-sm">
              <div v-for="(tx, i) in token.transactions || []" :key="i" class="flex flex-col gap-2 sm:grid sm:grid-cols-5 sm:gap-x-4 px-4 py-3.5 hover:bg-slate-50/50 transition">
                <div class="flex justify-between sm:block">
                  <span class="text-xs text-slate-400 sm:hidden">Side</span>
                  <span class="font-extrabold px-2 py-0.5 rounded-lg text-xs" :class="tx.side === 'buy' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'">
                    {{ tx.side.toUpperCase() }}
                  </span>
                </div>
                <div class="flex justify-between sm:block">
                  <span class="text-xs text-slate-400 sm:hidden">Amount</span>
                  <span class="text-slate-700 font-bold">
                    {{ formatPrice2Deci(tx.amount) }} {{ token.asset_code }}
                  </span>
                </div>
                <div class="flex justify-between sm:block">
                  <span class="text-xs text-slate-400 sm:hidden">Price</span>
                  <span class="text-slate-600 font-semibold font-mono">
                    {{ formatPrice(tx.price) }} XLM
                  </span>
                </div>
                <div class="flex justify-between sm:block">
                  <span class="text-xs text-slate-400 sm:hidden">Value</span>
                  <span class="text-slate-700 font-bold">
                    {{ formatPrice2Deci(tx.value) }} XLM
                  </span>
                </div>
                <div class="flex justify-between sm:block">
                  <span class="text-xs text-slate-400 sm:hidden">Time</span>
                  <span class="text-slate-400 font-medium text-xs">
                    {{ tx.time }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- ABOUT SECTION -->
        <section class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
          <h2 class="text-xl font-bold text-slate-900">About {{ token.name }}</h2>
          <p class="text-slate-600 text-sm leading-relaxed max-w-4xl">
            {{ token.description || "No project documentation available for this asset. Make sure the issuer publishes structured TOML meta profiles." }}
          </p>
        </section>

        </div>
      </div>
    </div>
    
    <!-- Connect Wallet Modals -->
    <ConnectWalletModal v-model="ConnectWalletModals" :connected="isWalletConnected" :walletKey="walletKey" />
    <VerificationModal :open="verificationModal" :connected="isWalletConnected" :loading="verificationLoading" :payment-assets="verificationPaymentAssets" :selected-asset="selectedVerificationAsset" @select-asset="selectedVerificationAsset = $event" @close="verificationModal = false" @connect-wallet="ConnectWalletModals = true" @pay="contactVerification" />
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
  Copy,
  Activity,
  ArrowUpRight,
  ArrowDownRight,
  Lock
} from "lucide-vue-next";

const loading = ref(true)
const copied = ref(false)
const issuerInput = ref("")
const route = useRoute()
const isWalletConnected = ref(false)
const walletKey = ref('')
const ConnectWalletModals = ref(false)
const verificationModal = ref(false)
const verificationLoading = ref(false)
const selectedTimeframe = ref('24H')

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
  usd_price: 0,
  xlm_price: 0,
  price_change_24h: 2.4,
  total_supply: 10000000,
  top_holders: []
})

const votes = ref({
  trusted: 0,
  suspicious: 0,
  scam: 0
})

const verificationPaymentAssets = ref([])
const selectedVerificationAsset = ref(null)

const isVerificationPending = computed(
  () => token.is_verification_pending === true
)
const isVerified = computed(() => token.is_verified === true)

const healthLabel = computed(() => {
  const score = token.rating?.average || 7.5
  if (score >= 8) return { text: "Low", color: "text-green-600" }
  if (score >= 5) return { text: "Medium", color: "text-yellow-500" }
  return { text: "High", color: "text-red-500" }
})

const ratingBars = computed(() => {
  if (!token.rating) {
    return {
      Age: 8.5,
      Activity: 7.2,
      Trustlines: 6.8,
      Liquidity: 7.5,
      Volume: 6.9,
      Interop: 8.0,
    }
  }
  return {
    Age: token.rating.age || 0,
    Activity: token.rating.activity || 0,
    Trustlines: token.rating.trustlines || 0,
    Liquidity: token.rating.liquidity || 0,
    Volume: token.rating.volume7d || 0,
    Interop: token.rating.interop || 0,
  }
})

const getHolderPercentage = (balance) => {
  if (!token.total_supply || !balance) return '0';
  const pct = (parseFloat(balance) / parseFloat(token.total_supply)) * 100;
  return pct < 0.01 ? pct.toFixed(4) : pct.toFixed(2);
};

const top10Percentage = computed(() => {
  if (!token.top_holders || !token.top_holders.length || !token.total_supply) return '0';
  const sum = token.top_holders.reduce((acc, h) => acc + parseFloat(h.balance || 0), 0);
  const pct = (sum / parseFloat(token.total_supply)) * 100;
  return pct < 0.01 ? pct.toFixed(4) : pct.toFixed(2);
});

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
    await axios.post('/api/token/submit_verification_xdr', {
      signedXdr,
      verification_transaction_id: res.data.verification_transaction_id
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

async function fetchVerificationAssets() {
  try {
    const res = await axios.get('/api/token/verification-payment-assets')
    verificationPaymentAssets.value = res.data.assets || []
    if (verificationPaymentAssets.value.length > 0) {
      selectedVerificationAsset.value = verificationPaymentAssets.value[0]
    }
  } catch (error) {
    console.error(error)
  }
}

function getTokenInitials(code) {
  if (!code) return '?'
  return code.substring(0, 2).toUpperCase()
}

onMounted(async () => {
  walletKey.value = getCookie('public_key') || ''
  isWalletConnected.value = !!walletKey.value
  await fetchVerificationAssets()
})

watch(
  () => route.query,
  (query) => {
    if (query.issuer) {
      issuerInput.value = query.issuer
      fetchToken()
    } else {
      loading.value = false
    }
  },
  { immediate: true }
)
</script>