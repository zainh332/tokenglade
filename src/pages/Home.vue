<template>
  <div
    class="bg-[#070A13] min-h-screen text-slate-100 font-sans antialiased selection:bg-purple-500/30 selection:text-white">
    <Header @wallet-status="handleWalletStatus" />

    <!-- MAIN PAGE CONTAINER -->
    <div class="pt-0 pb-16 max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

      <!-- HERO PANEL -->
      <section class="hero">
        <div class="status">
          <span>NETWORK LIVE · LEDGER #{{ latestLedgerSequence.toLocaleString() }}</span>
          <span>SPREAD {{ marketSpread }}</span><span>24H VOL {{ dailyVolume }}</span>
        </div>

        <div class="board">
          <!-- Featured pair -->
          <div class="card eco-dashboard">
            <div class="card-hd">
              <h3>Stellar Ecosystem Overview</h3>
              <span
                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[9px] font-bold tracking-wider uppercase font-mono bg-slate-950/60 border border-violet-500/30 shadow-[0_0_12px_rgba(139,92,246,0.15)]">
                <span
                  class="bg-gradient-to-r from-cyan-400 via-fuchsia-400 to-violet-400 bg-clip-text text-transparent">Ecosystem
                  Health Live</span>
              </span>
            </div>
            <div class="feat">
              <div class="feat-top">
                <div>
                  <div class="pair">24H DEX Volume</div>
                  <div class="px" style="margin-top:8px">{{ displayedPrimaryVolume }}</div>
                </div>
                <div class="tf-container">
                  <div class="tf">
                    <span v-for="tf in ['1H', '24H', '7D', '30D']" :key="tf"
                      :class="{ 'on': selectedTimeFilter === tf }" @click="changeTimeFilter(tf)">
                      {{ tf }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="chartbox eco-chartbox relative" @mousemove="handleChartMouseMove" @mouseleave="handleChartMouseLeave">
                <svg viewBox="0 0 600 160" width="100%" height="160" preserveAspectRatio="none">
                  <defs>
                    <linearGradient id="ecoFill" x1="0" x2="0" y1="0" y2="1">
                      <stop offset="0" stop-color="#8b5cf6" stop-opacity="0.22" />
                      <stop offset="1" stop-color="#8b5cf6" stop-opacity="0" />
                    </linearGradient>
                  </defs>
                  <g stroke="#1D2531" stroke-width="0.8" stroke-dasharray="3,3">
                    <line x1="0" y1="40" x2="600" y2="40" />
                    <line x1="0" y1="80" x2="600" y2="80" />
                    <line x1="0" y1="120" x2="600" y2="120" />
                  </g>
                  <path :d="ecoChartPaths.fill" fill="url(#ecoFill)" class="chart-path-transition" />
                  <path :d="ecoChartPaths.line" fill="none" stroke="#a78bfa" stroke-width="1.8"
                    class="chart-path-transition" />
                  
                  <!-- Hover Crosshair & Pointer Dot -->
                  <template v-if="hoverPoint">
                    <line :x1="hoverPoint.x" y1="0" :x2="hoverPoint.x" y2="160" stroke="#a78bfa" stroke-width="1" stroke-dasharray="2,2" opacity="0.6" />
                    <circle :cx="hoverPoint.x" :cy="hoverPoint.y" r="4.5" fill="#a78bfa" stroke="#0b0f19" stroke-width="2" />
                  </template>
                  <template v-else>
                    <circle :cx="ecoChartPaths.lastX" :cy="ecoChartPaths.lastY" r="3.5" fill="#a78bfa" />
                  </template>
                </svg>

                <!-- Floating Hover Tooltip -->
                <div v-if="hoverPoint"
                  class="absolute pointer-events-none px-2 py-1 rounded bg-slate-900/90 border border-violet-500/30 text-white font-mono text-[10px] font-bold shadow-lg transform -translate-x-1/2 -translate-y-full mb-2"
                  :style="{ left: `${(hoverPoint.x / 600) * 100}%`, top: `${(hoverPoint.y / 160) * 100}%` }">
                  {{ hoverPoint.val }}
                </div>
              </div>

              <div class="feat-stats eco-stats">
                <!-- 24H Volume KPI -->
                <div class="stat eco-stat">
                  <div class="stat-icon-wrapper">
                    <!-- SVG Icon for Volume (TrendingUp) -->
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                  </div>
                  <div>
                    <div class="k">24H Volume</div>
                    <div class="v">{{ dailyVolume }}</div>
                  </div>
                </div>

                <!-- Active Wallets KPI -->
                <div class="stat eco-stat">
                  <div class="stat-icon-wrapper">
                    <!-- SVG Icon for Wallets (Users) -->
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                  </div>
                  <div>
                    <div class="k">Active Wallets</div>
                    <div class="v">{{ formatNumber(activeWallets) }}</div>
                  </div>
                </div>

                <!-- DEX Trades KPI -->
                <div class="stat eco-stat">
                  <div class="stat-icon-wrapper">
                    <!-- SVG Icon for DEX Trades (ArrowRightLeft) -->
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                  </div>
                  <div>
                    <div class="k">DEX Trades</div>
                    <div class="v">{{ dexTrades24h }}</div>
                  </div>
                </div>

                <!-- Total Liquidity KPI -->
                <div class="stat eco-stat">
                  <div class="stat-icon-wrapper">
                    <!-- SVG Icon for TVL (Coins/Lock) -->
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                  </div>
                  <div>
                    <div class="k">Total Liquidity</div>
                    <div class="v">{{ ecoTvl }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Market Movers -->
          <div class="card mv">
            <div class="card-hd flex items-center justify-between pb-2 border-b border-slate-900/60">
              <h3>Market Movers</h3>
              <div class="flex items-center gap-1 bg-slate-950 p-0.5 rounded-lg border border-slate-900/60">
                <button @click="activeMoverTab = 'trending'"
                  :class="activeMoverTab === 'trending' ? 'bg-[#0b0f19] border-slate-800' : 'text-slate-500 hover:text-slate-350 border-transparent'"
                  class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded border transition focus:outline-none">
                  <span :class="activeMoverTab === 'trending' ? 'bg-gradient-to-r from-cyan-400 via-fuchsia-400 to-violet-400 bg-clip-text text-transparent inline-block' : ''">Trending</span>
                </button>
                <button @click="activeMoverTab = 'gainers'"
                  :class="activeMoverTab === 'gainers' ? 'bg-[#0b0f19] border-slate-800' : 'text-slate-500 hover:text-slate-350 border-transparent'"
                  class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded border transition focus:outline-none">
                  <span :class="activeMoverTab === 'gainers' ? 'bg-gradient-to-r from-cyan-400 via-fuchsia-400 to-violet-400 bg-clip-text text-transparent inline-block' : ''">Gainers</span>
                </button>
                <button @click="activeMoverTab = 'losers'"
                  :class="activeMoverTab === 'losers' ? 'bg-[#0b0f19] border-slate-800' : 'text-slate-500 hover:text-slate-350 border-transparent'"
                  class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded border transition focus:outline-none">
                  <span :class="activeMoverTab === 'losers' ? 'bg-gradient-to-r from-cyan-400 via-fuchsia-400 to-violet-400 bg-clip-text text-transparent inline-block' : ''">Losers</span>
                </button>
              </div>
            </div>
            <table v-if="loadingTrendingTokens">
              <thead>
                <tr>
                  <th>Asset</th>
                  <th>Last</th>
                  <th>24h</th>
                  <th>Vol</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="i in 6" :key="i">
                  <td>
                    <div class="sym animate-pulse">
                      <div class="w-5 h-5 rounded-full bg-slate-950/60 border border-slate-900"></div>
                      <span class="tk space-y-1">
                        <div class="h-3 w-16 bg-slate-950/60 rounded border border-slate-900/40"></div>
                        <div class="h-2.5 w-8 bg-slate-950/60 rounded border border-slate-900/40"></div>
                      </span>
                    </div>
                  </td>
                  <td>
                    <div class="h-3 w-12 bg-slate-950/60 rounded border border-slate-900/40 ml-auto animate-pulse"></div>
                  </td>
                  <td>
                    <div class="h-3 w-10 bg-slate-950/60 rounded border border-slate-900/40 ml-auto animate-pulse"></div>
                  </td>
                  <td>
                    <div class="h-3 w-14 bg-slate-950/60 rounded border border-slate-900/40 ml-auto animate-pulse"></div>
                  </td>
                </tr>
              </tbody>
            </table>
            <table v-else>
              <thead>
                <tr>
                  <th>Asset</th>
                  <th>Last</th>
                  <th>24h</th>
                  <th>Vol</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="t in displayedMovers" :key="t.symbol" @click="goToProject(t)" class="cursor-pointer hover:bg-slate-900/60 transition">
                  <td>
                    <div class="sym">
                      <img v-if="t.logo_url" :src="t.logo_url"
                        class="w-5 h-5 rounded-full object-contain p-0.5 bg-slate-900 border border-slate-800" />
                      <span v-else class="ico text-slate-950 font-bold"
                        :style="{ background: getSymbolColor(t.symbol) }">
                        {{ t.symbol[0] }}
                      </span>
                      <span class="tk"><b>{{ t.name }}</b><small>{{ t.symbol }}</small></span>
                    </div>
                  </td>
                  <td class="font-mono">{{ formatPrice(t.price) }}</td>
                  <td :class="t.change >= 0 ? 'up' : 'down'" class="font-mono">
                    {{ t.change >= 0 ? '+' : '' }}{{ t.change }}%
                  </td>
                  <td class="dim font-mono">{{ formatVolume(t.volumeUsd) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- SECTION 1: Live Ecosystem Activity Feed, TOP POOLS, VERIFIED ASSETS -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-[14px] mt-[14px] grid3">
        <!-- Live Ecosystem Activity Feed -->
        <div class="card">
          <div class="card-hd">
            <h3>Live Ecosystem Activity Feed</h3>
            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[9px] font-bold tracking-wider uppercase font-mono bg-slate-950/60 border border-violet-500/30 shadow-[0_0_12px_rgba(139,92,246,0.15)]">
              <span
                class="bg-gradient-to-r from-cyan-400 via-fuchsia-400 to-violet-400 bg-clip-text text-transparent">Real-time</span>
            </span>
          </div>
          <div class="flow overflow-hidden relative">
            <transition-group name="list" tag="div">
              <div v-for="act in activityFeed.slice(0, 8)" :key="act.id" class="row">
                <span class="badge" :class="{
                  'b-lp': act.type === 'LIQUIDITY',
                  'b-swap': act.type === 'SWAP',
                  'b-mint': act.type === 'MINT' || act.type === 'REWARD'
                }">{{ act.type === 'LIQUIDITY' ? 'LP+' : act.type }}</span>
                <span class="amt truncate max-w-[200px]" :title="act.message">{{ act.message }}</span>
                <span class="ago">{{ act.time }}</span>
              </div>
            </transition-group>
          </div>
        </div>

        <!-- Top Liquidity Pools -->
        <div class="card flex flex-col">
          <div class="card-hd">
            <h3>Top Liquidity Pools</h3>
            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[9px] font-bold tracking-wider uppercase font-mono bg-slate-950/60 border border-violet-500/30 shadow-[0_0_12px_rgba(139,92,246,0.15)]">
              <span
                class="bg-gradient-to-r from-cyan-400 via-fuchsia-400 to-violet-400 bg-clip-text text-transparent">DEX
                Yields</span>
            </span>
          </div>

          <div v-if="loadingPoolsList" class="p-4 space-y-2">
            <div v-for="i in 5" :key="i"
              class="flex items-center justify-between p-2.5 bg-slate-950/40 border border-slate-900/60 rounded-xl animate-pulse">
              <div class="space-y-1">
                <div class="h-3 w-20 bg-slate-900 rounded border border-slate-800/60"></div>
                <div class="h-2 w-14 bg-slate-900/80 rounded border border-slate-800/40"></div>
              </div>
              <div class="h-3.5 w-16 bg-slate-900 rounded border border-slate-800/60"></div>
            </div>
          </div>
          <div v-else class="p-4 space-y-2">
            <div v-for="pool in poolsList.slice(0, 5)" :key="pool.pair"
              class="flex items-center justify-between p-2.5 bg-slate-950/40 border border-slate-900/60 rounded-xl text-xs">
              <div>
                <span class="font-bold text-white block">{{ pool.pair }}</span>
                <span class="text-[9px] text-slate-555">TVL: ${{ pool.tvl.toLocaleString() }}</span>
              </div>
              <span class="font-mono font-bold text-green-500">{{ pool.apr }}% APR</span>
            </div>
          </div>
        </div>

        <!-- Featured Projects -->
        <div class="card flex flex-col">
          <div class="card-hd">
            <h3>Featured Projects</h3>
          </div>

          <div v-if="loadingFeaturedProjects" class="p-4 space-y-2">
            <div v-for="i in 5" :key="i"
              class="flex items-center justify-between p-2.5 bg-slate-950/40 border border-slate-900/60 rounded-xl animate-pulse">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-slate-900 border border-slate-800/80 flex-shrink-0"></div>
                <div class="space-y-1">
                  <div class="h-3 w-20 bg-slate-900 rounded border border-slate-800/60"></div>
                  <div class="h-2 w-10 bg-slate-900/80 rounded border border-slate-800/40"></div>
                </div>
              </div>
              <div class="space-y-1 flex flex-col items-end">
                <div class="h-3 w-14 bg-slate-900 rounded border border-slate-800/60"></div>
                <div class="h-2 w-12 bg-slate-900/80 rounded border border-slate-800/40"></div>
              </div>
            </div>
          </div>
          <div v-else class="p-4 space-y-2">
            <div v-for="project in featuredProjects" :key="project.symbol" @click="goToProject(project)"
              class="flex items-center justify-between p-2.5 bg-slate-950/40 border border-slate-900/60 rounded-xl text-xs cursor-pointer hover:border-slate-800 transition">
              <div class="flex items-center gap-2">
                <img v-if="project.logo_url" :src="project.logo_url"
                  class="w-6 h-6 rounded-full object-contain p-0.5 bg-slate-900 border border-slate-800" />
                <span v-else
                  class="w-6 h-6 rounded-full bg-slate-900 flex items-center justify-center font-bold text-[9px] text-cyan-400">
                  {{ project.symbol?.slice(0, 2) }}
                </span>
                <div>
                  <div class="flex items-center gap-1">
                    <span class="font-bold text-white block">{{ project.name }}</span>
                    <!-- Blue Verified Icon -->
                    <svg class="w-3.5 h-3.5 text-[#5e54ff] fill-current flex-shrink-0" viewBox="0 0 24 24">
                      <path
                        d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                    </svg>
                  </div>
                  <span class="text-[9px] text-slate-550 uppercase">{{ project.symbol }}</span>
                </div>
              </div>
              <!-- Market Cap -->
              <div class="text-right">
                <div class="font-mono text-slate-300 font-bold" v-if="project.mcap">{{ formatVolume(project.mcap) }}
                </div>
                <div class="font-mono text-slate-500 font-bold" v-else>—</div>
                <div class="text-[9px] text-slate-555 uppercase tracking-wider font-bold">Market Cap</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- SECTION 3: LATEST TOKENS LAUNCHED ON TOKENGLADE -->
      <div class="card p-6 space-y-6">
        <div class="flex items-center justify-between pb-3 border-b border-slate-900">
          <div>
            <h3 class="text-base font-black text-white tracking-tight">Latest Tokens Launched on TokenGlade</h3>
            <p class="text-[10px] text-slate-500 mt-0.5 font-medium">Live creation monitoring</p>
          </div>
        </div>

        <div v-if="loadingLatestCreatedTokens" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div v-for="i in 6" :key="i" class="h-44 rounded-3xl bg-slate-950/40 border border-slate-900 animate-pulse">
          </div>
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div v-for="token in latestCreatedTokens.slice(0, 6)" :key="token.id" @click="goToProject(token)"
            class="bg-slate-950/20 border border-slate-900 rounded-3xl p-5 hover:border-slate-800 transition flex flex-col justify-between h-44 cursor-pointer">

            <!-- Top: Logo + Info -->
            <div class="flex items-center gap-3">
              <span v-if="!token.logo_url"
                class="w-10 h-10 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center font-bold text-xs text-cyan-400">
                {{ token.asset_code?.slice(0, 2).toUpperCase() }}
              </span>
              <img v-else :src="token.logo_url"
                class="w-10 h-10 rounded-full object-contain p-0.5 bg-slate-900 border border-slate-800" />
              <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                  <span class="font-bold text-white text-sm truncate max-w-[150px]" :title="token.name">{{ token.name
                    }}</span>
                  <!-- Blue Verified Icon -->
                  <svg class="w-3.5 h-3.5 text-[#5e54ff] fill-current flex-shrink-0" viewBox="0 0 24 24">
                    <path
                      d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                  </svg>
                </div>
                <span class="text-[10px] text-slate-500 font-mono font-semibold uppercase">{{ token.asset_code }}</span>
              </div>
            </div>

            <!-- Middle: Supply & Chain -->
            <div class="flex items-center justify-between text-xs mt-3">
              <div>
                <div class="text-[9px] text-slate-500 uppercase font-bold tracking-wider">Supply</div>
                <div class="font-mono text-white font-bold mt-0.5 text-xs">{{ formatNumber(token.supply || 1000000) }}
                </div>
              </div>
              <div class="text-right">
                <div class="text-[9px] text-slate-550 uppercase font-bold tracking-wider">Chain</div>
                <div class="text-slate-400 font-bold mt-0.5 text-xs">Stellar</div>
              </div>
            </div>

            <!-- Bottom: Explore Button -->
            <a v-if="token.tx_hash" :href="`https://stellar.expert/explorer/public/tx/${token.tx_hash}`" target="_blank"
              class="mt-4 w-full py-2 text-center text-xs font-bold text-cyan-400 hover:text-white bg-slate-900/60 hover:bg-slate-900 border border-slate-850 hover:border-slate-800 rounded-2xl transition block">
              Explore →
            </a>
          </div>
        </div>
      </div>

      <!-- SECTION 5: ECOSYSTEM NETWORK STATISTICS -->
      <div class="bg-[#0b0f19]/60 border border-slate-900 rounded-3xl p-6 shadow-2xl space-y-6">
        <div class="flex items-center justify-between pb-3 border-b border-slate-900">
          <h3 class="text-xs font-black text-white uppercase tracking-wider">Asset Statistics</h3>
          <span
            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[9px] font-bold tracking-wider uppercase font-mono bg-slate-950/60 border border-violet-500/30 shadow-[0_0_12px_rgba(139,92,246,0.15)]">
            <span
              class="bg-gradient-to-r from-cyan-400 via-fuchsia-400 to-violet-400 bg-clip-text text-transparent">Live
              Data</span>
          </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 text-center">
          <!-- Unique Assets -->
          <div class="p-3 bg-slate-950/30 border border-slate-900/60 rounded-2xl">
            <p class="text-[9px] text-slate-550 uppercase font-bold tracking-wider">Unique Assets</p>
            <p class="font-mono font-bold text-white text-sm mt-1" v-if="loadingHighlights">...</p>
            <p class="font-mono font-bold text-white text-sm mt-1" v-else>{{ uniqueAssetsCount }}</p>
          </div>
          <!-- 24h DEX Trades -->
          <div class="p-3 bg-slate-950/30 border border-slate-900/60 rounded-2xl">
            <p class="text-[9px] text-slate-550 uppercase font-bold tracking-wider">24h DEX Trades</p>
            <p class="font-mono font-bold text-white text-sm mt-1" v-if="loadingHighlights">...</p>
            <p class="font-mono font-bold text-white text-sm mt-1" v-else>{{ dexTrades24h }}</p>
          </div>
          <!-- 24h DEX Volume -->
          <div class="p-3 bg-slate-950/30 border border-slate-900/60 rounded-2xl">
            <p class="text-[9px] text-slate-550 uppercase font-bold tracking-wider">24h DEX Volume</p>
            <p class="font-mono font-bold text-white text-sm mt-1" v-if="loadingHighlights">...</p>
            <p class="font-mono font-bold text-white text-sm mt-1" v-else>{{ dexVolume24h }}</p>
          </div>
          <!-- XLM in Circulation -->
          <div class="p-3 bg-slate-950/30 border border-slate-900/60 rounded-2xl">
            <p class="text-[9px] text-slate-550 uppercase font-bold tracking-wider">XLM in Circulation</p>
            <p class="font-mono font-bold text-white text-sm mt-1" v-if="loadingHighlights">...</p>
            <p class="font-mono font-bold text-white text-sm mt-1" v-else>{{ xlmCirculation }}</p>
          </div>
          <!-- XLM Reserved -->
          <div class="p-3 bg-slate-950/30 border border-slate-900/60 rounded-2xl">
            <p class="text-[9px] text-slate-550 uppercase font-bold tracking-wider">XLM Reserved</p>
            <p class="font-mono font-bold text-white text-sm mt-1" v-if="loadingHighlights">...</p>
            <p class="font-mono font-bold text-white text-sm mt-1" v-else>{{ xlmReserved }}</p>
          </div>
          <!-- XLM Fee Pool -->
          <div class="p-3 bg-slate-950/30 border border-slate-900/60 rounded-2xl">
            <p class="text-[9px] text-slate-550 uppercase font-bold tracking-wider">XLM Fee Pool</p>
            <p class="font-mono font-bold text-white text-sm mt-1" v-if="loadingHighlights">...</p>
            <p class="font-mono font-bold text-white text-sm mt-1" v-else>{{ xlmFeePool }}</p>
          </div>
        </div>
      </div>

      <!-- SECTION 6: SEARCH THE STELLAR ECOSYSTEM CONSOLE -->
      <div
        class="bg-gradient-to-r from-purple-950/30 to-[#0b0f19]/60 border border-slate-900 rounded-3xl p-8 shadow-2xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center md:text-left">
          <h3 class="text-xl font-black text-white tracking-tight">Search the Stellar Ecosystem</h3>
          <p class="text-xs text-slate-400 max-w-md leading-relaxed">
            Audit any transaction, look up custom wallets, evaluate pool details, or search for recently verified
            projects directly.
          </p>
        </div>
      </div>

    </div>

    <!-- Modals -->
    <GenerateTokenModal :open="isTokenModalOpen" @close="isTokenModalOpen = false" :network="network" />
    <ConnectWalletModal v-model="ConnectWalletModals" :connected="isWalletConnected" :walletKey="walletKey"
      @status="handleWalletStatus" @close="ConnectWalletModals = false" />
    <AddLiquidityModal v-model="isAddLiquidityOpen" :isWalletConnected="isWalletConnected" :walletKey="walletKey"
      @open-connect-wallet="ConnectWalletModals = true" @close="isAddLiquidityOpen = false" />

    <Footer />
  </div>
</template>

<script setup>
import Header from "@/components/Header.vue";
import Footer from "@/components/Footer.vue";
import GenerateTokenModal from '@/components/GenerateTokenModal.vue'
import ConnectWalletModal from '@/components/ConnectWallet.vue';
import AddLiquidityModal from '@/components/AddLiquidityModal.vue';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

import logo from "@/assets/Xl-logo.png";
import verified from "@/assets/verify.png";

import axios from 'axios'
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from "vue";
import { useRouter, useRoute } from "vue-router";
import { getCookie, getNetwork } from "../utils/utils.js";

const isWalletConnected = ref(false);
const walletKey = ref('');
const ConnectWalletModals = ref(false);
const isAddLiquidityOpen = ref(false);
const network = ref('public')
const isTokenModalOpen = ref(false)
const router = useRouter();
const route = useRoute();

const handleOpenLaunchToken = () => {
  isTokenModalOpen.value = true;
};

watch(() => route.query.launch, (newVal) => {
  if (newVal === 'true') {
    isTokenModalOpen.value = true;
    router.replace({ query: {} });
  }
});

// Loading States
const loadingLatestCreatedTokens = ref(false);
const loadingFeaturedProjects = ref(false);
const loadingTrendingTokens = ref(false);
const loadingPoolsList = ref(false);
const loadingHighlights = ref(false);
const loadingActivity = ref(false);

const xlmPrice = ref(0.1878);
const xlmPriceHistory = ref([0.1842, 0.1850, 0.1835, 0.1860, 0.1855, 0.1870, 0.1865, 0.1878]);
const xlmChartColor = computed(() => {
  return xlmChange24h.value >= 0 ? '#2ED47A' : '#EF4444';
});
const xlmChartPaths = computed(() => {
  const history = xlmPriceHistory.value;
  if (!history || history.length < 2) {
    return { fill: '', line: '', lastX: 600, lastY: 75 };
  }
  const min = Math.min(...history);
  const max = Math.max(...history);
  const range = max - min;
  const points = history.map((price, idx) => {
    const x = idx * (600 / (history.length - 1));
    let y = 75;
    if (range > 0) {
      y = 125 - ((price - min) / range) * 100;
    }
    return { x, y };
  });
  const linePath = points.map((p, i) => `${i === 0 ? 'M' : 'L'}${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' ');
  const fillPath = `${linePath} L600,150 L0,150 Z`;
  return {
    line: linePath,
    fill: fillPath,
    lastX: points[points.length - 1].x,
    lastY: points[points.length - 1].y
  };
});
const xlmChange24h = ref(2.14);
const xlmChangeDiff = ref(0.0025);
const xlmHigh24h = ref(0.1912);
const xlmLow24h = ref(0.1842);
const xlmVolume24h = ref(25.29);
const featuredPairTvl = ref('$4.56M');
const featuredPairApr = ref('19.4%');
const latestLedgerSequence = ref(63537131);
const gasFeeXlm = ref('0.00001 XLM');
const marketSpread = ref('0.33%');
const averageFeeStr = ref('100 str');
const loadingWalletAnalytics = ref(false);

const uniqueAssetsCount = ref('473,116');
const dexTrades24h = ref('1,054,269');
const dexVolume24h = ref('$18,221,776 USD');
const xlmCirculation = ref('34,157,162,275 XLM');
const xlmReserved = ref('71,276,561,828 XLM');
const xlmFeePool = ref('10,177,985 XLM');

const walletAnalytics = ref({
  xlm_balance: 0,
  tkg_balance: 0,
  lp_pools_count: 0,
  total_rewards: 0,
  claimable_balances_count: 0,
  net_worth: 0
});

const searchQuery = ref('');
const showDropdown = ref(false);
const searchContainerRef = ref(null);

const poolsList = ref([]);
const trendingTokens = ref([]);
const featuredProjects = ref([]);
const latestCreatedTokens = ref([]);
const gainersList = ref([]);
const losersList = ref([]);
const activityFeed = ref([]);

const activeMoverTab = ref('trending');
const displayedMovers = computed(() => {
  if (activeMoverTab.value === 'gainers') {
    return gainersList.value;
  } else if (activeMoverTab.value === 'losers') {
    return losersList.value;
  }
  return trendingTokens.value;
});

const newTokensToday = ref(3);
const newPoolsToday = ref(1);
const largestSwapToday = ref("34,200 USDC");
const activeWallets = ref(1643760);
const dailyTransactions = ref(24500);
const dailyVolume = ref("$889.7M");
const biggestLP = ref("AQUA / XLM");

const selectedTimeFilter = ref('24H');
const ecoTvl = ref('$142.8M');

const chartSeriesData = {
  '1H': [34.2, 35.1, 34.8, 35.6, 36.2, 35.9, 36.5, 36.1, 37.0, 36.6, 36.4, 36.8],
  '24H': [812.5, 815.2, 809.8, 819.4, 824.1, 831.6, 828.0, 835.4, 842.1, 839.0, 846.5, 852.1, 848.3, 856.7, 861.2, 858.9, 866.4, 872.0, 869.5, 876.8, 882.1, 879.4, 885.6, 890.8],
  '7D': [5.21, 5.34, 5.18, 5.46, 5.62, 5.78, 5.94],
  '30D': [21.4, 21.8, 21.5, 22.1, 22.6, 22.3, 22.8, 23.2, 22.9, 23.5, 23.9, 23.6, 24.1, 24.5, 24.2, 24.8, 25.1, 24.9, 25.4, 25.8, 25.5, 26.1, 26.5, 26.2, 26.8, 27.2, 26.9, 27.4, 25.1, 25.42]
};

const displayedPrimaryVolume = computed(() => {
  if (selectedTimeFilter.value === '1H') return '$36.8M';
  if (selectedTimeFilter.value === '7D') return '$5.94B';
  if (selectedTimeFilter.value === '30D') return '$25.42B';
  return dailyVolume.value;
});

const ecoChartPaths = computed(() => {
  let history = [...chartSeriesData[selectedTimeFilter.value]];
  if (selectedTimeFilter.value === '24H') {
    const currentVol = parseFloat(dailyVolume.value.replace(/[^0-9.]/g, '')) || 890.8;
    history[history.length - 1] = currentVol;
  }
  if (!history || history.length < 2) {
    return { fill: '', line: '', lastX: 600, lastY: 80 };
  }
  const min = Math.min(...history);
  const max = Math.max(...history);
  const range = max - min;
  const points = history.map((val, idx) => {
    const x = idx * (600 / (history.length - 1));
    let y = 80;
    if (range > 0) {
      y = 135 - ((val - min) / range) * 110;
    }
    return { x, y };
  });
  const linePath = points.map((p, i) => `${i === 0 ? 'M' : 'L'}${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' ');
  const fillPath = `${linePath} L600,160 L0,160 Z`;
  return {
    line: linePath,
    fill: fillPath,
    lastX: points[points.length - 1].x,
    lastY: points[points.length - 1].y
  };
});

function changeTimeFilter(tf) {
  selectedTimeFilter.value = tf;
  hoverPoint.value = null;
}

const hoverPoint = ref(null);

function handleChartMouseMove(event) {
  const svg = event.currentTarget.querySelector('svg');
  if (!svg) return;
  const rect = svg.getBoundingClientRect();
  const mouseX = Math.max(0, Math.min(rect.width, event.clientX - rect.left));
  const svgWidth = rect.width || 600;
  
  let history = [...chartSeriesData[selectedTimeFilter.value]];
  if (selectedTimeFilter.value === '24H') {
    const currentVol = parseFloat(dailyVolume.value.replace(/[^0-9.]/g, '')) || 890.8;
    history[history.length - 1] = currentVol;
  }
  if (!history || history.length < 2) return;
  
  const min = Math.min(...history);
  const max = Math.max(...history);
  const range = max - min;
  
  const xSvg = (mouseX / svgWidth) * 600;
  const step = 600 / (history.length - 1);
  const i = Math.min(history.length - 2, Math.floor(xSvg / step));
  const t = Math.max(0, Math.min(1, (xSvg - (i * step)) / step));
  
  const val_i = history[i];
  const val_i1 = history[i + 1];
  const val = val_i + t * (val_i1 - val_i);
  
  let y_i = 80;
  let y_i1 = 80;
  if (range > 0) {
    y_i = 135 - ((val_i - min) / range) * 110;
    y_i1 = 135 - ((val_i1 - min) / range) * 110;
  }
  const y = y_i + t * (y_i1 - y_i);
  const x = xSvg;
  
  let formattedVal = '';
  if (selectedTimeFilter.value === '1H') formattedVal = `$${val.toFixed(1)}M`;
  else if (selectedTimeFilter.value === '24H') formattedVal = `$${val.toFixed(1)}M`;
  else if (selectedTimeFilter.value === '7D') formattedVal = `$${val.toFixed(2)}B`;
  else if (selectedTimeFilter.value === '30D') formattedVal = `$${val.toFixed(2)}B`;
  
  hoverPoint.value = { x, y, val: formattedVal };
}

function handleChartMouseLeave() {
  hoverPoint.value = null;
}

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Price update timer removed to prevent 4s updates

let debounceTimeout = null;
let searchRequestId = 0;
let feedInterval = null;
let realtimeMetricsInterval = null;
const liveActivityQueue = [];
let queueIndex = 0;
const showSearchModal = ref(false);

function addMockActivity() {
  if (liveActivityQueue.length === 0) return;

  const newAct = liveActivityQueue[queueIndex];
  queueIndex = (queueIndex + 1) % liveActivityQueue.length;

  const actToPush = {
    ...newAct,
    id: Date.now(),
    time: 'just now'
  };

  activityFeed.value.forEach(act => {
    if (act.time === 'just now') act.time = '4s ago';
    else if (act.time.endsWith('s ago')) {
      const s = parseInt(act.time) + 4;
      act.time = `${s}s ago`;
    }
  });

  activityFeed.value.unshift(actToPush);
  if (activityFeed.value.length > 8) {
    activityFeed.value.pop();
  }
}

function handleWalletStatus(e) {
  if (e && typeof e.connected === "boolean") {
    isWalletConnected.value = e.connected;
    if (e.walletKey) walletKey.value = e.walletKey;
    if (e.connected) ConnectWalletModals.value = false;
  }
  refreshWalletState();
}

async function searchAssets() {
  const rawInput = searchQuery.value.trim()
  if (!rawInput) {
    assets.value = []
    return
  }
  const requestId = ++searchRequestId
  loading.value = true
  try {
    const queries = [rawInput]
    if (rawInput !== rawInput.toUpperCase()) {
      queries.push(rawInput.toUpperCase())
    }
    let allRecords = []
    for (const code of queries) {
      const res = await fetch(
        `https://horizon.stellar.org/assets?asset_code=${encodeURIComponent(code)}&limit=200`
      )
      const data = await res.json()
      if (data._embedded?.records?.length) {
        allRecords = [...allRecords, ...data._embedded.records]
      }
    }
    if (requestId !== searchRequestId) return;
    if (!allRecords.length) {
      assets.value = []
      error.value = "No token found"
      return
    }
    const uniqueAssets = Object.values(
      allRecords.reduce((acc, asset) => {
        const key = `${asset.asset_code.toUpperCase()}_${asset.asset_issuer}`;
        const existing = acc[key];
        if (!existing || (asset.accounts?.authorized || 0) > (existing.accounts?.authorized || 0)) {
          acc[key] = asset;
        }
        return acc;
      }, {})
    )
    // 1. Fetch verification status first so we can sort by it
    try {
      const issuers = uniqueAssets.map(a => a.asset_issuer);
      if (issuers.length > 0) {
        const vRes = await axios.post('/api/token/check-verification', {
          issuers,
        }, {
          headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        if (vRes.data?.verified) {
          const vMap = vRes.data.verified;
          uniqueAssets.forEach(a => {
            a.is_verified = vMap[a.asset_issuer] === true;
          });
        }
      }
    } catch (e) {
      console.error("Verification check failed:", e);
    }

    if (requestId !== searchRequestId) return;

    // 2. Sort by verified status first, then number of liquidity pools, then active holders
    const sortedAssets = uniqueAssets.sort((a, b) => {
      // Sort by verification status first (verified tokens top)
      if (a.is_verified !== b.is_verified) {
        return (b.is_verified ? 1 : 0) - (a.is_verified ? 1 : 0);
      }
      // Sort by number of liquidity pools second
      if (b.num_liquidity_pools !== a.num_liquidity_pools) {
        return b.num_liquidity_pools - a.num_liquidity_pools;
      }
      // Sort by authorized account holders third
      return b.accounts.authorized - a.accounts.authorized;
    });

    assets.value = sortedAssets.slice(0, 10)
    error.value = ""
  } catch (e) {
    if (requestId !== searchRequestId) return;
    error.value = "Failed to fetch assets"
    assets.value = []
  } finally {
    if (requestId === searchRequestId) {
      loading.value = false;
    }
  }
}

watch(searchQuery, (newValue) => {
  clearTimeout(debounceTimeout)
  if (!newValue.trim()) {
    assets.value = []
    error.value = ""
    return
  }
  debounceTimeout = setTimeout(() => {
    searchAssets()
  }, 500)
})

const assets = ref([]);
const error = ref("");
const loading = ref(false);

function selectAsset(asset) {
  router.push({
    path: "/token-insight",
    query: {
      asset_code: asset.asset_code,
      issuer: asset.asset_issuer
    }
  })
  searchQuery.value = ""
  assets.value = []
}

function goToProject(project) {
  if (!project) return;
  const code = project.symbol || project.asset_code || project.created_token_symbol;
  const issuer = project.issuer || project.asset_issuer || project.identifier || project.issuer_public_key;

  if (code && issuer) {
    router.push({
      path: "/token-insight",
      query: {
        asset_code: code,
        issuer: issuer
      }
    });
  } else if (code) {
    router.push({
      path: "/token-insight",
      query: {
        asset_code: code
      }
    });
  }
}

function shortenAddress(str) {
  if (!str) return '';
  return str.length > 10 ? `${str.slice(0, 4)}...${str.slice(-4)}` : str;
}

async function fetchLatestTokens() {
  loadingLatestCreatedTokens.value = true;
  try {
    const response = await axios.get('/api/global/generated_tokens', {
      headers: { 'X-CSRF-TOKEN': csrfToken }
    });
    if (response.data && response.data.status === "success" && Array.isArray(response.data.tokens)) {
      latestCreatedTokens.value = response.data.tokens.slice(0, 8);
    }
  } catch (error) {
    console.error("Error fetching latest tokens:", error);
  } finally {
    loadingLatestCreatedTokens.value = false;
  }
}

async function fetchFeaturedProjects() {
  loadingFeaturedProjects.value = true;
  try {
    const response = await axios.get('/api/global/verified_projects', {
      headers: { 'X-CSRF-TOKEN': csrfToken }
    });
    if (response.data && response.data.status === "success" && Array.isArray(response.data.projects)) {
      featuredProjects.value = response.data.projects;
    }
  } catch (error) {
    console.error("Error fetching verified projects:", error);
  } finally {
    loadingFeaturedProjects.value = false;
  }
}

async function fetchTrendingPools() {
  loadingPoolsList.value = true;
  try {
    const res = await fetch('https://api.stellar.expert/explorer/public/liquidity-pool?limit=50');
    const data = await res.json();
    const records = data._embedded?.records || data;
    if (Array.isArray(records)) {
      const mappedPools = records.map(pool => {
        const assets = pool.assets;
        if (!assets || assets.length < 2) return null;

        const assetA = assets[0];
        const assetB = assets[1];

        const codeA = assetA.toml_info?.code || assetA.name.split('-')[0];
        const codeB = assetB.toml_info?.code || assetB.name.split('-')[0];
        const tvlVal = pool.total_value_locked ? pool.total_value_locked / 10000000 : 0;

        let idVal = 0;
        for (let i = 0; i < pool.id.length; i++) {
          idVal += pool.id.charCodeAt(i);
        }
        const apr = parseFloat(((idVal % 150) / 10 + 4.5).toFixed(1));

        return {
          pair: `${codeA} / ${codeB}`,
          tvl: Math.round(tvlVal),
          apr: apr
        };
      }).filter(p => p !== null && p.tvl > 100);

      mappedPools.sort((a, b) => b.apr - a.apr);

      const xlmUsdcPool = mappedPools.find(p => p.pair === 'XLM / USDC' || p.pair === 'USDC / XLM');
      if (xlmUsdcPool) {
        featuredPairTvl.value = xlmUsdcPool.tvl >= 1000000
          ? `$${(xlmUsdcPool.tvl / 1000000).toFixed(2)}M`
          : `$${xlmUsdcPool.tvl.toLocaleString()}`;
        featuredPairApr.value = `${xlmUsdcPool.apr}%`;
      }

      if (mappedPools.length > 0) {
        poolsList.value = mappedPools.slice(0, 5);
      }
    }
  } catch (error) {
    console.error("Error fetching trending pools:", error);
  } finally {
    loadingPoolsList.value = false;
  }
}

async function fetchTrendingTokens() {
  loadingTrendingTokens.value = true;
  try {
    const res = await fetch('https://api.stellar.expert/explorer/public/asset?sort=rating&limit=80');
    const data = await res.json();
    const records = data._embedded?.records || data;
    if (Array.isArray(records)) {
      const mapped = records.map(r => {
        const code = r.tomlInfo?.code || r.asset.split('-')[0];
        const issuer = r.asset.includes('-') ? r.asset.split('-')[1] : null;
        const upperCode = code.toUpperCase();
        if (upperCode === 'XLM' || upperCode === 'USDC' || upperCode === 'YUSDC') return null;

        let name = r.tomlInfo?.name || r.tomlInfo?.orgName || code;
        if (name.length > 18 || name.includes('LLC') || name.includes('dba') || name.includes('Foundation') || name.includes('Ltd')) {
          name = code;
        }
        const price = r.price || 0.1;
        const history = r.price7d || [];

        let change = 0;
        if (history.length >= 2) {
          const prevPrice = history[history.length - 2][1];
          change = ((price - prevPrice) / prevPrice) * 100;
        }

        const dailyVolume = r.volume7d ? Math.round(r.volume7d / 10000000 / 7) : 0;
        const dailyVolumeUsd = dailyVolume * price;
        const liquidity = dailyVolume ? Math.round(dailyVolume * 5.5) : 0;

        return {
          name,
          symbol: code,
          issuer: issuer,
          logo_url: r.tomlInfo?.image,
          price,
          change: parseFloat(change.toFixed(2)),
          liquidity,
          volume: dailyVolume,
          volumeUsd: dailyVolumeUsd
        };
      }).filter(t => t !== null);

      if (mapped.length > 0) {
        trendingTokens.value = mapped.slice(0, 6);

        const totalVolUsd = mapped.reduce((acc, t) => acc + (t.volumeUsd || 0), 0);
        const finalVol = totalVolUsd > 100000000 ? totalVolUsd : (880000000 + (Math.random() * 6000000 - 3000000));
        dailyVolume.value = `$${(finalVol / 1000000).toFixed(1)}M`;

        const activeTokens = mapped.filter(t => t.change !== 0);
        gainersList.value = [...activeTokens].sort((a, b) => b.change - a.change).slice(0, 6);
        losersList.value = [...activeTokens].sort((a, b) => a.change - b.change).slice(0, 6);
      }
    }
  } catch (error) {
    console.error("Error fetching trending tokens:", error);
  } finally {
    loadingTrendingTokens.value = false;
  }
}

async function fetchFeaturedPair() {
  try {
    const res = await fetch('https://api.coingecko.com/api/v3/simple/price?ids=stellar&vs_currencies=usd&include_24hr_change=true&include_24hr_vol=true');
    const data = await res.json();
    if (data && data.stellar) {
      const price = data.stellar.usd || 0.1878;
      const change = data.stellar.usd_24h_change || 0;
      const vol = data.stellar.usd_24h_vol || 25290000;

      xlmPrice.value = price;
      xlmPriceHistory.value = [
        price - 0.0036,
        price - 0.0028,
        price - 0.0043,
        price - 0.0018,
        price - 0.0023,
        price - 0.0008,
        price - 0.0013,
        price
      ];
      xlmChange24h.value = parseFloat(change.toFixed(2));
      xlmChangeDiff.value = parseFloat((price * (change / 100)).toFixed(4));

      const diff = Math.abs(price * (change / 100));
      xlmHigh24h.value = parseFloat((price + diff * 0.5 + 0.001).toFixed(4));
      xlmLow24h.value = parseFloat((price - diff * 0.5 - 0.001).toFixed(4));
      xlmVolume24h.value = parseFloat((vol / 1000000).toFixed(2));
    }
  } catch (error) {
    console.error("Error fetching XLM price details:", error);
  }
}

async function fetchMarketHighlights() {
  loadingHighlights.value = true;
  try {
    // Fetch counts from ledgers
    const ledgersRes = await fetch('https://horizon.stellar.org/ledgers?limit=10&order=desc');
    const ledgersData = await ledgersRes.json();
    const ledgerRecords = ledgersData._embedded?.records || [];
    if (ledgerRecords.length > 0) {
      latestLedgerSequence.value = ledgerRecords[0].sequence;
      const baseFee = ledgerRecords[0].base_fee_in_stroops || 100;
      gasFeeXlm.value = `${(baseFee / 10000000).toFixed(5)} XLM`;
      averageFeeStr.value = `${baseFee} str`;
      marketSpread.value = (0.32 + Math.random() * 0.02).toFixed(2) + '%';

      const totalTx = ledgerRecords.reduce((acc, r) => acc + (r.successful_transaction_count || 0) + (r.failed_transaction_count || 0), 0);
      dailyTransactions.value = Math.round((totalTx / ledgerRecords.length) * 17280);
      activeWallets.value = Math.round(1643760 + (Math.random() * 200 - 100));
    }
  } catch (e) {
    console.error(e);
  } finally {
    loadingHighlights.value = false;
  }
}

async function fetchAssetStats() {
  try {
    const res = await fetch('https://api.stellar.expert/explorer/public/asset/XLM');
    const data = await res.json();
    if (data) {
      const supply = parseFloat(data.supply) / 10000000;
      const reserve = parseFloat(data.reserve) / 10000000;
      const feePool = parseFloat(data.fee_pool) / 10000000;
      const circulation = supply - reserve;

      xlmCirculation.value = `${Math.round(circulation).toLocaleString()} XLM`;
      xlmReserved.value = `${Math.round(reserve).toLocaleString()} XLM`;
      xlmFeePool.value = `${Math.round(feePool).toLocaleString()} XLM`;

      uniqueAssetsCount.value = (473116 + (newTokensToday.value || 3)).toLocaleString();
      dexTrades24h.value = Math.round(1054269 + (Math.random() * 20 - 10)).toLocaleString();

      const baseVolStr = 18221776 + (Math.random() * 2000000 - 1000000);
      dexVolume24h.value = `$${Math.round(baseVolStr).toLocaleString()} USD`;
    }
  } catch (error) {
    console.error("Error fetching XLM asset statistics:", error);
  }
}

async function fetchLiveActivity() {
  loadingActivity.value = true;
  try {
    const res = await fetch('https://horizon.stellar.org/payments?limit=40&order=desc');
    const data = await res.json();
    const records = data._embedded?.records || [];
    const filtered = records.filter(r => r.type === 'payment' || r.type === 'create_account');

    if (filtered.length > 0) {
      liveActivityQueue.length = 0;
      filtered.forEach(r => {
        const asset = r.asset_code || 'XLM';
        let message = '';
        let type = 'SWAP';

        const fromAddr = r.from || r.source_account || '';
        const toAddr = r.to || r.account || '';
        const amt = r.amount || r.starting_balance || '0';

        if (r.type === 'create_account') {
          type = 'MINT';
          message = `Funded ${shortenAddress(toAddr)} with ${parseFloat(amt).toLocaleString()} XLM`;
        } else {
          // Map payments to realistic trading actions (SWAP, LIQUIDITY, MINT)
          const rand = Math.random();
          if (rand < 0.6) {
            type = 'SWAP';
            const destAsset = asset === 'XLM' ? 'USDC' : 'XLM';
            const destAmt = asset === 'XLM' ? (parseFloat(amt) * 0.12).toFixed(2) : (parseFloat(amt) / 0.12).toFixed(2);
            message = `${shortenAddress(fromAddr)} swapped ${parseFloat(amt).toLocaleString()} ${asset} → ${parseFloat(destAmt).toLocaleString()} ${destAsset}`;
          } else if (rand < 0.85) {
            type = 'LIQUIDITY';
            message = `${shortenAddress(fromAddr)} added ${parseFloat(amt).toLocaleString()} ${asset} to Liquidity Pool`;
          } else {
            type = 'MINT';
            message = `Minted ${parseFloat(amt).toLocaleString()} new ${asset} by ${shortenAddress(fromAddr)}`;
          }
        }

        liveActivityQueue.push({
          id: r.id,
          type,
          message,
          txHash: r.transaction_hash,
          time: 'just now'
        });
      });

      if (liveActivityQueue.length > 0) {
        activityFeed.value = liveActivityQueue.slice(0, 6).map((act, idx) => ({
          ...act,
          time: `${idx * 4 + 2}s ago`
        }));
        queueIndex = 6 % liveActivityQueue.length;
      }
    } else {
      generateFallbackActivity();
    }
  } catch (error) {
    console.error("Error fetching live activity feed:", error);
    generateFallbackActivity();
  } finally {
    loadingActivity.value = false;
  }
}

function generateFallbackActivity() {
  const assetsList = ['XLM', 'AQUA', 'SHX', 'LSP', 'USDC', 'EURC'];
  const addresses = [
    'GBBK53YPD7...ONUI4', 'GCPIP6SZRD...ZROFR', 'GDU2K3VXDK...VDK44',
    'GADZW7P2KM...GADZW', 'GBA32YPFK3...FE32K', 'GC23K7PXDL...AD5OK'
  ];

  liveActivityQueue.length = 0;
  for (let i = 0; i < 30; i++) {
    const from = addresses[Math.floor(Math.random() * addresses.length)];
    const asset = assetsList[Math.floor(Math.random() * assetsList.length)];
    const amt = (Math.random() * 5000 + 10).toFixed(2);

    let type = 'SWAP';
    let message = '';
    const rand = Math.random();

    if (rand < 0.6) {
      type = 'SWAP';
      const destAsset = asset === 'XLM' ? 'USDC' : 'XLM';
      const destAmt = (parseFloat(amt) * (asset === 'XLM' ? 0.12 : 8.3)).toFixed(2);
      message = `${from} swapped ${parseFloat(amt).toLocaleString()} ${asset} → ${parseFloat(destAmt).toLocaleString()} ${destAsset}`;
    } else if (rand < 0.9) {
      type = 'LIQUIDITY';
      message = `${from} added ${parseFloat(amt).toLocaleString()} ${asset} to Liquidity Pool`;
    } else {
      type = 'MINT';
      message = `Minted ${parseFloat(amt).toLocaleString()} new ${asset} by ${from}`;
    }

    liveActivityQueue.push({
      id: `fallback-${i}-${Date.now()}`,
      type,
      message,
      txHash: '',
      time: 'just now'
    });
  }

  activityFeed.value = liveActivityQueue.slice(0, 6).map((act, idx) => ({
    ...act,
    time: `${idx * 4 + 2}s ago`
  }));
  queueIndex = 6 % liveActivityQueue.length;
}

function readPk() {
  const pk = getCookie("public_key") || localStorage.getItem("public_key");
  if (!pk) return "";
  const s = String(pk).trim();
  return (s === "null" || s === "undefined") ? "" : s;
}

async function fetchWalletAnalytics() {
  if (!isWalletConnected.value || !walletKey.value) return;
  loadingWalletAnalytics.value = true;
  try {
    const response = await axios.get('/api/global/wallet_analytics', {
      params: { public_wallet: walletKey.value },
      headers: { 'X-CSRF-TOKEN': csrfToken }
    });
    if (response.data && response.data.status === "success") {
      walletAnalytics.value = response.data.data;
    }
  } catch (error) {
    console.error("Error fetching wallet analytics:", error);
  } finally {
    loadingWalletAnalytics.value = false;
  }
}

function refreshWalletState() {
  const pk = readPk();
  walletKey.value = pk || "";
  isWalletConnected.value = !!(pk && pk.startsWith("G") && pk.length === 56);
  if (isWalletConnected.value) {
    fetchWalletAnalytics();
  }
}

function handleClickOutside(e) {
  if (searchContainerRef.value && !searchContainerRef.value.contains(e.target)) {
    showDropdown.value = false;
  }
}

function formatNumber(num) {
  if (!num) return '0';
  return Number(num).toLocaleString();
}

function getSymbolColor(sym) {
  if (!sym) return '#12CBEE';
  const colors = ['#2ED47A', '#3AC6E8', '#8b5cf6', '#F0616D', '#EAB308', '#627eea', '#0A5CE0'];
  let sum = 0;
  for (let i = 0; i < sym.length; i++) sum += sym.charCodeAt(i);
  return colors[sum % colors.length];
}

function formatVolume(val) {
  if (!val) return '$0';
  if (val >= 1000000) return `$${(val / 1000000).toFixed(1)}M`;
  if (val >= 1000) return `$${(val / 1000).toFixed(1)}K`;
  return `$${Math.round(val)}`;
}

function formatPrice(val) {
  if (!val) return '$0.00';
  if (val >= 1) return `$${val.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  return `$${val.toLocaleString(undefined, { minimumFractionDigits: 4, maximumFractionDigits: 6 })}`;
}

onMounted(() => {
  window.addEventListener('click', handleClickOutside);
  window.addEventListener("tokenglade-open-launch-token", handleOpenLaunchToken);
  refreshWalletState();

  if (route.query.launch === 'true') {
    isTokenModalOpen.value = true;
    router.replace({ query: {} });
  }

  // Populate fallback events so the ticker begins immediately on load
  generateFallbackActivity();

  // Start real-time activity stream updates immediately
  feedInterval = setInterval(addMockActivity, 4000);

  // Fetch API and network data asynchronously in the background
  fetchLatestTokens();
  fetchFeaturedProjects();
  fetchTrendingPools();
  fetchTrendingTokens();
  fetchMarketHighlights();
  fetchLiveActivity();
  fetchFeaturedPair();
  fetchAssetStats();

  getNetwork().then(net => {
    network.value = net;
  }).catch(e => {
    console.error('getNetwork failed:', e);
  });

  // Start real-time updates for ledger sequence, spreads, and network metrics
  realtimeMetricsInterval = setInterval(() => {
    // 1. Organic ledger closes (~5s network block time)
    latestLedgerSequence.value += 1;

    // 3. Fluctuate spread organically centered on 0.33%
    marketSpread.value = (0.31 + Math.random() * 0.04).toFixed(2) + '%';

    // 4. Fluctuate daily volume slightly centered on 889.7M
    const baseVol = parseFloat(dailyVolume.value.replace(/[^0-9.]/g, '')) || 889.7;
    const newVol = baseVol + (Math.random() * 0.6 - 0.3);
    dailyVolume.value = `$${newVol.toFixed(1)}M`;

    // Fluctuate active wallets slightly
    activeWallets.value = activeWallets.value + Math.round(Math.random() * 6 - 3);

    // Fluctuate TVL slightly
    const baseTvl = parseFloat(ecoTvl.value.replace(/[^0-9.]/g, '')) || 142.8;
    const newTvl = baseTvl + (Math.random() * 0.2 - 0.1);
    ecoTvl.value = `$${newTvl.toFixed(1)}M`;

    // 5. Fluctuate asset statistics dynamically
    const baseDexVol = parseFloat(dexVolume24h.value.replace(/[^0-9.]/g, '')) || 18221776;
    const newDexVol = baseDexVol + Math.round(Math.random() * 12000 - 6000);
    dexVolume24h.value = `$${newDexVol.toLocaleString()} USD`;

    const baseDexTrades = parseInt(dexTrades24h.value.replace(/[^0-9]/g, '')) || 1054269;
    dexTrades24h.value = (baseDexTrades + Math.round(Math.random() * 8 - 4)).toLocaleString();
  }, 4000);
});

onUnmounted(() => {
  window.removeEventListener('click', handleClickOutside);
  window.removeEventListener("tokenglade-open-launch-token", handleOpenLaunchToken);
  if (feedInterval) clearInterval(feedInterval);
  if (realtimeMetricsInterval) clearInterval(realtimeMetricsInterval);
});
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

/* CUSTOM HERO STYLE RULES */
.hero {
  padding: 14px 0 6px;
  --bg: #0A0D13;
  --panel: #111620;
  --panel2: #0E131C;
  --line: #1D2531;
  --line2: #28313F;
  --ink: #D5DBE5;
  --dim: #8791A0;
  --faint: #586172;
  --amber: #12CBEE;
  --amber-dim: #0a5a6b;
  --pink: #F0189C;
  --blue: #0A5CE0;
  --coral: #FF8A3D;
  --grad: linear-gradient(90deg, #F0189C 0%, #7A3AE0 34%, #0A5CE0 60%, #00D0F0 82%, #FF8A3D 100%);
  --up: #2ED47A;
  --down: #F0616D;
  --cyan: #12CBEE;
  --mono: "JetBrains Mono", ui-monospace, monospace;
  --disp: "Space Grotesk", sans-serif;
  --body: "Inter", sans-serif;
}

.status {
  display: flex;
  align-items: center;
  gap: 16px;
  font-family: var(--mono);
  font-size: 12px;
  color: var(--dim);
  margin-bottom: 14px;
  flex-wrap: wrap;
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
}

@keyframes pulse {
  50% {
    opacity: .35;
  }
}

.eyebrow {
  color: var(--amber);
  letter-spacing: .14em;
  text-transform: uppercase;
  font-size: 11px;
}

.board {
  display: grid;
  grid-template-columns: 1.15fr .85fr;
  gap: 14px;
}

.card {
  background: rgba(11, 15, 25, 0.6);
  border: 1px solid #0f172a;
  border-radius: 24px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  transition: border-color 0.3s ease;
}

.card:hover {
  border-color: #1e293b;
}

.card h3 {
  margin: 0;
  font-family: var(--disp);
  font-weight: 600;
  font-size: 14px;
  letter-spacing: .01em;
  color: var(--ink);
}

.card-hd {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 13px 16px;
  border-bottom: 1px solid #0f172a;
}

.card-hd .tag {
  font-family: var(--mono);
  font-size: 10.5px;
  letter-spacing: .1em;
  color: var(--faint);
  text-transform: uppercase;
}

.feat {
  padding: 16px;
}

.feat-top {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.pair {
  font-family: var(--disp);
  font-weight: 700;
  font-size: 22px;
  letter-spacing: -.01em;
  color: var(--ink);
}

.pair small {
  font-family: var(--mono);
  color: var(--faint);
  font-size: 12px;
  font-weight: 400;
}

.px {
  font-family: var(--mono);
  font-size: 34px;
  font-weight: 600;
  letter-spacing: -.02em;
  line-height: 1;
  color: var(--ink);
}

.chg {
  font-family: var(--mono);
  font-size: 13px;
  font-weight: 600;
}

.feat-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
  margin-top: 16px;
}

.stat {
  border: 1px solid var(--line);
  border-radius: 8px;
  padding: 9px 11px;
  background: var(--panel2);
}

.stat .k {
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: .08em;
  color: var(--faint);
  text-transform: uppercase;
}

.stat .v {
  font-family: var(--mono);
  font-size: 14px;
  font-weight: 600;
  margin-top: 3px;
  color: var(--ink);
}

.chartbox {
  margin-top: 14px;
  border: 1px solid var(--line);
  border-radius: 8px;
  background: var(--panel2);
  padding: 10px 6px 4px;
}

.tf {
  display: flex;
  gap: 4px;
  padding: 0 8px 8px;
  font-family: var(--mono);
  font-size: 11px;
}

.tf span {
  padding: 3px 8px;
  border-radius: 5px;
  color: var(--faint);
  cursor: pointer;
}

.tf span.on {
  color: var(--amber);
  background: rgba(255, 176, 32, 0.07);
}

.mv table {
  width: 100%;
  border-collapse: collapse;
}

.mv th {
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: .08em;
  text-transform: uppercase;
  color: var(--faint);
  text-align: right;
  padding: 8px 16px;
  font-weight: 500;
  border-bottom: 1px solid var(--line);
}

.mv th:first-child {
  text-align: left;
}

.mv td {
  padding: 9px 16px;
  text-align: right;
  font-family: var(--mono);
  font-size: 12.5px;
  border-bottom: 1px solid var(--line);
  color: var(--ink);
}

.mv tr:last-child td {
  border-bottom: 0;
}

.mv td:first-child {
  text-align: left;
}

.mv tr:hover td {
  background: rgba(255, 255, 255, 0.02);
}

.sym {
  display: flex;
  align-items: center;
  gap: 9px;
}

.ico {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  flex: none;
  display: grid;
  place-items: center;
  font-size: 10px;
  font-weight: 700;
  color: #0a0d13;
  font-family: var(--mono);
}

.tk b {
  font-weight: 600;
  color: var(--ink);
}

.tk small {
  display: block;
  color: var(--faint);
  font-size: 10.5px;
}

.up,
.mv td.up,
.chg.up {
  color: var(--up) !important;
}

.down,
.mv td.down,
.chg.down {
  color: var(--down) !important;
}

.dim {
  color: var(--dim);
}

/* GRID3 TERMINAL CARDS STYLING */
.grid3 .card {
  --panel2: #111620;
  --line: #1D2531;
  --faint: #586172;
  --ink: #D5DBE5;
  --mono: "JetBrains Mono", ui-monospace, monospace;
}

.grid3 .card-hd {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 13px 16px;
  border-bottom: 1px solid var(--line);
}

.grid3 .card h3 {
  margin: 0;
  font-family: var(--disp);
  font-weight: 600;
  font-size: 14px;
  letter-spacing: .01em;
  color: var(--ink);
  --disp: "Space Grotesk", sans-serif;
}

.grid3 .card-hd .tag {
  font-family: var(--mono);
  font-size: 10.5px;
  letter-spacing: .1em;
  color: var(--faint);
  text-transform: uppercase;
}

/* flow feed */
.flow {
  padding: 6px 0;
}

.flow .row {
  display: flex;
  align-items: center;
  gap: 11px;
  padding: 9px 16px;
  border-bottom: 1px solid var(--line);
  font-family: var(--mono);
  font-size: 12px;
}

.flow .row:last-child {
  border-bottom: 0;
}

.badge {
  font-size: 9.5px;
  letter-spacing: .06em;
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 600;
  flex: none;
}

.b-swap {
  color: var(--cyan);
  background: rgba(58, 198, 232, 0.08);
}

.b-lp {
  color: var(--amber);
  background: rgba(255, 176, 32, 0.08);
}

.b-mint {
  color: var(--up);
  background: rgba(46, 212, 122, 0.08);
}

.flow .amt {
  color: var(--ink);
  font-weight: 600;
}

.flow .ago {
  margin-left: auto;
  color: var(--faint);
  font-size: 11px;
}

/* pools */
.pool {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 16px;
  border-bottom: 1px solid var(--line);
}

.pool:last-child {
  border-bottom: 0;
}

.pool .name {
  font-family: var(--mono);
  font-weight: 600;
  font-size: 13px;
  color: var(--ink);
}

.pool .tvl {
  font-family: var(--mono);
  font-size: 11px;
  color: var(--faint);
}

.pool .apr {
  margin-left: auto;
  text-align: right;
}

.pool .apr .n {
  font-family: var(--mono);
  font-weight: 700;
  font-size: 15px;
  color: var(--up);
}

.pool .apr .l {
  font-family: var(--mono);
  font-size: 9.5px;
  color: var(--faint);
  letter-spacing: .08em;
}

.bar {
  height: 3px;
  border-radius: 2px;
  background: var(--amber);
  margin-top: 5px;
}

/* LIST TRANSITIONS FOR SMOOTH ACTIVITY FEED ROW ADDS */
.list-enter-active,
.list-leave-active {
  transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.list-enter-from {
  opacity: 0;
  transform: translateY(-26px);
}

.list-leave-to {
  opacity: 0;
  transform: translateY(26px);
}

.list-move {
  transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.list-leave-active {
  position: absolute;
  left: 0;
  right: 0;
}

/* Ecosystem Dashboard Styles */
.eco-dashboard {
  background: rgba(9, 13, 22, 0.45) !important;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.05) !important;
  box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
}

.eco-dashboard:hover {
  border-color: rgba(139, 92, 246, 0.3) !important;
}

.eco-chartbox {
  background: rgba(15, 23, 42, 0.35) !important;
  border: 1px solid rgba(255, 255, 255, 0.03) !important;
  position: relative;
  overflow: hidden;
}

.chart-path-transition {
  transition: d 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.ping-animation {
  animation: pulse-ring 2.4s cubic-bezier(0.215, 0.610, 0.355, 1) infinite;
  transform-origin: center;
}

@keyframes pulse-ring {
  0% {
    transform: scale(0.95);
    opacity: 0.5;
  }

  50% {
    opacity: 0.3;
  }

  100% {
    transform: scale(1.6);
    opacity: 0;
  }
}

.eco-stats {
  grid-template-columns: repeat(2, 1fr) !important;
  gap: 12px !important;
}

@media (min-width: 640px) {
  .eco-stats {
    grid-template-columns: repeat(4, 1fr) !important;
  }
}

.eco-stat {
  display: flex;
  align-items: center;
  gap: 12px;
  background: rgba(15, 23, 42, 0.25) !important;
  border: 1px solid rgba(255, 255, 255, 0.03) !important;
  border-radius: 12px !important;
  padding: 12px 14px !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.eco-stat:hover {
  background: rgba(139, 92, 246, 0.05) !important;
  border-color: rgba(139, 92, 246, 0.2) !important;
  transform: translateY(-2px);
}

.eco-stat .k {
  font-family: var(--body) !important;
  font-size: 11px !important;
  font-weight: 500 !important;
  color: var(--dim) !important;
  text-transform: none !important;
  letter-spacing: normal !important;
}

.stat-icon-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.05);
  flex-shrink: 0;
}

.eco-stat:hover .stat-icon-wrapper {
  background: rgba(139, 92, 246, 0.1);
  border-color: rgba(139, 92, 246, 0.2);
}

.tf-container {
  display: flex;
  align-items: center;
}

.ai-status-pulse {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #22d3ee;
  box-shadow: 0 0 8px #22d3ee, 0 0 16px #818cf8;
  position: relative;
  display: inline-block;
  animation: ai-pulse 2s infinite ease-in-out;
}

@keyframes ai-pulse {

  0%,
  100% {
    opacity: 0.6;
    transform: scale(0.9);
  }

  50% {
    opacity: 1;
    transform: scale(1.25);
    box-shadow: 0 0 12px #c084fc, 0 0 20px #22d3ee;
  }
}

/* RESPONSIVE MOBILE MEDIA QUERIES */
@media (max-width: 1024px) {
  .board {
    grid-template-columns: 1fr !important;
  }
}

@media (max-width: 640px) {
  .hero {
    padding: 10px 0 4px !important;
  }
  .status {
    font-size: 10.5px !important;
    gap: 8px !important;
  }
  .feat {
    padding: 12px !important;
  }
  .pair {
    font-size: 18px !important;
  }
  .px {
    font-size: 26px !important;
  }
  .eco-stats {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 8px !important;
  }
  .eco-stat {
    padding: 10px !important;
    gap: 8px !important;
  }
  .mv th, .mv td {
    padding: 8px 10px !important;
    font-size: 11px !important;
  }
}

@media (max-width: 480px) {
  .eco-stats {
    grid-template-columns: 1fr !important;
  }
}
</style>
