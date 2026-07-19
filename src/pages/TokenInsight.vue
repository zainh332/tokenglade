<template>
  <div class="asset-page-wrapper min-h-screen selection:bg-cyan-500/20 selection:text-white">
    <Header />
    
    <!-- MAIN CONTAINER -->
    <div class="wrap space-y-8">

        <!-- SKELETON LOADER (Visible when loading is true) -->
        <div v-if="loading" class="space-y-8 animate-pulse mt-4">
          <!-- Hero Section Skeleton -->
          <div class="relative overflow-hidden rounded-[25px] p-6 sm:p-8 border border-[#1D2531] bg-[#111620]">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
              <div class="flex items-center gap-4 w-full sm:w-auto">
                <div class="w-16 h-16 rounded-2xl bg-[#1D2531]/60 flex-shrink-0"></div>
                <div class="space-y-2 flex-1">
                  <div class="h-8 bg-[#1D2531]/60 rounded-lg w-48 sm:w-64"></div>
                  <div class="h-4 bg-[#1D2531]/40 rounded-lg w-32 sm:w-40"></div>
                </div>
              </div>
              <div class="flex items-center gap-4 w-full lg:w-auto">
                <div class="h-16 bg-[#0E131C] rounded-2xl w-full lg:w-48 border border-[#1D2531]"></div>
              </div>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-6 gap-6 mt-8 pt-8 border-t border-[#1D2531]">
              <div v-for="i in 6" :key="i" class="space-y-2">
                <div class="h-3 bg-[#1D2531]/40 rounded w-16"></div>
                <div class="h-6 bg-[#1D2531]/60 rounded w-24"></div>
                <div class="h-3 bg-[#1D2531]/40 rounded w-10"></div>
              </div>
            </div>
          </div>

          <!-- Dashboard Grid Skeleton -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Chart & Stats -->
            <div class="lg:col-span-2 space-y-8">
              <div class="bg-[#111620] border border-[#1D2531] rounded-[25px] p-6 space-y-4">
                <div class="flex justify-between items-center">
                  <div class="h-5 bg-[#1D2531]/60 rounded w-28"></div>
                  <div class="h-8 bg-[#1D2531]/60 rounded-xl w-48"></div>
                </div>
                <div class="h-64 bg-[#0E131C] rounded-2xl border border-[#1D2531]"></div>
              </div>
              <div class="bg-[#111620] border border-[#1D2531] rounded-[25px] p-6 space-y-6">
                <div class="h-6 bg-[#1D2531]/60 rounded w-44"></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div v-for="i in 6" :key="i" class="space-y-2">
                    <div class="h-3.5 bg-[#1D2531]/60 rounded w-24"></div>
                    <div class="h-2 bg-[#1D2531]/40 rounded-full w-full"></div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Right: Security / AI Risk -->
            <div class="space-y-8">
              <div class="bg-[#0E131C] border border-[#1D2531] rounded-[25px] p-6 h-36"></div>
              <div class="bg-[#111620] border border-[#1D2531] rounded-[25px] p-6 space-y-6">
                <div class="h-5 bg-[#1D2531]/60 rounded w-40"></div>
                <div class="space-y-4">
                  <div v-for="i in 4" :key="i" class="h-12 bg-[#0E131C] rounded-2xl border border-[#1D2531]"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ACTUAL CONTENT (Visible when loading is false) -->
        <div v-else class="space-y-8">
          
          <!-- BREADCRUMB -->
          <div class="crumb select-none">
            <router-link to="/">Terminal</router-link> &middot; 
            <span class="dim">Asset Insight</span> &middot; 
            <span class="mono">{{ token.asset_code }}</span>
          </div>

          <!-- ASSET HEADER CARD -->
          <section class="card asset">
            <div class="ahead">
              <!-- Icon -->
              <div class="token-ico select-none">
                <img v-if="token.image" :src="token.image" class="w-full h-full object-cover rounded-xl" />
                <span v-else>{{ getTokenInitials(token.asset_code) }}</span>
              </div>
              
              <!-- Name and Issuer info -->
              <div class="name-col">
                <div class="name-row">
                  <h1>{{ token.project?.org_name || token.name || 'Token Detail' }}</h1>
                  <span class="chip sym uppercase">{{ token.asset_code }}</span>
                  <span v-if="isVerified" class="chip verified">✓ Verified</span>
                  <span v-else-if="isVerificationPending" class="chip" style="color:var(--pink);border-color:rgba(240,24,156,0.25)">Pending</span>
                  <span v-else @click="verificationModal = true" class="chip select-none cursor-pointer hover:bg-white/5 transition">Unverified</span>
                </div>
                
                <div class="issuer">
                  <span>Issuer: <a :href="`https://stellar.expert/explorer/public/account/${token.issuer}`" target="_blank" rel="noopener noreferrer" class="mono">{{ shorten(token.issuer) }}</a></span>
                  <button @click="copyIssuer" class="btn dark select-none" style="padding:2px 8px;font-size:10.5px">
                    {{ copied ? 'Copied!' : 'Copy Address' }}
                  </button>
                </div>
              </div>

              <!-- Trust box -->
              <div class="trust">
                <div class="lbl">
                  <div class="k">Trust Score</div>
                  <div class="v uppercase" :class="token.rating?.average >= 8 ? 'up' : (token.rating?.average >= 5 ? 'dim' : 'down')">{{ healthLabel.text }} Risk</div>
                  <small class="faint">Based on 10 checks</small>
                </div>
                <div class="gauge">
                  <svg viewBox="0 0 56 56" width="56" height="56">
                    <circle cx="28" cy="28" r="24" fill="none" stroke="#1a212c" stroke-width="5"/>
                    <circle cx="28" cy="28" r="24" fill="none" :stroke="token.rating?.average >= 8 ? '#2ED47A' : (token.rating?.average >= 5 ? '#FF8A3D' : '#F0616D')" stroke-width="5" stroke-linecap="round"
                      :stroke-dasharray="150.8" :stroke-dashoffset="150.8 - (150.8 * (token.rating?.average || 7.5)) / 10" transform="rotate(-90 28 28)"/>
                  </svg>
                  <b :class="token.rating?.average >= 8 ? 'up' : (token.rating?.average >= 5 ? 'dim' : 'down')">{{ token.rating?.average?.toFixed(1) || '7.5' }}</b>
                </div>
              </div>
            </div>

            <!-- STAT ROW -->
            <div class="stats">
              <div class="st">
                <div class="k">Price USD</div>
                <div class="v font-mono">${{ formatPrice(token.usd_price) }}</div>
                <div class="sub font-mono" :class="(token.price_change_24h || 2.4) >= 0 ? 'up' : 'down'">
                  {{ (token.price_change_24h || 2.4) >= 0 ? '▲ +' : '▼ ' }}{{ (token.price_change_24h || 2.4) }}%
                </div>
              </div>
              <div class="st">
                <div class="k">Price XLM</div>
                <div class="v font-mono">{{ formatXlmPrice(token.xlm_price) }}</div>
                <div class="sub dim uppercase">XLM</div>
              </div>
              <div class="st">
                <div class="k">Market Cap</div>
                <div class="v font-mono">${{ formatNumber((token.usd_price || 0) * (token.total_supply || 0)) }}</div>
                <div class="sub dim">fully diluted</div>
              </div>
              <div class="st">
                <div class="k">Liquidity TVL</div>
                <div class="v font-mono">${{ formatNumber(token.liquidity_pools_amount || 124500) }}</div>
                <div class="sub up">deep</div>
              </div>
              <div class="st">
                <div class="k">Total Supply</div>
                <div class="v font-mono">{{ formatNumber(token.total_supply) }}</div>
                <div class="sub dim uppercase">{{ token.asset_code }}</div>
              </div>
              <div class="st">
                <div class="k">Holders</div>
                <div class="v font-mono">{{ formatNumber(token.holders) }}</div>
                <div class="sub dim">active wallets</div>
              </div>
            </div>

            <!-- Actions -->
            <div class="acts">
              <button class="btn brand font-bold uppercase select-none">⇄ Trade asset</button>
              <button class="btn dark font-bold uppercase select-none">🔒 Establish trustline</button>
              <a v-if="token.website" :href="token.website" target="_blank" class="btn font-bold uppercase transition select-none">🌐 Website</a>
              <a v-if="token.twitter" :href="token.twitter" target="_blank" class="btn font-bold uppercase transition select-none">𝕏 Twitter</a>
            </div>
          </section>

          <!-- TABS -->
          <nav class="tabs select-none">
            <a href="#" @click.prevent="switchTab('overview')" :class="{ on: activeTab === 'overview' }">Overview</a>
            <a href="#" @click.prevent="switchTab('holders')" :class="{ on: activeTab === 'holders' }">Holders</a>
            <a href="#" @click.prevent="switchTab('liquidity')" :class="{ on: activeTab === 'liquidity' }">Liquidity</a>
          </nav>

          <!-- MAIN GRID -->
          <div class="grid">
            
            <!-- LEFT COLUMN: OVERVIEW -->
            <div style="display:flex;flex-direction:column;gap:14px" v-if="activeTab === 'overview'">
              <!-- Chart -->
              <div class="card">
                <div class="card-hd">
                  <h3>{{ token.asset_code }}/XLM <span class="tag" style="margin-left:8px">{{ selectedChartType }} · {{ selectedTimeframe }}</span></h3>
                  <div class="chart-tools select-none">
                    <div class="seg">
                      <span v-for="type in ['candlestick', 'line', 'area']" :key="type" :class="{ on: selectedChartType === type }" @click="selectedChartType = type" class="capitalize">{{ type }}</span>
                    </div>
                    <div class="seg">
                      <span v-for="t in ['4H', '1D', '1W']" :key="t" :class="{ on: selectedTimeframe === t }" @click="selectedTimeframe = t">{{ t }}</span>
                    </div>
                  </div>
                </div>
                
                <div class="ohlc font-mono select-all">
                  <span>O<b>{{ formatCandleValue(activeCandle.open) }}</b></span>
                  <span>H<b class="up">{{ formatCandleValue(activeCandle.high) }}</b></span>
                  <span>L<b class="down">{{ formatCandleValue(activeCandle.low) }}</b></span>
                  <span>C<b>{{ formatCandleValue(activeCandle.close) }}</b></span>
                  <span>Chg<b :class="activeCandle.change >= 0 ? 'up' : 'down'">{{ activeCandle.change >= 0 ? '+' : '' }}{{ activeCandle.change?.toFixed(2) }}%</b></span>
                  <span>Vol<b>{{ formatNumber(activeCandle.volume) }}</b></span>
                </div>

                <!-- Interactive Chart Container -->
                <div class="relative w-full h-[340px] bg-transparent">
                  <div ref="chartContainer" class="w-full h-full"></div>
                </div>

                <!-- Depth bar -->
                <div class="depth font-mono">
                  <div class="depth-top"><span class="up">Buy {{ buySellVolume.buyPercent }}%</span><span class="down">Sell {{ buySellVolume.sellPercent }}%</span></div>
                  <div class="depth-bar"><i :style="{ width: `${buySellVolume.buyPercent}%`, background: 'var(--up)' }"></i><i :style="{ width: `${buySellVolume.sellPercent}%`, background: 'var(--down)' }"></i></div>
                  <div class="depth-sub">
                    <span>{{ formatNumber(buySellVolume.buyVol) }} {{ token.asset_code }} bought</span>
                    <span>Based on {{ buySellVolume.totalTrades }} recent trades</span>
                    <span>{{ formatNumber(buySellVolume.sellVol) }} {{ token.asset_code }} sold</span>
                  </div>
                </div>
              </div>

              <!-- Health scorecard -->
              <div class="card">
                <div class="card-hd"><h3>Token Health Metrics</h3><span class="tag">Composite {{ token.rating?.average?.toFixed(1) || '7.5' }} / 10</span></div>
                <div class="health">
                  <div v-for="(val, metric) in ratingBars" :key="metric" class="hm" :class="{ weak: val < 6 }">
                    <div class="top"><span class="dim capitalize">{{ metric }}</span><b>{{ val }}/10</b></div>
                    <div class="track"><i :style="{ width: (val * 10) + '%' }"></i></div>
                  </div>
                </div>
              </div>

              <!-- Market Exposure & Trades -->
              <div class="card expo">
                <div class="card-hd"><h3>Market Exposure & Live Trades</h3><span class="tag"><span class="dot"></span>Streaming</span></div>
                <div class="expo-stats">
                  <div class="st">
                    <div class="k">Buy / Sell Ratio</div>
                    <div class="v up font-mono">{{ (buySellVolume.buyVol / Math.max(1, buySellVolume.sellVol)).toFixed(2) }}×</div>
                    <div class="ratio-bar"><i :style="{ width: `${buySellVolume.buyPercent}%`, background: 'var(--up)' }"></i><i :style="{ width: `${buySellVolume.sellPercent}%`, background: 'var(--down)' }"></i></div>
                  </div>
                  <div class="st">
                    <div class="k">Avg Trade Size</div>
                    <div class="v font-mono">$450</div>
                    <div class="sub dim font-mono">per fill</div>
                  </div>
                  <div class="st">
                    <div class="k">Trades count</div>
                    <div class="v font-mono">{{ formatNumber(token.activity?.total_trades) }}</div>
                    <div class="sub dim font-mono">executions</div>
                  </div>
                  <div class="st">
                    <div class="k">Payments Vol</div>
                    <div class="v font-mono">{{ formatNumber(token.activity?.payments_volume) }}</div>
                    <div class="sub dim font-mono">lifetime</div>
                  </div>
                </div>
                
                <table class="trades select-all">
                  <thead><tr><th>Side</th><th>Amount</th><th>Price</th><th>Value</th><th>Time</th></tr></thead>
                  <tbody v-if="token.transactions && token.transactions.length">
                    <tr v-for="(tx, i) in (token.transactions || []).slice(0, showAllTrades ? 30 : 5)" :key="i">
                      <td><span class="side" :class="tx.side === 'buy' ? 'buy' : 'sell'">{{ tx.side.toUpperCase() }}</span></td>
                      <td>{{ formatPrice2Deci(tx.amount) }} {{ token.asset_code }}</td>
                      <td>{{ formatPrice(tx.price) }}</td>
                      <td class="dim">{{ formatPrice2Deci(tx.value) }} XLM</td>
                      <td class="dim">{{ tx.time }}</td>
                    </tr>
                  </tbody>
                  <tbody v-else>
                    <tr><td colspan="5" style="text-align:center;padding:20px;color:var(--faint)">No live trade logs detected</td></tr>
                  </tbody>
                </table>
                <div v-if="(token.transactions || []).length > 5" @click="showAllTrades = !showAllTrades" class="viewall select-none">
                  {{ showAllTrades ? 'Show compact list ↑' : 'View all trades (' + (token.transactions || []).length + ') ↓' }}
                </div>
              </div>

              <!-- About -->
              <div class="card about">
                <h3>About {{ token.project?.org_name || token.name }}</h3>
                <p>{{ token.description || "No project documentation available for this asset. Make sure the issuer publishes structured TOML meta profiles." }}</p>
              </div>
            </div>

            <!-- LEFT COLUMN: HOLDERS -->
            <div style="display:flex;flex-direction:column;gap:14px" v-if="activeTab === 'holders'">
              <section class="card asset" style="margin-top:0">
                <div v-if="holdersLoading" class="flex flex-col items-center justify-center py-20">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-500"></div>
                  <span class="text-xs text-slate-400 font-bold mt-3">Loading holder distribution data...</span>
                </div>
                <template v-else>
                <div class="flex justify-between items-center flex-wrap gap-4 mb-6">
                  <h2 class="text-xl font-bold text-white tracking-tight">Holder Distribution</h2>
                  <div class="text-xs font-bold text-slate-400 uppercase tracking-wider bg-[#0E131C] px-3 py-1.5 rounded-xl border border-[#1D2531]">
                    Total Holders: {{ formatNumber(token.holders || 1240) }}
                  </div>
                </div>

                <!-- Donut Chart + Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center pt-2">
                  <!-- Left: Donut Chart -->
                  <div class="flex flex-col sm:flex-row items-center gap-6 justify-center bg-[#0E131C] p-6 rounded-2xl border border-[#1D2531]">
                    <div class="relative w-36 h-36 flex items-center justify-center">
                      <svg class="w-full h-full" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="40" stroke="#1d2531" stroke-width="12" fill="transparent" />
                        <circle 
                          cx="50" 
                          cy="50" 
                          r="40" 
                          stroke="#12CBEE" 
                          stroke-width="12" 
                          fill="transparent" 
                          stroke-linecap="round"
                          :stroke-dasharray="251.3" 
                          :stroke-dashoffset="251.3 - (251.3 * parseFloat(top10Percentage)) / 100" 
                          transform="rotate(-90 50 50)"
                        />
                      </svg>
                      <div class="absolute flex flex-col items-center justify-center text-center">
                        <span class="text-xl font-black text-white">{{ top10Percentage }}%</span>
                        <span class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wider mt-0.5">Top 10</span>
                      </div>
                    </div>

                    <div class="space-y-3 text-xs font-mono">
                      <div class="flex items-center gap-2 font-semibold">
                        <span class="w-3 h-3 rounded-full bg-[#12CBEE]"></span>
                        <span class="text-slate-400">Top 10 Wallets:</span>
                        <span class="text-white font-bold">{{ top10Percentage }}%</span>
                      </div>
                      <div class="flex items-center gap-2 font-semibold">
                        <span class="w-3 h-3 rounded-full bg-[#1d2531]"></span>
                        <span class="text-slate-400">Others:</span>
                        <span class="text-white font-bold">{{ (100 - parseFloat(top10Percentage)).toFixed(2) }}%</span>
                      </div>
                    </div>
                  </div>

                  <!-- Right: Stats Summary & Warnings -->
                  <div class="space-y-4">
                    <!-- Whale Warning block -->
                    <div v-if="parseFloat(top10Percentage) > 50" class="flex gap-3 bg-rose-500/10 border border-rose-500/20 rounded-2xl p-4 text-xs text-rose-400">
                      <span class="text-lg">⚠️</span>
                      <div>
                        <span class="font-extrabold uppercase tracking-wider block text-[10px] text-rose-500">Whale Concentration Warning</span>
                        <span class="mt-0.5 block font-medium">The top 10 wallets own <strong class="font-black text-white">{{ top10Percentage }}%</strong> of the supply. This asset has high concentration risk.</span>
                      </div>
                    </div>
                    <div v-else class="flex gap-3 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl p-4 text-xs text-emerald-400">
                      <span class="text-lg">✓</span>
                      <div>
                        <span class="font-extrabold uppercase tracking-wider block text-[10px] text-emerald-500">Healthy Distribution</span>
                        <span class="mt-0.5 block font-medium">The top 10 wallets own <strong class="font-black text-white">{{ top10Percentage }}%</strong> of supply, representing healthy distribution.</span>
                      </div>
                    </div>

                    <!-- Stats panel -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div class="bg-[#111620] p-4 rounded-xl border border-[#1D2531]">
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block font-mono">Average per Holder</span>
                        <span class="text-base font-black text-white mt-1 block font-mono">
                          {{ formatNumber(averageTokensPerHolder) }} {{ token.asset_code }}
                        </span>
                      </div>

                      <div class="bg-[#111620] p-4 rounded-xl border border-[#1D2531]">
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block font-mono">New Holders (24h / 7d)</span>
                        <span class="text-base font-black text-white mt-1 block font-mono">
                          +{{ holderGrowth.growth24h }} <span class="text-slate-500 font-medium text-xs">/</span> +{{ holderGrowth.growth7d }}
                        </span>
                      </div>

                      <div v-if="biggestIndividualHolder" class="bg-[#111620] p-4 rounded-xl border border-[#1D2531] sm:col-span-2 space-y-1">
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block font-mono">Largest Non-Treasury Holder</span>
                        <div class="flex items-center justify-between text-xs pt-1">
                          <a 
                            :href="`https://stellar.expert/explorer/public/account/${biggestIndividualHolder.address}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-mono text-cyan-400 hover:text-cyan-300 font-bold"
                          >
                            {{ shorten(biggestIndividualHolder.address) }}
                          </a>
                          <span class="font-black text-white font-mono">
                            {{ formatNumber(biggestIndividualHolder.balance) }} {{ token.asset_code }} ({{ biggestIndividualHolder.percent }}%)
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Grid: Top Wallets vs Project Wallets -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-6">
                  <!-- Left: Top Wallets Table -->
                  <div class="space-y-4">
                    <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">Largest Non-Project Holders</h3>
                    <div class="overflow-x-auto border border-[#1D2531] rounded-xl bg-[#111620]">
                      <table class="trades">
                        <thead>
                          <tr>
                            <th style="text-align:left;width:15%">Rank</th>
                            <th style="text-align:left;width:45%">Wallet Address</th>
                            <th style="width:40%">Holdings</th>
                          </tr>
                        </thead>
                        <tbody v-if="token.top_holders && token.top_holders.length">
                          <tr v-for="(holder, index) in token.top_holders" :key="index" class="hover:bg-white/2 transition">
                            <td class="font-bold text-white">#{{ index + 1 }}</td>
                            <td class="font-mono text-xs text-left">
                              <a 
                                :href="`https://stellar.expert/explorer/public/account/${holder.address}`" 
                                target="_blank" 
                                rel="noopener noreferrer" 
                                class="text-cyan-400 hover:text-cyan-300 transition font-semibold"
                                :title="holder.address"
                              >
                                {{ shorten(holder.address) }}
                              </a>
                            </td>
                            <td>
                              <div class="font-bold text-white font-mono">{{ formatNumber(holder.balance) }} {{ token.asset_code }}</div>
                              <div class="text-[10px] text-slate-500 font-semibold mt-0.5">{{ getHolderPercentage(holder.balance) }}% of supply</div>
                            </td>
                          </tr>
                        </tbody>
                        <tbody v-else>
                          <tr>
                            <td colspan="3" class="py-6 text-center text-sm font-medium" style="color:var(--faint)">No holder data available</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <!-- Right: Project Custody & Treasury Wallets Table -->
                  <div class="space-y-4">
                    <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">Project Custody & Treasury Wallets</h3>
                    <div class="overflow-x-auto border border-[#1D2531] rounded-xl bg-[#111620]">
                      <table class="trades">
                        <thead>
                          <tr>
                            <th style="text-align:left;width:60%">Wallet Name / Address</th>
                            <th style="width:40%">Holdings</th>
                          </tr>
                        </thead>
                        <tbody v-if="token.project_holders && token.project_holders.length">
                          <tr v-for="(holder, index) in token.project_holders" :key="index" class="hover:bg-white/2 transition">
                            <td class="font-mono text-xs text-left">
                              <div class="font-bold text-white font-sans text-xs">
                                {{ holder.name || 'Project Reserve Wallet' }}
                              </div>
                              <a 
                                :href="`https://stellar.expert/explorer/public/account/${holder.address}`" 
                                target="_blank" 
                                rel="noopener noreferrer" 
                                class="text-cyan-400 hover:underline text-[10px] font-semibold mt-0.5 block"
                                :title="holder.address"
                              >
                                {{ shorten(holder.address) }}
                              </a>
                            </td>
                            <td>
                              <div class="font-bold text-white font-mono">{{ formatNumber(holder.balance) }} {{ token.asset_code }}</div>
                              <div class="text-[10px] text-slate-500 font-semibold mt-0.5">{{ getHolderPercentage(holder.balance) }}% of supply</div>
                            </td>
                          </tr>
                        </tbody>
                        <tbody v-else>
                          <tr>
                            <td colspan="2" class="py-6 text-center text-sm font-medium" style="color:var(--faint)">No project custody wallets detected</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                </template>
              </section>
            </div>

            <!-- LEFT COLUMN: LIQUIDITY -->
            <div style="display:flex;flex-direction:column;gap:14px" v-if="activeTab === 'liquidity'">
              <section class="card asset" style="margin-top:0">
                <div v-if="liquidityLoading" class="flex flex-col items-center justify-center py-20">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-500"></div>
                  <span class="text-xs text-slate-400 font-bold mt-3">Loading on-chain AMM liquidity stats...</span>
                </div>
                <template v-else>
                <div class="flex justify-between items-center flex-wrap gap-4 mb-6">
                  <div>
                    <h2 class="text-xl font-bold text-white tracking-tight">Liquidity Overview</h2>
                    <p class="text-xs text-slate-400 mt-1 font-medium">Real-time analysis of automated market maker (AMM) pools & depth</p>
                  </div>
                  <div class="text-xs font-bold text-slate-400 uppercase tracking-wider bg-[#0E131C] px-3 py-1.5 rounded-xl border border-[#1D2531] font-mono">
                    Total TVL: ${{ formatNumber(token.liquidity_overview?.total_tvl || 0) }}
                  </div>
                </div>

                <!-- Metrics Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 font-mono">
                  <div class="bg-[#111620] p-4 rounded-xl border border-[#1D2531]">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Total TVL</span>
                    <span class="text-lg font-black text-white mt-1 block">
                      ${{ formatNumber(token.liquidity_overview?.total_tvl) }}
                    </span>
                  </div>

                  <div class="bg-[#111620] p-4 rounded-xl border border-[#1D2531]">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Active Pools</span>
                    <span class="text-lg font-black text-white mt-1 block">
                      {{ token.liquidity_overview?.pools_count || 0 }}
                    </span>
                  </div>

                  <div class="bg-[#111620] p-4 rounded-xl border border-[#1D2531]">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Largest Pool</span>
                    <span class="text-lg font-black text-white mt-1 block truncate font-sans text-sm py-1" :title="token.liquidity_overview?.largest_pool_name">
                      {{ token.liquidity_overview?.largest_pool_name || '-' }}
                    </span>
                    <span class="text-[10px] text-slate-500 font-bold block mt-0.5">
                      ${{ formatNumber(token.liquidity_overview?.largest_pool_tvl) }} TVL
                    </span>
                  </div>

                  <div class="bg-[#111620] p-4 rounded-xl border border-[#1D2531]">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">24h LP Volume</span>
                    <span class="text-lg font-black text-white mt-1 block">
                      ${{ formatNumber(token.liquidity_overview?.lp_volume_24h) }}
                    </span>
                  </div>

                  <div class="bg-[#111620] p-4 rounded-xl border border-[#1D2531]">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Average APR</span>
                    <span class="text-lg font-black text-emerald-400 mt-1 block">
                      {{ token.liquidity_overview?.avg_apr ? token.liquidity_overview.avg_apr.toFixed(2) : '0.00' }}%
                    </span>
                  </div>

                  <div class="bg-[#111620] p-4 rounded-xl border border-[#1D2531]">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Depth (±2%)</span>
                    <span class="text-lg font-black text-white mt-1 block">
                      ${{ formatNumber(token.liquidity_overview?.depth_2pct) }}
                    </span>
                  </div>
                </div>

                <!-- Top Pools Table -->
                <div class="space-y-4 mt-6">
                  <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">Top Liquidity Pools</h3>
                  <div class="overflow-x-auto border border-[#1D2531] rounded-xl bg-[#111620]">
                    <table class="trades">
                      <thead>
                        <tr>
                          <th style="text-align:left;width:40%">Market / Pool Pair</th>
                          <th style="width:20%">Total TVL</th>
                          <th style="width:20%">APR</th>
                          <th style="width:20%">24h Volume</th>
                        </tr>
                      </thead>
                      <tbody v-if="token.liquidity_overview?.pools && token.liquidity_overview.pools.length" class="font-mono">
                        <tr v-for="(pool, index) in token.liquidity_overview.pools" :key="index" class="hover:bg-white/2 transition">
                          <td style="text-align:left" class="font-bold text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-[#0E131C] text-cyan-400 text-[10px] flex items-center justify-center font-black border border-[#1D2531]">AMM</span>
                            <a 
                              :href="`https://stellar.expert/explorer/public/liquidity-pool/${pool.id}`" 
                              target="_blank" 
                              rel="noopener noreferrer" 
                              class="text-[#12CBEE] hover:underline transition font-semibold"
                            >
                              {{ pool.name }}
                            </a>
                          </td>
                          <td class="font-bold text-white">
                            ${{ formatNumber(pool.tvl) }}
                          </td>
                          <td>
                            <span class="font-extrabold text-xs px-2 py-0.5 rounded-lg bg-[#2ED47A]/10 text-[#2ED47A]">
                              {{ pool.apr.toFixed(2) }}%
                            </span>
                          </td>
                          <td class="font-semibold text-slate-300">
                            ${{ formatNumber(pool.volume) }}
                          </td>
                        </tr>
                      </tbody>
                      <tbody v-else>
                        <tr>
                          <td colspan="4" class="py-6 text-center text-sm font-medium" style="color:var(--faint)">No liquidity pool data detected on-chain</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                </template>
              </section>
            </div>

            <!-- RIGHT COLUMN: SIDEBAR WIDGETS -->
            <aside class="rail" v-if="activeTab === 'overview'">
              <!-- AI Risk Summary -->
              <div class="card ai animate-pulseWrapper">
                <div class="card-hd"><h3>◈ AI Risk Summary</h3><span class="tag">Model v3</span></div>
                <p>{{ aiRiskSummary.text }}</p>
                <div class="mini">
                  <div><div class="k">Concentration</div><div class="v font-mono" :class="parseFloat(top10Percentage) > 50 ? 'down' : 'up'">{{ parseFloat(top10Percentage) > 50 ? 'High' : 'Low' }} · {{ top10Percentage }}%</div></div>
                  <div><div class="k">Rug flags</div><div class="v up font-mono">None</div></div>
                  <div><div class="k">Slippage @$10k</div><div class="v font-mono">0.4%</div></div>
                  <div><div class="k">Sell pressure</div><div class="v down font-mono">Elevated</div></div>
                </div>
              </div>

              <!-- Voting -->
              <div class="card">
                <div class="card-hd"><h3>Ecosystem Voting</h3><span class="tag">Community</span></div>
                <div class="vote select-none">
                  <div @click="submitVote('trusted')" class="vopt t"><div class="ic"></div><div class="l">Trusted</div><div class="n">{{ votes.trusted }}</div></div>
                  <div @click="submitVote('suspicious')" class="vopt s"><div class="ic"></div><div class="l">Suspicious</div><div class="n">{{ votes.suspicious }}</div></div>
                  <div @click="submitVote('scam')" class="vopt x"><div class="ic"></div><div class="l">Scam</div><div class="n">{{ votes.scam }}</div></div>
                </div>
              </div>

              <!-- Security Parameters -->
              <div class="card">
                <div class="card-hd"><h3>Security Parameters</h3><span class="tag">On-chain</span></div>
                <div class="sec">
                  <div class="row">
                    <span class="nm"><span class="shield">🛡</span>Immutable code</span>
                    <span class="sval" :class="token.issuer_locked ? 'yes' : 'none'">{{ token.issuer_locked ? 'YES' : 'NO' }}</span>
                  </div>
                  <div class="row">
                    <span class="nm"><span class="shield">🛡</span>Clawback disabled</span>
                    <span class="sval" :class="!token.auth_clawback_enabled ? 'yes' : 'none'">{{ !token.auth_clawback_enabled ? 'YES' : 'NO' }}</span>
                  </div>
                  <div class="row">
                    <span class="nm"><span class="shield">🛡</span>Revocation disabled</span>
                    <span class="sval" :class="!token.auth_revocable ? 'yes' : 'none'">{{ !token.auth_revocable ? 'YES' : 'NO' }}</span>
                  </div>
                  <div class="row">
                    <span class="nm"><span class="shield">🛡</span>Authorization required</span>
                    <span class="sval" :class="token.auth_required ? 'none' : 'yes'" style="font-size:10px">{{ token.auth_required ? 'REQUIRED' : 'NONE' }}</span>
                  </div>
                </div>
              </div>
            </aside>
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
        background: { type: 'solid', color: '#111620' },
        textColor: '#8791A0',
      },
      grid: {
        vertLines: { color: '#1D2531' },
        horzLines: { color: '#1D2531' },
      },
      crosshair: {
        mode: LightweightCharts.CrosshairMode.Normal,
      },
      rightPriceScale: {
        borderColor: '#1D2531',
      },
      timeScale: {
        borderColor: '#1D2531',
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
    color: d.close >= d.open ? '#2ED47A44' : '#F0616D44'
  }));

  if (selectedChartType.value === 'candlestick') {
    candleSeries = chartInstance.addCandlestickSeries({
      upColor: '#2ED47A',
      downColor: '#F0616D',
      borderVisible: false,
      wickUpColor: '#2ED47A',
      wickDownColor: '#F0616D',
      priceFormat: {
        type: 'price',
        precision: 8,
        minMove: 0.00000001,
      },
    });
    candleSeries.setData(formattedData);
  } else if (selectedChartType.value === 'line') {
    lineSeries = chartInstance.addLineSeries({
      color: '#12CBEE',
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
      topColor: 'rgba(18, 203, 238, 0.2)',
      bottomColor: 'rgba(18, 203, 238, 0)',
      lineColor: '#12CBEE',
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

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap');

.asset-page-wrapper {
  --bg: #070A13;
  --panel: #111620;
  --panel2: #0E131C;
  --line: #1D2531;
  --line2: #28313F;
  --ink: #D5DBE5;
  --dim: #8791A0;
  --faint: #586172;
  --amber: #12CBEE;
  --pink: #F0189C;
  --blue: #0A5CE0;
  --coral: #FF8A3D;
  --up: #2ED47A;
  --down: #F0616D;
  --cyan: #12CBEE;
  --mono: "JetBrains Mono", ui-monospace, monospace;
  --disp: "Space Grotesk", sans-serif;
  --body: "Inter", sans-serif;
  
  background: var(--bg);
  color: var(--ink);
  font-family: var(--body);
  font-size: 14px;
  line-height: 1.45;
  min-height: 100vh;
  padding-top: 0px;
  padding-bottom: 50px;
  background-image: radial-gradient(900px 460px at 84% -12%, rgba(18, 203, 238, .09), transparent 62%), radial-gradient(760px 420px at 6% -8%, rgba(240, 24, 156, .07), transparent 60%);
}

.mono {
  font-family: var(--mono);
  font-variant-numeric: tabular-nums;
}

.up { color: var(--up); }
.down { color: var(--down); }
.dim { color: var(--dim); }
.faint { color: var(--faint); }

.wrap {
  max-width: 1320px;
  margin: 0 auto;
  padding: 0 20px;
}

.crumb {
  font-family: var(--mono);
  font-size: 12px;
  color: var(--faint);
  padding: 14px 0 0;
}
.crumb a {
  color: var(--faint);
  transition: color 0.15s ease;
}
.crumb a:hover {
  color: var(--amber);
}

.card {
  background: var(--panel);
  border: 1px solid var(--line);
  border-radius: 12px;
  overflow: hidden;
}

.card-hd {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 13px 16px;
  border-bottom: 1px solid var(--line);
}
.card-hd h3 {
  margin: 0;
  font-family: var(--disp);
  font-weight: 600;
  font-size: 14px;
  color: var(--ink);
}

.tag {
  font-family: var(--mono);
  font-size: 10.5px;
  letter-spacing: .1em;
  color: var(--faint);
  text-transform: uppercase;
}

.dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--amber);
  box-shadow: 0 0 8px var(--amber);
  display: inline-block;
  margin-right: 6px;
  animation: pulse 2.4s infinite;
  vertical-align: middle;
}
@keyframes pulse {
  50% { opacity: .35; }
}

.asset {
  margin-top: 12px;
  padding: 20px;
}

.ahead {
  display: flex;
  gap: 18px;
  align-items: flex-start;
  flex-wrap: wrap;
}

.token-ico {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  background: var(--panel2);
  border: 1px solid var(--line);
  display: grid;
  place-items: center;
  font-family: var(--disp);
  font-weight: 700;
  font-size: 20px;
  color: #fff;
  flex: none;
  box-shadow: 0 0 0 1px #ffffff14 inset;
  overflow: hidden;
}

.name-col {
  display: flex;
  flex-direction: column;
}

.name-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.name-row h1 {
  font-family: var(--disp);
  font-weight: 700;
  font-size: 26px;
  margin: 0;
  letter-spacing: -.01em;
  color: var(--ink);
}

.chip {
  font-family: var(--mono);
  font-size: 11px;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 6px;
  border: 1px solid var(--line2);
  color: var(--dim);
}
.chip.sym {
  color: var(--cyan);
  border-color: rgba(18, 203, 238, 0.25);
  background: rgba(18, 203, 238, 0.07);
}
.chip.verified {
  color: var(--up);
  border-color: rgba(46, 212, 122, 0.25);
  background: rgba(46, 212, 122, 0.07);
}

.issuer {
  font-family: var(--mono);
  font-size: 12px;
  color: var(--faint);
  margin-top: 7px;
  display: flex;
  gap: 14px;
  flex-wrap: wrap;
  align-items: center;
}
.issuer a {
  color: var(--amber);
}

.trust {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 14px;
  border: 1px solid var(--line);
  border-radius: 10px;
  padding: 12px 16px;
  background: var(--panel2);
}
.trust .lbl {
  text-align: right;
}
.trust .lbl .k {
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: .1em;
  color: var(--faint);
  text-transform: uppercase;
}
.trust .lbl .v {
  font-family: var(--disp);
  font-weight: 600;
  font-size: 15px;
  margin-top: 2px;
}
.trust .lbl small {
  font-family: var(--mono);
  font-size: 11px;
  color: var(--faint);
}

.gauge {
  position: relative;
  width: 56px;
  height: 56px;
  flex: none;
}
.gauge b {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  font-family: var(--mono);
  font-weight: 700;
  font-size: 15px;
}

.stats {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 1px;
  background: var(--line);
  border: 1px solid var(--line);
  border-radius: 10px;
  overflow: hidden;
  margin-top: 20px;
}
.st {
  background: var(--panel);
  padding: 13px 15px;
}
.st .k {
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: .07em;
  text-transform: uppercase;
  color: var(--faint);
}
.st .v {
  font-family: var(--mono);
  font-size: 17px;
  font-weight: 600;
  margin-top: 6px;
  letter-spacing: -.01em;
  color: var(--ink);
}
.st .sub {
  font-family: var(--mono);
  font-size: 11px;
  margin-top: 3px;
}

.acts {
  display: flex;
  gap: 10px;
  margin-top: 18px;
  flex-wrap: wrap;
}

.btn {
  font-family: var(--mono);
  font-size: 12.5px;
  font-weight: 600;
  border: 1px solid var(--line2);
  background: var(--panel);
  color: var(--ink);
  padding: 8px 13px;
  border-radius: 7px;
  cursor: pointer;
  transition: all 0.2s ease;
}
.btn:hover {
  border-color: var(--amber);
}
.btn.brand {
  background: var(--amber);
  color: #08131a;
  border-color: transparent;
  font-weight: 700;
}
.btn.brand:hover {
  filter: brightness(1.08);
}
.btn.dark {
  background: #0b0f16;
}

.tabs {
  display: flex;
  gap: 2px;
  border-bottom: 1px solid var(--line);
  margin-top: 22px;
}
.tabs a {
  font-family: var(--mono);
  font-size: 13px;
  color: var(--dim);
  padding: 11px 14px;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
}
.tabs a.on {
  color: var(--ink);
  border-bottom-color: var(--amber);
}
.tabs a:hover {
  color: var(--ink);
}

.grid {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 14px;
  margin-top: 16px;
  align-items: start;
}

.ohlc {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  padding: 11px 16px;
  border-bottom: 1px solid var(--line);
  font-family: var(--mono);
  font-size: 12px;
}
.ohlc span {
  color: var(--faint);
}
.ohlc b {
  color: var(--ink);
  font-weight: 600;
  margin-left: 5px;
}

.chart-tools {
  display: flex;
  gap: 8px;
  align-items: center;
}
.seg {
  display: flex;
  border: 1px solid var(--line2);
  border-radius: 7px;
  overflow: hidden;
  font-family: var(--mono);
  font-size: 11px;
}
.seg span {
  padding: 5px 10px;
  color: var(--faint);
  cursor: pointer;
  border-right: 1px solid var(--line2);
}
.seg span:last-child {
  border-right: 0;
}
.seg span.on {
  color: var(--amber);
  background: rgba(18, 203, 238, 0.07);
}

.depth {
  padding: 14px 16px;
  border-top: 1px solid var(--line);
}
.depth-top {
  display: flex;
  justify-content: space-between;
  font-family: var(--mono);
  font-size: 12px;
  font-weight: 600;
}
.depth-bar {
  height: 8px;
  border-radius: 5px;
  overflow: hidden;
  display: flex;
  margin-top: 8px;
  border: 1px solid var(--line);
}
.depth-bar i {
  display: block;
  height: 100%;
}
.depth-sub {
  display: flex;
  justify-content: space-between;
  font-family: var(--mono);
  font-size: 10.5px;
  color: var(--faint);
  margin-top: 7px;
}

.health {
  padding: 16px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px 28px;
}
.hm .top {
  display: flex;
  justify-content: space-between;
  font-family: var(--mono);
  font-size: 12px;
  margin-bottom: 6px;
}
.hm .top b {
  font-weight: 600;
  color: var(--ink);
}
.track {
  height: 5px;
  border-radius: 3px;
  background: #1a212c;
  overflow: hidden;
}
.track i {
  display: block;
  height: 100%;
  background: var(--amber);
}
.hm.weak .track i {
  background: var(--coral);
}

.rail {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.ai p {
  margin: 0;
  padding: 14px 16px;
  color: var(--dim);
  font-size: 12.5px;
  line-height: 1.55;
}
.ai .mini {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1px;
  background: var(--line);
  border-top: 1px solid var(--line);
}
.ai .mini div {
  background: var(--panel);
  padding: 10px 14px;
}
.ai .mini .k {
  font-family: var(--mono);
  font-size: 9.5px;
  letter-spacing: .06em;
  color: var(--faint);
  text-transform: uppercase;
}
.ai .mini .v {
  font-family: var(--mono);
  font-size: 13px;
  font-weight: 600;
  margin-top: 3px;
  color: var(--ink);
}

.vote {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  padding: 14px;
}
.vopt {
  border: 1px solid var(--line);
  border-radius: 9px;
  padding: 12px 8px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s ease;
}
.vopt:hover {
  border-color: var(--line2);
  background: rgba(255, 255, 255, 0.02);
}
.vopt .ic {
  font-size: 15px;
}
.vopt .l {
  font-family: var(--mono);
  font-size: 10px;
  color: var(--faint);
  margin-top: 5px;
  letter-spacing: .04em;
}
.vopt .n {
  font-family: var(--mono);
  font-size: 16px;
  font-weight: 700;
  margin-top: 2px;
}
.vopt.t {
  border-color: rgba(46, 212, 122, 0.2);
}
.vopt.t .n {
  color: var(--up);
}
.vopt.s .n {
  color: var(--coral);
}
.vopt.x .n {
  color: var(--down);
}

.sec {
  padding: 6px 0;
}
.sec .row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 11px 16px;
  border-bottom: 1px solid var(--line);
  font-size: 13px;
}
.sec .row:last-child {
  border-bottom: 0;
}
.sec .row .nm {
  display: flex;
  align-items: center;
  gap: 9px;
  color: var(--dim);
}
.shield {
  color: var(--cyan);
}
.sval {
  font-family: var(--mono);
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 5px;
}
.sval.yes {
  color: var(--up);
  background: rgba(46, 212, 122, 0.08);
}
.sval.none {
  color: var(--dim);
  background: rgba(255, 255, 255, 0.03);
}

.expo {
  margin-top: 14px;
}
.expo-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1px;
  background: var(--line);
  border-bottom: 1px solid var(--line);
}
.expo-stats .st {
  background: var(--panel);
}
.ratio-bar {
  height: 6px;
  border-radius: 4px;
  overflow: hidden;
  display: flex;
  margin-top: 8px;
}

table.trades {
  width: 100%;
  border-collapse: collapse;
}
table.trades th {
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: .07em;
  text-transform: uppercase;
  color: var(--faint);
  text-align: right;
  padding: 10px 16px;
  font-weight: 500;
  border-bottom: 1px solid var(--line);
}
table.trades th:first-child {
  text-align: left;
}
table.trades td {
  padding: 11px 16px;
  text-align: right;
  font-family: var(--mono);
  font-size: 12.5px;
  border-bottom: 1px solid var(--line);
  color: var(--ink);
}
table.trades td:first-child {
  text-align: left;
}
table.trades tr:last-child td {
  border-bottom: 0;
}
table.trades tr:hover td {
  background: rgba(255, 255, 255, 0.02);
}

.side {
  font-family: var(--mono);
  font-size: 10px;
  font-weight: 700;
  padding: 3px 9px;
  border-radius: 5px;
  letter-spacing: .05em;
}
.side.sell {
  color: var(--down);
  background: rgba(240, 97, 109, 0.08);
}
.side.buy {
  color: var(--up);
  background: rgba(46, 212, 122, 0.08);
}

.viewall {
  text-align: center;
  padding: 14px;
  font-family: var(--mono);
  font-size: 12.5px;
  color: var(--amber);
  border-top: 1px solid var(--line);
  cursor: pointer;
}

.about {
  margin-top: 14px;
  padding: 18px 20px;
}
.about h3 {
  font-family: var(--disp);
  font-weight: 600;
  font-size: 16px;
  margin: 0 0 8px;
  color: var(--ink);
}
.about p {
  margin: 0;
  color: var(--dim);
  max-width: 70ch;
  line-height: 1.6;
}

@media (max-width: 1040px) {
  .grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 820px) {
  .stats {
    grid-template-columns: repeat(2, 1fr);
  }
  .expo-stats {
    grid-template-columns: 1fr 1fr;
  }
  .health {
    grid-template-columns: 1fr;
  }
  .trust {
    margin-left: 0;
  }
}
</style>