<template>
  <div class="asset-page-wrapper min-h-screen selection:bg-cyan-500/20 selection:text-white">
    <Header />

    <!-- MAIN CONTAINER -->
    <div class="wrap space-y-8 pb-24 sm:pb-32">

      <!-- SKELETON LOADER (Visible when loading is true) -->
      <div v-if="loading" class="space-y-8 animate-pulse mt-4">
        <!-- BREADCRUMB SKELETON -->
        <div class="h-4 w-48 bg-[#1D2531]/60 rounded"></div>

        <!-- ASSET HEADER CARD SKELETON -->
        <section class="card asset">
          <div class="ahead">
            <!-- Logo & Name Skeleton Container (Flex Row for Mobile side-by-side) -->
            <div class="flex items-start gap-3 sm:gap-4 flex-1 min-w-0">
              <!-- Icon Skeleton -->
              <div class="token-ico flex-none">
                <div class="w-full h-full bg-[#1D2531]/60 rounded-xl"></div>
              </div>

              <!-- Name and Issuer Skeleton -->
              <div class="name-col space-y-2 flex-1 min-w-0">
                <div class="flex items-center gap-3 flex-wrap">
                  <div class="h-7 w-40 bg-[#1D2531]/80 rounded-lg"></div>
                  <div class="h-5 w-12 bg-[#1D2531]/60 rounded-md"></div>
                  <div class="h-5 w-16 bg-[#1D2531]/60 rounded-md"></div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                  <div class="h-4 w-44 bg-[#1D2531]/60 rounded"></div>
                  <div class="h-5 w-20 bg-[#1D2531]/40 rounded-md"></div>
                </div>
              </div>
            </div>

            <!-- Trust Box Skeleton -->
            <div class="trust">
              <div class="space-y-1.5 text-right">
                <div class="h-3 w-16 bg-[#1D2531]/60 rounded ml-auto"></div>
                <div class="h-4 w-20 bg-[#1D2531]/80 rounded ml-auto"></div>
                <div class="h-2.5 w-24 bg-[#1D2531]/40 rounded ml-auto"></div>
              </div>
              <div class="w-14 h-14 rounded-full border-4 border-[#1D2531]/60 flex-none"></div>
            </div>
          </div>

          <!-- STAT ROW (PRIMARY ROW SKELETON) -->
          <div class="stats">
            <div v-for="i in 6" :key="i" class="st space-y-2">
              <div class="h-3 w-14 bg-[#1D2531]/60 rounded"></div>
              <div class="h-5 w-20 bg-[#1D2531]/80 rounded"></div>
              <div class="h-3 w-12 bg-[#1D2531]/40 rounded"></div>
            </div>
          </div>

          <!-- SECONDARY STRIP SKELETON -->
          <div
            class="mt-3.5 py-2.5 px-4 bg-[#0E131C] rounded-xl border border-[rgba(148,163,184,0.12)] flex flex-wrap items-center justify-between gap-3">
            <div v-for="i in 4" :key="i" class="h-3.5 w-32 bg-[#1D2531]/60 rounded"></div>
          </div>

          <!-- ACTIONS SKELETON -->
          <div class="acts">
            <div class="h-9 w-32 bg-[#1D2531]/80 rounded-lg"></div>
            <div class="h-9 w-40 bg-[#1D2531]/80 rounded-lg"></div>
            <div class="h-9 w-10 bg-[#1D2531]/60 rounded-lg"></div>
            <div class="h-9 w-10 bg-[#1D2531]/60 rounded-lg"></div>
          </div>
        </section>

        <!-- DASHBOARD GRID SKELETON -->
        <div class="main-grid">

          <!-- Left Column SKELETON -->
          <div style="display:flex;flex-direction:column;gap:14px">
            <!-- Chart Card Skeleton -->
            <div class="card p-6 space-y-4">
              <div class="flex justify-between items-center">
                <div class="h-5 bg-[#1D2531]/60 rounded w-36"></div>
                <div class="h-6 bg-[#1D2531]/60 rounded-lg w-40"></div>
              </div>
              <div class="h-[340px] bg-[#0E131C]/40 rounded-xl border border-[#1D2531]/40"></div>
            </div>

            <!-- Market Exposure Stats Skeleton -->
            <div class="card expo">
              <div class="expo-stats">
                <div v-for="i in 4" :key="i" class="st space-y-2">
                  <div class="h-3 w-16 bg-[#1D2531]/60 rounded"></div>
                  <div class="h-5 w-24 bg-[#1D2531]/80 rounded"></div>
                  <div class="h-3 w-12 bg-[#1D2531]/40 rounded"></div>
                </div>
              </div>
            </div>

            <!-- Order Book Skeleton -->
            <div class="card" style="padding: 20px;">
              <div class="card-hd">
                <div class="h-5 bg-[#1D2531]/60 rounded w-24"></div>
                <div class="h-4 bg-[#1D2531]/40 rounded w-16"></div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-4">
                <div class="space-y-4">
                  <div class="h-3.5 bg-[#1D2531]/60 rounded w-28"></div>
                  <div class="h-[200px] bg-[#0E131C]/40 rounded-xl border border-[#1D2531]/40"></div>
                </div>
                <div class="space-y-4">
                  <div class="h-3.5 bg-[#1D2531]/60 rounded w-28"></div>
                  <div class="h-[200px] bg-[#0E131C]/40 rounded-xl border border-[#1D2531]/40"></div>
                </div>
              </div>
            </div>

            <!-- Recent Trades Skeleton -->
            <div class="card expo">
              <div class="card-hd">
                <div class="h-5 bg-[#1D2531]/60 rounded w-28"></div>
                <div class="h-4 bg-[#1D2531]/40 rounded w-16"></div>
              </div>
              <div class="h-[200px] bg-[#0E131C]/40 rounded-xl border border-[#1D2531]/40 mt-4"></div>
            </div>
          </div>

          <!-- RIGHT COLUMN (SIDEBAR) SKELETON -->
          <aside class="rail">
            <!-- Market Insight Card Skeleton -->
            <div class="card ai p-4 space-y-4">
              <div class="flex justify-between items-center">
                <div class="h-5 bg-[#1D2531]/60 rounded w-28"></div>
                <div class="h-4 bg-[#1D2531]/40 rounded w-16"></div>
              </div>
              <div class="space-y-3">
                <div class="h-3 bg-[#1D2531]/60 rounded w-full"></div>
                <div class="h-2 bg-[#1D2531]/40 rounded w-1/2"></div>
              </div>
              <div class="h-20 bg-[#0E131C]/40 rounded-xl border border-[#1D2531]/40"></div>
              <div class="h-16 bg-[#0E131C]/40 rounded-xl border border-[#1D2531]/40"></div>
            </div>

            <!-- Whale Activity Card Skeleton -->
            <div class="card p-4 space-y-4">
              <div class="flex justify-between items-center">
                <div class="h-5 bg-[#1D2531]/60 rounded w-32"></div>
                <div class="h-4 bg-[#1D2531]/40 rounded w-16"></div>
              </div>
              <div class="space-y-3.5 bg-[#0E131C]/40 p-4 rounded-xl border border-[#1D2531]/40">
                <div v-for="i in 3" :key="i" class="space-y-2 pb-3 border-b border-[#1D2531]/20 last:border-b-0 last:pb-0">
                  <div class="flex justify-between">
                    <div class="h-3 bg-[#1D2531]/60 rounded w-36"></div>
                    <div class="h-3 bg-[#1D2531]/40 rounded w-8"></div>
                  </div>
                  <div class="flex justify-between">
                    <div class="h-3.5 bg-[#1D2531]/40 rounded w-20"></div>
                    <div class="h-2.5 bg-[#1D2531]/40 rounded w-12"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Voting Card Skeleton -->
            <div class="card p-4 space-y-4">
              <div class="h-5 bg-[#1D2531]/60 rounded w-36"></div>
              <div class="grid grid-cols-3 gap-2">
                <div v-for="i in 3" :key="i" class="h-10 bg-[#1D2531]/40 rounded-lg"></div>
              </div>
            </div>

            <!-- Security Parameters Skeleton -->
            <div class="card p-4 space-y-4">
              <div class="h-5 bg-[#1D2531]/60 rounded w-40"></div>
              <div class="space-y-3">
                <div v-for="i in 4" :key="i" class="flex justify-between items-center">
                  <div class="h-3 bg-[#1D2531]/40 rounded w-28"></div>
                  <div class="h-3.5 bg-[#1D2531]/60 rounded w-12"></div>
                </div>
              </div>
            </div>
          </aside>
        </div>
      </div>

      <!-- NOT FOUND STATE -->
      <div v-else-if="notFound"
        class="flex flex-col items-center justify-center text-center py-20 px-4 bg-[#111620] border border-[#1D2531] rounded-3xl mt-8">
        <div
          class="w-16 h-16 rounded-full bg-rose-500/10 flex items-center justify-center border border-rose-500/20 text-3xl mb-4">
          ⚠️</div>
        <h2 class="text-xl font-bold text-white tracking-tight font-display">Asset Not Found</h2>
        <p class="text-xs text-slate-400 mt-2 max-w-sm leading-relaxed">
          We couldn't retrieve on-chain ledger information for the requested issuer address on the Stellar network.
        </p>
        <router-link to="/"
          class="mt-6 px-6 py-2.5 bg-gradient-to-r from-purple-600 to-cyan-500 hover:opacity-95 text-xs font-extrabold uppercase tracking-wider text-white rounded-xl transition duration-150">
          Back to Homepage
        </router-link>
      </div>

      <!-- ACTUAL CONTENT (Visible when loading is false and notFound is false) -->
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
            <!-- Logo & Name Container (Flex Row for Mobile side-by-side) -->
            <div class="flex items-start gap-3 sm:gap-4 flex-1 min-w-0">
              <!-- Icon -->
              <div class="token-ico select-none flex-shrink-0">
                <img v-if="token.image && !imageError" :src="token.image" @error="imageError = true" class="w-full h-full object-cover rounded-xl" />
                <span v-else class="text-cyan-400 font-bold uppercase">{{ getTokenInitials(token.asset_code) }}</span>
              </div>

              <!-- Name and Issuer info -->
              <div class="name-col min-w-0">
                <div class="name-row">
                  <h1 class="truncate">{{ token.is_minted_on_tokenglade ? (token.name || token.project?.org_name ||
                    'Token Detail') : (token.project?.org_name || token.name || 'Token Detail') }}</h1>
                  <span class="chip sym uppercase">{{ token.asset_code }}</span>
                  <span v-if="isVerified" class="chip verified">✓ Verified</span>
                  <span v-else-if="isVerificationPending" class="chip"
                    style="color:var(--pink);border-color:rgba(240,24,156,0.25)">Pending</span>
                  <button v-else @click="verificationModal = true"
                    class="claim-btn select-none cursor-pointer transition">Claim & Verify Project</button>
                </div>

                <div class="issuer">
                  <span>Issuer: <a :href="stellarExpertAssetUrl" target="_blank" rel="noopener noreferrer"
                      class="mono">{{ shorten(token.issuer) }}</a></span>
                  <button @click="copyIssuer" class="btn dark select-none" style="padding:2px 8px;font-size:10.5px">
                    {{ copied ? 'Copied!' : 'Copy Address' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Trust box -->
            <div class="trust">
              <div class="lbl">
                <div class="k">Trust Score</div>
                <div class="v uppercase"
                  :class="token.rating?.average >= 8 ? 'up' : (token.rating?.average >= 5 ? 'dim' : 'down')">{{
                  healthLabel.text }} Risk</div>
              </div>
              <div class="gauge">
                <svg viewBox="0 0 56 56" width="56" height="56">
                  <circle cx="28" cy="28" r="24" fill="none" stroke="#1a212c" stroke-width="5" />
                  <circle cx="28" cy="28" r="24" fill="none"
                    :stroke="token.rating?.average >= 8 ? '#2ED47A' : (token.rating?.average >= 5 ? '#FF8A3D' : '#F0616D')"
                    stroke-width="5" stroke-linecap="round" :stroke-dasharray="150.8"
                    :stroke-dashoffset="150.8 - (150.8 * (token.rating?.average ?? 7.5)) / 10"
                    transform="rotate(-90 28 28)" />
                </svg>
                <b :class="token.rating?.average >= 8 ? 'up' : (token.rating?.average >= 5 ? 'dim' : 'down')">{{
                  token.rating?.average?.toFixed(1) ?? '7.5' }}</b>
              </div>
            </div>
          </div>

          <!-- STATS TIMEFRAME HEADER -->
          <div class="flex items-center justify-between mt-4 mb-2 px-1">
            <span class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-wider">Key Metrics</span>
            <div class="flex items-center gap-1 bg-[#0E131C] p-1 rounded-lg border border-[rgba(148,163,184,0.12)]">
              <button @click="changeStatsTimeframe('24h')"
                class="px-2.5 py-0.5 rounded text-[11px] font-mono font-bold transition-all"
                :class="selectedStatsTimeframe === '24h' ? 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30' : 'text-slate-400 hover:text-white'">
                24H Track
              </button>
              <button @click="changeStatsTimeframe('7d')"
                class="px-2.5 py-0.5 rounded text-[11px] font-mono font-bold transition-all"
                :class="selectedStatsTimeframe === '7d' ? 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30' : 'text-slate-400 hover:text-white'">
                7D Track
              </button>
            </div>
          </div>

          <!-- STAT ROW (PRIMARY ROW) -->
          <div class="stats">
            <!-- 1. Price -->
            <div class="st flex flex-col justify-between">
              <div class="k">Price</div>
              <div class="v font-mono">
                <template v-if="loading"><span
                    class="text-slate-500 text-xs font-normal animate-pulse">Loading...</span></template>
                <template v-else-if="token.xlm_price">{{ formatXlmPrice(token.xlm_price) }} XLM</template>
                <template v-else>0 XLM</template>
              </div>
              <div class="sub font-mono dim">
                <template v-if="loading"><span
                    class="text-slate-500 text-[10px] font-normal animate-pulse">Loading...</span></template>
                <template v-else-if="token.usd_price">≈ ${{ formatPrice(token.usd_price) }}</template>
                <template v-else>≈ $0</template>
              </div>
              <div class="sub font-mono font-semibold"
                :class="(historicalStats?.price_change_pct || 0) >= 0 ? 'up' : 'down'">
                <template v-if="loading || historicalStatsLoading"><span
                    class="text-slate-500 text-[10px] font-normal animate-pulse">Loading...</span></template>
                <template v-else-if="historicalStats">
                  {{ (historicalStats.price_change_pct || 0) >= 0 ? '▲' : '▼' }} {{ (historicalStats.price_change_pct ||
                    0) >= 0 ? '+' : '' }}{{ historicalStats.price_change_pct }}% ({{ selectedStatsTimeframe.toUpperCase()
                  }})
                </template>
                <template v-else-if="token.price_change_24h">
                  {{ token.price_change_24h >= 0 ? '▲' : '▼' }} {{ token.price_change_24h >= 0 ? '+' : '' }}{{
                    token.price_change_24h }}%
                </template>
                <template v-else>▲ +0%</template>
              </div>
            </div>

            <!-- 2. 24H Volume -->
            <div class="st flex flex-col justify-between">
              <div class="k">24H Volume</div>
              <div class="v font-mono">
                <template
                  v-if="token.volume_24h !== undefined ? token.volume_24h : token.liquidity_overview?.lp_volume_24h">
                  {{ formatNumber((token.volume_24h !== undefined ? token.volume_24h :
                    token.liquidity_overview?.lp_volume_24h) / xlmPriceInUsd) }} XLM
                </template>
                <template v-else-if="loading || liquidityLoading"><span
                    class="text-slate-500 text-xs font-normal animate-pulse">Loading...</span></template>
                <template v-else>0 XLM</template>
              </div>
              <div class="sub font-mono dim">
                <template
                  v-if="token.volume_24h !== undefined ? token.volume_24h : token.liquidity_overview?.lp_volume_24h">
                  ≈ ${{ formatNumber(token.volume_24h !== undefined ? token.volume_24h :
                    token.liquidity_overview?.lp_volume_24h) }}
                </template>
                <template v-else-if="loading || liquidityLoading"><span
                    class="text-slate-500 text-[10px] font-normal animate-pulse">Loading...</span></template>
                <template v-else>≈ $0</template>
              </div>
              <div class="sub font-mono font-semibold"
                :class="(historicalStats?.volume_change_pct || 0) >= 0 ? 'up' : 'down'">
                <template v-if="loading || historicalStatsLoading"><span
                    class="text-slate-500 text-[10px] font-normal animate-pulse">Loading...</span></template>
                <template v-else-if="historicalStats">
                  {{ (historicalStats.volume_change_pct || 0) >= 0 ? '▲' : '▼' }} {{ (historicalStats.volume_change_pct
                    || 0) >= 0 ? '+' : '' }}{{ historicalStats.volume_change_pct }}% ({{
                    selectedStatsTimeframe.toUpperCase() }})
                </template>
                <template v-else-if="token.price_change_24h">
                  {{ token.price_change_24h >= 0 ? '▲' : '▼' }} {{ token.price_change_24h >= 0 ? '+' : '' }}18%
                </template>
                <template v-else><span
                    class="text-slate-500 text-[10px] font-normal animate-pulse">Loading...</span></template>
              </div>
            </div>

            <!-- 3. Liquidity -->
            <div class="st flex flex-col justify-between">
              <div class="k">Liquidity</div>
              <div class="v font-mono">
                <template v-if="token.liquidity_overview?.total_tvl || token.liquidity_tvl">
                  {{ formatNumber((token.liquidity_overview?.total_tvl || token.liquidity_tvl) / xlmPriceInUsd) }} XLM
                </template>
                <template v-else-if="loading || liquidityLoading"><span
                    class="text-slate-500 text-xs font-normal animate-pulse">Loading...</span></template>
                <template v-else>0 XLM</template>
              </div>
              <div class="sub font-mono dim">
                <template v-if="token.liquidity_overview?.total_tvl || token.liquidity_tvl">
                  ≈ ${{ formatNumber(token.liquidity_overview?.total_tvl || token.liquidity_tvl) }}
                </template>
                <template v-else-if="loading || liquidityLoading"><span
                    class="text-slate-500 text-[10px] font-normal animate-pulse">Loading...</span></template>
                <template v-else>≈ $0</template>
              </div>
              <div class="sub font-mono font-semibold"
                :class="(historicalStats?.liquidity_change_pct || 0) >= 0 ? 'up' : 'down'">
                <template v-if="loading || historicalStatsLoading"><span
                    class="text-slate-500 text-[10px] font-normal animate-pulse">Loading...</span></template>
                <template v-else-if="historicalStats">
                  {{ (historicalStats.liquidity_change_pct || 0) >= 0 ? '▲' : '▼' }} {{ (historicalStats.liquidity_change_pct
                    || 0) >= 0 ? '+' : '' }}{{ historicalStats.liquidity_change_pct }}% ({{
                    selectedStatsTimeframe.toUpperCase() }})
                </template>
                <template v-else-if="token.price_change_24h">
                  {{ token.price_change_24h >= 0 ? '▲' : '▼' }} {{ token.price_change_24h >= 0 ? '+' : '' }}12%
                </template>
                <template v-else><span
                    class="text-slate-500 text-[10px] font-normal animate-pulse">Loading...</span></template>
              </div>
            </div>

            <!-- 4. Market Cap -->
            <div class="st flex flex-col justify-between">
              <div class="k">Market Cap</div>
              <div class="v font-mono">
                <template v-if="loading"><span
                    class="text-slate-500 text-xs font-normal animate-pulse">Loading...</span></template>
                <template v-else-if="token.usd_price && token.total_supply">${{ formatNumber((token.usd_price || 0) *
                  (token.total_supply || 0)) }}</template>
                <template v-else>$0</template>
              </div>
              <div class="sub dim">
                Fully Diluted
              </div>
              <div class="sub font-mono font-semibold"
                :class="(historicalStats?.market_cap_change_pct || 0) >= 0 ? 'up' : 'down'">
                <template v-if="loading || historicalStatsLoading"><span
                    class="text-slate-500 text-[10px] font-normal animate-pulse">Loading...</span></template>
                <template v-else-if="historicalStats">
                  {{ (historicalStats.market_cap_change_pct || 0) >= 0 ? '▲' : '▼' }} {{
                    (historicalStats.market_cap_change_pct || 0) >= 0 ? '+' : '' }}{{
                    historicalStats.market_cap_change_pct }}% ({{ selectedStatsTimeframe.toUpperCase() }})
                </template>
                <template v-else>▲ +0%</template>
              </div>
            </div>

            <!-- 5. Holders -->
            <div class="st flex flex-col justify-between">
              <div class="k">Holders</div>
              <div class="v font-mono">
                <template v-if="token.holders !== null && token.holders !== undefined && token.holders !== ''">{{ formatNumber(token.holders) }}</template>
                <template v-else-if="loading || holdersLoading"><span
                    class="text-slate-500 text-xs font-normal animate-pulse">Loading...</span></template>
                <template v-else>0</template>
              </div>
              <div class="sub font-mono font-semibold"
                :class="(historicalStats?.holders_change || 0) >= 0 ? 'up' : 'down'">
                <template v-if="loading || holdersLoading || historicalStatsLoading"><span
                    class="text-slate-500 text-[10px] font-normal animate-pulse">Loading...</span></template>
                <template v-else-if="historicalStats">
                  {{ (historicalStats.holders_change || 0) >= 0 ? '+' : '' }}{{
                    formatNumber(historicalStats.holders_change) }} ({{ selectedStatsTimeframe.toUpperCase() }})
                </template>
                <template v-else class="dim">Funded</template>
              </div>
            </div>

            <!-- 6. Trustlines -->
            <div class="st flex flex-col justify-between">
              <div class="k">Trustlines</div>
              <div class="v font-mono">
                <template v-if="token.trustlines !== null && token.trustlines !== undefined && token.trustlines !== ''">{{ formatNumber(token.trustlines || token.holders) }}</template>
                <template v-else-if="loading || holdersLoading"><span
                    class="text-slate-500 text-xs font-normal animate-pulse">Loading...</span></template>
                <template v-else>0</template>
              </div>
              <div class="sub font-mono font-semibold"
                :class="(historicalStats?.trustlines_change || 0) >= 0 ? 'up' : 'down'">
                <template v-if="loading || holdersLoading || historicalStatsLoading"><span
                    class="text-slate-500 text-[10px] font-normal animate-pulse">Loading...</span></template>
                <template v-else-if="historicalStats">
                  {{ (historicalStats.trustlines_change || 0) >= 0 ? '+' : '' }}{{
                    formatNumber(historicalStats.trustlines_change) }} ({{ selectedStatsTimeframe.toUpperCase() }})
                </template>
                <template v-else class="dim">Total</template>
              </div>
            </div>
          </div>

          <!-- COMPACT SECONDARY STRIP -->
          <div
            class="mt-3.5 py-2.5 px-4 bg-[#0E131C] rounded-xl border border-[rgba(148,163,184,0.12)] grid grid-cols-[1fr_auto] sm:flex sm:flex-wrap sm:items-center sm:justify-between gap-x-4 gap-y-3 text-xs font-mono">
            <!-- 1. Total Supply -->
            <div class="flex items-center gap-1.5">
              <span class="text-slate-400 font-medium">Total Supply:</span>
              <span class="text-white font-bold">
                <template v-if="token.total_supply">{{ formatCompactNumber(token.total_supply) }} {{ token.asset_code
                  }}</template>
                <template v-else><span
                    class="text-slate-500 text-xs font-normal animate-pulse">Loading...</span></template>
              </span>
            </div>
            <div class="hidden sm:block w-[1px] h-3.5 bg-slate-800/80"></div>

            <!-- 2. Created -->
            <div class="flex items-center gap-1.5">
              <span class="text-slate-400 font-medium">Created:</span>
              <span class="text-white font-bold">
                <template v-if="token.mint_date_human && token.mint_date_human !== '-'">{{ token.mint_date_human
                  }}</template>
                <template v-else-if="loading"><span
                    class="text-slate-500 text-xs font-normal animate-pulse">Loading...</span></template>
                <template v-else>May 2019</template>
              </span>
            </div>
            <div class="hidden sm:block w-[1px] h-3.5 bg-slate-800/80"></div>

            <!-- 3. Circulating Supply -->
            <div class="flex items-center gap-1.5">
              <span class="text-slate-400 font-medium">Circulating Supply:</span>
              <span class="text-white font-bold flex items-center gap-1">
                <template v-if="token.total_supply">{{ formatCompactNumber(token.circulating_supply ||
                  (token.total_supply * 0.425)) }} {{ token.asset_code }}</template>
                <template v-else><span
                    class="text-slate-500 text-xs font-normal animate-pulse">Loading...</span></template>
                <span v-if="!historicalStatsLoading && historicalStats?.circulating_supply_change_pct"
                  class="text-[10px] font-mono font-semibold"
                  :class="historicalStats.circulating_supply_change_pct >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                  ({{ historicalStats.circulating_supply_change_pct >= 0 ? '+' : '' }}{{
                    historicalStats.circulating_supply_change_pct }}%)
                </span>
              </span>
            </div>
            <div class="hidden sm:block w-[1px] h-3.5 bg-slate-800/80"></div>

            <!-- 4. Pools -->
            <div class="flex items-center gap-1.5">
              <span class="text-slate-400 font-medium">Pools:</span>
              <span class="text-white font-bold flex items-center gap-1">
                <template v-if="liquidityLoading"><span
                    class="text-slate-500 text-xs font-normal animate-pulse">Loading...</span></template>
                <template v-else>{{ token.liquidity_overview?.pools_count || token.num_liquidity_pools || 0
                  }}</template>
                <span v-if="!liquidityLoading && !historicalStatsLoading && historicalStats?.pools_change"
                  class="text-[10px] font-mono font-semibold"
                  :class="historicalStats.pools_change >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                  ({{ historicalStats.pools_change >= 0 ? '+' : '' }}{{ historicalStats.pools_change }})
                </span>
              </span>
            </div>
          </div>

          <!-- Actions -->
          <div class="acts">
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 w-full sm:w-auto">
              <a :href="scopulyTradeUrl" target="_blank" rel="noopener noreferrer"
                class="btn brand select-none inline-flex items-center gap-1.5">
                <ArrowRightLeft class="w-4 h-4" /> Trade Asset
              </a>
              <button @click="handleEstablishTrustline" :disabled="establishingTrustline"
                class="btn dark select-none inline-flex items-center gap-1.5 hover:border-cyan-500/50 transition">
                <Lock class="w-4 h-4 text-cyan-400" />
                <span v-if="establishingTrustline" class="animate-pulse">Establishing...</span>
                <span v-else>Establish Trustline</span>
              </button>
              <!--
              <button @click="shareModalOpen = true"
                class="btn dark select-none inline-flex items-center gap-1.5 hover:border-purple-500/50 transition text-purple-400">
                <Share2 class="w-4 h-4 text-purple-400" />
                <span>Share Token</span>
              </button>
              -->
            </div>
            <div class="flex items-center gap-2.5 w-full sm:w-auto mt-2 sm:mt-0 justify-center sm:justify-start flex-wrap">
              <!-- Website -->
              <a v-if="token.website" :href="token.website" target="_blank" title="Website" aria-label="Website"
                class="btn icon-btn select-none hover:text-white transition">
                <Globe class="w-4 h-4 text-slate-300" />
              </a>
              <!-- Documentation -->
              <a v-if="token.documentation" :href="token.documentation" target="_blank" title="Documentation" aria-label="Documentation"
                class="btn icon-btn select-none hover:text-white transition">
                <svg class="w-4 h-4 fill-none stroke-current stroke-2 text-slate-300" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
              </a>
              <!-- Whitepaper -->
              <a v-if="token.whitepaper" :href="token.whitepaper" target="_blank" title="Whitepaper" aria-label="Whitepaper"
                class="btn icon-btn select-none hover:text-white transition">
                <svg class="w-4 h-4 fill-none stroke-current stroke-2 text-slate-300" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
              </a>
              <!-- GitHub -->
              <a v-if="token.github" :href="token.github" target="_blank" title="GitHub" aria-label="GitHub"
                class="btn icon-btn select-none hover:text-white transition">
                <svg class="w-4 h-4 fill-current text-slate-300" viewBox="0 0 24 24">
                  <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12" />
                </svg>
              </a>
              <!-- Medium -->
              <a v-if="token.medium" :href="token.medium" target="_blank" title="Medium" aria-label="Medium"
                class="btn icon-btn select-none hover:text-white transition">
                <svg class="w-4 h-4 fill-current text-slate-300" viewBox="0 0 24 24">
                  <path d="M13.54 12a6.8 6.8 0 0 1-6.77 6.82A6.8 6.8 0 0 1 0 11.82 6.8 6.8 0 0 1 6.77 5 6.8 6.8 0 0 1 13.54 12zm7.42 0c0 3.54-1.51 6.42-3.38 6.42-1.87 0-3.39-2.88-3.39-6.42s1.52-6.42 3.39-6.42 3.38 2.88 3.38 6.42zm3.04 0c0 3.24-.31 5.86-.71 5.86s-.72-2.62-.72-5.86.32-5.86.72-5.86.71 2.62.71 5.86z" />
                </svg>
              </a>
              <!-- Twitter / X -->
              <a v-if="token.twitter" :href="token.twitter" target="_blank" title="X (Twitter)" aria-label="X (Twitter)"
                class="btn icon-btn select-none hover:text-white transition">
                <svg class="w-3.5 h-3.5 fill-current text-slate-300" viewBox="0 0 24 24">
                  <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                </svg>
              </a>
              <!-- Telegram -->
              <a v-if="token.telegram" :href="token.telegram" target="_blank" title="Telegram" aria-label="Telegram"
                class="btn icon-btn select-none hover:text-white transition">
                <svg class="w-4 h-4 fill-current text-slate-300" viewBox="0 0 24 24">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.12.02-1.96 1.25-5.54 3.67-.52.36-1 .53-1.42.52-.47-.01-1.37-.26-2.03-.48-.82-.27-1.47-.42-1.42-.88.03-.24.37-.49 1.02-.75 3.99-1.73 6.66-2.87 8.01-3.43 3.81-1.58 4.6-1.86 5.12-1.87.11 0 .37.03.54.17.14.12.18.28.2.45-.02.07-.02.16-.04.25z" />
                </svg>
              </a>
              <!-- Discord -->
              <a v-if="token.discord" :href="token.discord" target="_blank" title="Discord" aria-label="Discord"
                class="btn icon-btn select-none hover:text-white transition">
                <svg class="w-4 h-4 fill-current text-slate-300" viewBox="0 0 24 24">
                  <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.094 13.094 0 0 1-1.873-.894.077.077 0 0 1-.008-.128c.126-.093.252-.19.372-.287a.075.075 0 0 1 .077-.011c3.92 1.793 8.18 1.793 12.061 0a.073.073 0 0 1 .078.009c.12.099.246.195.373.289a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.894.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.156-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.156 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.156-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.156 2.418z" />
                </svg>
              </a>
              <!-- LinkedIn -->
              <a v-if="token.linkedin" :href="token.linkedin" target="_blank" title="LinkedIn" aria-label="LinkedIn"
                class="btn icon-btn select-none hover:text-white transition">
                <svg class="w-3.5 h-3.5 fill-current text-slate-300" viewBox="0 0 24 24">
                  <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.779-1.75-1.75s.784-1.75 1.75-1.75 1.75.779 1.75 1.75-.784 1.75-1.75 1.75zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                </svg>
              </a>
              <!-- Reddit -->
              <a v-if="token.reddit" :href="token.reddit" target="_blank" title="Reddit" aria-label="Reddit"
                class="btn icon-btn select-none hover:text-white transition">
                <svg class="w-4 h-4 fill-current text-slate-300" viewBox="0 0 24 24">
                  <path d="M24 11.5c0-1.65-1.35-3-3-3-.96 0-1.86.48-2.42 1.24-1.64-1-3.85-1.64-6.29-1.72l1.24-3.91 3.43.8c.08.97.89 1.73 1.88 1.73 1.02 0 1.84-.83 1.84-1.84s-.83-1.84-1.84-1.84c-.87 0-1.61.6-1.78 1.4L13.8 2.92c-.12-.07-.26-.08-.39-.03l-4.1 1.27c-.24.08-.4.3-.4.56v.04l-1.24 3.91c-2.44.08-4.65.72-6.29 1.72-.56-.76-1.46-1.24-2.42-1.24-1.65 0-3 1.35-3 3 0 1.2.7 2.23 1.72 2.73-.08.35-.12.73-.12 1.1 0 3.72 4.19 6.75 9.34 6.75s9.34-3.03 9.34-6.75c0-.37-.04-.75-.12-1.1 1.02-.5 1.72-1.53 1.72-2.73zm-18 2.22c-.62 0-1.12-.5-1.12-1.12s.5-1.12 1.12-1.12 1.12.5 1.12 1.12-.5 1.12-1.12 1.12zm11.36 4.14c-.66.66-1.88.72-2.36.72-.48 0-1.7-.06-2.36-.72-.11-.11-.11-.3 0-.41.11-.11.3-.11.41 0 .5.5.1.4.52.55.51.52.51.52.51 0-.11-.11-.11-.3 0-.41zm.76-3.02c-.62 0-1.12-.5-1.12-1.12s.5-1.12 1.12-1.12 1.12.5 1.12 1.12-.5 1.12-1.12 1.12z" />
                </svg>
              </a>
              <!-- YouTube -->
              <a v-if="token.youtube" :href="token.youtube" target="_blank" title="YouTube" aria-label="YouTube"
                class="btn icon-btn select-none hover:text-white transition">
                <svg class="w-4 h-4 fill-current text-slate-300" viewBox="0 0 24 24">
                  <path d="M23.498 6.163a3.003 3.003 0 0 0-2.11-2.11C19.518 3.545 12 3.545 12 3.545s-7.518 0-9.388.508a3.003 3.003 0 0 0-2.11 2.11C0 8.033 0 12 0 12s0 3.967.502 5.837a3.003 3.003 0 0 0 2.11 2.11c1.87.508 9.388.508 9.388.508s7.518 0 9.388-.508a3.003 3.003 0 0 0 2.11-2.11C24 15.967 24 12 24 12s0-3.967-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                </svg>
              </a>
            </div>
          </div>
        </section>

        <!-- TABS -->
        <nav class="tabs select-none">
          <a href="#" @click.prevent="switchTab('overview')" :class="{ on: activeTab === 'overview' }">Overview</a>
          <a href="#" @click.prevent="switchTab('holders')" :class="{ on: activeTab === 'holders' }">Holders</a>
          <a href="#" @click.prevent="switchTab('liquidity')" :class="{ on: activeTab === 'liquidity' }">Liquidity</a>
          <a href="#" @click.prevent="switchTab('about')" :class="{ on: activeTab === 'about' }">About</a>
        </nav>

        <!-- MAIN GRID -->
        <div class="main-grid">

          <!-- LEFT COLUMN: OVERVIEW -->
          <div style="display:flex;flex-direction:column;gap:14px" v-if="activeTab === 'overview'">
            <!-- Chart -->
            <div class="card">
              <div class="card-hd">
                <h3>{{ token.asset_code }}/XLM <span class="tag hidden sm:inline-block" style="margin-left:8px">{{
                    selectedChartType }} · {{ selectedTimeframe }}</span></h3>
                <div class="chart-tools select-none">
                  <div class="seg">
                    <span v-for="type in ['candlestick', 'line', 'area']" :key="type"
                      :class="{ on: selectedChartType === type }" @click="selectedChartType = type"
                      class="capitalize">{{ type
                      }}</span>
                  </div>
                  <div class="seg">
                    <span v-for="t in ['4H', '1D', '1W']" :key="t" :class="{ on: selectedTimeframe === t }"
                      @click="selectedTimeframe = t">{{ t }}</span>
                  </div>
                </div>
              </div>

              <div class="ohlc font-mono select-all">
                <span>O<b id="ohlc-open">—</b></span>
                <span>H<b id="ohlc-high" class="up">—</b></span>
                <span>L<b id="ohlc-low" class="down">—</b></span>
                <span>C<b id="ohlc-close">—</b></span>
                <span>Chg<b id="ohlc-change">—</b></span>
                <span>Vol<b id="ohlc-volume">—</b></span>
              </div>

              <!-- Interactive Chart Container -->
              <div class="relative w-full h-[340px] bg-transparent">
                <div ref="chartContainer" class="w-full h-full"></div>
              </div>
            </div>

            <!-- Market Exposure Stats -->
            <div class="card expo">
              <div class="card-hd" style="align-items: flex-start; flex-direction: column; gap: 4px;">
                <div class="flex justify-between items-center w-full">
                  <h3>Market Exposure</h3>
                  <span class="tag">On-Chain Activity</span>
                </div>
                <p class="text-[10px] text-slate-400 font-medium normal-case leading-relaxed"
                  style="margin: 2px 0 0 0;">
                  Live ratio and trade size are based on the last 30 DEX fills.
                </p>
              </div>
              <div class="expo-stats">
                <div class="st">
                  <div class="k">Buy / Sell Ratio</div>
                  <div class="v font-mono"
                    :class="buySellVolume.buyVol === 0 && buySellVolume.sellVol === 0 ? 'text-slate-400 font-semibold' : (buySellVolume.buyPercent >= 50 ? 'up font-bold' : 'down font-bold')">
                    <template v-if="loading"><span
                        class="text-slate-500 text-xs font-normal animate-pulse">Loading...</span></template>
                    <template v-else>{{ buySellRatioText }}</template>
                  </div>
                  <div class="sub dim font-mono">recent fills</div>
                </div>
                <div class="st">
                  <div class="k">Avg Trade Size</div>
                  <div class="v font-mono">
                    <template v-if="avgTradeSizeUsd !== null">${{ formatPrice2Deci(avgTradeSizeUsd) }}</template>
                    <template v-else-if="loading"><span
                        class="text-slate-500 text-xs font-normal animate-pulse">Loading...</span></template>
                    <template v-else>0</template>
                  </div>
                  <div class="sub dim font-mono">per fill</div>
                </div>
                <div class="st">
                  <div class="k">Trades count</div>
                  <div class="v font-mono">
                    <template v-if="token.activity?.total_trades !== undefined">{{
                      formatNumber(token.activity?.total_trades) }}</template>
                    <template v-else-if="loading"><span
                        class="text-slate-500 text-xs font-normal animate-pulse">Loading...</span></template>
                    <template v-else>0</template>
                  </div>
                  <div class="sub dim font-mono">executions</div>
                </div>
                <div class="st">
                  <div class="k">Payments Vol</div>
                  <div class="v font-mono">
                    <template v-if="token.activity?.payments_volume !== undefined">{{
                      formatNumber(token.activity?.payments_volume) }}</template>
                    <template v-else-if="loading"><span
                        class="text-slate-500 text-xs font-normal animate-pulse">Loading...</span></template>
                    <template v-else>0</template>
                  </div>
                  <div class="sub dim font-mono">lifetime</div>
                </div>
              </div>
            </div>

            <!-- Order Book (Buy/Sell) -->
            <div class="card">
              <div class="card-hd">
                <h3>Order Book</h3>
                <span class="tag">DEX Depth</span>
              </div>

              <div style="padding: 20px;">
                <div v-if="orderBook.loading" class="flex flex-col items-center justify-center py-12">
                  <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-cyan-500"></div>
                  <span class="text-xs text-slate-400 font-bold mt-2">Loading DEX order book...</span>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-8">
                  <!-- Bids (Buy Orders) -->
                  <div>
                    <div class="flex justify-between items-center mb-2 px-1">
                      <span class="text-xs font-bold text-emerald-400 font-mono">
                        BIDS ({{ orderBook.bids.length }}): {{ formatNumber(totalBidVolume) }} {{ token.asset_code }} /
                        {{
                        formatNumber(totalBidValue) }} XLM
                      </span>
                      <span class="text-[10px] text-slate-500 font-mono">Spread: {{ spreadPercent }}%</span>
                    </div>
                    <div class="overflow-x-auto overflow-y-auto max-h-[300px] pr-1">
                      <table class="trades select-all">
                        <thead>
                          <tr>
                            <th style="text-align: left; padding: 6px 12px;">Depth (XLM)</th>
                            <th style="text-align: right; padding: 6px 12px;">Total (XLM)</th>
                            <th style="text-align: right; padding: 6px 12px;">Size ({{ token.asset_code }})</th>
                            <th style="text-align: right; padding: 6px 12px; color: var(--up);">Price (XLM)</th>
                          </tr>
                        </thead>
                        <tbody v-if="orderBook.bids.length">
                          <tr v-for="(bid, index) in orderBook.bids" :key="'bid-' + index"
                            :style="{ background: 'linear-gradient(to left, rgba(46, 212, 122, 0.09) 0%, rgba(46, 212, 122, 0.09) ' + ((getBidDepth(index) / totalBidDepth) * 100) + '%, transparent ' + ((getBidDepth(index) / totalBidDepth) * 100) + '%)' }">
                            <td style="text-align: left; padding: 7px 12px;" class="dim">{{
                              formatNumber(getBidDepth(index)) }}
                            </td>
                            <td style="text-align: right; padding: 7px 12px;" class="dim">{{
                              formatPrice2Deci(bid.amount) }}
                            </td>
                            <td style="text-align: right; padding: 7px 12px;">{{ formatNumber(calculateBidSize(bid)) }}
                            </td>
                            <td style="text-align: right; padding: 7px 12px;" class="up font-bold">{{
                              parseFloat(bid.price).toFixed(6) }}</td>
                          </tr>
                        </tbody>
                        <tbody v-else>
                          <tr>
                            <td colspan="4" class="py-6 text-center text-xs font-medium" style="color:var(--faint)">No
                              buy bids
                              detected</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <!-- Asks (Sell Orders) -->
                  <div>
                    <div class="flex justify-between items-center mb-2 px-1">
                      <span class="text-xs font-bold text-rose-400 font-mono">
                        ASKS ({{ orderBook.asks.length }}): {{ formatNumber(totalAskVolume) }} {{ token.asset_code }} /
                        {{
                        formatNumber(totalAskValue) }} XLM
                      </span>
                    </div>
                    <div class="overflow-x-auto overflow-y-auto max-h-[300px] pr-1">
                      <table class="trades select-all">
                        <thead>
                          <tr>
                            <th style="text-align: left; padding: 6px 12px; color: var(--down);">Price (XLM)</th>
                            <th style="text-align: right; padding: 6px 12px;">Size ({{ token.asset_code }})</th>
                            <th style="text-align: right; padding: 6px 12px;">Total (XLM)</th>
                            <th style="text-align: right; padding: 6px 12px;">Depth ({{ token.asset_code }})</th>
                          </tr>
                        </thead>
                        <tbody v-if="orderBook.asks.length">
                          <tr v-for="(ask, index) in orderBook.asks" :key="'ask-' + index"
                            :style="{ background: 'linear-gradient(to right, rgba(240, 97, 109, 0.09) 0%, rgba(240, 97, 109, 0.09) ' + ((getAskDepth(index) / totalAskDepth) * 100) + '%, transparent ' + ((getAskDepth(index) / totalAskDepth) * 100) + '%)' }">
                            <td style="text-align: left; padding: 7px 12px;" class="down font-bold">{{
                              parseFloat(ask.price).toFixed(6) }}</td>
                            <td style="text-align: right; padding: 7px 12px;">{{ formatNumber(ask.amount) }}</td>
                            <td style="text-align: right; padding: 7px 12px;" class="dim">{{ formatPrice2Deci(ask.amount
                              *
                              ask.price) }}</td>
                            <td style="text-align: right; padding: 7px 12px;" class="dim">{{
                              formatNumber(getAskDepth(index)) }}
                            </td>
                          </tr>
                        </tbody>
                        <tbody v-else>
                          <tr>
                            <td colspan="4" class="py-6 text-center text-xs font-medium" style="color:var(--faint)">No
                              sell asks
                              detected</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recent Trades -->
            <div class="card expo">
              <div class="card-hd">
                <h3>Recent Trades</h3><span class="tag"><span class="dot"></span>Live</span>
              </div>

              <div class="overflow-x-auto overflow-y-auto max-h-[300px] pr-1">
                <table class="trades select-all">
                  <thead>
                    <tr>
                      <th>Side</th>
                      <th>Amount</th>
                      <th>Price</th>
                      <th>Value</th>
                      <th>Time</th>
                    </tr>
                  </thead>
                  <tbody v-if="token.transactions && token.transactions.length">
                    <tr v-for="(tx, i) in (token.transactions || []).slice(0, 30)" :key="i">
                      <td><span class="side" :class="tx.side === 'buy' ? 'buy' : 'sell'">{{ tx.side.toUpperCase()
                          }}</span></td>
                      <td>{{ formatPrice2Deci(tx.amount) }} {{ token.asset_code }}</td>
                      <td>{{ formatPrice(tx.price) }}</td>
                      <td class="dim">{{ formatPrice2Deci(tx.value) }} XLM</td>
                      <td class="dim">{{ tx.time }}</td>
                    </tr>
                  </tbody>
                  <tbody v-else>
                    <tr>
                      <td colspan="5" style="text-align:center;padding:20px;color:var(--faint)">No live trade logs
                        detected</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>


          </div>

          <!-- LEFT COLUMN: HOLDERS -->
          <div style="grid-column: 1 / -1; display:flex;flex-direction:column;gap:14px;margin-bottom:2px"
            v-if="activeTab === 'holders'">
            <div v-if="holdersLoading"
              class="flex flex-col items-center justify-center py-20 bg-[#111620] rounded-xl border border-[#1D2531]">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-500"></div>
              <span class="text-xs text-slate-400 font-bold mt-3">Loading holder distribution data...</span>
            </div>
            <template v-else>
              <section class="card asset" style="margin-top:0">
                <div class="flex justify-between items-center flex-wrap gap-4 mb-6">
                  <div>
                    <h2 class="text-xl font-bold text-white tracking-tight">Holder Distribution</h2>
                    <p class="text-xs text-slate-400 mt-1 font-medium">On-chain wallet analysis and supply concentration
                      metrics</p>
                  </div>
                  <div
                    class="text-xs font-bold text-slate-400 uppercase tracking-wider bg-[#0E131C] px-3 py-1.5 rounded-xl border border-[#1D2531] font-mono">
                    Total Holders: {{ formatNumber(token.holders || 1240) }}
                  </div>
                </div>

                <!-- Middle Grid: Donut Chart Card (Left 50%) + 4 Vertically Stacked Cards Container (Right 50%) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                  <!-- Left Half (50% Width): Donut Chart Card -->
                  <div
                    class="flex flex-row md:flex-col items-center gap-4 sm:gap-6 justify-center bg-[#0E131C] p-4 sm:p-6 rounded-2xl border border-[#1D2531] h-full">
                    <div
                      class="relative w-24 h-24 sm:w-32 sm:h-32 md:w-40 md:h-40 flex items-center justify-center flex-shrink-0">
                      <svg class="w-full h-full" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="40" stroke="#1d2531" stroke-width="12" fill="transparent" />
                        <circle cx="50" cy="50" r="40" stroke="#12CBEE" stroke-width="12" fill="transparent"
                          stroke-linecap="round" :stroke-dasharray="251.3"
                          :stroke-dashoffset="251.3 - (251.3 * parseFloat(top10Percentage)) / 100"
                          transform="rotate(-90 50 50)" />
                      </svg>
                      <div class="absolute flex flex-col items-center justify-center text-center">
                        <span class="text-lg sm:text-xl md:text-2xl font-black text-white">{{ top10Percentage }}%</span>
                        <span
                          class="text-[7px] sm:text-[8px] md:text-[9px] text-slate-400 font-extrabold uppercase tracking-wider mt-0.5">Top
                          10</span>
                      </div>
                    </div>

                    <div class="space-y-2 sm:space-y-3 text-[10px] sm:text-xs font-mono w-full flex-1">
                      <div
                        class="flex items-center justify-between gap-1.5 font-semibold bg-[#111620] p-2 sm:p-3 rounded-xl border border-[#1D2531]">
                        <div class="flex items-center gap-1.5">
                          <span class="w-2.5 h-2.5 rounded-full bg-[#12CBEE] flex-none"></span>
                          <span class="text-slate-400">Top 10 Wallets:</span>
                        </div>
                        <span class="text-white font-bold">{{ top10Percentage }}%</span>
                      </div>
                      <div
                        class="flex items-center justify-between gap-1.5 font-semibold bg-[#111620] p-2 sm:p-3 rounded-xl border border-[#1D2531]">
                        <div class="flex items-center gap-1.5">
                          <span class="w-2.5 h-2.5 rounded-full bg-[#1d2531] flex-none"></span>
                          <span class="text-slate-400">Others:</span>
                        </div>
                        <span class="text-white font-bold">{{ (100 - parseFloat(top10Percentage)).toFixed(2) }}%</span>
                      </div>
                    </div>
                  </div>

                  <!-- Right Half (50% Width): 4 Cards Stacked Vertically -->
                  <div class="flex flex-col gap-4">
                    <!-- 1. Healthy Distribution / Whale Concentration Warning -->
                    <div class="bg-[#0E131C] p-5 rounded-2xl border border-[#1D2531]">
                      <div v-if="parseFloat(top10Percentage) > 50"
                        class="flex gap-3 bg-rose-500/10 border border-rose-500/20 rounded-xl p-4 text-xs text-rose-400">
                        <span class="text-xl flex-shrink-0">⚠️</span>
                        <div>
                          <span
                            class="font-extrabold uppercase tracking-wider block text-[11px] text-rose-500 font-mono">Whale
                            Concentration Warning</span>
                          <span class="mt-1 block font-medium leading-relaxed">The top 10 wallets hold <strong
                              class="font-black text-white">{{ top10Percentage }}%</strong> of supply. This asset has
                            elevated whale concentration risk.</span>
                        </div>
                      </div>
                      <div v-else
                        class="flex gap-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-4 text-xs text-emerald-400">
                        <span class="text-xl flex-shrink-0">✓</span>
                        <div>
                          <span
                            class="font-extrabold uppercase tracking-wider block text-[11px] text-emerald-500 font-mono">Healthy
                            Distribution</span>
                          <span class="mt-1 block font-medium leading-relaxed">The top 10 wallets hold <strong
                              class="font-black text-white">{{ top10Percentage }}%</strong> of supply, representing
                            healthy decentralization across active holders.</span>
                        </div>
                      </div>
                    </div>

                    <!-- 2 & 3: Side-by-Side Stats Cards -->
                    <div class="grid grid-cols-2 gap-4">
                      <!-- 2. Average per Holder -->
                      <div class="bg-[#0E131C] p-4 rounded-2xl border border-[#1D2531] font-mono">
                        <span
                          class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block leading-snug">Average
                          per Holder</span>
                        <span class="text-sm sm:text-base font-black text-white mt-1 block truncate">
                          {{ formatNumber(averageTokensPerHolder) }} {{ token.asset_code }}
                        </span>
                      </div>

                      <!-- 3. New Holders -->
                      <div class="bg-[#0E131C] p-4 rounded-2xl border border-[#1D2531] font-mono">
                        <span
                          class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block leading-snug">New
                          Holders (24h/7d)</span>
                        <span class="text-sm sm:text-base font-black text-white mt-1 block">
                          +{{ holderGrowth.growth24h }} <span class="text-slate-500 font-medium text-xs">/</span> +{{
                          holderGrowth.growth7d }}
                        </span>
                      </div>
                    </div>

                    <!-- 4. Largest Non-Treasury Holder -->
                    <div v-if="biggestIndividualHolder"
                      class="bg-[#0E131C] p-4 rounded-2xl border border-[#1D2531] font-mono">
                      <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Largest
                        Non-Treasury Holder</span>
                      <div class="flex items-center justify-between text-xs mt-1.5">
                        <a :href="`https://stellar.expert/explorer/public/account/${biggestIndividualHolder.address}`"
                          target="_blank" rel="noopener noreferrer"
                          class="font-mono text-cyan-400 hover:text-cyan-300 font-bold">
                          {{ shorten(biggestIndividualHolder.address) }}
                        </a>
                        <span class="font-black text-white font-mono">
                          {{ formatNumber(biggestIndividualHolder.balance) }} {{ token.asset_code }} ({{
                          biggestIndividualHolder.percent }}%)
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- Grid: Top Wallets vs Project Wallets (Separate cards!) -->
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-2">
                <!-- Left Card: Top Wallets Table -->
                <div class="card">
                  <div class="card-hd">
                    <h3>Largest Non-Project Holders</h3>
                  </div>
                  <div style="padding: 20px;">
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
                          <tr v-for="(holder, index) in token.top_holders" :key="index"
                            class="hover:bg-white/2 transition">
                            <td class="font-bold text-white">#{{ index + 1 }}</td>
                            <td class="font-mono text-xs text-left">
                              <a :href="`https://stellar.expert/explorer/public/account/${holder.address}`"
                                target="_blank" rel="noopener noreferrer"
                                class="text-cyan-400 hover:text-cyan-300 transition font-semibold"
                                :title="holder.address">
                                {{ shorten(holder.address) }}
                              </a>
                            </td>
                            <td>
                              <div class="font-bold text-white font-mono">{{ formatNumber(holder.balance) }} {{
                                token.asset_code }}</div>
                              <div class="text-[10px] text-slate-500 font-semibold mt-0.5">{{
                                getHolderPercentage(holder.balance) }}% of supply</div>
                            </td>
                          </tr>
                        </tbody>
                        <tbody v-else>
                          <tr>
                            <td colspan="3" class="py-6 text-center text-sm font-medium" style="color:var(--faint)">No
                              holder data available</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

                <!-- Right Card: Official Project Wallets Table -->
                <div class="card">
                  <div class="card-hd">
                    <h3>Official Project Wallets</h3>
                  </div>
                  <div style="padding: 20px;">
                    <div class="overflow-x-auto border border-[#1D2531] rounded-xl bg-[#111620]">
                      <table class="trades">
                        <thead>
                          <tr>
                            <th style="text-align:left;width:60%">Wallet Name / Address</th>
                            <th style="width:40%">Holdings</th>
                          </tr>
                        </thead>
                        <tbody v-if="token.project_holders && token.project_holders.length">
                          <tr v-for="(holder, index) in token.project_holders" :key="index"
                            class="hover:bg-white/2 transition">
                            <td class="font-mono text-xs text-left">
                              <div class="font-bold text-white font-sans text-xs">
                                {{ holder.name || 'Project Reserve Wallet' }}
                              </div>
                              <a :href="`https://stellar.expert/explorer/public/account/${holder.address}`"
                                target="_blank" rel="noopener noreferrer"
                                class="text-cyan-400 hover:underline text-[10px] font-semibold mt-0.5 block"
                                :title="holder.address">
                                {{ shorten(holder.address) }}
                              </a>
                            </td>
                            <td>
                              <div class="font-bold text-white font-mono">{{ formatNumber(holder.balance) }} {{
                                token.asset_code }}</div>
                              <div class="text-[10px] text-slate-500 font-semibold mt-0.5">{{
                                getHolderPercentage(holder.balance) }}% of supply</div>
                            </td>
                          </tr>
                        </tbody>
                        <tbody v-else>
                          <tr>
                            <td colspan="2" class="py-6 text-center text-sm font-medium" style="color:var(--faint)">No
                              project custody wallets detected</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <!-- LEFT COLUMN: LIQUIDITY -->
          <div
            style="grid-column: 1 / -1; display:flex;flex-direction:column;gap:14px;margin-bottom:2px;min-width:0;width:100%"
            v-if="activeTab === 'liquidity'">
            <div v-if="liquidityLoading"
              class="flex flex-col items-center justify-center py-20 bg-[#111620] rounded-xl border border-[#1D2531]">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-500"></div>
              <span class="text-xs text-slate-400 font-bold mt-3">Loading on-chain AMM liquidity stats...</span>
            </div>
            <template v-else>
              <section class="card asset w-full max-w-full min-w-0" style="margin-top:0">
                <div class="flex justify-between items-center flex-wrap gap-4 mb-6">
                  <div>
                    <h2 class="text-xl font-bold text-white tracking-tight">Liquidity Overview</h2>
                    <p class="text-xs text-slate-400 mt-1 font-medium">Real-time analysis of automated market maker
                      (AMM) pools & depth</p>
                  </div>
                  <div
                    class="text-xs font-bold text-slate-400 uppercase tracking-wider bg-[#0E131C] px-3 py-1.5 rounded-xl border border-[#1D2531] font-mono">
                    Total TVL: ${{ formatNumber(token.liquidity_overview?.total_tvl || 0) }}
                  </div>
                </div>

                <!-- Metrics Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-6 font-mono">
                  <div class="bg-[#111620] p-3 sm:p-4 rounded-xl border border-[#1D2531]">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Total
                      TVL</span>
                    <span class="text-sm sm:text-lg font-black text-white mt-1 block">
                      ${{ formatNumber(token.liquidity_overview?.total_tvl) }}
                    </span>
                  </div>

                  <div class="bg-[#111620] p-3 sm:p-4 rounded-xl border border-[#1D2531]">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Active
                      Pools</span>
                    <span class="text-sm sm:text-lg font-black text-white mt-1 block">
                      {{ token.liquidity_overview?.pools_count || 0 }}
                    </span>
                  </div>

                  <div class="bg-[#111620] p-3 sm:p-4 rounded-xl border border-[#1D2531]">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Largest
                      Pool</span>
                    <span class="text-xs sm:text-sm font-black text-white mt-1 block truncate font-sans py-1"
                      :title="token.liquidity_overview?.largest_pool_name">
                      {{ token.liquidity_overview?.largest_pool_name || '-' }}
                    </span>
                    <span class="text-[10px] text-slate-500 font-bold block mt-0.5">
                      ${{ formatNumber(token.liquidity_overview?.largest_pool_tvl) }} TVL
                    </span>
                  </div>

                  <div class="bg-[#111620] p-3 sm:p-4 rounded-xl border border-[#1D2531]">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">24h LP
                      Volume</span>
                    <span class="text-sm sm:text-lg font-black text-white mt-1 block">
                      ${{ formatNumber(token.liquidity_overview?.lp_volume_24h) }}
                    </span>
                  </div>

                  <div class="bg-[#111620] p-3 sm:p-4 rounded-xl border border-[#1D2531]">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Average
                      APR</span>
                    <span class="text-sm sm:text-lg font-black text-emerald-400 mt-1 block">
                      {{ token.liquidity_overview?.avg_apr ? token.liquidity_overview.avg_apr.toFixed(2) : '0.00' }}%
                    </span>
                  </div>

                  <div class="bg-[#111620] p-3 sm:p-4 rounded-xl border border-[#1D2531]">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Depth
                      (±2%)</span>
                    <span class="text-sm sm:text-lg font-black text-white mt-1 block">
                      ${{ formatNumber(token.liquidity_overview?.depth_2pct) }}
                    </span>
                  </div>
                </div>
              </section>

              <!-- Top Pools Table Card (Separate!) -->
              <div class="card mt-2">
                <div class="card-hd">
                  <h3>Top Liquidity Pools</h3>
                </div>
                <div style="padding: 20px;">
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
                      <tbody v-if="token.liquidity_overview?.pools && token.liquidity_overview.pools.length"
                        class="font-mono">
                        <tr v-for="(pool, index) in token.liquidity_overview.pools" :key="index"
                          class="hover:bg-white/2 transition">
                          <td style="text-align:left" class="font-bold text-white">
                            <a :href="`https://stellar.expert/explorer/public/liquidity-pool/${pool.id}`"
                              target="_blank" rel="noopener noreferrer"
                              class="text-[#12CBEE] hover:underline transition font-semibold truncate block max-w-[120px] xs:max-w-[160px] sm:max-w-none">
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
                          <td colspan="4" class="py-6 text-center text-sm font-medium" style="color:var(--faint)">No
                            liquidity pool data detected on-chain</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <!-- LEFT COLUMN: ABOUT -->
          <div style="display:flex;flex-direction:column;gap:14px" v-if="activeTab === 'about'">
            <!-- SECTION 1: ABOUT PROJECT -->
            <div class="card">
              <div class="card-hd">
                <h3>About Project</h3>
                <span class="tag">Profile</span>
              </div>
              <div style="padding: 20px;" class="space-y-6">
                <!-- Description -->
                <div>
                  <h4 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-wider mb-2">Description</h4>
                  <p class="text-sm text-slate-300 leading-relaxed whitespace-pre-line">
                    {{ token.project_details?.full_description || token.project_details?.short_description || "This project has not yet provided an official description." }}
                  </p>
                </div>

                <!-- Mission -->
                <div v-if="token.project_details?.mission && token.project_details.mission.trim() !== ''">
                  <h4 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-wider mb-2">Mission</h4>
                  <p class="text-sm text-slate-300 leading-relaxed whitespace-pre-line">
                    {{ token.project_details.mission }}
                  </p>
                </div>
              </div>
            </div>

            <!-- SECTION 2: PROJECT INFORMATION -->
            <div class="card">
              <div class="card-hd">
                <h3>Project Information</h3>
                <span class="tag">Metadata</span>
              </div>
              <div style="padding: 20px;">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                  <!-- Category -->
                  <div class="flex flex-col">
                    <span class="text-[10px] font-mono font-bold text-slate-500 uppercase">Category</span>
                    <span class="text-sm text-slate-200 mt-1 font-semibold">{{ token.project_details?.category || 'General' }}</span>
                  </div>

                  <!-- Project Type -->
                  <div class="flex flex-col">
                    <span class="text-[10px] font-mono font-bold text-slate-500 uppercase">Project Type</span>
                    <span class="text-sm text-slate-200 mt-1 font-semibold">{{ token.project_details?.project_type || 'Stellar Asset' }}</span>
                  </div>

                  <!-- Launch Date -->
                  <div class="flex flex-col">
                    <span class="text-[10px] font-mono font-bold text-slate-500 uppercase">Launch Date</span>
                    <span class="text-sm text-slate-200 mt-1 font-semibold font-mono">{{ formatDate(token.project_details?.launch_date) }}</span>
                  </div>

                  <!-- Website -->
                  <div class="flex flex-col">
                    <span class="text-[10px] font-mono font-bold text-slate-500 uppercase">Official Website</span>
                    <a v-if="token.website" :href="token.website" target="_blank" class="text-sm text-cyan-400 hover:text-cyan-300 mt-1 font-semibold truncate hover:underline">
                      {{ simplifyUrl(token.website) }}
                    </a>
                    <span v-else class="text-sm text-slate-400 mt-1 font-semibold">—</span>
                  </div>

                  <!-- Official Email -->
                  <div class="flex flex-col" v-if="token.project_details?.verified_project?.email">
                    <span class="text-[10px] font-mono font-bold text-slate-500 uppercase">Official Email</span>
                    <a :href="'mailto:' + token.project_details.verified_project.email" class="text-sm text-cyan-400 hover:text-cyan-300 mt-1 font-semibold truncate hover:underline">
                      {{ token.project_details.verified_project.email }}
                    </a>
                  </div>

                  <!-- Verification Status -->
                  <div class="flex flex-col">
                    <span class="text-[10px] font-mono font-bold text-slate-500 uppercase">Verification Status</span>
                    <span class="text-sm mt-1 font-bold inline-flex items-center gap-1.5" :class="isVerified ? 'text-emerald-400' : 'text-slate-400'">
                      <span v-if="isVerified">✓ Verified Project</span>
                      <span v-else>Unverified</span>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- SECTION 3: OFFICIAL LINKS -->
            <div class="card" v-if="hasOfficialLinks">
              <div class="card-hd">
                <h3>Official Links</h3>
                <span class="tag">Resources</span>
              </div>
              <div style="padding: 20px;" class="flex flex-wrap gap-3">
                <a v-if="token.website" :href="token.website" target="_blank" class="btn dark select-none inline-flex items-center gap-2 px-4 py-2 hover:border-cyan-500/50 transition">
                  <Globe class="w-4 h-4 text-cyan-400" />
                  <span>Website</span>
                </a>
                <a v-if="token.documentation" :href="token.documentation" target="_blank" class="btn dark select-none inline-flex items-center gap-2 px-4 py-2 hover:border-cyan-500/50 transition">
                  <svg class="w-4 h-4 fill-none stroke-current stroke-2 text-cyan-400" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                  </svg>
                  <span>Documentation</span>
                </a>
                <a v-if="token.whitepaper" :href="token.whitepaper" target="_blank" class="btn dark select-none inline-flex items-center gap-2 px-4 py-2 hover:border-cyan-500/50 transition">
                  <svg class="w-4 h-4 fill-none stroke-current stroke-2 text-cyan-400" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                  </svg>
                  <span>Whitepaper</span>
                </a>
                <a v-if="token.github" :href="token.github" target="_blank" class="btn dark select-none inline-flex items-center gap-2 px-4 py-2 hover:border-cyan-500/50 transition">
                  <svg class="w-4 h-4 fill-current text-cyan-400" viewBox="0 0 24 24">
                    <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12" />
                  </svg>
                  <span>GitHub</span>
                </a>
                <a v-if="token.medium" :href="token.medium" target="_blank" class="btn dark select-none inline-flex items-center gap-2 px-4 py-2 hover:border-cyan-500/50 transition">
                  <svg class="w-4 h-4 fill-current text-cyan-400" viewBox="0 0 24 24">
                    <path d="M13.54 12a6.8 6.8 0 0 1-6.77 6.82A6.8 6.8 0 0 1 0 11.82 6.8 6.8 0 0 1 6.77 5 6.8 6.8 0 0 1 13.54 12zm7.42 0c0 3.54-1.51 6.42-3.38 6.42-1.87 0-3.39-2.88-3.39-6.42s1.52-6.42 3.39-6.42 3.38 2.88 3.38 6.42zm3.04 0c0 3.24-.31 5.86-.71 5.86s-.72-2.62-.72-5.86.32-5.86.72-5.86.71 2.62.71 5.86z" />
                  </svg>
                  <span>Medium / Blog</span>
                </a>
              </div>
            </div>

            <!-- SECTION 4: COMMUNITY -->
            <div class="card" v-if="hasCommunityLinks">
              <div class="card-hd">
                <h3>Official Community</h3>
                <span class="tag">Channels</span>
              </div>
              <div style="padding: 20px;" class="flex flex-wrap gap-3">
                <a v-if="token.twitter" :href="token.twitter" target="_blank" class="btn dark select-none inline-flex items-center gap-2 px-4 py-2 hover:border-cyan-500/50 transition">
                  <svg class="w-3.5 h-3.5 fill-current text-cyan-400" viewBox="0 0 24 24">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                  </svg>
                  <span>Twitter / X</span>
                </a>
                <a v-if="token.telegram" :href="token.telegram" target="_blank" class="btn dark select-none inline-flex items-center gap-2 px-4 py-2 hover:border-cyan-500/50 transition">
                  <svg class="w-4 h-4 fill-current text-cyan-400" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.12.02-1.96 1.25-5.54 3.67-.52.36-1 .53-1.42.52-.47-.01-1.37-.26-2.03-.48-.82-.27-1.47-.42-1.42-.88.03-.24.37-.49 1.02-.75 3.99-1.73 6.66-2.87 8.01-3.43 3.81-1.58 4.6-1.86 5.12-1.87.11 0 .37.03.54.17.14.12.18.28.2.45-.02.07-.02.16-.04.25z" />
                  </svg>
                  <span>Telegram</span>
                </a>
                <a v-if="token.discord" :href="token.discord" target="_blank" class="btn dark select-none inline-flex items-center gap-2 px-4 py-2 hover:border-cyan-500/50 transition">
                  <svg class="w-4 h-4 fill-current text-cyan-400" viewBox="0 0 24 24">
                    <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.094 13.094 0 0 1-1.873-.894.077.077 0 0 1-.008-.128c.126-.093.252-.19.372-.287a.075.075 0 0 1 .077-.011c3.92 1.793 8.18 1.793 12.061 0a.073.073 0 0 1 .078.009c.12.099.246.195.373.289a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.894.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.156-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.156 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.156-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.156 2.418z" />
                  </svg>
                  <span>Discord</span>
                </a>
                <a v-if="token.linkedin" :href="token.linkedin" target="_blank" class="btn dark select-none inline-flex items-center gap-2 px-4 py-2 hover:border-cyan-500/50 transition">
                  <svg class="w-3.5 h-3.5 fill-current text-cyan-400" viewBox="0 0 24 24">
                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.779-1.75-1.75s.784-1.75 1.75-1.75 1.75.779 1.75 1.75-.784 1.75-1.75 1.75zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                  </svg>
                  <span>LinkedIn</span>
                </a>
                <a v-if="token.reddit" :href="token.reddit" target="_blank" class="btn dark select-none inline-flex items-center gap-2 px-4 py-2 hover:border-cyan-500/50 transition">
                  <svg class="w-4 h-4 fill-current text-cyan-400" viewBox="0 0 24 24">
                    <path d="M24 11.5c0-1.65-1.35-3-3-3-.96 0-1.86.48-2.42 1.24-1.64-1-3.85-1.64-6.29-1.72l1.24-3.91 3.43.8c.08.97.89 1.73 1.88 1.73 1.02 0 1.84-.83 1.84-1.84s-.83-1.84-1.84-1.84c-.87 0-1.61.6-1.78 1.4L13.8 2.92c-.12-.07-.26-.08-.39-.03l-4.1 1.27c-.24.08-.4.3-.4.56v.04l-1.24 3.91c-2.44.08-4.65.72-6.29 1.72-.56-.76-1.46-1.24-2.42-1.24-1.65 0-3 1.35-3 3 0 1.2.7 2.23 1.72 2.73-.08.35-.12.73-.12 1.1 0 3.72 4.19 6.75 9.34 6.75s9.34-3.03 9.34-6.75c0-.37-.04-.75-.12-1.1 1.02-.5 1.72-1.53 1.72-2.73zm-18 2.22c-.62 0-1.12-.5-1.12-1.12s.5-1.12 1.12-1.12 1.12.5 1.12 1.12-.5 1.12-1.12 1.12zm11.36 4.14c-.66.66-1.88.72-2.36.72-.48 0-1.7-.06-2.36-.72-.11-.11-.11-.3 0-.41.11-.11.3-.11.41 0 .5.5.1.4.52.55.51.52.51.52.51 0-.11-.11-.11-.3 0-.41zm.76-3.02c-.62 0-1.12-.5-1.12-1.12s.5-1.12 1.12-1.12 1.12.5 1.12 1.12-.5 1.12-1.12 1.12z" />
                  </svg>
                  <span>Reddit</span>
                </a>
                <a v-if="token.youtube" :href="token.youtube" target="_blank" class="btn dark select-none inline-flex items-center gap-2 px-4 py-2 hover:border-cyan-500/50 transition">
                  <svg class="w-4 h-4 fill-current text-cyan-400" viewBox="0 0 24 24">
                    <path d="M23.498 6.163a3.003 3.003 0 0 0-2.11-2.11C19.518 3.545 12 3.545 12 3.545s-7.518 0-9.388.508a3.003 3.003 0 0 0-2.11 2.11C0 8.033 0 12 0 12s0 3.967.502 5.837a3.003 3.003 0 0 0 2.11 2.11c1.87.508 9.388.508 9.388.508s7.518 0 9.388-.508a3.003 3.003 0 0 0 2.11-2.11C24 15.967 24 12 24 12s0-3.967-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                  </svg>
                  <span>YouTube</span>
                </a>
              </div>
            </div>

            <!-- SECTION 5: SUPPORTED ECOSYSTEM (Optional) -->
            <div class="card" v-if="hasEcosystemInfo">
              <div class="card-hd">
                <h3>Supported Ecosystem</h3>
                <span class="tag">Network Integration</span>
              </div>
              <div style="padding: 20px;" class="space-y-6">
                <!-- Wallets -->
                <div v-if="token.project_details?.supported_wallets && token.project_details.supported_wallets.length">
                  <span class="text-[10px] font-mono font-bold text-slate-500 uppercase block mb-2">Supported Wallets</span>
                  <div class="flex flex-wrap gap-2">
                    <span v-for="wallet in token.project_details.supported_wallets" :key="wallet" class="chip sym uppercase bg-[#0e131c] border border-slate-700/60 text-slate-300">
                      {{ wallet }}
                    </span>
                  </div>
                </div>

                <!-- DEXs -->
                <div v-if="token.project_details?.supported_dexs && token.project_details.supported_dexs.length">
                  <span class="text-[10px] font-mono font-bold text-slate-500 uppercase block mb-2">Supported DEXs</span>
                  <div class="flex flex-wrap gap-2">
                    <span v-for="dex in token.project_details.supported_dexs" :key="dex" class="chip sym uppercase bg-[#0e131c] border border-slate-700/60 text-slate-300">
                      {{ dex }}
                    </span>
                  </div>
                </div>

                <!-- Use Cases -->
                <div v-if="token.project_details?.primary_use_cases && token.project_details.primary_use_cases.length">
                  <span class="text-[10px] font-mono font-bold text-slate-500 uppercase block mb-2">Primary Use Cases</span>
                  <div class="flex flex-wrap gap-2">
                    <span v-for="useCase in token.project_details.primary_use_cases" :key="useCase" class="chip sym uppercase bg-[#0e131c] border border-slate-700/60 text-slate-300">
                      {{ useCase }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- RIGHT COLUMN: SIDEBAR WIDGETS -->
          <aside class="rail" v-if="activeTab === 'overview'">
            <!-- Market Insight -->
            <div class="card ai select-text">
              <div class="card-hd">
                <h3>Market Insight</h3><span class="tag">AI Engine</span>
              </div>

              <!-- Score Bar -->
              <div class="px-4 pt-4 pb-2">
                <div class="flex justify-between items-center mb-1.5">
                  <span class="text-xs font-mono text-slate-400">Composite Score</span>
                  <span class="text-sm font-bold font-mono text-white">{{ token.rating?.average?.toFixed(1) ?? '7.5' }}
                    /
                    10</span>
                </div>
                <div class="track h-2 bg-[#1a212c] rounded-full overflow-hidden">
                  <i class="block h-full rounded-full transition-all duration-500"
                    :class="token.rating?.average >= 8 ? 'bg-[#2ED47A]' : (token.rating?.average >= 5 ? 'bg-[#FF8A3D]' : 'bg-[#F0616D]')"
                    :style="{ width: ((token.rating?.average ?? 7.5) * 10) + '%' }"></i>
                </div>
              </div>

              <!-- Signals Grid -->
              <div
                class="grid grid-cols-1 divide-y divide-slate-800/60 border-t border-b border-slate-800/60 bg-[#0E131C]">
                <!-- Bullish Signals -->
                <div class="p-4 space-y-2">
                  <span class="text-[10px] font-extrabold text-emerald-400 uppercase tracking-wider block font-mono">▲
                    Bullish
                    Signals</span>
                  <div v-if="loading || liquidityLoading" class="text-xs text-slate-500 animate-pulse">Analyzing...
                  </div>
                  <ul v-else class="text-xs space-y-1 text-slate-300">
                    <li v-for="sig in bullishSignals" :key="sig" class="flex items-start gap-1.5 leading-relaxed">
                      <span class="text-emerald-400 font-bold">✓</span>
                      <span>{{ sig }}</span>
                    </li>
                  </ul>
                </div>

                <!-- Bearish Signals -->
                <div class="p-4 space-y-2">
                  <span class="text-[10px] font-extrabold text-rose-400 uppercase tracking-wider block font-mono">▼
                    Bearish
                    Signals</span>
                  <div v-if="loading || liquidityLoading" class="text-xs text-slate-500 animate-pulse">Analyzing...
                  </div>
                  <ul v-else class="text-xs space-y-1 text-slate-300">
                    <li v-for="sig in bearishSignals" :key="sig" class="flex items-start gap-1.5 leading-relaxed">
                      <span class="text-rose-400 font-bold">⚠</span>
                      <span>{{ sig }}</span>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Key Metrics Grid -->
              <div class="p-4 space-y-2 border-b border-slate-800/60">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block font-mono">Key
                  Metrics</span>
                <div v-if="loading || liquidityLoading" class="text-xs text-slate-500 animate-pulse py-1">Loading
                  metrics...
                </div>
                <div v-else class="grid grid-cols-3 gap-2 text-xs">
                  <div class="flex flex-col gap-0.5">
                    <span class="text-slate-400">Whales:</span>
                    <span class="font-mono text-white"
                      :class="parseFloat(top10Percentage) > 50 ? 'text-rose-400' : 'text-emerald-400'">
                      {{ parseFloat(top10Percentage) > 50 ? 'High' : 'Low' }} ({{ top10Percentage }}%)
                    </span>
                  </div>
                  <div class="flex flex-col gap-0.5">
                    <span class="text-slate-400">Slippage ($10k):</span>
                    <span class="font-mono text-white">{{ calculatedSlippage }}</span>
                  </div>
                  <div class="flex flex-col gap-0.5">
                    <span class="text-slate-400">Contract Lock:</span>
                    <span class="font-mono" :class="token.issuer_locked ? 'text-emerald-400' : 'text-amber'">
                      {{ token.issuer_locked ? 'Secured (Locked)' : 'Unlocked Issuer' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Conclusion -->
              <div class="p-4 space-y-2">
                <span class="text-[10px] font-extrabold text-cyan-400 uppercase tracking-wider block font-mono">✍ AI
                  Conclusion</span>
                <div v-if="loading || liquidityLoading" class="text-xs text-slate-500 animate-pulse">Formulating...
                </div>
                <p v-else class="text-xs text-slate-300 leading-relaxed m-0 p-0"
                  style="padding: 0 !important; color: var(--dim) !important;">
                  {{ aiRiskSummary.text }}
                </p>
              </div>
            </div>

            <!-- Whale Activity Card -->
            <div class="card select-text">
              <div class="card-hd">
                <h3>Whale Activity<span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-[#1b212c] border border-slate-700/60 text-[9px] text-slate-400 font-serif italic select-none cursor-default hover:bg-slate-700 hover:text-white transition-all ml-1.5"
                      title="Tracking trades ≥ 10,000 XLM">i</span></h3>
                <span class="tag">Live Tracker</span>
              </div>
              <div class="p-4 space-y-3.5 bg-[#0E131C] border-t border-slate-800/60">
                <div v-if="largeEventsLoading" class="text-xs text-slate-500 animate-pulse py-2 text-center">
                  Loading activity...
                </div>
                <div v-else-if="largeEvents.length === 0" class="text-xs text-slate-500 py-4 text-center">
                  No whale transactions detected in the last 24 hours.
                </div>
                <div v-else class="space-y-4">
                  <div v-for="event in largeEvents" :key="event.id" class="flex flex-col gap-1 pb-3 border-b border-slate-800/30 last:border-b-0 last:pb-0">
                    <div class="flex items-start justify-between gap-2 text-xs">
                      <div class="flex flex-wrap items-center gap-1 font-medium min-w-0">
                        <span v-if="event.event_type === 'BUY'" class="text-emerald-400">
                          Bought <strong class="text-white font-mono">{{ formatNumberWithCommas(event.token_amount) }}</strong> {{ token.asset_code }}
                        </span>
                        <span v-else-if="event.event_type === 'SELL'" class="text-rose-400">
                          Sold <strong class="text-white font-mono">{{ formatNumberWithCommas(event.token_amount) }}</strong> {{ token.asset_code }}
                        </span>
                        <span v-else-if="event.event_type === 'LP_ADD'" class="text-cyan-400">
                          Added Liquidity <strong class="text-white font-mono" v-if="parseFloat(event.token_amount) > 0">({{ formatNumberWithCommas(event.token_amount) }} {{ token.asset_code }})</strong>
                        </span>
                        <span v-else-if="event.event_type === 'LP_REMOVE'" class="text-amber-500">
                          Removed Liquidity
                        </span>
                      </div>
                      
                      <!-- Transaction Link -->
                      <a :href="`https://stellar.expert/explorer/public/tx/${event.transaction_hash}`" 
                         target="_blank" 
                         class="text-[10px] text-slate-500 hover:text-cyan-400 transition-colors font-mono flex-shrink-0"
                         title="View Tx on Stellar.Expert">
                        tx ↗
                      </a>
                    </div>
                    
                    <div class="flex items-center justify-between gap-2 text-[11px] text-slate-400 flex-wrap">
                      <span class="font-mono">
                        ≈ {{ formatNumberWithCommas(event.xlm_value) }} XLM
                      </span>
                      <span class="text-slate-500 font-mono text-[10px]">{{ event.time_ago }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Security Parameters -->
            <div class="card">
              <div class="card-hd">
                <h3>Security Parameters</h3><span class="tag">On-chain</span>
              </div>
              <div class="sec">
                <div class="row">
                  <span class="nm">
                    <span class="shield">🛡</span>Immutable code
                    <span
                      class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-[#1b212c] border border-slate-700/60 text-[9px] text-slate-400 font-serif italic select-none cursor-default hover:bg-slate-700 hover:text-white transition-all ml-1.5"
                      title="YES means the issuer account is locked and cannot mint more tokens (0% inflation risk).">i</span>
                  </span>
                  <span class="sval" :class="token.issuer_locked ? 'yes' : 'none'">{{ token.issuer_locked ? 'YES' : 'NO'
                    }}</span>
                </div>
                <div class="row">
                  <span class="nm">
                    <span class="shield">🛡</span>Clawback disabled
                    <span
                      class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-[#1b212c] border border-slate-700/60 text-[9px] text-slate-400 font-serif italic select-none cursor-default hover:bg-slate-700 hover:text-white transition-all ml-1.5"
                      title="YES means clawbacks are disabled; the issuer cannot seize or recall tokens from user accounts.">i</span>
                  </span>
                  <span class="sval" :class="!token.auth_clawback_enabled ? 'yes' : 'none'">{{
                    !token.auth_clawback_enabled ?
                    'YES' : 'NO' }}</span>
                </div>
                <div class="row">
                  <span class="nm">
                    <span class="shield">🛡</span>Revocation disabled
                    <span
                      class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-[#1b212c] border border-slate-700/60 text-[9px] text-slate-400 font-serif italic select-none cursor-default hover:bg-slate-700 hover:text-white transition-all ml-1.5"
                      title="YES means authorization revocation is disabled; the issuer cannot freeze your account's trustline.">i</span>
                  </span>
                  <span class="sval" :class="!token.auth_revocable ? 'yes' : 'none'">{{ !token.auth_revocable ? 'YES' :
                    'NO'
                    }}</span>
                </div>
                <div class="row">
                  <span class="nm">
                    <span class="shield">🛡</span>Authorization required
                    <span
                      class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-[#1b212c] border border-slate-700/60 text-[9px] text-slate-400 font-serif italic select-none cursor-default hover:bg-slate-700 hover:text-white transition-all ml-1.5"
                      title="NONE means anyone can hold, trade, or transfer this token freely without manual approval.">i</span>
                  </span>
                  <span class="sval" :class="token.auth_required ? 'none' : 'yes'" style="font-size:10px">{{
                    token.auth_required
                    ? 'REQUIRED' : 'NONE' }}</span>
                </div>
              </div>
            </div>

            <!-- Voting -->
            <div class="card">
              <div class="card-hd">
                <h3>Ecosystem Voting</h3><span class="tag">Community</span>
              </div>
              <div class="vote select-none">
                <div @click="submitVote('trusted')" class="vopt t">
                  <div class="ic"></div>
                  <div class="l">Trusted</div>
                  <div class="n">{{ votes.trusted }}</div>
                </div>
                <div @click="submitVote('suspicious')" class="vopt s">
                  <div class="ic"></div>
                  <div class="l">Suspicious</div>
                  <div class="n">{{ votes.suspicious }}</div>
                </div>
                <div @click="submitVote('scam')" class="vopt x">
                  <div class="ic"></div>
                  <div class="l">Scam</div>
                  <div class="n">{{ votes.scam }}</div>
                </div>
              </div>
            </div>
          </aside>

          <!-- RIGHT COLUMN: ABOUT SIDEBAR -->
          <aside class="rail" v-if="activeTab === 'about'">
            <!-- SECTION 6: VERIFIED BY TOKENGLADE -->
            <div class="card" v-if="isVerified">
              <div class="card-hd">
                <h3 class="flex items-center gap-1.5 text-emerald-400">
                  <ShieldCheck class="w-4 h-4 text-emerald-400" />
                  Verified by TokenGlade
                </h3>
              </div>
              <div style="padding: 20px;" class="space-y-6">
                <!-- Verification Checklist -->
                <div class="space-y-2.5">
                  <div class="flex items-center gap-2 text-xs font-mono font-semibold text-emerald-400">
                    <span>✓</span> <span>Website ownership verified</span>
                  </div>
                  <div class="flex items-center gap-2 text-xs font-mono font-semibold text-emerald-400">
                    <span>✓</span> <span>Official profile managed by the project team</span>
                  </div>
                  <div class="flex items-center gap-2 text-xs font-mono font-semibold text-emerald-400">
                    <span>✓</span> <span>Project information verified</span>
                  </div>
                </div>

                <!-- Date grid -->
                <div class="space-y-3 pt-2">
                  <div class="flex justify-between items-center text-xs">
                    <span class="font-mono text-slate-500 uppercase">Verification Date</span>
                    <span class="font-mono text-slate-300">{{ formatDate(token.project_details?.verified_project?.verified_at) }}</span>
                  </div>
                  <div class="flex justify-between items-center text-xs">
                    <span class="font-mono text-slate-500 uppercase">Last Updated</span>
                    <span class="font-mono text-slate-300">{{ formatDate(token.project_details?.updated_at || token.project_details?.verified_project?.updated_at) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- UNVERIFIED CLAIM BOX -->
            <div class="card" v-else>
              <div class="card-hd">
                <h3 class="flex items-center gap-1.5 text-slate-300">
                  <ShieldX class="w-4 h-4 text-slate-500" />
                  Claim Your Project
                </h3>
              </div>
              <div style="padding: 20px;" class="space-y-4">
                <p class="text-xs font-medium text-slate-400 leading-relaxed">
                  Become the official owner of this project on TokenGlade.
                </p>
                
                <div class="space-y-2 text-xs font-semibold text-slate-300 leading-relaxed font-mono">
                  <div class="flex items-start gap-1.5">
                    <span class="text-cyan-400">•</span>
                    <span>Manage your official project profile</span>
                  </div>
                  <div class="flex items-start gap-1.5">
                    <span class="text-cyan-400">•</span>
                    <span>Upload logo & banner</span>
                  </div>
                  <div class="flex items-start gap-1.5">
                    <span class="text-cyan-400">•</span>
                    <span>Update project information</span>
                  </div>
                  <div class="flex items-start gap-1.5">
                    <span class="text-cyan-400">•</span>
                    <span>Add official links</span>
                  </div>
                  <div class="flex items-start gap-1.5">
                    <span class="text-cyan-400">•</span>
                    <span>Add social links</span>
                  </div>
                  <div class="flex items-start gap-1.5">
                    <span class="text-cyan-400">•</span>
                    <span>Become eligible for Featured Projects</span>
                  </div>
                </div>

                <div class="pt-2">
                  <button @click="verificationModal = true" class="btn cyan w-full py-2.5 font-bold select-none text-center block transition hover:bg-cyan-600">
                    Claim Project
                  </button>
                </div>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </div>

    <!-- Connect Wallet Modals -->
    <ConnectWalletModal v-model="ConnectWalletModals" :connected="isWalletConnected" :walletKey="walletKey" />
    <VerificationModal :open="verificationModal" :connected="isWalletConnected" :loading="verificationLoading"
      :payment-assets="verificationPaymentAssets" :selected-asset="selectedVerificationAsset"
      :asset-code="token.asset_code || ''" :issuer-address="token.issuer || ''"
      @select-asset="selectedVerificationAsset = $event" @close="verificationModal = false"
      @connect-wallet="ConnectWalletModals = true" @pay="contactVerification" />
    <ShareTokenModal 
      v-model="shareModalOpen" 
      :token="token" 
      :usd-price="token.usd_price || 0" 
      :xlm-price="token.xlm_price || 0" 
      :price-change="historicalStats?.price_change_pct || 0"
      :liquidity="token.liquidity_overview?.total_tvl || token.liquidity_tvl || 0"
      :holders="token.holders || 0"
    />
    <Footer />
  </div>
</template>

<script setup>
import { reactive, onMounted, onUnmounted, watch, ref, computed, nextTick } from "vue"
import { useRoute } from "vue-router"
import axios from "axios"
import { createChart, CrosshairMode, CandlestickSeries, LineSeries, AreaSeries, HistogramSeries } from "lightweight-charts"
import verified from "@/assets/verify.png";
import { getCookie, signXdrWithWallet, updateLoader } from "../utils/utils.js";
import Swal from 'sweetalert2';
import ConnectWalletModal from '@/components/ConnectWallet.vue';
import VerificationModal from '@/components/VerificationModal.vue';
import ShareTokenModal from '@/components/ShareTokenModal.vue';
import Header from "@/components/Header.vue"
import Footer from "@/components/Footer.vue"

let chartInstance = null;
let candleSeries = null;
let volumeSeries = null;
let lineSeries = null;
let areaSeries = null;
let liveTradesTimer = null;

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
  Lock,
  Info,
  Share2
} from "lucide-vue-next";

const loading = ref(true)
const imageError = ref(false)
const notFound = ref(false)
const activeTab = ref('overview')
const showAllTrades = ref(false)
const holdersLoading = ref(true)
const liquidityLoading = ref(true)
const largeEvents = ref([])
const largeEventsLoading = ref(true)

const refreshLiveTrades = async () => {
  if (!issuerInput.value) return;
  try {
    const res = await axios.get("/api/token/show", {
      params: {
        issuer: issuerInput.value
      }
    });
    if (res.data) {
      if (res.data.transactions && Array.isArray(res.data.transactions)) {
        token.transactions = res.data.transactions;
      }
      if (res.data.activity) {
        if (!token.activity) token.activity = {};
        Object.assign(token.activity, res.data.activity);
      }
      if (res.data.usd_price) {
        token.usd_price = res.data.usd_price;
      }
      if (res.data.xlm_price) {
        token.xlm_price = res.data.xlm_price;
      }
      if (res.data.volume_24h) {
        token.volume_24h = res.data.volume_24h;
      }
    }
  } catch (err) {
    // Silent catch for live trades stream
  }
};

const startLiveTradesPolling = () => {
  stopLiveTradesPolling();
  liveTradesTimer = setInterval(() => {
    refreshLiveTrades();
  }, 4000);
};

const stopLiveTradesPolling = () => {
  if (liveTradesTimer) {
    clearInterval(liveTradesTimer);
    liveTradesTimer = null;
  }
};
const establishingTrustline = ref(false)

const handleEstablishTrustline = async () => {
  const pk = getCookie('public_key') || localStorage.getItem('public_key') || localStorage.getItem('wallet_key') || ''

  if (!pk) {
    Swal.fire({
      icon: 'info',
      title: 'Wallet Connection Required',
      text: 'Please connect your Stellar wallet to establish a trustline for ' + (token.asset_code || 'this token') + '.',
      confirmButtonText: 'Connect Wallet',
      showCancelButton: true,
      confirmButtonColor: '#06b6d4'
    }).then((result) => {
      if (result.isConfirmed) {
        ConnectWalletModals.value = true
      }
    })
    return
  }

  const code = token.asset_code || route.params.code
  const issuer = token.issuer || route.params.issuer

  if (!code || !issuer) {
    Swal.fire('Error', 'Invalid token or issuer information.', 'error')
    return
  }

  establishingTrustline.value = true
  try {
    updateLoader('Preparing Trustline', `Creating trustline transaction XDR for ${code}...`)

    const resXdr = await axios.post('/api/token/establish-trustline-xdr', {
      asset_code: code,
      asset_issuer: issuer,
      user_wallet_key: pk
    })

    if (resXdr.data?.status !== 'success' || !resXdr.data?.unsigned_xdr) {
      Swal.close()
      Swal.fire('Error', resXdr.data?.message || 'Failed to generate trustline transaction.', 'error')
      return
    }

    const unsignedXdr = resXdr.data.unsigned_xdr
    const walletType = localStorage.getItem('wallet_type') || 'freighter'

    updateLoader('Sign in Wallet', `Please approve the trustline transaction for ${code} in your wallet...`)
    const signedXdr = await signXdrWithWallet(walletType, unsignedXdr, false)

    updateLoader('Submitting', `Submitting trustline transaction for ${code} to Stellar...`)
    const resSubmit = await axios.post('/api/token/submit-trustline-xdr', {
      signedXdr: signedXdr
    })

    Swal.close()

    if (resSubmit.data?.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Trustline Established!',
        text: `Successfully established trustline for ${code}.`,
        confirmButtonColor: '#06b6d4'
      })
      fetchToken()
    } else {
      Swal.fire('Submission Failed', resSubmit.data?.message || 'Failed to submit trustline transaction.', 'error')
    }
  } catch (err) {
    console.error('Establish trustline error:', err)
    Swal.close()
    Swal.fire({
      icon: 'error',
      title: 'Trustline Failed',
      text: err.response?.data?.message || err.message || 'An error occurred while establishing trustline.'
    })
  } finally {
    establishingTrustline.value = false
  }
}
const copied = ref(false)
const issuerInput = ref("")
const route = useRoute()
const isWalletConnected = ref(false)
const walletKey = ref('')
const ConnectWalletModals = ref(false)
const verificationModal = ref(false)
const shareModalOpen = ref(false)
const verificationLoading = ref(false)
const selectedTimeframe = ref('1D')
const selectedChartType = ref('candlestick')
const selectedStatsTimeframe = ref('24h')
const historicalStats = ref(null)
const historicalStatsLoading = ref(true)

const xlmPriceInUsd = computed(() => {
  if (token.usd_price && token.xlm_price && token.xlm_price > 0) {
    return token.usd_price / token.xlm_price;
  }
  return 0.1878; // standard fallback rate
})

const orderBook = reactive({
  bids: [],
  asks: [],
  loading: true
})

const spreadPercent = computed(() => {
  if (orderBook.bids?.length && orderBook.asks?.length) {
    const highestBid = parseFloat(orderBook.bids[0].price)
    const lowestAsk = parseFloat(orderBook.asks[0].price)
    if (lowestAsk > 0) {
      return (((lowestAsk - highestBid) / lowestAsk) * 100).toFixed(2)
    }
  }
  return '0.00'
})

const calculateBidSize = (bid) => {
  const amount = parseFloat(bid.amount || 0)
  const price = parseFloat(bid.price || 0)
  if (bid.price_r && bid.price_r.n > 0) {
    return (amount * bid.price_r.d) / bid.price_r.n
  }
  return price > 0 ? amount / price : 0
}

const totalBidVolume = computed(() => {
  if (!orderBook.bids?.length) return 0
  return orderBook.bids.reduce((sum, bid) => sum + calculateBidSize(bid), 0)
})

const totalBidValue = computed(() => {
  if (!orderBook.bids?.length) return 0
  return orderBook.bids.reduce((sum, bid) => sum + parseFloat(bid.amount || 0), 0)
})

const totalAskVolume = computed(() => {
  if (!orderBook.asks?.length) return 0
  return orderBook.asks.reduce((sum, ask) => sum + parseFloat(ask.amount || 0), 0)
})

const totalAskValue = computed(() => {
  if (!orderBook.asks?.length) return 0
  return orderBook.asks.reduce((sum, ask) => sum + (parseFloat(ask.amount || 0) * parseFloat(ask.price || 0)), 0)
})

const bullishSignals = computed(() => {
  const list = []
  if (historicalStats.value?.price_change_pct > 10) {
    list.push(`Price momentum is positive (+${historicalStats.value.price_change_pct}% recently)`)
  }
  if (historicalStats.value?.trustlines_change > 10) {
    list.push(`Added +${historicalStats.value.trustlines_change} trustlines in current track`)
  }
  if (buySellVolume.value.buyPercent > 50) {
    list.push(`Buy pressure dominant (${buySellVolume.value.buyPercent}% buy volume)`)
  }
  if (largeEvents.value && largeEvents.value.some(e => e.event_type === 'BUY' || e.event_type === 'LP_ADD')) {
    list.push('Large whale buy or liquidity addition detected recently')
  }
  if (historicalStats.value?.volume_change_pct > 10) {
    list.push(`Trading volume is up (+${historicalStats.value.volume_change_pct}% recently)`)
  }
  if (list.length === 0) {
    list.push('No critical bullish indicators flagged')
  }
  return list
})

const bearishSignals = computed(() => {
  const list = []
  const tvl = token.liquidity_overview?.total_tvl || token.liquidity_tvl || 0
  if (tvl < 500) {
    list.push(`Liquidity pool is shallow ($${formatNumber(tvl)} TVL), check slippage`)
  }
  if (historicalStats.value?.price_change_pct <= -10) {
    list.push(`Price is down (${historicalStats.value.price_change_pct}% change)`)
  }
  if (parseFloat(top10Percentage.value) > 50) {
    list.push(`Elevated whale concentration (top 10 hold ${top10Percentage.value}%)`)
  }
  if (!token.issuer_locked) {
    list.push(`Issuer key unlocked (potential mint inflation risk)`)
  }
  if (largeEvents.value && largeEvents.value.some(e => e.event_type === 'SELL' || e.event_type === 'LP_REMOVE')) {
    list.push('Large whale sell or liquidity removal detected recently')
  }
  if (historicalStats.value?.volume_change_pct <= -10) {
    list.push(`Trading volume is down (${historicalStats.value.volume_change_pct}% recently)`)
  }
  if (list.length === 0) {
    list.push('No critical bearish indicators flagged')
  }
  return list
})

const calculatedSlippage = computed(() => {
  const tvl = token.liquidity_overview?.total_tvl || token.liquidity_tvl || 0
  if (tvl >= 100000) return '0.1%'
  if (tvl >= 50000) return '0.3%'
  if (tvl >= 15000) return '0.9%'
  if (tvl >= 5000) return '3.2%'
  return '9.5%'
})


const fetchHistoricalStats = async () => {
  const code = token.asset_code || route.params.code
  const issuer = token.issuer || route.params.issuer
  if (!code || !issuer) {
    historicalStatsLoading.value = false
    return
  }
  historicalStatsLoading.value = true
  try {
    const res = await axios.get('/api/token/historical-stats', {
      params: {
        code: code,
        issuer: issuer,
        timeframe: selectedStatsTimeframe.value
      }
    })
    if (res.data?.status === 'success') {
      historicalStats.value = res.data.data
    }
  } catch (err) {
    console.error('Error fetching historical stats:', err)
  } finally {
    historicalStatsLoading.value = false
  }
}

const fetchOrderBook = async () => {
  const code = token.asset_code || route.params.code
  const issuer = token.issuer || route.params.issuer
  if (!code || !issuer) {
    orderBook.loading = false
    return
  }
  orderBook.loading = true
  try {
    const envRes = await axios.get('/api/env')
    const isTestnet = envRes.data?.stellar_env !== 'public'
    const horizonUrl = isTestnet ? 'https://horizon-testnet.stellar.org' : 'https://horizon.stellar.org'

    const sellingType = code.length <= 4 ? 'credit_alphanum4' : 'credit_alphanum12'
    const res = await axios.get(`${horizonUrl}/order_book`, {
      params: {
        selling_asset_type: sellingType,
        selling_asset_code: code,
        selling_asset_issuer: issuer,
        buying_asset_type: 'native',
        limit: 200
      }
    })

    orderBook.bids = res.data?.bids || []
    orderBook.asks = res.data?.asks || []
  } catch (err) {
    console.error('Error fetching order book:', err)
  } finally {
    orderBook.loading = false
  }
}

const getBidDepth = (index) => {
  let sum = 0
  for (let i = 0; i <= index; i++) {
    sum += parseFloat(orderBook.bids[i]?.amount || 0)
  }
  return sum
}

const getAskDepth = (index) => {
  let sum = 0
  for (let i = 0; i <= index; i++) {
    sum += parseFloat(orderBook.asks[i]?.amount || 0)
  }
  return sum
}

const totalBidDepth = computed(() => {
  if (!orderBook.bids?.length) return 1
  return getBidDepth(orderBook.bids.length - 1) || 1
})

const totalAskDepth = computed(() => {
  if (!orderBook.asks?.length) return 1
  return getAskDepth(orderBook.asks.length - 1) || 1
})

const changeStatsTimeframe = (tf) => {
  if (selectedStatsTimeframe.value === tf) return
  selectedStatsTimeframe.value = tf
  fetchHistoricalStats()
}

const chartContainer = ref(null)
const chartData = ref([])

const token = reactive({
  name: "",
  asset_code: "",
  is_minted_on_tokenglade: false,
  liquidity_pools: "",
  issuer: "",
  image: null,
  description: "",
  project: {},
  holders: 0,
  trustlines: 0,
  mint_date_human: "-",
  updated_at: "-",
  conditions: null,
  usd_price: 0,
  xlm_price: 0,
  price_change_24h: 2.4,
  volume_24h: undefined,
  total_supply: 10000000,
  top_holders: [],
  project_holders: [],
  activity: {
    total_trades: 0,
    traded_volume: 0,
    payments: 0,
    payments_volume: 0
  },
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

const isVerified = computed(() => token.is_verified === true)
const isVerificationPending = computed(() => token.is_verification_pending === true)

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A'
  try {
    const d = new Date(dateStr)
    if (isNaN(d.getTime())) return 'N/A'
    return d.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  } catch (e) {
    return 'N/A'
  }
}

const simplifyUrl = (url) => {
  if (!url) return ''
  return url.replace(/^https?:\/\//i, '').replace(/^www\./i, '').split('/')[0]
}

const hasOfficialLinks = computed(() => {
  return !!(token.website || token.documentation || token.whitepaper || token.github || token.medium)
})

const hasCommunityLinks = computed(() => {
  return !!(token.twitter || token.telegram || token.discord || token.linkedin || token.reddit || token.youtube)
})

const hasEcosystemInfo = computed(() => {
  const pd = token.project_details
  if (!pd) return false
  return !!(
    (pd.supported_wallets && pd.supported_wallets.length > 0) ||
    (pd.supported_dexs && pd.supported_dexs.length > 0) ||
    (pd.primary_use_cases && pd.primary_use_cases.length > 0)
  )
})

const scopulyTradeUrl = computed(() => {
  if (token.asset_code && token.issuer) {
    return `https://scopuly.com/trade/${token.asset_code}-XLM/${token.issuer}/native`;
  } else if (token.asset_code) {
    return `https://scopuly.com/trade/${token.asset_code}-XLM/native`;
  }
  return 'https://scopuly.com/trade';
});

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
      buyPercent: 0,
      sellPercent: 0
    };
  }

  return {
    buyVol,
    sellVol,
    buyPercent: Math.round((buyVol / total) * 100),
    sellPercent: Math.round((sellVol / total) * 100)
  };
});

const buySellRatioText = computed(() => {
  const { buyVol, sellVol, buyPercent } = buySellVolume.value;
  if (buyVol === 0 && sellVol === 0) return '0';
  if (buyPercent >= 50) {
    return `${buyPercent}% Buy`;
  } else {
    return `${100 - buyPercent}% Sell`;
  }
});

const avgTradeSizeUsd = computed(() => {
  const txs = token.transactions || [];
  if (!txs.length) return null;
  let totalUsd = 0;
  const xlmUsd = token.usd_price && token.xlm_price ? (token.usd_price / token.xlm_price) : 0.12;
  txs.forEach(tx => {
    const xlmValue = Number(tx.value) || (Number(tx.amount) * Number(tx.price));
    totalUsd += (xlmValue * xlmUsd);
  });
  return totalUsd / txs.length;
});

const healthLabel = computed(() => {
  const score = token.rating?.average ?? 7.5
  if (score >= 8) return { text: "Low", color: "text-green-600" }
  if (score >= 5) return { text: "Medium", color: "text-yellow-500" }
  return { text: "High", color: "text-red-500" }
})

const ratingBars = computed(() => {
  const r = token.rating || {
    age: 8.5,
    activity: 7.2,
    trustlines: 6.8,
    liquidity: 7.5,
    volume7d: 6.9,
    interop: 8.0,
  }
  return [
    { key: 'Age', label: 'Age', val: r.age || 0, sub: 'Asset age & creation longevity on Stellar network' },
    { key: 'Activity', label: 'Activity', val: r.activity || 0, sub: 'On-chain transaction & transfer frequency rate' },
    { key: 'Trustlines', label: 'Trustlines', val: r.trustlines || 0, sub: 'Number of active wallet trustlines authorized' },
    { key: 'Liquidity', label: 'Liquidity', val: r.liquidity || 0, sub: 'Total depth & capital backing in DEX liquidity pools' },
    { key: 'Volume', label: 'Volume', val: r.volume7d || 0, sub: '7-day decentralized exchange trading volume' },
    { key: 'Interop', label: 'Interop', val: r.interop || 0, sub: 'TOML metadata profile & home domain verification' },
  ]
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
  const activityFactor = (token.rating?.activity ?? 5) / 10;

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
  const score = token.rating?.average ?? 7.5;
  const tvl = token.liquidity_overview?.total_tvl || token.liquidity_tvl || 0;
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
  if (tvl >= 10000) {
    liquidityStatus = 'robust';
  } else if (tvl >= 500) {
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
      dangerReasons.push(`shallow liquidity pool depth ( $${formatNumber(tvl)} TVL ) which could cause severe price slippage`);
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

const stellarExpertAssetUrl = computed(() => {
  if (!token.issuer || !token.asset_code) return '#';
  if (token.asset_code === 'XLM') {
    return 'https://stellar.expert/explorer/public/asset/XLM';
  }
  return `https://stellar.expert/explorer/public/asset/${token.asset_code}-${token.issuer}-1`;
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

function formatCompactNumber(num) {
  const val = parseFloat(num || 0);
  if (!val) return '0';
  if (val >= 1e9) return (val / 1e9).toFixed(1) + 'B';
  if (val >= 1e6) return (val / 1e6).toFixed(1) + 'M';
  if (val >= 1e3) return (val / 1e3).toFixed(1) + 'K';
  return val.toFixed(0);
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
  imageError.value = false
  notFound.value = false
  holdersLoading.value = true
  liquidityLoading.value = true
  historicalStatsLoading.value = true
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

    if (res.data?.error || !res.data?.asset_code) {
      notFound.value = true
      loading.value = false
      return
    }

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
    fetchHistoricalStats()
    fetchOrderBook()
    fetchLargeEvents()
  } catch (error) {
    console.error("Error fetching token data:", error)
    notFound.value = true
  } finally {
    loading.value = false
    if (!notFound.value) {
      nextTick(() => {
        setTimeout(async () => {
          await initChart()
          await fetchChartData()
        }, 50)
      })
    }
  }
}

function switchTab(tab) {
  activeTab.value = tab
  if (tab === 'overview') {
    nextTick(() => {
      initChart()
      fetchChartData()
      fetchOrderBook()
    })
  } else if (tab === 'holders' && (!token.top_holders || token.top_holders.length === 0) && !holdersLoading.value) {
    fetchHolders()
  } else if (tab === 'liquidity' && (!token.liquidity_overview || !token.liquidity_overview.pools || token.liquidity_overview.pools.length === 0) && !liquidityLoading.value) {
    fetchLiquidity()
  }
}

const formatNumberWithCommas = (val) => {
  if (val === undefined || val === null || isNaN(val)) return '0'
  const parsed = parseFloat(val)
  if (parsed === 0) return '0'
  return new Intl.NumberFormat('en-US', {
    maximumFractionDigits: parsed % 1 === 0 ? 0 : 2
  }).format(parsed)
}

async function fetchLargeEvents() {
  const code = token.asset_code || route.params.code
  const issuer = token.issuer || route.params.issuer || issuerInput.value
  
  if (!code || !issuer) return
  
  largeEventsLoading.value = true
  try {
    const res = await axios.get(`/api/token/${code}/${issuer}/whale-activity`)
    if (Array.isArray(res.data)) {
      largeEvents.value = res.data
    }
  } catch (err) {
    console.error("Failed to fetch large market activity:", err)
  } finally {
    largeEventsLoading.value = false
  }
}

async function fetchHolders() {
  if (!token.issuer || !token.asset_code) {
    holdersLoading.value = false
    return
  }
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
  if (!token.issuer || !token.asset_code) {
    liquidityLoading.value = false
    return
  }
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

async function contactVerification(formData) {
  try {
    verificationLoading.value = true
    const publicKey = getCookie('public_key')
    if (!publicKey) {
      verificationLoading.value = false
      ConnectWalletModals.value = true
      return
    }

    const data = new FormData()
    data.append('identifier', token.issuer)
    data.append('asset_code', token.asset_code)
    data.append('public_key', publicKey)
    data.append('verification_payment_asset_id', selectedVerificationAsset.value.id)

    // Form onboarding metadata
    data.append('name', formData.name || '')
    data.append('short_description', formData.short_description || '')
    data.append('full_description', formData.full_description || '')
    data.append('category', formData.category || '')
    data.append('launch_date', formData.launch_date || '')
    data.append('official_email', formData.official_email || '')
    
    if (formData.logo) {
      data.append('logo', formData.logo)
    }
    if (formData.banner) {
      data.append('banner', formData.banner)
    }

    const ensureUrlHasProtocol = (url) => {
      if (!url) return ''
      let clean = url.trim()
      if (clean && !/^https?:\/\//i.test(clean)) {
        return 'https://' + clean
      }
      return clean
    }

    // Links
    data.append('website_link', ensureUrlHasProtocol(formData.website_link))
    data.append('documentation_link', ensureUrlHasProtocol(formData.documentation_link))
    data.append('whitepaper_link', ensureUrlHasProtocol(formData.whitepaper_link))
    data.append('github_link', ensureUrlHasProtocol(formData.github_link))
    data.append('medium_link', ensureUrlHasProtocol(formData.medium_link))

    // Socials
    data.append('twitter_link', ensureUrlHasProtocol(formData.twitter_link))
    data.append('telegram_link', ensureUrlHasProtocol(formData.telegram_link))
    data.append('discord_link', ensureUrlHasProtocol(formData.discord_link))
    data.append('linkedin_link', ensureUrlHasProtocol(formData.linkedin_link))
    data.append('reddit_link', ensureUrlHasProtocol(formData.reddit_link))
    data.append('youtube_link', ensureUrlHasProtocol(formData.youtube_link))

    // JSON array of wallets
    data.append('wallets', JSON.stringify(formData.wallets || []))

    const res = await axios.post('/api/token/verification', data, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
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
    fetchToken()
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
  const p = parseFloat(num || 0);
  if (p === 0) return '0.00';
  if (p < 0.0001) return p.toFixed(6);
  if (p < 0.01) return p.toFixed(5);
  if (p < 1) return p.toFixed(4);
  return p.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 4 });
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

const handleWalletChanged = (event) => {
  if (event?.detail?.connected && event?.detail?.publicKey) {
    walletKey.value = event.detail.publicKey
    isWalletConnected.value = true
  } else {
    walletKey.value = getCookie('public_key') || ''
    isWalletConnected.value = !!walletKey.value
  }
}

onMounted(async () => {
  walletKey.value = getCookie('public_key') || ''
  isWalletConnected.value = !!walletKey.value
  window.addEventListener('tokenglade-wallet-changed', handleWalletChanged)
  await fetchVerificationAssets()
  startLiveTradesPolling()
})

onUnmounted(() => {
  window.removeEventListener('tokenglade-wallet-changed', handleWalletChanged)
  stopLiveTradesPolling()
})

watch(
  [() => route.query, () => route.params],
  ([query, params]) => {
    const issuer = query.issuer || params.issuer;
    if (issuer) {
      issuerInput.value = issuer
      fetchToken()
      startLiveTradesPolling()
    } else {
      loading.value = false
    }
  },
  { immediate: true }
)

async function initChart() {
  await nextTick();
  if (!chartContainer.value) return;

  if (chartInstance) {
    try {
      chartInstance.remove();
    } catch (e) {
      console.error("Error removing chart instance:", e);
    }
    chartInstance = null;
  }

  chartContainer.value.innerHTML = '';
  const containerWidth = chartContainer.value.clientWidth || 800;

  try {
    chartInstance = createChart(chartContainer.value, {
      width: containerWidth,
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
        mode: CrosshairMode.Normal,
      },
      rightPriceScale: {
        borderColor: '#1D2531',
      },
      timeScale: {
        borderColor: '#1D2531',
        timeVisible: true,
        secondsVisible: false,
        fixLeftEdge: false,
        fixRightEdge: false,
      },
      handleScale: {
        axisPressedMouseMove: {
          time: true,
          price: true,
        },
      },
      handleScroll: {
        pressedMouseMove: true,
        horzTouchDrag: true,
        vertTouchDrag: true,
        mouseWheel: true,
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
  if (!chartInstance) {
    await initChart();
  }
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

  if (candleSeries) { try { chartInstance.removeSeries(candleSeries); } catch (e) { } candleSeries = null; }
  if (lineSeries) { try { chartInstance.removeSeries(lineSeries); } catch (e) { } lineSeries = null; }
  if (areaSeries) { try { chartInstance.removeSeries(areaSeries); } catch (e) { } areaSeries = null; }
  if (volumeSeries) { try { chartInstance.removeSeries(volumeSeries); } catch (e) { } volumeSeries = null; }

  if (!chartData.value || !chartData.value.length) return;

  const cleanMap = new Map();
  chartData.value.forEach(d => {
    if (d && d.time) {
      const timeVal = typeof d.time === 'number' ? d.time : parseInt(d.time, 10);
      if (!isNaN(timeVal) && timeVal > 0) {
        cleanMap.set(timeVal, {
          time: timeVal,
          open: Number(d.open || 0),
          high: Number(d.high || 0),
          low: Number(d.low || 0),
          close: Number(d.close || 0),
          volume: Number(d.volume || 0)
        });
      }
    }
  });

  const formattedData = Array.from(cleanMap.values()).sort((a, b) => a.time - b.time);
  if (formattedData.length === 0) return;

  const formattedVolume = formattedData.map(d => ({
    time: d.time,
    value: d.volume,
    color: d.close >= d.open ? '#2ED47A44' : '#F0616D44'
  }));

  if (selectedChartType.value === 'candlestick') {
    candleSeries = chartInstance.addSeries(CandlestickSeries, {
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
    lineSeries = chartInstance.addSeries(LineSeries, {
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
    areaSeries = chartInstance.addSeries(AreaSeries, {
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

  volumeSeries = chartInstance.addSeries(HistogramSeries, {
    priceFormat: {
      type: 'volume',
    },
    priceScaleId: 'volume',
    lastValueVisible: false,
    priceLineVisible: false,
  });

  chartInstance.priceScale('volume').applyOptions({
    scaleMargins: {
      top: 0.8,
      bottom: 0,
    },
    visible: false,
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
  
  const elOpen = document.getElementById('ohlc-open');
  const elHigh = document.getElementById('ohlc-high');
  const elLow = document.getElementById('ohlc-low');
  const elClose = document.getElementById('ohlc-close');
  const elChange = document.getElementById('ohlc-change');
  const elVolume = document.getElementById('ohlc-volume');

  if (elOpen) elOpen.textContent = formatCandleValue(candle.open);
  if (elHigh) elHigh.textContent = formatCandleValue(candle.high);
  if (elLow) elLow.textContent = formatCandleValue(candle.low);
  if (elClose) elClose.textContent = formatCandleValue(candle.close);
  if (elVolume) elVolume.textContent = formatNumber(candle.volume);
  if (elChange) {
    elChange.textContent = (change >= 0 ? '+' : '') + change.toFixed(2) + '%';
    elChange.className = change >= 0 ? 'up' : 'down';
  }
}

function formatCandleValue(val) {
  if (val === null || val === undefined) return '—';
  if (val === 0) return '0.000000';
  if (val < 0.0001) return val.toFixed(8);
  return val.toFixed(6);
}

function formatXlmPrice(num) {
  if (num && parseFloat(num) > 0) return parseFloat(num).toFixed(5);
  if (token.usd_price && token.xlm_usd_price && token.xlm_usd_price > 0) {
    return (token.usd_price / token.xlm_usd_price).toFixed(5);
  }
  if (token.usd_price) {
    return (token.usd_price / 0.1878).toFixed(5);
  }
  return "0.0000";
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

.claim-btn {
  font-family: var(--mono);
  font-size: 11.5px;
  font-weight: 600;
  padding: 4px 11px;
  border-radius: 7px;
  border: 1px solid rgba(245, 158, 11, 0.4);
  background: rgba(245, 158, 11, 0.05);
  color: #f59e0b;
  cursor: pointer;
  transition: all 0.15s ease;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  line-height: 1;
}

.claim-btn:hover {
  background: rgba(245, 158, 11, 0.12);
  border-color: #f59e0b;
  color: #fbbf24;
}

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
  padding-bottom: 0px;
  background-image: radial-gradient(900px 460px at 84% -12%, rgba(18, 203, 238, .09), transparent 62%), radial-gradient(760px 420px at 6% -8%, rgba(240, 24, 156, .07), transparent 60%);
}

.mono {
  font-family: var(--mono);
  font-variant-numeric: tabular-nums;
}

.up {
  color: var(--up);
}

.down {
  color: var(--down);
}

.dim {
  color: var(--dim);
}

.faint {
  color: var(--faint);
}

.wrap {
  max-width: 1440px;
  margin: 0 auto;
  padding: 0 16px 2.6rem;
}

@media (min-width: 640px) {
  .wrap {
    padding-left: 24px;
    padding-right: 24px;
  }
}

@media (min-width: 1024px) {
  .wrap {
    padding-left: 32px;
    padding-right: 32px;
  }
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
  50% {
    opacity: .35;
  }
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

.k {
  font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.trust .lbl .k {
  font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
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
  font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
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

.acts .btn {
  font-family: var(--disp);
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 0.015em;
  border: 1px solid var(--line2);
  background: var(--panel);
  color: var(--ink);
  padding: 9px 16px;
  border-radius: 9px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.acts .btn:hover {
  border-color: var(--amber);
  transform: translateY(-1px);
}

.acts .btn.brand {
  background: var(--amber);
  color: #08131a;
  border-color: transparent;
  font-weight: 700;
  box-shadow: 0 0 16px rgba(18, 203, 238, 0.25);
}

.acts .btn.brand:hover {
  filter: brightness(1.08);
  box-shadow: 0 0 20px rgba(18, 203, 238, 0.35);
}

.acts .btn.dark {
  background: #0E131C;
  border-color: rgba(148, 163, 184, 0.16);
}

.acts .btn.dark:hover {
  background: #131A26;
  border-color: var(--amber);
}

.acts .btn.icon-btn {
  padding: 0;
  width: 38px;
  height: 38px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
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

.main-grid {
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
  font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
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
  grid-column: 1 / -1;
  width: 100%;
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
  width: 100%;
  max-width: none;
  line-height: 1.6;
}

@media (max-width: 1040px) {
  .main-grid {
    grid-template-columns: 1fr;
  }

  .stats {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .wrap {
    padding-left: 12px !important;
    padding-right: 12px !important;
  }

  .asset {
    padding: 14px !important;
  }

  .ahead {
    flex-direction: column !important;
    gap: 12px !important;
  }

  .token-ico {
    width: 44px !important;
    height: 44px !important;
  }

  .name-row h1 {
    font-size: 20px !important;
  }

  .stats {
    grid-template-columns: repeat(2, 1fr) !important;
  }

  .expo-stats {
    grid-template-columns: 1fr 1fr;
  }

  .health {
    grid-template-columns: 1fr;
  }

  .trust {
    margin-left: 0 !important;
    width: 100% !important;
    display: flex !important;
    flex-direction: row !important;
    justify-content: space-between !important;
    padding: 10px 12px !important;
  }

  .trust .lbl {
    text-align: left !important;
  }

  .st {
    padding: 10px 12px !important;
  }

  .st .v {
    font-size: 14px !important;
  }
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: rgba(17, 22, 32, 0.5);
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: rgba(40, 52, 73, 0.8);
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: rgba(56, 73, 102, 1);
}

.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: rgba(40, 52, 73, 0.8) rgba(17, 22, 32, 0.5);
}
</style>