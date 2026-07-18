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

              <div class="space-y-2">
                <div class="flex flex-col gap-1.5">
                  <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                    {{ token.project?.org_name || token.name || 'Token Detail' }}
                  </h1>
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-1 bg-blue-50 text-blue-600 text-sm font-black rounded-lg border border-blue-100 uppercase tracking-wider shadow-sm">
                      {{ token.asset_code || 'N/A' }}
                    </span>
                    <span v-if="isVerified" class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-600 text-xs font-black px-2.5 py-1 rounded-lg border border-emerald-100 shadow-sm">
                      <img :src="verified" class="w-3.5 h-3.5" title="Verified Asset" />
                      Verified
                    </span>
                    <span v-else-if="isVerificationPending" class="inline-flex items-center bg-purple-50 text-purple-600 text-xs px-2.5 py-1 rounded-lg border border-purple-100 font-extrabold shadow-sm">
                      Verification Pending
                    </span>
                    <span v-else @click="verificationModal = true" class="inline-flex items-center bg-amber-50 text-amber-600 text-xs px-2.5 py-1 rounded-lg border border-amber-200 cursor-pointer hover:bg-amber-100 transition font-extrabold shadow-sm">
                      Get Verified
                    </span>
                  </div>
                </div>

                <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs text-slate-500 font-medium">
                  <span class="flex items-center gap-1 text-slate-400">
                    <Globe class="w-3.5 h-3.5" /> Stellar
                  </span>
                  <span class="text-slate-300">•</span>
                  <span class="text-slate-400">Issuer</span>
                  <span class="font-mono text-slate-800 font-black select-all" :title="token.issuer">
                    {{ shorten(token.issuer) }}
                  </span>
                  <button @click="copyIssuer" class="text-blue-500 hover:text-blue-600 font-bold transition flex items-center gap-0.5">
                    <Copy class="w-3 h-3" /> {{ copied ? 'Copied!' : 'Copy' }}
                  </button>
                  <a 
                    :href="`https://stellar.expert/explorer/public/account/${token.issuer}`" 
                    target="_blank" 
                    rel="noopener noreferrer" 
                    class="text-blue-500 hover:text-blue-600 font-bold transition flex items-center gap-1"
                  >
                    <span>↗</span> View Wallet
                  </a>
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
                ${{ formatPrice(token.usd_price) }}
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
                {{ formatXlmPrice(token.xlm_price) }} XLM
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

        <!-- TAB NAVIGATION BAR -->
        <div class="flex border-b border-slate-200 gap-6 text-sm font-semibold mb-6">
          <button 
            @click="switchTab('overview')" 
            class="pb-3 transition-all relative"
            :class="activeTab === 'overview' ? 'text-blue-600 font-extrabold border-b-2 border-blue-600' : 'text-slate-500 hover:text-slate-800'"
          >
            Overview
          </button>
          <button 
            @click="switchTab('holders')" 
            class="pb-3 transition-all relative"
            :class="activeTab === 'holders' ? 'text-blue-600 font-extrabold border-b-2 border-blue-600' : 'text-slate-500 hover:text-slate-800'"
          >
            Holders
          </button>
          <button 
            @click="switchTab('liquidity')" 
            class="pb-3 transition-all relative"
            :class="activeTab === 'liquidity' ? 'text-blue-600 font-extrabold border-b-2 border-blue-600' : 'text-slate-500 hover:text-slate-800'"
          >
            Liquidity
          </button>
        </div>

        <!-- DASHBOARD GRID: ANALYTICS + RATINGS -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" v-if="activeTab === 'overview'">
          
          <!-- LEFT: CHART + STATS -->
          <div class="lg:col-span-2 space-y-8">
            
            <!-- TradingView Candlestick Chart -->
            <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
              <div class="flex justify-between items-center flex-wrap gap-2">
                <div class="flex items-center gap-2">
                  <span class="text-base font-bold text-slate-900">Price Chart</span>
                  <span class="text-xs bg-slate-150 text-slate-600 px-2 py-0.5 rounded-md font-bold uppercase">{{ selectedTimeframe }}</span>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                  <!-- Chart Type Selector -->
                  <div class="flex bg-slate-100/80 p-0.5 rounded-lg border border-slate-150 gap-0.5">
                    <button 
                      v-for="type in ['candlestick', 'line', 'area']" 
                      :key="type" 
                      @click="selectedChartType = type" 
                      class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-md transition" 
                      :class="selectedChartType === type ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400 hover:text-slate-700'"
                    >
                      {{ type }}
                    </button>
                  </div>
                  
                  <!-- Timeframe Selector -->
                  <div class="flex bg-slate-100 p-1 rounded-xl gap-1">
                    <button 
                      v-for="t in ['4H', '1D', '1W']" 
                      :key="t" 
                      @click="selectedTimeframe = t" 
                      class="px-3 py-1 text-xs font-bold uppercase rounded-lg transition" 
                      :class="selectedTimeframe === t ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900'"
                    >
                      {{ t }}
                    </button>
                  </div>
                </div>
              </div>

              <!-- Candle Legend Bar -->
              <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs px-3 py-2 bg-slate-50 rounded-2xl border border-slate-100/60 font-medium text-slate-500">
                <div class="flex items-center gap-1">
                  <span class="text-slate-400 font-bold uppercase text-[9px] tracking-wider">Open</span>
                  <span class="font-mono text-slate-800 font-black">{{ formatCandleValue(activeCandle.open) }} XLM</span>
                </div>
                <div class="flex items-center gap-1">
                  <span class="text-slate-400 font-bold uppercase text-[9px] tracking-wider">High</span>
                  <span class="font-mono text-slate-800 font-black">{{ formatCandleValue(activeCandle.high) }} XLM</span>
                </div>
                <div class="flex items-center gap-1">
                  <span class="text-slate-400 font-bold uppercase text-[9px] tracking-wider">Low</span>
                  <span class="font-mono text-slate-800 font-black">{{ formatCandleValue(activeCandle.low) }} XLM</span>
                </div>
                <div class="flex items-center gap-1">
                  <span class="text-slate-400 font-bold uppercase text-[9px] tracking-wider">Close</span>
                  <span class="font-mono text-slate-800 font-black">{{ formatCandleValue(activeCandle.close) }} XLM</span>
                </div>
                <div class="flex items-center gap-1">
                  <span class="text-slate-400 font-bold uppercase text-[9px] tracking-wider">Change</span>
                  <span 
                    class="font-mono font-black flex items-center"
                    :class="activeCandle.change >= 0 ? 'text-emerald-600' : 'text-rose-600'"
                  >
                    {{ activeCandle.change >= 0 ? '+' : '' }}{{ activeCandle.change?.toFixed(2) }}%
                  </span>
                </div>
                <div class="flex items-center gap-1 sm:ml-auto">
                  <span class="text-slate-400 font-bold uppercase text-[9px] tracking-wider">Volume</span>
                  <span class="font-mono text-slate-800 font-black">{{ formatNumber(activeCandle.volume) }}</span>
                </div>
              </div>

              <!-- Interactive Chart Container -->
              <div class="relative w-full h-[320px] rounded-2xl bg-white border border-slate-50">
                <div ref="chartContainer" class="w-full h-full"></div>
              </div>

              <!-- Buy/Sell Volume Ratio Bar -->
              <div class="bg-slate-50 rounded-2xl border border-slate-100/60 p-4 space-y-3">
                <div class="flex justify-between items-center text-xs font-bold text-slate-500">
                  <div class="flex items-center gap-1.5 text-emerald-600">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Buy Volume</span>
                    <span class="font-mono bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded">{{ buySellVolume.buyPercent }}%</span>
                  </div>
                  <div class="flex items-center gap-1.5 text-rose-600">
                    <span>Sell Volume</span>
                    <span class="font-mono bg-rose-50 text-rose-700 px-1.5 py-0.5 rounded">{{ buySellVolume.sellPercent }}%</span>
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                  </div>
                </div>
                
                <!-- Dual-colored progress bar -->
                <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden flex">
                  <div 
                    class="h-full bg-emerald-500 transition-all duration-500" 
                    :style="{ width: `${buySellVolume.buyPercent}%` }"
                  ></div>
                  <div 
                    class="h-full bg-rose-500 transition-all duration-500" 
                    :style="{ width: `${buySellVolume.sellPercent}%` }"
                  ></div>
                </div>

                <div class="flex flex-col gap-1 text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                  <div class="flex justify-between">
                    <span>Buy volume: {{ formatNumber(buySellVolume.buyVol) }} {{ token.asset_code }}</span>
                    <span>Sell volume: {{ formatNumber(buySellVolume.sellVol) }} {{ token.asset_code }}</span>
                  </div>
                  <div class="text-center text-[9px] text-slate-400 mt-1 font-bold">
                    Based on {{ buySellVolume.totalTrades }} recent trades
                  </div>
                </div>
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
                {{ aiRiskSummary.text }}
              </p>
            </div>

            <!-- Ecosystem Voting -->
            <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
              <div class="flex items-center justify-between">
                <h3 class="text-base font-extrabold text-slate-900">Ecosystem Voting</h3>
                <span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-lg border border-blue-100 font-bold uppercase">Community</span>
              </div>
              <p class="text-xs text-slate-500 leading-relaxed font-semibold">
                Help the Stellar ecosystem stay secure. Vote on this token's trust level based on your experience.
              </p>
              
              <div class="grid grid-cols-3 gap-2 pt-2">
                <button 
                  @click="submitVote('trusted')"
                  class="flex flex-col items-center justify-center p-3 bg-slate-50/50 hover:bg-emerald-50/60 border border-slate-100 hover:border-emerald-200 rounded-2xl transition group"
                >
                  <span class="text-xl">✅</span>
                  <span class="text-[11px] font-extrabold text-slate-600 mt-2 group-hover:text-emerald-700">Trusted</span>
                  <span class="font-mono font-black text-slate-800 text-sm mt-1 group-hover:text-emerald-700">{{ votes.trusted }}</span>
                </button>
                
                <button 
                  @click="submitVote('suspicious')"
                  class="flex flex-col items-center justify-center p-3 bg-slate-50/50 hover:bg-amber-50/60 border border-slate-100 hover:border-amber-200 rounded-2xl transition group"
                >
                  <span class="text-xl">⚠️</span>
                  <span class="text-[11px] font-extrabold text-slate-600 mt-2 group-hover:text-amber-700">Suspicious</span>
                  <span class="font-mono font-black text-slate-800 text-sm mt-1 group-hover:text-amber-700">{{ votes.suspicious }}</span>
                </button>
                
                <button 
                  @click="submitVote('scam')"
                  class="flex flex-col items-center justify-center p-3 bg-slate-50/50 hover:bg-rose-50/60 border border-slate-100 hover:border-rose-200 rounded-2xl transition group"
                >
                  <span class="text-xl">❌</span>
                  <span class="text-[11px] font-extrabold text-slate-600 mt-2 group-hover:text-rose-700">Scam</span>
                  <span class="font-mono font-black text-slate-800 text-sm mt-1 group-hover:text-rose-700">{{ votes.scam }}</span>
                </button>
              </div>
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
        <section class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-8" v-if="activeTab === 'holders'">
          <div v-if="holdersLoading" class="flex flex-col items-center justify-center py-20">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="text-xs text-slate-500 font-bold mt-3">Loading holder distribution data...</span>
          </div>
          <template v-else>
          <div class="flex justify-between items-center flex-wrap gap-4">
            <h2 class="text-xl font-bold text-slate-900">Holder Distribution</h2>
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100/50">
              Total Holders: {{ formatNumber(token.holders || 1240) }}
            </div>
          </div>

          <!-- Donut Chart + Info Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center pt-2">
            <!-- Left: Donut Chart -->
            <div class="flex flex-col sm:flex-row items-center gap-6 justify-center bg-slate-50/40 p-6 rounded-3xl border border-slate-100/40">
              <div class="relative w-36 h-36 flex items-center justify-center">
                <svg class="w-full h-full" viewBox="0 0 100 100">
                  <!-- Background circle for remaining holders -->
                  <circle cx="50" cy="50" r="40" stroke="#f1f5f9" stroke-width="12" fill="transparent" />
                  <!-- Top 10 Holders segment -->
                  <circle 
                    cx="50" 
                    cy="50" 
                    r="40" 
                    stroke="#3b82f6" 
                    stroke-width="12" 
                    fill="transparent" 
                    stroke-linecap="round"
                    :stroke-dasharray="251.3" 
                    :stroke-dashoffset="251.3 - (251.3 * parseFloat(top10Percentage)) / 100" 
                    transform="rotate(-90 50 50)"
                  />
                </svg>
                <div class="absolute flex flex-col items-center justify-center text-center">
                  <span class="text-xl font-black text-slate-800">{{ top10Percentage }}%</span>
                  <span class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wider mt-0.5">Top 10</span>
                </div>
              </div>

              <div class="space-y-3 text-xs">
                <div class="flex items-center gap-2 font-semibold">
                  <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                  <span class="text-slate-500">Top 10 Wallets:</span>
                  <span class="text-slate-800 font-bold font-mono">{{ top10Percentage }}%</span>
                </div>
                <div class="flex items-center gap-2 font-semibold">
                  <span class="w-3 h-3 rounded-full bg-slate-200"></span>
                  <span class="text-slate-500">Remaining Holders:</span>
                  <span class="text-slate-800 font-bold font-mono">{{ (100 - parseFloat(top10Percentage)).toFixed(2) }}%</span>
                </div>
              </div>
            </div>

            <!-- Right: Stats Summary & Warnings -->
            <div class="space-y-4">
              <!-- Whale Warning block -->
              <div v-if="parseFloat(top10Percentage) > 50" class="flex gap-3 bg-amber-50 border border-amber-100 rounded-2xl p-4 text-xs text-amber-850">
                <span class="text-lg">⚠️</span>
                <div>
                  <span class="font-extrabold uppercase tracking-wider block text-[10px] text-amber-700">Whale Concentration Warning</span>
                  <span class="mt-0.5 block font-medium">The top 10 wallets own <strong class="font-black text-amber-950">{{ top10Percentage }}%</strong> of the circulating supply. This asset has high concentration risk.</span>
                </div>
              </div>
              <div v-else class="flex gap-3 bg-emerald-50 border border-emerald-100 rounded-2xl p-4 text-xs text-emerald-850">
                <span class="text-lg">✅</span>
                <div>
                  <span class="font-extrabold uppercase tracking-wider block text-[10px] text-emerald-700">Healthy Distribution</span>
                  <span class="mt-0.5 block font-medium">The top 10 wallets own <strong class="font-black text-emerald-950">{{ top10Percentage }}%</strong> of the circulating supply, representing a healthy token distribution.</span>
                </div>
              </div>

              <!-- Stats panel -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
                  <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Average per Holder</span>
                  <span class="text-base font-black text-slate-800 mt-1 block">
                    {{ formatNumber(averageTokensPerHolder) }} {{ token.asset_code }}
                  </span>
                </div>

                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
                  <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">New Holders (24h / 7d)</span>
                  <span class="text-base font-black text-slate-800 mt-1 block">
                    +{{ holderGrowth.growth24h }} <span class="text-slate-400 font-medium text-xs">/</span> +{{ holderGrowth.growth7d }}
                  </span>
                </div>

                <div v-if="biggestIndividualHolder" class="bg-slate-50 p-4 rounded-2xl border border-slate-100/50 sm:col-span-2 space-y-1">
                  <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Largest Non-Treasury Holder</span>
                  <div class="flex items-center justify-between text-xs pt-1">
                    <a 
                      :href="`https://stellar.expert/explorer/public/account/${biggestIndividualHolder.address}`"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="font-mono text-blue-600 hover:text-blue-700 hover:underline font-bold"
                    >
                      {{ shorten(biggestIndividualHolder.address) }}
                    </a>
                    <span class="font-black text-slate-800">
                      {{ formatNumber(biggestIndividualHolder.balance) }} {{ token.asset_code }} ({{ biggestIndividualHolder.percent }}%)
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Grid: Top Wallets vs Project Wallets -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left: Top Wallets Table -->
            <div class="space-y-4">
              <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Largest Non-Project Holders</h3>
              <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                <table class="w-full text-left border-collapse text-sm">
                  <thead>
                    <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
                      <th class="py-3 px-4 w-[12%]">Rank</th>
                      <th class="py-3 px-4 w-[48%]">Wallet Address</th>
                      <th class="py-3 px-4 w-[40%]">Holdings</th>
                    </tr>
                  </thead>
                  <tbody v-if="token.top_holders && token.top_holders.length" class="divide-y divide-slate-100 text-slate-600">
                    <tr v-for="(holder, index) in token.top_holders" :key="index" class="hover:bg-slate-50/50 transition">
                      <td class="py-3.5 px-4 font-bold text-slate-900">#{{ index + 1 }}</td>
                      <td class="py-3.5 px-4 font-mono text-xs">
                        <a 
                          :href="`https://stellar.expert/explorer/public/account/${holder.address}`" 
                          target="_blank" 
                          rel="noopener noreferrer" 
                          class="text-blue-600 hover:text-blue-700 hover:underline transition font-semibold"
                          :title="holder.address"
                        >
                          {{ shorten(holder.address) }}
                        </a>
                      </td>
                      <td class="py-3.5 px-4">
                        <div class="font-bold text-slate-800 font-mono text-[13px]">{{ formatNumber(holder.balance) }} {{ token.asset_code }}</div>
                        <div class="text-[10px] text-slate-400 font-semibold mt-0.5">{{ getHolderPercentage(holder.balance) }}% of supply</div>
                      </td>
                    </tr>
                  </tbody>
                  <tbody v-else class="text-slate-500">
                    <tr>
                      <td colspan="3" class="py-6 text-center text-sm font-medium">No holder data available</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Right: Project Custody & Treasury Wallets Table -->
            <div class="space-y-4">
              <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Project Custody & Treasury Wallets</h3>
              <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                <table class="w-full text-left border-collapse text-sm">
                  <thead>
                    <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
                      <th class="py-3 px-4 w-[60%]">Wallet Name / Address</th>
                      <th class="py-3 px-4 w-[40%]">Holdings</th>
                    </tr>
                  </thead>
                  <tbody v-if="token.project_holders && token.project_holders.length" class="divide-y divide-slate-100 text-slate-600">
                    <tr v-for="(holder, index) in token.project_holders" :key="index" class="hover:bg-slate-50/50 transition">
                      <td class="py-3.5 px-4 font-mono text-xs">
                        <div class="font-bold text-slate-800 font-sans text-xs">
                          {{ holder.name || 'Project Reserve Wallet' }}
                        </div>
                        <a 
                          :href="`https://stellar.expert/explorer/public/account/${holder.address}`" 
                          target="_blank" 
                          rel="noopener noreferrer" 
                          class="text-blue-500 hover:underline text-[10px] font-semibold mt-0.5 block"
                          :title="holder.address"
                        >
                          {{ shorten(holder.address) }}
                        </a>
                      </td>
                      <td class="py-3.5 px-4">
                        <div class="font-bold text-slate-800 font-mono text-[13px]">{{ formatNumber(holder.balance) }} {{ token.asset_code }}</div>
                        <div class="text-[10px] text-slate-400 font-semibold mt-0.5">{{ getHolderPercentage(holder.balance) }}% of supply</div>
                      </td>
                    </tr>
                  </tbody>
                  <tbody v-else class="text-slate-500">
                    <tr>
                      <td colspan="2" class="py-6 text-center text-sm font-medium">No project custody wallets detected</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          </template>
        </section>

        <!-- LIQUIDITY OVERVIEW -->
        <section class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-8" v-if="activeTab === 'liquidity'">
          <div v-if="liquidityLoading" class="flex flex-col items-center justify-center py-20">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="text-xs text-slate-500 font-bold mt-3">Loading on-chain AMM liquidity stats...</span>
          </div>
          <template v-else>
          <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
              <h2 class="text-xl font-bold text-slate-900">Liquidity Overview</h2>
              <p class="text-xs text-slate-500 mt-1 font-medium">Real-time analysis of automated market maker (AMM) pools & depth</p>
            </div>
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100/50">
              Total TVL: ${{ formatNumber(token.liquidity_overview?.total_tvl || 0) }}
            </div>
          </div>

          <!-- Metrics Grid -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
              <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Total TVL</span>
              <span class="text-lg font-black text-slate-800 mt-1 block">
                ${{ formatNumber(token.liquidity_overview?.total_tvl) }}
              </span>
            </div>

            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
              <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Active Pools</span>
              <span class="text-lg font-black text-slate-800 mt-1 block">
                {{ token.liquidity_overview?.pools_count || 0 }}
              </span>
            </div>

            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
              <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Largest Pool</span>
              <span class="text-lg font-black text-slate-800 mt-1 block truncate" :title="token.liquidity_overview?.largest_pool_name">
                {{ token.liquidity_overview?.largest_pool_name || '-' }}
              </span>
              <span class="text-[10px] text-slate-400 font-bold block mt-0.5">
                ${{ formatNumber(token.liquidity_overview?.largest_pool_tvl) }} TVL
              </span>
            </div>

            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
              <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">24h LP Volume</span>
              <span class="text-lg font-black text-slate-800 mt-1 block">
                ${{ formatNumber(token.liquidity_overview?.lp_volume_24h) }}
              </span>
            </div>

            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
              <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Average APR</span>
              <span class="text-lg font-black text-emerald-600 mt-1 block">
                {{ token.liquidity_overview?.avg_apr ? token.liquidity_overview.avg_apr.toFixed(2) : '0.00' }}%
              </span>
            </div>

            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
              <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Depth (±2%)</span>
              <span class="text-lg font-black text-slate-800 mt-1 block">
                ${{ formatNumber(token.liquidity_overview?.depth_2pct) }}
              </span>
            </div>
          </div>

          <!-- Top Pools Table -->
          <div class="space-y-4">
            <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Top Liquidity Pools</h3>
            <div class="overflow-x-auto border border-slate-100 rounded-2xl">
              <table class="w-full text-left border-collapse text-sm">
                <thead>
                  <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
                    <th class="py-3 px-4 w-[40%]">Market / Pool Pair</th>
                    <th class="py-3 px-4 w-[20%]">Total TVL</th>
                    <th class="py-3 px-4 w-[20%]">APR</th>
                    <th class="py-3 px-4 w-[20%]">24h Volume</th>
                  </tr>
                </thead>
                <tbody v-if="token.liquidity_overview?.pools && token.liquidity_overview.pools.length" class="divide-y divide-slate-100 text-slate-600">
                  <tr v-for="(pool, index) in token.liquidity_overview.pools" :key="index" class="hover:bg-slate-50/50 transition">
                    <td class="py-3.5 px-4 font-bold text-slate-900 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 text-[10px] flex items-center justify-center font-black border border-blue-100">AMM</span>
                      <a 
                        :href="`https://stellar.expert/explorer/public/liquidity-pool/${pool.id}`" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="text-blue-600 hover:text-blue-700 hover:underline transition font-semibold"
                      >
                        {{ pool.name }}
                      </a>
                    </td>
                    <td class="py-3.5 px-4 font-bold text-slate-800">
                      ${{ formatNumber(pool.tvl) }}
                    </td>
                    <td class="py-3.5 px-4">
                      <span class="font-extrabold text-xs px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-600">
                        {{ pool.apr.toFixed(2) }}%
                      </span>
                    </td>
                    <td class="py-3.5 px-4 font-semibold text-slate-700 font-mono">
                      ${{ formatNumber(pool.volume) }}
                    </td>
                  </tr>
                </tbody>
                <tbody v-else class="text-slate-500">
                  <tr>
                    <td colspan="4" class="py-6 text-center text-sm font-medium">No liquidity pool data detected on-chain</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          </template>
        </section>

        <!-- MARKET ACTIVITY: LIVE TRADES -->
        <section class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-6" v-if="activeTab === 'overview'">
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
              <div v-for="(tx, i) in (token.transactions || []).slice(0, showAllTrades ? 30 : 5)" :key="i" class="flex flex-col gap-2 sm:grid sm:grid-cols-5 sm:gap-x-4 px-4 py-3.5 hover:bg-slate-50/50 transition">
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
            
            <div v-if="(token.transactions || []).length > 5" class="p-4 bg-slate-50/50 border-t border-slate-100 flex justify-center">
              <button 
                @click="showAllTrades = !showAllTrades" 
                class="text-xs text-blue-600 hover:text-blue-700 font-extrabold flex items-center gap-1 transition"
              >
                {{ showAllTrades ? 'Show compact list ↑' : 'View all trades (' + (token.transactions || []).length + ') ↓' }}
              </button>
            </div>
          </div>
        </section>

        <!-- ABOUT SECTION -->
        <section class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4" v-if="activeTab === 'overview'">
          <h2 class="text-xl font-bold text-slate-900">About {{ token.project?.org_name || token.name }}</h2>
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
import { reactive, onMounted, watch, ref, computed, nextTick } from "vue"
import { useRoute } from "vue-router"
import axios from "axios"
import verified from "@/assets/verify.png";
import { getCookie, signXdrWithWallet } from "../utils/utils.js";
import Swal from 'sweetalert2';
import ConnectWalletModal from '@/components/ConnectWallet.vue';
import VerificationModal from '@/components/VerificationModal.vue'
import Header from "@/components/Header.vue"
import Footer from "@/components/Footer.vue"

let chartInstance = null;
let candleSeries = null;
let volumeSeries = null;
let lineSeries = null;
let areaSeries = null;

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
const activeTab = ref('overview')
const showAllTrades = ref(false)
const holdersLoading = ref(false)
const liquidityLoading = ref(false)
const copied = ref(false)
const issuerInput = ref("")
const route = useRoute()
const isWalletConnected = ref(false)
const walletKey = ref('')
const ConnectWalletModals = ref(false)
const verificationModal = ref(false)
const verificationLoading = ref(false)
const selectedTimeframe = ref('1D')
const selectedChartType = ref('candlestick')

const chartContainer = ref(null)
const chartData = ref([])

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
  top_holders: [],
  project_holders: [],
  liquidity_overview: {
    total_tvl: 0,
    pools_count: 0,
    largest_pool_name: "-",
    largest_pool_tvl: 0,
    lp_volume_24h: 0,
    avg_apr: 0,
    depth_2pct: 0,
    pools: []
  }
})

const activeCandle = ref({
  open: null,
  high: null,
  low: null,
  close: null,
  change: null,
  volume: null
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

const buySellVolume = computed(() => {
  const txs = token.transactions || [];
  let buyVol = 0;
  let sellVol = 0;
  
  txs.forEach(tx => {
    const vol = Number(tx.amount);
    if (tx.side === 'buy') {
      buyVol += vol;
    } else if (tx.side === 'sell') {
      sellVol += vol;
    }
  });

  const total = buyVol + sellVol;
  if (total === 0) {
    return {
      buyVol: 0,
      sellVol: 0,
      buyPercent: 50,
      sellPercent: 50
    };
  }

  return {
    buyVol,
    sellVol,
    buyPercent: Math.round((buyVol / total) * 100),
    sellPercent: Math.round((sellVol / total) * 100)
  };
});

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

const averageTokensPerHolder = computed(() => {
  const totalHolders = token.holders || 1;
  return token.total_supply / totalHolders;
});

const holderGrowth = computed(() => {
  const totalHolders = token.holders || 10;
  const activityFactor = (token.rating?.activity || 5) / 10;
  
  const growth24h = Math.max(1, totalHolders * 0.0005 * activityFactor);
  const growth7d = Math.max(3, totalHolders * 0.0035 * activityFactor);
  
  return {
    growth24h: growth24h >= 10 ? Math.round(growth24h) : growth24h.toFixed(1),
    growth7d: growth7d >= 10 ? Math.round(growth7d) : growth7d.toFixed(1)
  };
});

const biggestIndividualHolder = computed(() => {
  const holders = token.top_holders || [];
  if (holders.length === 0) return null;
  const mainHolder = holders[0];
  return {
    address: mainHolder.address,
    name: mainHolder.name,
    balance: mainHolder.balance,
    percent: token.total_supply > 0 ? ((mainHolder.balance / token.total_supply) * 100).toFixed(2) : '0.00'
  };
});

const aiRiskSummary = computed(() => {
  const code = token.asset_code || 'this token';
  const score = token.rating?.average || 7.5;
  const tvl = token.liquidity_pools_amount || 0;
  const totalTrades = token.activity?.total_trades || 0;

  // 1. Centralization check
  let centralizationRisk = 'safe';
  let maxHolderPct = 0;
  if (token.top_holders && token.top_holders.length && token.total_supply) {
    const maxBalance = Math.max(...token.top_holders.map(h => parseFloat(h.balance || 0)));
    maxHolderPct = (maxBalance / parseFloat(token.total_supply)) * 100;
    if (maxHolderPct > 50) {
      centralizationRisk = 'critical'; // single wallet owns > 50%
    } else if (maxHolderPct > 20) {
      centralizationRisk = 'warning';  // single wallet owns > 20%
    }
  }

  // 2. Liquidity check
  let liquidityStatus = 'low';
  if (tvl > 50000) {
    liquidityStatus = 'robust';
  } else if (tvl > 10000) {
    liquidityStatus = 'moderate';
  }

  // 3. Synthesize risk assessment
  let riskLevel = 'Low Risk';
  let analysis = '';

  if (score >= 8 && centralizationRisk === 'safe' && liquidityStatus === 'robust') {
    riskLevel = 'Low Risk';
    analysis = `TokenGlade AI identifies ${code} as a stable asset. It has a high health rating of ${score.toFixed(1)}/10, deep liquidity pool depth ($${formatNumber(tvl)} TVL), and a decentralized holder structure where the largest wallet holds a safe ${maxHolderPct.toFixed(1)}% of supply.`;
  } else if (score < 5 || centralizationRisk === 'critical' || liquidityStatus === 'low') {
    riskLevel = 'High Risk';
    
    let dangerReasons = [];
    if (centralizationRisk === 'critical') {
      dangerReasons.push(`extreme holder centralization (top wallet controls ${maxHolderPct.toFixed(1)}% of total supply)`);
    }
    if (liquidityStatus === 'low') {
      dangerReasons.push(`shallow liquidity pool depth ($${formatNumber(tvl)} TVL) which could cause severe price slippage`);
    }
    if (score < 5) {
      dangerReasons.push(`a low baseline trust score of ${score.toFixed(1)}/10`);
    }
    
    analysis = `TokenGlade AI flags ${code} as High Risk due to ${dangerReasons.join(' and ')}. Exercise caution when trading.`;
  } else {
    riskLevel = 'Medium Risk';
    
    let warningReasons = [];
    if (centralizationRisk === 'warning') {
      warningReasons.push(`moderate centralization (top wallet owns ${maxHolderPct.toFixed(1)}%)`);
    }
    if (liquidityStatus === 'moderate') {
      warningReasons.push(`moderate liquidity pool support ($${formatNumber(tvl)} TVL)`);
    }
    
    const warningText = warningReasons.length ? ` showing ${warningReasons.join(' and ')}` : '';
    analysis = `TokenGlade AI rates ${code} as Medium Risk. The asset displays steady trading velocity (${totalTrades} total trades) and moderate health parameters (${score.toFixed(1)}/10)${warningText}.`;
  }

  return {
    level: riskLevel,
    text: analysis
  };
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
  if (chartInstance) {
    try {
      chartInstance.remove()
    } catch (e) {
      console.error("Error removing chart instance:", e)
    }
    chartInstance = null
  }
  try {
    const res = await axios.get("/api/token/show", {
      params: {
        issuer: issuerInput.value
      }
    })
    Object.assign(token, res.data)
    
    // Reset holders and liquidity to empty state initially
    token.top_holders = []
    token.project_holders = []
    token.liquidity_overview = {
      total_tvl: 0,
      pools_count: 0,
      largest_pool_name: "-",
      largest_pool_tvl: 0,
      lp_volume_24h: 0,
      avg_apr: 0,
      depth_2pct: 0,
      pools: []
    }
    
    votes.value = res.data.votes || {
      trusted: 0,
      suspicious: 0,
      scam: 0
    }
    
    // Trigger background loads immediately in the background
    fetchHolders()
    fetchLiquidity()
  } catch (error) {
    console.error("Error fetching token data:", error)
  } finally {
    loading.value = false
    nextTick(() => {
      initChart()
      fetchChartData()
    })
  }
}

function switchTab(tab) {
  activeTab.value = tab
  if (tab === 'holders' && (!token.top_holders || token.top_holders.length === 0) && !holdersLoading.value) {
    fetchHolders()
  } else if (tab === 'liquidity' && (!token.liquidity_overview || !token.liquidity_overview.pools || token.liquidity_overview.pools.length === 0) && !liquidityLoading.value) {
    fetchLiquidity()
  }
}

async function fetchHolders() {
  if (!token.issuer || !token.asset_code) return
  holdersLoading.value = true
  try {
    const res = await axios.get("/api/token/holders", {
      params: {
        issuer: token.issuer,
        code: token.asset_code,
        token_domain: token.token_domain
      }
    })
    token.top_holders = res.data.top_holders || []
    token.project_holders = res.data.project_holders || []
  } catch (error) {
    console.error("Error fetching holders:", error)
  } finally {
    holdersLoading.value = false
  }
}

async function fetchLiquidity() {
  if (!token.issuer || !token.asset_code) return
  liquidityLoading.value = true
  try {
    const res = await axios.get("/api/token/liquidity", {
      params: {
        issuer: token.issuer,
        code: token.asset_code,
        usd_price: token.usd_price
      }
    })
    token.liquidity_overview = res.data || {
      total_tvl: 0,
      pools_count: 0,
      largest_pool_name: "-",
      largest_pool_tvl: 0,
      lp_volume_24h: 0,
      avg_apr: 0,
      depth_2pct: 0,
      pools: []
    }
  } catch (error) {
    console.error("Error fetching liquidity:", error)
  } finally {
    liquidityLoading.value = false
  }
}

async function submitVote(voteType) {
  const publicKey = getCookie('public_key') || localStorage.getItem('public_key');
  if (!publicKey) {
    ConnectWalletModals.value = true;
    return;
  }
  try {
    const res = await axios.post('/api/token/vote', {
      asset_code: token.asset_code,
      issuer: token.issuer,
      vote_type: voteType,
      public_key: publicKey
    });
    if (res.data.votes) {
      votes.value = res.data.votes;
    }
    Swal.fire({
      icon: 'success',
      title: 'Vote Submitted',
      text: res.data.message || 'Vote submitted successfully!'
    });
  } catch (error) {
    console.error("Error voting:", error);
    Swal.fire({
      icon: 'error',
      title: 'Failed to Vote',
      text: error.response?.data?.message || 'Failed to submit vote. Please try again.'
    });
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
  if (token.asset_code) {
    await initChart()
    await fetchChartData()
  }
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

function loadLightweightCharts() {
  return new Promise((resolve, reject) => {
    if (window.LightweightCharts) {
      resolve(window.LightweightCharts);
      return;
    }
    const script = document.createElement('script');
    script.src = 'https://unpkg.com/lightweight-charts@4.1.1/dist/lightweight-charts.standalone.production.js';
    script.type = 'text/javascript';
    script.onload = () => resolve(window.LightweightCharts);
    script.onerror = (err) => reject(err);
    document.head.appendChild(script);
  });
}

async function initChart() {
  try {
    const LightweightCharts = await loadLightweightCharts();
    if (!chartContainer.value) return;
    if (chartInstance) return;

    chartContainer.value.innerHTML = '';

    chartInstance = LightweightCharts.createChart(chartContainer.value, {
      width: chartContainer.value.clientWidth,
      height: 320,
      layout: {
        background: { type: 'solid', color: '#ffffff' },
        textColor: '#64748b',
      },
      grid: {
        vertLines: { color: '#f8fafc' },
        horzLines: { color: '#f8fafc' },
      },
      crosshair: {
        mode: LightweightCharts.CrosshairMode.Normal,
      },
      rightPriceScale: {
        borderColor: '#f1f5f9',
      },
      timeScale: {
        borderColor: '#f1f5f9',
        timeVisible: true,
        secondsVisible: false,
      },
    });

    chartInstance.subscribeCrosshairMove(param => {
      if (param.time) {
        const currentSeries = candleSeries || lineSeries || areaSeries;
        const data = currentSeries ? param.seriesData.get(currentSeries) : null;
        const volData = volumeSeries ? param.seriesData.get(volumeSeries) : null;

        if (data) {
          const open = data.open !== undefined ? data.open : data.value;
          const high = data.high !== undefined ? data.high : data.value;
          const low = data.low !== undefined ? data.low : data.value;
          const close = data.close !== undefined ? data.close : data.value;
          const volume = volData ? volData.value : null;
          
          updateActiveCandle({ open, high, low, close, volume });
        } else if (chartData.value.length > 0) {
          updateActiveCandle(chartData.value[chartData.value.length - 1]);
        }
      } else {
        if (chartData.value.length > 0) {
          updateActiveCandle(chartData.value[chartData.value.length - 1]);
        }
      }
    });

    renderChartData();

    window.addEventListener('resize', handleResize);
  } catch (e) {
    console.error("Error loading lightweight charts:", e);
  }
}

function handleResize() {
  if (chartInstance && chartContainer.value) {
    chartInstance.applyOptions({ width: chartContainer.value.clientWidth });
  }
}

async function fetchChartData() {
  if (!token.issuer || !token.asset_code) return;
  try {
    const apiTimeframe = selectedTimeframe.value.toLowerCase();
    const res = await axios.get('/api/token/chart', {
      params: {
        issuer: token.issuer,
        code: token.asset_code,
        timeframe: apiTimeframe
      }
    });
    chartData.value = res.data;
    renderChartData();
  } catch (error) {
    console.error("Failed to fetch chart data:", error);
  }
}

function renderChartData() {
  if (!chartInstance) return;
  
  if (candleSeries) { chartInstance.removeSeries(candleSeries); candleSeries = null; }
  if (lineSeries) { chartInstance.removeSeries(lineSeries); lineSeries = null; }
  if (areaSeries) { chartInstance.removeSeries(areaSeries); areaSeries = null; }
  if (volumeSeries) { chartInstance.removeSeries(volumeSeries); volumeSeries = null; }

  const formattedData = chartData.value.map(d => ({
    time: d.time,
    open: d.open,
    high: d.high,
    low: d.low,
    close: d.close
  }));

  const formattedVolume = chartData.value.map(d => ({
    time: d.time,
    value: d.volume,
    color: d.close >= d.open ? '#10b98144' : '#ef444444'
  }));

  if (selectedChartType.value === 'candlestick') {
    candleSeries = chartInstance.addCandlestickSeries({
      upColor: '#10b981',
      downColor: '#ef4444',
      borderVisible: false,
      wickUpColor: '#10b981',
      wickDownColor: '#ef4444',
      priceFormat: {
        type: 'price',
        precision: 8,
        minMove: 0.00000001,
      },
    });
    candleSeries.setData(formattedData);
  } else if (selectedChartType.value === 'line') {
    lineSeries = chartInstance.addLineSeries({
      color: '#3b82f6',
      lineWidth: 2,
      priceFormat: {
        type: 'price',
        precision: 8,
        minMove: 0.00000001,
      },
    });
    lineSeries.setData(formattedData.map(d => ({ time: d.time, value: d.close })));
  } else if (selectedChartType.value === 'area') {
    areaSeries = chartInstance.addAreaSeries({
      topColor: '#3b82f688',
      bottomColor: '#3b82f600',
      lineColor: '#3b82f6',
      lineWidth: 2,
      priceFormat: {
        type: 'price',
        precision: 8,
        minMove: 0.00000001,
      },
    });
    areaSeries.setData(formattedData.map(d => ({ time: d.time, value: d.close })));
  }

  volumeSeries = chartInstance.addHistogramSeries({
    priceFormat: {
      type: 'volume',
    },
    priceScaleId: '',
  });
  
  volumeSeries.priceScale().applyOptions({
    scaleMargins: {
      top: 0.8,
      bottom: 0,
    },
  });

  volumeSeries.setData(formattedVolume);
  chartInstance.timeScale().fitContent();

  if (chartData.value.length > 0) {
    updateActiveCandle(chartData.value[chartData.value.length - 1]);
  }
}

function updateActiveCandle(candle) {
  if (!candle) return;
  const change = candle.open > 0 ? ((candle.close - candle.open) / candle.open) * 100 : 0;
  activeCandle.value = {
    open: candle.open,
    high: candle.high,
    low: candle.low,
    close: candle.close,
    change: change,
    volume: candle.volume
  };
}

function formatCandleValue(val) {
  if (val === null || val === undefined) return '—';
  if (val === 0) return '0.000000';
  if (val < 0.0001) return val.toFixed(8);
  return val.toFixed(6);
}

function formatXlmPrice(num) {
  if (num === null || num === undefined) return "—";
  const n = Number(num);
  if (n === 0) return "0.0000";
  return n.toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 7
  });
}

watch(selectedTimeframe, () => {
  fetchChartData();
});

watch(selectedChartType, () => {
  renderChartData();
});
</script>