<template>
  <div class="asset-page-wrapper min-h-screen selection:bg-cyan-500/20 selection:text-white">
    <Header />

    <!-- MAIN CONTAINER -->
    <div class="wrap space-y-6 pb-24 sm:pb-32 pt-4">

      <!-- ERROR / NOT FOUND STATE -->
      <div v-if="notFound" class="card p-12 text-center max-w-xl mx-auto space-y-4 my-12">
        <AlertCircle class="w-12 h-12 text-rose-500 mx-auto" />
        <h2 class="text-xl font-bold text-white">Wallet Address Not Found</h2>
        <p class="text-xs text-slate-400 font-mono break-all">{{ address }}</p>
        <p class="text-sm text-slate-300">
          This address does not appear to exist on the Stellar network or is invalid. Please verify the address and try again.
        </p>
        <router-link to="/" class="inline-block text-xs uppercase tracking-wider font-extrabold px-6 py-2.5 bg-slate-900 border border-slate-800 hover:border-slate-700 transition rounded-lg text-white">
          Back to Home
        </router-link>
      </div>

      <!-- CONNECTION / NETWORK ERROR STATE -->
      <div v-else-if="connectionError" class="card p-12 text-center max-w-xl mx-auto space-y-4 my-12">
        <AlertCircle class="w-12 h-12 text-amber-500 mx-auto animate-pulse" />
        <h2 class="text-xl font-bold text-white">Horizon Node Connection Refused</h2>
        <p class="text-xs text-slate-400 font-mono break-all">{{ address }}</p>
        <p class="text-sm text-slate-300">
          Stellar Horizon nodes are temporarily refusing connection, rate-limiting your requests, or your local DNS is offline. Please try again.
        </p>
        <div class="flex items-center justify-center gap-3">
          <button @click="retryLoad" class="text-xs uppercase tracking-wider font-extrabold px-6 py-2.5 bg-slate-900 border border-slate-800 hover:border-slate-700 transition rounded-lg text-white">
            Retry Connection
          </button>
          <router-link to="/" class="text-xs uppercase tracking-wider font-extrabold px-6 py-2.5 bg-transparent text-slate-400 hover:text-white transition">
            Back to Home
          </router-link>
        </div>
      </div>

      <!-- MAIN PAGE LAYOUT -->
      <div v-else class="space-y-6">

        <!-- NON-BLOCKING INDEXING NOTICE -->
        <div v-if="overviewData && overviewData.tracking_status === 'ACTIVE' && isIndexing && overviewData.indexing_status !== 'failed'" 
             class="flex items-center justify-between p-3.5 bg-cyan-950/20 border border-cyan-800/30 rounded-xl text-cyan-400">
          <div class="flex items-center gap-2.5">
            <Loader2 class="w-4 h-4 animate-spin text-cyan-400" />
            <span class="text-xs font-semibold">Historical wallet intelligence is being prepared.</span>
          </div>
          <span class="text-[10px] font-mono text-cyan-500 bg-cyan-950/40 px-2.5 py-1 rounded">Preparing Index...</span>
        </div>

        <!-- INDEXING FAILED NOTICE -->
        <div v-else-if="overviewData && overviewData.indexing_status === 'failed'" 
             class="flex items-center justify-between p-3.5 bg-amber-950/20 border border-amber-800/30 rounded-xl text-amber-400 animate-pulse">
          <div class="flex items-center gap-2.5">
            <AlertCircle class="w-4 h-4 text-amber-400" />
            <span class="text-xs font-semibold">Historical indexing failed due to temporary network issues. Real-time updates will continue.</span>
          </div>
          <button @click="retryIndexing" class="text-[9px] font-mono uppercase tracking-wider font-bold text-amber-500 hover:text-white bg-amber-950/40 px-2.5 py-1 rounded border border-amber-900/30 transition">
            Retry Indexing
          </button>
        </div>

        <!-- SECTION 1: WALLET HERO -->
        <section class="card asset-hero p-6 relative overflow-hidden">
          <div class="hero-glow"></div>
          
          <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <!-- Left Side: Address and Meta -->
            <div class="space-y-3.5 flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <span class="text-[10px] font-mono tracking-widest text-cyan-400 uppercase bg-cyan-950/40 border border-cyan-900/30 px-2 py-0.5 rounded">
                  Stellar Wallet
                </span>
                <span v-if="overviewData" class="text-[10px] font-mono uppercase px-2 py-0.5 rounded"
                      :class="overviewData.tracking_status === 'ACTIVE' ? 'bg-emerald-950/40 text-emerald-400 border border-emerald-900/30' : 'bg-slate-900/40 text-slate-400 border border-slate-800/30'">
                  {{ overviewData.tracking_status }}
                </span>
              </div>
              
              <div class="flex items-center gap-3">
                <h1 class="text-base sm:text-lg md:text-xl font-mono text-white break-all leading-none font-bold select-all flex-1 min-w-0" :title="address">
                  {{ address }}
                </h1>
                <button @click="copyAddress" class="p-2 bg-slate-900/80 border border-slate-800 rounded-lg hover:border-slate-700 hover:text-white transition flex-shrink-0">
                  <Check v-if="copied" class="w-4 h-4 text-emerald-400" />
                  <Copy v-else class="w-4 h-4 text-slate-400" />
                </button>
              </div>

              <!-- Mini stats -->
              <div v-if="overviewData" class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs font-mono text-slate-400 pt-1">
                <div v-if="overviewData.wallet_age_days !== null">
                  Age: <span class="text-white">{{ overviewData.wallet_age_days }} Days</span>
                </div>
                <div v-if="overviewData.first_activity">
                  First Act: <span class="text-white">{{ formatDate(overviewData.first_activity) }}</span>
                </div>
                <div v-if="overviewData.last_activity">
                  Latest Act: <span class="text-white">{{ formatDate(overviewData.last_activity) }}</span>
                </div>
              </div>
            </div>

            <!-- Right Side: Value & Key balances -->
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-4 lg:text-right w-full lg:w-auto">
              <!-- Highlighted Portfolio Value Card -->
              <div class="bg-gradient-to-br from-slate-950/80 to-slate-900/60 border border-cyan-500/30 p-4 rounded-xl flex-1 sm:flex-none min-w-[170px] space-y-1 shadow-lg shadow-cyan-950/10">
                <div class="text-[10px] uppercase text-cyan-400 tracking-widest font-bold">Portfolio Value</div>
                <div class="text-2xl font-black text-white font-mono leading-none py-1">
                  <span v-if="overviewLoading" class="inline-block w-28 h-7 bg-slate-850 animate-pulse rounded"></span>
                  <span v-else>${{ formatNumber(overviewData?.portfolio_value_usd, 2) }}</span>
                </div>
                <div class="text-[11px] text-slate-400 font-mono">
                  <span v-if="overviewLoading" class="inline-block w-16 h-3 bg-slate-850 animate-pulse rounded"></span>
                  <span v-else>{{ formatNumber(overviewData?.portfolio_value_xlm, 2) }} XLM</span>
                </div>
              </div>

              <!-- XLM Balance -->
              <div class="bg-slate-950/40 border border-slate-900 p-4 rounded-xl flex-1 sm:flex-none min-w-[110px] space-y-1">
                <div class="text-[10px] uppercase text-slate-500 tracking-wider font-semibold">XLM Balance</div>
                <div class="text-lg font-bold text-white font-mono leading-tight py-1">
                  <span v-if="overviewLoading" class="inline-block w-16 h-5 bg-slate-850 animate-pulse rounded"></span>
                  <span v-else>{{ formatNumber(overviewData?.xlm_balance, 2) }}</span>
                </div>
                <div class="text-[11px] text-slate-500 font-semibold uppercase">Native XLM</div>
              </div>

              <!-- Assets Held -->
              <div class="bg-slate-950/40 border border-slate-900 p-4 rounded-xl flex-1 sm:flex-none min-w-[100px] space-y-1">
                <div class="text-[10px] uppercase text-slate-500 tracking-wider font-semibold">Assets Held</div>
                <div class="text-lg font-bold text-white font-mono leading-tight py-1">
                  <span v-if="overviewLoading" class="inline-block w-10 h-5 bg-slate-850 animate-pulse rounded"></span>
                  <span v-else>{{ overviewData?.assets_held ?? 0 }}</span>
                </div>
                <div class="text-[11px] text-slate-500 font-semibold uppercase">Positions</div>
              </div>

              <!-- Trustlines -->
              <div class="bg-slate-950/40 border border-slate-900 p-4 rounded-xl flex-1 sm:flex-none min-w-[100px] space-y-1">
                <div class="text-[10px] uppercase text-slate-500 tracking-wider font-semibold">Trustlines</div>
                <div class="text-lg font-bold text-cyan-400 font-mono leading-tight py-1">
                  <span v-if="overviewLoading" class="inline-block w-10 h-5 bg-slate-850 animate-pulse rounded"></span>
                  <span v-else>{{ overviewData?.trustlines_count ?? 0 }}</span>
                </div>
                <div class="text-[11px] text-slate-500 font-semibold uppercase">Established</div>
              </div>

              <!-- Pools -->
              <div class="bg-slate-950/40 border border-slate-900 p-4 rounded-xl flex-1 sm:flex-none min-w-[100px] space-y-1">
                <div class="text-[10px] uppercase text-slate-500 tracking-wider font-semibold">Pools</div>
                <div class="text-lg font-bold text-amber-400 font-mono leading-tight py-1">
                  <span v-if="overviewLoading" class="inline-block w-10 h-5 bg-slate-850 animate-pulse rounded"></span>
                  <span v-else>{{ overviewData?.pools_count ?? 0 }}</span>
                </div>
                <div class="text-[11px] text-slate-500 font-semibold uppercase">Participated</div>
              </div>
            </div>
          </div>
        </section>

        <!-- PORTFOLIO GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- SECTION 2: PORTFOLIO OVERVIEW (LEFT 2 COLS) -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Assets card with Holdings and Trustlines tabs -->
            <section class="card">
              <div class="card-hd border-b border-slate-900 pb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h3 class="flex items-center gap-2">
                  <span class="dot"></span> Assets
                </h3>
                
                <!-- TABS -->
                <div class="flex items-center gap-1.5">
                  <button @click="activeAssetsTab = 'holdings'" 
                          class="text-xs uppercase tracking-wider font-extrabold px-3 py-1 rounded border transition-colors"
                          :class="activeAssetsTab === 'holdings' ? 'bg-slate-900 border-slate-750 text-white' : 'bg-transparent border-transparent text-slate-400 hover:text-slate-300'">
                    Holdings <span class="ml-1 text-[10px] font-mono text-cyan-400 font-semibold">{{ holdingsCount }}</span>
                  </button>
                  <button @click="activeAssetsTab = 'trustlines'" 
                          class="text-xs uppercase tracking-wider font-extrabold px-3 py-1 rounded border transition-colors"
                          :class="activeAssetsTab === 'trustlines' ? 'bg-slate-900 border-slate-750 text-white' : 'bg-transparent border-transparent text-slate-400 hover:text-slate-300'">
                    Trustlines <span class="ml-1 text-[10px] font-mono text-cyan-400 font-semibold">{{ trustlinesCount }}</span>
                  </button>
                  <button @click="activeAssetsTab = 'pools'" 
                          class="text-xs uppercase tracking-wider font-extrabold px-3 py-1 rounded border transition-colors"
                          :class="activeAssetsTab === 'pools' ? 'bg-slate-900 border-slate-750 text-white' : 'bg-transparent border-transparent text-slate-400 hover:text-slate-300'">
                    Pools <span class="ml-1 text-[10px] font-mono text-cyan-400 font-semibold">{{ poolsCount }}</span>
                  </button>
                </div>
              </div>

              <!-- SKELETON LOADER -->
              <div v-if="holdingsLoading" class="p-6 space-y-4 animate-pulse">
                <div class="h-8 bg-slate-900 rounded-lg w-full" v-for="i in 4" :key="i"></div>
              </div>

              <!-- HOLDINGS TAB -->
              <div v-else-if="activeAssetsTab === 'holdings'">
                <!-- SEARCH AND SORT CONTROLS -->
                <div class="px-6 py-3 flex flex-col sm:flex-row gap-3 items-center justify-between border-b border-slate-900 bg-slate-950/10">
                  <!-- Search input -->
                  <div class="relative w-full sm:max-w-xs">
                    <input type="text" 
                           v-model="holdingsSearchQuery" 
                           placeholder="Search assets..." 
                           class="w-full bg-slate-950 border border-slate-850 rounded-lg px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-slate-700 transition" />
                  </div>
                  
                  <!-- Sort selectors -->
                  <div class="flex items-center gap-2 self-stretch sm:self-auto justify-between sm:justify-start">
                    <span class="text-[10px] font-mono text-slate-500 uppercase tracking-wider">Sort by:</span>
                    <div class="flex items-center gap-1">
                      <button v-for="field in ['value', 'balance', 'allocation']" :key="field"
                              @click="toggleHoldingsSort(field)"
                              class="text-[10px] font-mono font-bold uppercase tracking-wider px-2.5 py-1 rounded transition border"
                              :class="holdingsSortField === field ? 'bg-slate-900 border-slate-750 text-white' : 'bg-transparent border-transparent text-slate-400 hover:text-slate-300'">
                        {{ field }}
                        <span v-if="holdingsSortField === field">
                          {{ holdingsSortDirection === 'desc' ? '▼' : '▲' }}
                        </span>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- EMPTY STATE -->
                <div v-if="regularHoldingsValued.length === 0" class="p-12 text-center text-slate-400">
                  <Coins class="w-8 h-8 mx-auto mb-2 text-slate-650" />
                  <p class="text-sm font-semibold">No token holdings found.</p>
                  <p class="text-xs text-slate-500 font-mono mt-1">This wallet does not contain any assets or trustlines.</p>
                </div>

                <!-- TABLE -->
                <div v-else class="overflow-auto max-h-[350px] custom-scrollbar">
                  <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead>
                      <tr class="border-b border-slate-900 text-[10px] font-mono uppercase tracking-wider text-slate-500 bg-slate-950/20">
                        <th class="py-3.5 px-4 font-semibold cursor-pointer select-none" @click="toggleHoldingsSort('asset')">
                          ASSET
                          <span v-if="holdingsSortField === 'asset'">{{ holdingsSortDirection === 'desc' ? '▼' : '▲' }}</span>
                        </th>
                        <th class="py-3.5 px-4 font-semibold text-right cursor-pointer select-none" @click="toggleHoldingsSort('balance')">
                          BALANCE
                          <span v-if="holdingsSortField === 'balance'">{{ holdingsSortDirection === 'desc' ? '▼' : '▲' }}</span>
                        </th>
                        <th class="py-3.5 px-4 font-semibold text-right">PRICE</th>
                        <th class="py-3.5 px-4 font-semibold text-right cursor-pointer select-none" @click="toggleHoldingsSort('value')">
                          VALUE
                          <span v-if="holdingsSortField === 'value'">{{ holdingsSortDirection === 'desc' ? '▼' : '▲' }}</span>
                        </th>
                        <th class="py-3.5 px-4 font-semibold text-right cursor-pointer select-none" @click="toggleHoldingsSort('allocation')">
                          ALLOCATION
                          <span v-if="holdingsSortField === 'allocation'">{{ holdingsSortDirection === 'desc' ? '▼' : '▲' }}</span>
                        </th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900 text-xs font-mono">
                      <tr v-for="hold in regularHoldingsValued" :key="hold.id" class="hover:bg-slate-900/30 transition-colors">
                        <td class="py-3.5 px-4 flex items-center gap-3">
                          <div class="w-7 h-7 rounded-full bg-slate-950 border border-slate-850/40 flex items-center justify-center text-[10px] font-bold text-slate-400 select-none flex-shrink-0 overflow-hidden">
                            <img v-if="hold.logo_url" :src="hold.logo_url" class="w-full h-full object-cover" @error="hold.logo_url = null" />
                            <template v-else>
                              <span v-if="hold.asset_code === 'XLM'" class="text-cyan-400 text-xs">★</span>
                              <span v-else-if="hold.asset_type === 'liquidity_pool_shares'">LP</span>
                              <span v-else>{{ hold.asset_code?.slice(0, 3) }}</span>
                            </template>
                          </div>
                          <div>
                            <div class="font-sans font-bold text-white uppercase text-xs flex items-center gap-1.5">
                              <router-link v-if="hold.asset_code !== 'XLM' && hold.asset_type !== 'liquidity_pool_shares'"
                                           :to="{ path: '/token-insight', query: { asset_code: hold.asset_code, issuer: hold.asset_issuer } }"
                                           class="text-cyan-400 hover:text-cyan-300 transition-colors">
                                {{ hold.asset_code }}
                              </router-link>
                              <span v-else>{{ hold.asset_code }}</span>
                              <span v-if="hold.asset_type === 'liquidity_pool_shares'" class="text-[9px] lowercase bg-slate-900 text-slate-500 px-1 py-0.2 rounded">pool</span>
                            </div>
                            <div class="text-[10px] text-slate-500 break-all select-all max-w-[200px] truncate" :title="hold.asset_issuer">
                              {{ hold.asset_issuer ? shortenAddress(hold.asset_issuer) : 'Stellar Network' }}
                            </div>
                          </div>
                        </td>
                        <td class="py-3.5 px-4 text-right text-white font-semibold">
                          {{ formatNumber(hold.balance, 4) }}
                        </td>
                        <td class="py-3.5 px-4 text-right text-slate-400">
                          {{ hold.price_usd ? '$' + formatNumber(hold.price_usd, 6) : '--' }}
                        </td>
                        <td class="py-3.5 px-4 text-right text-white font-bold">
                          {{ hold.value_usd ? '$' + formatNumber(hold.value_usd, 2) : '--' }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                          <div class="flex items-center justify-end gap-2.5">
                            <span class="text-cyan-400 font-semibold">{{ formatNumber(hold.local_allocation_percentage, 1) }}%</span>
                            <div class="w-12 h-1.5 bg-slate-900 rounded overflow-hidden hidden sm:block">
                              <div class="bg-cyan-500 h-full rounded" :style="`width: ${hold.local_allocation_percentage}%`"></div>
                            </div>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- TRUSTLINES TAB -->
              <div v-else-if="activeAssetsTab === 'trustlines'">
                <!-- SEARCH CONTROLS -->
                <div class="px-6 py-3 flex flex-col sm:flex-row gap-3 items-center justify-between border-b border-slate-900 bg-slate-950/10">
                  <div class="relative w-full sm:max-w-xs">
                    <input type="text" 
                           v-model="trustlinesSearchQuery" 
                           placeholder="Search by code or issuer..." 
                           class="w-full bg-slate-950 border border-slate-850 rounded-lg px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-slate-700 transition" />
                  </div>
                  <div class="text-[10px] text-slate-500 font-mono">
                    Showing {{ Math.min(trustlinesLimit, trustlinesFiltered.length) }} of {{ trustlinesFiltered.length }} trustlines
                  </div>
                </div>

                <!-- EMPTY STATE -->
                <div v-if="trustlinesFiltered.length === 0" class="p-12 text-center text-slate-400">
                  <Coins class="w-8 h-8 mx-auto mb-2 text-slate-750" />
                  <p class="text-sm font-semibold">No trustlines match your query.</p>
                </div>

                <!-- TABLE -->
                <div v-else class="overflow-auto max-h-[350px] custom-scrollbar">
                  <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead>
                      <tr class="border-b border-slate-900 text-[10px] font-mono uppercase tracking-wider text-slate-500 bg-slate-950/20">
                        <th class="py-3.5 px-4 font-semibold">ASSET</th>
                        <th class="py-3.5 px-4 font-semibold">ISSUER</th>
                        <th class="py-3.5 px-4 font-semibold text-right">BALANCE</th>
                        <th class="py-3.5 px-4 font-semibold text-right">LIMIT</th>
                        <th class="py-3.5 px-4 font-semibold text-center">STATUS</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900 text-xs font-mono">
                      <tr v-for="hold in trustlinesPaginated" :key="hold.id" class="hover:bg-slate-900/30 transition-colors">
                        <!-- Asset code -->
                        <td class="py-3.5 px-4">
                          <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-slate-950 border border-slate-850/40 flex items-center justify-center text-[10px] font-bold text-slate-400 select-none flex-shrink-0 overflow-hidden">
                              <img v-if="hold.logo_url" :src="hold.logo_url" class="w-full h-full object-cover" @error="hold.logo_url = null" />
                              <template v-else>
                                <span v-if="hold.asset_type === 'liquidity_pool_shares'">LP</span>
                                <span v-else>{{ hold.asset_code?.slice(0, 3) }}</span>
                              </template>
                            </div>
                            <div>
                              <!-- Clickable asset code -->
                              <div class="font-sans font-bold text-white uppercase text-xs flex items-center gap-1.5">
                                <router-link v-if="hold.asset_code !== 'XLM' && hold.asset_type !== 'liquidity_pool_shares'"
                                             :to="{ path: '/token-insight', query: { asset_code: hold.asset_code, issuer: hold.asset_issuer } }"
                                             class="text-cyan-400 hover:text-cyan-300 transition-colors">
                                  {{ hold.asset_code }}
                                </router-link>
                                <span v-else>{{ hold.asset_code }}</span>
                                <span v-if="hold.asset_type === 'liquidity_pool_shares'" class="text-[9px] lowercase bg-slate-900 text-slate-500 px-1 py-0.2 rounded">pool</span>
                              </div>
                              <div class="text-[10.5px] text-slate-500 uppercase tracking-widest font-mono">
                                {{ hold.asset_type === 'liquidity_pool_shares' ? 'pool shares' : (hold.asset_type?.replace('credit_alphanum', 'alpha') || 'issued') }}
                              </div>
                            </div>
                          </div>
                        </td>

                        <!-- Issuer (shortened and copyable) -->
                        <td class="py-3.5 px-4">
                          <div v-if="hold.asset_issuer" class="flex items-center gap-2">
                            <span class="text-xs text-slate-400 select-all font-mono">
                              {{ shortenAddress(hold.asset_issuer) }}
                            </span>
                            <button @click="copyIssuer(hold.asset_issuer, hold.id)" class="p-1 bg-slate-950 border border-slate-850 hover:border-slate-750 rounded text-slate-400 hover:text-white transition flex-shrink-0">
                              <Check v-if="issuerCopiedId === hold.id" class="w-3 h-3 text-emerald-400" />
                              <Copy v-else class="w-3 h-3 text-slate-500" />
                            </button>
                          </div>
                          <span class="text-slate-650">--</span>
                        </td>

                        <!-- Balance -->
                        <td class="py-3.5 px-4 text-right text-white font-semibold">
                          {{ formatNumber(hold.balance, 4) }} <span class="text-[10px] text-slate-500 font-normal uppercase">{{ hold.asset_code }}</span>
                        </td>

                        <!-- Limit -->
                        <td class="py-3.5 px-4 text-right text-slate-400 font-mono">
                          {{ hold.limit !== null && hold.limit !== undefined ? formatNumber(hold.limit, 7) : '--' }}
                        </td>

                        <!-- Status flag badge -->
                        <td class="py-3.5 px-4 text-center">
                          <span class="text-[10.5px] font-mono px-2 py-0.5 rounded"
                                :class="{
                                  'bg-emerald-950/40 text-emerald-400 border border-emerald-900/30': getTrustlineStatus(hold) === 'Active',
                                  'bg-amber-950/40 text-amber-400 border border-amber-900/30': getTrustlineStatus(hold) === 'Maintain Liabilities Only',
                                  'bg-rose-950/40 text-rose-400 border border-rose-900/30': getTrustlineStatus(hold) === 'Unauthorized',
                                  'bg-slate-900 text-slate-550': getTrustlineStatus(hold) === '--'
                                }">
                            {{ getTrustlineStatus(hold) }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- LOAD MORE TRUSTLINES PAGINATION -->
                <div v-if="hasMoreTrustlines" class="p-4 text-center border-t border-slate-900 bg-slate-950/10">
                  <button @click="loadMoreTrustlines" 
                          class="text-[10px] font-mono font-bold uppercase tracking-wider px-5 py-2 bg-slate-900 border border-slate-880 hover:border-slate-700 transition rounded-lg text-slate-300">
                    Load More Trustlines
                  </button>
                </div>
              </div>

              <!-- POOLS TAB -->
              <div v-else-if="activeAssetsTab === 'pools'">
                <!-- SEARCH AND SORT CONTROLS -->
                <div class="px-6 py-3 flex flex-col sm:flex-row gap-3 items-center justify-between border-b border-slate-900 bg-slate-950/10">
                  <div class="relative w-full sm:max-w-xs">
                    <input type="text" 
                           v-model="poolsSearchQuery" 
                           placeholder="Search pools..." 
                           class="w-full bg-slate-950 border border-slate-850 rounded-lg px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-slate-700 transition" />
                  </div>
                  
                  <div class="flex items-center gap-2 self-stretch sm:self-auto justify-between sm:justify-start">
                    <span class="text-[10px] font-mono text-slate-500 uppercase tracking-wider">Sort by:</span>
                    <div class="flex items-center gap-1">
                      <button v-for="field in ['value', 'balance', 'allocation']" :key="field"
                              @click="togglePoolsSort(field)"
                              class="text-[10px] font-mono font-bold uppercase tracking-wider px-2.5 py-1 rounded transition border"
                              :class="poolsSortField === field ? 'bg-slate-900 border-slate-750 text-white' : 'bg-transparent border-transparent text-slate-400 hover:text-slate-300'">
                        {{ field }}
                        <span v-if="poolsSortField === field">
                          {{ poolsSortDirection === 'desc' ? '▼' : '▲' }}
                        </span>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- POOLS TABLE -->
                <div class="overflow-auto max-h-[350px] custom-scrollbar">
                  <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead>
                      <tr class="border-b border-slate-900 text-[10px] text-slate-500 font-mono uppercase tracking-wider bg-slate-950/20">
                        <th class="py-3.5 px-4 font-semibold text-left">LIQUIDITY POOL</th>
                        <th class="py-3.5 px-4 font-semibold text-right cursor-pointer select-none" @click="togglePoolsSort('balance')">
                          USER SHARES
                          <span v-if="poolsSortField === 'balance'">{{ poolsSortDirection === 'desc' ? '▼' : '▲' }}</span>
                        </th>
                        <th class="py-3.5 px-4 font-semibold text-right">SHARE PRICE</th>
                        <th class="py-3.5 px-4 font-semibold text-right cursor-pointer select-none" @click="togglePoolsSort('value')">
                          VALUATION
                          <span v-if="poolsSortField === 'value'">{{ poolsSortDirection === 'desc' ? '▼' : '▲' }}</span>
                        </th>
                        <th class="py-3.5 px-4 font-semibold text-right cursor-pointer select-none" @click="togglePoolsSort('allocation')">
                          ALLOCATION
                          <span v-if="poolsSortField === 'allocation'">{{ poolsSortDirection === 'desc' ? '▼' : '▲' }}</span>
                        </th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900 text-xs font-mono">
                      <tr v-for="hold in poolsValued" :key="hold.id" class="hover:bg-slate-900/30 transition-colors">
                        <td class="py-3.5 px-4 flex items-center gap-3">
                          <div class="w-7 h-7 rounded-full bg-slate-950 border border-slate-850/40 flex items-center justify-center text-[10px] font-bold text-slate-400 select-none flex-shrink-0 overflow-hidden">
                            <span class="text-[9px] uppercase font-bold text-amber-500">LP</span>
                          </div>
                          <div>
                            <div class="font-sans font-bold text-white uppercase text-xs flex items-center gap-1.5">
                              {{ hold.asset_code }}
                              <span class="text-[9px] lowercase bg-slate-900 text-slate-500 px-1 py-0.2 rounded">pool</span>
                            </div>
                            <div class="text-[10px] text-slate-500 break-all select-all max-w-[200px] truncate" :title="hold.pool_id">
                              ID: {{ hold.pool_id ? shortenAddress(hold.pool_id) : 'Unknown Pool' }}
                            </div>
                          </div>
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-white">{{ formatNumber(hold.balance, 4) }}</td>
                        <td class="py-3.5 px-4 text-right font-mono text-slate-400">${{ formatNumber(hold.price_usd, 4) }}</td>
                        <td class="py-3.5 px-4 text-right font-mono text-cyan-400 font-bold">${{ formatNumber(hold.value_usd, 2) }}</td>
                        <td class="py-3.5 px-4 text-right font-mono text-slate-400">{{ formatNumber(hold.local_allocation_percentage, 1) }}%</td>
                      </tr>
                      <tr v-if="poolsValued.length === 0">
                        <td colspan="5" class="py-8 px-4 text-center text-xs font-mono text-slate-500">
                          No liquidity pools found matching search filter
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </section>
          </div>

          <!-- ALLOCATION DONUT CHART (RIGHT 1 COL) -->
          <div class="space-y-6">
            <section class="card h-full flex flex-col">
              <div class="card-hd">
                <h3 class="flex items-center gap-2">
                  <span class="dot"></span> Asset Allocation
                </h3>
                <span class="tag">Allocation</span>
              </div>
              
              <div class="p-6 flex-1 flex flex-col justify-center items-center space-y-6">
                <!-- SVG Donut Chart -->
                <div class="relative w-44 h-44 flex items-center justify-center">
                  <svg class="w-full h-full transform -rotate-90" viewBox="0 0 42 42">
                    <circle class="donut-hole" cx="21" cy="21" r="15.91549430918954" fill="transparent"></circle>
                    <circle class="donut-ring" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#1D2531" stroke-width="3.5"></circle>
                    
                    <circle v-for="(slice, index) in donutSlices" :key="index"
                            cx="21" cy="21" r="15.91549430918954" fill="transparent"
                            :stroke="slice.color" stroke-width="3.5"
                            :stroke-dasharray="`${slice.percentage} ${100 - slice.percentage}`"
                            :stroke-dashoffset="slice.offset"
                            class="donut-segment transition-all duration-300"></circle>
                  </svg>
                  
                  <div class="absolute text-center space-y-0.5 select-none pointer-events-none">
                    <span class="text-[10px] text-slate-500 font-mono uppercase tracking-wider block">PORTFOLIO</span>
                    <span class="text-lg font-extrabold text-white font-mono leading-none">
                      {{ overviewData?.portfolio_value_usd !== null && overviewData?.portfolio_value_usd !== undefined ? '$' + formatNumber(overviewData.portfolio_value_usd, 2) : '--' }}
                    </span>
                  </div>
                </div>

                <!-- Donut Legend -->
                <div class="w-full space-y-2">
                  <div v-for="slice in legendSlices" :key="slice.label" class="flex items-center justify-between text-xs font-mono p-1 rounded hover:bg-slate-900/20">
                    <div class="flex items-center gap-2">
                      <span class="w-2.5 h-2.5 rounded-sm" :style="`background-color: ${slice.color}`"></span>
                      <span class="text-white font-semibold">{{ slice.label }}</span>
                    </div>
                    <span class="text-slate-400">{{ formatNumber(slice.percentage, 1) }}%</span>
                  </div>
                </div>
              </div>
            </section>
          </div>

        </div>

        <!-- SECTION 3: HISTORICAL PORTFOLIO CHART -->
        <section class="card relative">
          <div class="card-hd">
            <h3 class="flex items-center gap-2">
              <span class="dot"></span> Historical Portfolio Value
            </h3>
            
            <!-- Filter Controls -->
            <div class="flex items-center gap-3">
              <!-- Asset Choice -->
              <select v-model="historyFilterAsset" @change="loadHistoryData"
                      class="bg-slate-900 border border-slate-800 text-xs font-mono text-slate-300 p-1.5 rounded-lg outline-none focus:border-cyan-400 max-w-[150px]">
                <option value="portfolio">Portfolio (Total)</option>
                <option value="XLM">XLM</option>
                <option v-for="asset in nonXlmHoldings" :key="asset.id" :value="asset.asset_code">
                  {{ asset.asset_code }}
                </option>
              </select>

              <!-- Time Filters -->
              <div class="flex items-center bg-slate-950 p-1 rounded-lg border border-slate-900">
                <button v-for="tf in timeframes" :key="tf" 
                        @click="changeTimeframe(tf)"
                        class="text-[10px] font-mono font-bold uppercase tracking-wider px-2.5 py-1 rounded transition"
                        :class="activeTimeframe === tf ? 'bg-cyan-500 text-slate-950' : 'text-slate-500 hover:text-slate-300'">
                  {{ tf }}
                </button>
              </div>
            </div>
          </div>

          <div class="p-6 space-y-4">
            <!-- PORTFOLIO VALUE CHANGE STATS -->
            <div v-if="historyChange" class="flex items-baseline gap-3 pb-2 font-mono">
              <span class="text-2xl font-extrabold text-white">${{ formatNumber(historyChange.latestValue, 2) }}</span>
              <span class="text-xs font-bold flex items-center gap-1" :class="historyChange.isPositive ? 'text-emerald-400' : 'text-rose-400'">
                <span>{{ historyChange.isPositive ? '▲' : '▼' }}</span>
                <span>{{ historyChange.isPositive ? '+' : '' }}${{ formatNumber(historyChange.absolute, 2) }}</span>
                <span>({{ historyChange.isPositive ? '+' : '' }}{{ formatNumber(historyChange.percentage, 2) }}%)</span>
                <span class="text-slate-500 font-sans font-normal text-[10px] ml-1">{{ activeTimeframe }}</span>
              </span>
            </div>

            <!-- NOTIFY TRACKING START DATE -->
            <div v-if="historyStartDate" class="text-[10.5px] font-mono text-slate-500 flex items-center gap-1.5">
              <span>ⓘ</span> Historical tracking available since <span class="text-slate-400 font-semibold">{{ formatDate(historyStartDate) }}</span>.
            </div>

            <!-- CHART WRAPPER -->
            <div class="relative w-full h-[320px] rounded-xl border border-slate-900 bg-[#111620] overflow-hidden">
              <!-- Loader -->
              <div v-if="historyLoading" class="absolute inset-0 bg-[#111620]/80 z-20 flex flex-col justify-center items-center space-y-2">
                <Loader2 class="w-6 h-6 animate-spin text-cyan-400" />
                <span class="text-xs font-mono text-slate-500">Loading historical data...</span>
              </div>
              
              <!-- Empty state -->
              <div v-else-if="!historyData || historyData.length === 0" 
                   class="absolute inset-0 z-10 flex flex-col justify-center items-center p-8 text-center text-slate-400 space-y-3">
                <BarChart3 class="w-10 h-10 text-slate-700 animate-pulse" />
                <p class="text-sm font-semibold">No historical snapshots available yet</p>
                <div class="text-xs text-slate-500 font-mono space-y-1">
                  <div>Tracking started on: <span class="text-slate-400 font-bold">{{ formatDate(overviewData?.first_indexed_at || overviewData?.created_at) }}</span></div>
                  <div class="text-[11px] max-w-sm mt-2 text-slate-600">Snapshots are taken every 6 hours for tracked wallets. Historical logs from before this tracking date do not exist.</div>
                </div>
              </div>

              <!-- Container -->
              <div ref="chartContainer" class="w-full h-full"></div>
            </div>
          </div>
        </section>

        <!-- SECTION 4: CLAIMABLE BALANCES (COLLAPSIBLE) -->
        <section class="card">
          <button @click="isClaimsCollapsed = !isClaimsCollapsed" 
                  class="w-full flex items-center justify-between p-4 hover:bg-slate-900/20 transition-colors focus:outline-none select-none">
            <div class="flex items-center gap-3">
              <span class="dot" style="background-color: #FF8A3D; box-shadow: 0 0 8px #FF8A3D"></span>
              <span class="font-sans font-bold text-white text-sm">Claimable Balances</span>
              <span class="text-xs bg-slate-900 border border-slate-880 text-slate-400 px-2 py-0.5 rounded font-mono">
                {{ overviewData?.claimable_count ?? 0 }} Claims
              </span>
              <span class="text-xs text-slate-500 font-mono hidden sm:inline">
                | Claimable Value: ${{ overviewData?.claimable_value_usd ? formatNumber(overviewData.claimable_value_usd, 2) : '0.00' }}
              </span>
            </div>
            <div class="flex items-center gap-1 text-xs text-cyan-400 hover:text-cyan-300 font-mono">
              <span>{{ isClaimsCollapsed ? 'View Details' : 'Hide Details' }}</span>
              <span>{{ isClaimsCollapsed ? '→' : '↓' }}</span>
            </div>
          </button>

          <!-- COLLAPSIBLE CONTENT -->
          <div v-show="!isClaimsCollapsed" class="border-t border-slate-900">
            <!-- Empty state -->
            <div v-if="claimableHoldings.length === 0" class="p-8 text-center text-slate-400">
              <p class="text-sm font-semibold">No pending claimable balances found.</p>
            </div>
            <!-- Claims table -->
            <div v-else class="overflow-auto max-h-[350px] custom-scrollbar">
              <table class="w-full text-left border-collapse min-w-[600px]">
                <thead>
                  <tr class="border-b border-slate-900 text-[10px] font-mono uppercase tracking-wider text-slate-500 bg-slate-950/20">
                    <th class="py-3.5 px-4 font-semibold">Asset</th>
                    <th class="py-3.5 px-4 font-semibold text-right">Balance</th>
                    <th class="py-3.5 px-4 font-semibold text-right">Price (USD)</th>
                    <th class="py-3.5 px-4 font-semibold text-right">Value (USD)</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-900 text-xs font-mono">
                  <tr v-for="hold in claimableHoldings" :key="hold.id" class="hover:bg-slate-900/30 transition-colors">
                    <td class="py-3.5 px-4 flex items-center gap-3">
                      <div class="w-7 h-7 rounded-full bg-slate-950 border border-slate-850/40 flex items-center justify-center text-[10px] font-bold text-slate-400 select-none flex-shrink-0 overflow-hidden">
                        <img v-if="hold.logo_url" :src="hold.logo_url" class="w-full h-full object-cover" @error="hold.logo_url = null" />
                        <template v-else>
                          <span v-if="hold.asset_code === 'XLM'" class="text-cyan-400 text-xs">★</span>
                          <span v-else>{{ hold.asset_code?.slice(0, 3) }}</span>
                        </template>
                      </div>
                      <div>
                        <div class="font-sans font-bold text-white uppercase text-xs flex items-center gap-1.5">
                          <router-link v-if="hold.asset_code !== 'XLM' && hold.asset_type !== 'liquidity_pool_shares'"
                                       :to="{ path: '/token-insight', query: { asset_code: hold.asset_code, issuer: hold.asset_issuer } }"
                                       class="text-cyan-400 hover:text-cyan-300 transition-colors">
                            {{ hold.asset_code }}
                          </router-link>
                          <span v-else>{{ hold.asset_code }}</span>
                          <span class="text-[9px] lowercase bg-amber-950/40 text-amber-550 px-1 py-0.2 rounded">claimable</span>
                        </div>
                        <div class="text-[10px] text-slate-500 break-all select-all max-w-[200px] truncate" :title="hold.asset_issuer">
                          {{ hold.asset_issuer ? shortenAddress(hold.asset_issuer) : 'Stellar Network' }}
                        </div>
                      </div>
                    </td>
                    <td class="py-3.5 px-4 text-right text-white font-semibold">
                      {{ formatNumber(hold.balance, 4) }}
                    </td>
                    <td class="py-3.5 px-4 text-right text-slate-400">
                      {{ hold.price_usd ? '$' + formatNumber(hold.price_usd, 6) : '--' }}
                    </td>
                    <td class="py-3.5 px-4 text-right text-white font-bold">
                      {{ hold.value_usd ? '$' + formatNumber(hold.value_usd, 2) : '--' }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- ACTIVITY AND BEHAVIOR GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- SECTION 4: TRADING ACTIVITY (LEFT 2 COLS) -->
          <div class="lg:col-span-2 space-y-6">
            <section class="card">
              <div class="card-hd">
                <h3 class="flex items-center gap-2">
                  <span class="dot"></span> Trading Activity
                </h3>
                <span class="tag">Blockchain Events</span>
              </div>

              <!-- Filter tabs -->
              <div class="flex items-center gap-1 border-b border-slate-900 bg-slate-950/20 p-2 overflow-x-auto whitespace-nowrap scrollbar-none">
                <button v-for="tab in activityTabs" :key="tab.value"
                        @click="changeActivityTab(tab.value)"
                        class="text-[10px] font-mono font-bold uppercase tracking-wider px-3.5 py-2 rounded-lg transition"
                        :class="activeActivityTab === tab.value ? 'bg-slate-900 border border-slate-850 text-white font-extrabold' : 'text-slate-400 hover:text-slate-300'">
                  {{ tab.label }}
                </button>
              </div>

              <!-- SKELETON LOADER -->
              <div v-if="activityLoading" class="p-6 space-y-4 animate-pulse">
                <div class="h-10 bg-slate-900 rounded-lg w-full" v-for="i in 5" :key="i"></div>
              </div>

              <!-- EMPTY STATE -->
              <div v-else-if="filteredEvents.length === 0" class="p-16 text-center text-slate-400">
                <Activity class="w-10 h-10 mx-auto mb-2 text-slate-700 animate-pulse" />
                <p class="text-sm font-semibold">No activity events found in this category.</p>
                <p class="text-xs text-slate-500 font-mono mt-1">If the wallet is new, indexing may take a moment to finish.</p>
              </div>

              <!-- EVENTS LIST -->
              <div v-else class="divide-y divide-slate-900">
                <div v-for="event in filteredEvents" :key="event.id" class="p-4 flex items-center justify-between gap-4 hover:bg-slate-900/10 transition-colors">
                  <!-- Left & Center details -->
                  <div class="flex items-center gap-4 min-w-0">
                    <div class="min-w-0">
                      <!-- Human-Readable Cards -->
                      <div class="font-sans text-xs text-slate-300">
                        <!-- BUY -->
                        <div v-if="event.event_type === 'BUY'" class="space-y-0.5">
                          <div>
                            <span class="text-[10px] uppercase font-mono font-bold tracking-widest text-emerald-400 bg-emerald-950/40 px-2 py-0.5 rounded border border-emerald-900/30 mr-2">BUY</span>
                            <span class="font-bold text-white">Bought {{ formatNumber(event.amount, 2) }} {{ event.asset_code }}</span>
                          </div>
                          <div class="text-[10px] text-slate-500 font-mono">
                            Spent {{ formatNumber(event.counter_amount, 2) }} {{ event.counter_asset_code || 'XLM' }}
                          </div>
                        </div>

                        <!-- SELL -->
                        <div v-else-if="event.event_type === 'SELL'" class="space-y-0.5">
                          <div>
                            <span class="text-[10px] uppercase font-mono font-bold tracking-widest text-rose-400 bg-rose-950/40 px-2 py-0.5 rounded border border-rose-900/30 mr-2">SELL</span>
                            <span class="font-bold text-white">Sold {{ formatNumber(event.amount, 2) }} {{ event.asset_code }}</span>
                          </div>
                          <div class="text-[10px] text-slate-500 font-mono">
                            Received {{ formatNumber(event.counter_amount, 2) }} {{ event.counter_asset_code || 'XLM' }}
                          </div>
                        </div>

                        <!-- PAYMENT IN -->
                        <div v-else-if="event.event_type === 'PAYMENT_IN'" class="space-y-0.5">
                          <div>
                            <span class="text-[10px] uppercase font-mono font-bold tracking-widest text-teal-400 bg-teal-950/40 px-2 py-0.5 rounded border border-teal-900/30 mr-2">INCOMING</span>
                            <span class="font-bold text-emerald-400">+{{ formatNumber(event.amount, 2) }} {{ event.asset_code || 'XLM' }}</span>
                          </div>
                          <div class="text-[10px] text-slate-500 font-mono">
                            Received from <span class="text-slate-400 select-all font-semibold">{{ shortenAddress(event.counterparty_address) }}</span>
                          </div>
                        </div>

                        <!-- PAYMENT OUT -->
                        <div v-else-if="event.event_type === 'PAYMENT_OUT'" class="space-y-0.5">
                          <div>
                            <span class="text-[10px] uppercase font-mono font-bold tracking-widest text-purple-400 bg-purple-950/40 px-2 py-0.5 rounded border border-purple-900/30 mr-2">OUTGOING</span>
                            <span class="font-bold text-rose-400">-{{ formatNumber(event.amount, 2) }} {{ event.asset_code || 'XLM' }}</span>
                          </div>
                          <div class="text-[10px] text-slate-500 font-mono">
                            Sent to <span class="text-slate-400 select-all font-semibold">{{ shortenAddress(event.counterparty_address) }}</span>
                          </div>
                        </div>

                        <!-- LP ADD -->
                        <div v-else-if="event.event_type === 'LP_ADD'" class="space-y-0.5">
                          <div>
                            <span class="text-[10px] uppercase font-mono font-bold tracking-widest text-cyan-400 bg-cyan-950/40 px-2 py-0.5 rounded border border-cyan-900/30 mr-2">LP ADD</span>
                            <span class="font-bold text-white">Added Liquidity</span>
                          </div>
                          <div class="text-[10px] text-slate-500 font-mono">
                            Provided {{ formatNumber(event.amount, 2) }} {{ event.asset_code || 'LP' }}
                          </div>
                        </div>

                        <!-- LP REMOVE -->
                        <div v-else-if="event.event_type === 'LP_REMOVE'" class="space-y-0.5">
                          <div>
                            <span class="text-[10px] uppercase font-mono font-bold tracking-widest text-cyan-400 bg-cyan-950/40 px-2 py-0.5 rounded border border-cyan-900/30 mr-2">LP REMOVE</span>
                            <span class="font-bold text-white">Removed Liquidity</span>
                          </div>
                          <div class="text-[10px] text-slate-500 font-mono">
                            Withdrew {{ formatNumber(event.amount, 2) }} {{ event.asset_code || 'LP' }}
                          </div>
                        </div>

                        <!-- TRUSTLINE ADD -->
                        <div v-else-if="event.event_type === 'TRUSTLINE_ADD'" class="space-y-0.5">
                          <div>
                            <span class="text-[10px] uppercase font-mono font-bold tracking-widest text-slate-400 bg-slate-900 border border-slate-800 px-2 py-0.5 rounded mr-2">TRUSTLINE</span>
                            <span class="font-bold text-white">Added Trustline</span>
                          </div>
                          <div class="text-[10px] text-slate-500 font-mono">
                            Opened trustline for token <span class="text-slate-400 font-semibold">{{ event.asset_code }}</span>
                          </div>
                        </div>

                        <!-- TRUSTLINE REMOVE -->
                        <div v-else-if="event.event_type === 'TRUSTLINE_REMOVE'" class="space-y-0.5">
                          <div>
                            <span class="text-[10px] uppercase font-mono font-bold tracking-widest text-slate-500 bg-slate-900 border border-slate-800 px-2 py-0.5 rounded mr-2">TRUSTLINE</span>
                            <span class="font-bold text-white">Removed Trustline</span>
                          </div>
                          <div class="text-[10px] text-slate-500 font-mono">
                            Closed trustline for token <span class="text-slate-400 font-semibold">{{ event.asset_code }}</span>
                          </div>
                        </div>

                        <!-- CLAIMABLE BALANCE CLAIM -->
                        <div v-else-if="event.event_type === 'CLAIMABLE_BALANCE_CLAIM'" class="space-y-0.5">
                          <div>
                            <span class="text-[10px] uppercase font-mono font-bold tracking-widest text-amber-400 bg-amber-950/40 px-2 py-0.5 rounded border border-amber-900/30 mr-2">CLAIM</span>
                            <span class="font-bold text-white">Claimed Pending Balance</span>
                          </div>
                          <div class="text-[10px] text-slate-500 font-mono">
                            Claimed +{{ formatNumber(event.amount, 2) }} {{ event.asset_code || 'XLM' }}
                          </div>
                        </div>

                        <!-- DEFAULT -->
                        <div v-else class="space-y-0.5">
                          <div>
                            <span class="text-[10px] uppercase font-mono font-bold tracking-widest text-slate-500 bg-slate-900 border border-slate-800 px-2 py-0.5 rounded mr-2">OTHER</span>
                            <span class="font-bold text-white">{{ getEventName(event.event_type) }}</span>
                          </div>
                          <div class="text-[10px] text-slate-500 font-mono" v-if="event.amount">
                            Amount: {{ formatNumber(event.amount, 2) }} {{ event.asset_code }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Right details: Value & link -->
                  <div class="text-right flex-shrink-0 space-y-1 font-mono">
                    <div class="text-xs text-white font-extrabold">
                      {{ event.value_usd ? '$' + formatNumber(event.value_usd, 2) : '--' }}
                    </div>
                    <div class="text-[10px] text-slate-500 flex items-center justify-end gap-1.5">
                      <span>{{ formatRelativeTime(event.occurred_at) }}</span>
                      <a target="_blank" 
                         :href="`https://stellar.expert/explorer/public/tx/${event.transaction_hash}`" 
                         class="p-1 bg-slate-900 border border-slate-850 hover:border-slate-750 rounded text-slate-400 hover:text-white transition">
                        <ArrowUpRight class="w-3 h-3" />
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <!-- PAGINATION LOAD MORE -->
              <div v-if="hasMoreEvents" class="p-4 text-center border-t border-slate-900 bg-slate-950/10">
                <button @click="loadMoreEvents" 
                        class="text-[10px] font-mono font-bold uppercase tracking-wider px-5 py-2 bg-slate-900 border border-slate-800 hover:border-slate-700 transition rounded-lg text-slate-300">
                  Load More Activity
                </button>
              </div>
            </section>
          </div>

          <!-- SECTION 5: TRADING BEHAVIOR (RIGHT 1 COL) -->
          <div class="space-y-6">
            <section class="card">
              <div class="card-hd">
                <h3 class="flex items-center gap-2">
                  <span class="dot"></span> Trading Behavior
                </h3>
                <span class="tag">Behavior Analysis</span>
              </div>

              <!-- SKELETON LOADER -->
              <div v-if="metricsLoading" class="p-6 space-y-4 animate-pulse">
                <div class="h-12 bg-slate-900 rounded-lg w-full" v-for="i in 5" :key="i"></div>
              </div>

              <!-- INDEXING STATE -->
              <div v-else-if="isIndexing" class="p-12 text-center text-slate-400">
                <BarChart3 class="w-8 h-8 mx-auto mb-2 text-slate-750 animate-pulse" />
                <p class="text-xs font-mono text-slate-500">
                  Trading behavior will appear after wallet activity indexing is complete.
                </p>
              </div>

              <!-- EMPTY STATE -->
              <div v-else-if="!metricsData" class="p-12 text-center text-slate-400">
                <BarChart3 class="w-8 h-8 mx-auto mb-2 text-slate-750" />
                <p class="text-sm font-semibold">Metrics unavailable</p>
                <p class="text-xs text-slate-500 font-mono mt-1">Check back once indexing has been completed.</p>
              </div>

              <!-- METRICS DISPLAY -->
              <div v-else class="divide-y divide-slate-900">
                
                <div class="p-4 flex items-center justify-between text-xs font-mono">
                  <span class="text-slate-500 font-sans">Buy Volume (24H)</span>
                  <span class="text-emerald-400 font-semibold text-right leading-none">
                    {{ metricsData.buy_volume_xlm_24h !== null && metricsData.buy_volume_xlm_24h !== undefined ? formatNumber(metricsData.buy_volume_xlm_24h, 2) + ' XLM' : '--' }}
                  </span>
                </div>

                <div class="p-4 flex items-center justify-between text-xs font-mono">
                  <span class="text-slate-500 font-sans">Sell Volume (24H)</span>
                  <span class="text-rose-400 font-semibold text-right leading-none">
                    {{ metricsData.sell_volume_xlm_24h !== null && metricsData.sell_volume_xlm_24h !== undefined ? formatNumber(metricsData.sell_volume_xlm_24h, 2) + ' XLM' : '--' }}
                  </span>
                </div>

                <div class="p-4 flex items-center justify-between text-xs font-mono">
                  <span class="text-slate-500 font-sans">Buy Volume (7D)</span>
                  <span class="text-emerald-400 font-bold text-right leading-none">
                    {{ metricsData.buy_volume_xlm_7d !== null && metricsData.buy_volume_xlm_7d !== undefined ? formatNumber(metricsData.buy_volume_xlm_7d, 2) + ' XLM' : '--' }}
                  </span>
                </div>

                <div class="p-4 flex items-center justify-between text-xs font-mono">
                  <span class="text-slate-500 font-sans">Sell Volume (7D)</span>
                  <span class="text-rose-400 font-bold text-right leading-none">
                    {{ metricsData.sell_volume_xlm_7d !== null && metricsData.sell_volume_xlm_7d !== undefined ? formatNumber(metricsData.sell_volume_xlm_7d, 2) + ' XLM' : '--' }}
                  </span>
                </div>

                <div class="p-4 flex items-center justify-between text-xs font-mono">
                  <span class="text-slate-500 font-sans">Avg Trade Size</span>
                  <span class="text-white font-semibold text-right leading-none">
                    {{ metricsData.average_trade_size_xlm !== null && metricsData.average_trade_size_xlm !== undefined ? formatNumber(metricsData.average_trade_size_xlm, 2) + ' XLM' : '--' }}
                  </span>
                </div>

                <div class="p-4 flex items-center justify-between text-xs font-mono">
                  <span class="text-slate-500 font-sans">Largest Trade</span>
                  <span class="text-cyan-400 font-extrabold text-right leading-none">
                    {{ metricsData.largest_trade_xlm !== null && metricsData.largest_trade_xlm !== undefined ? formatNumber(metricsData.largest_trade_xlm, 2) + ' XLM' : '--' }}
                  </span>
                </div>

                <div class="p-4 flex items-center justify-between text-xs font-mono">
                  <span class="text-slate-500 font-sans">Tx Count (24H)</span>
                  <span class="text-slate-300 text-right leading-none font-semibold">
                    {{ metricsData.transaction_count_24h !== null && metricsData.transaction_count_24h !== undefined ? metricsData.transaction_count_24h : '--' }}
                  </span>
                </div>

                <div class="p-4 flex items-center justify-between text-xs font-mono">
                  <span class="text-slate-500 font-sans">Tx Count (7D)</span>
                  <span class="text-slate-300 text-right leading-none font-semibold">
                    {{ metricsData.transaction_count_7d !== null && metricsData.transaction_count_7d !== undefined ? metricsData.transaction_count_7d : '--' }}
                  </span>
                </div>

                <div class="p-4 flex items-center justify-between text-xs font-mono">
                  <span class="text-slate-500 font-sans">Most Traded</span>
                  <span class="text-slate-300 text-right uppercase tracking-wider font-extrabold">
                    {{ mostTradedAsset }}
                  </span>
                </div>

              </div>
            </section>
          </div>

        </div>

      </div>

    </div>
    
    <Footer />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";
import { createChart, CrosshairMode } from "lightweight-charts";
import { Copy, Check, AlertCircle, Coins, Loader2, BarChart3, Activity, ArrowUpRight } from "lucide-vue-next";

import Header from "@/components/Header.vue";
import Footer from "@/components/Footer.vue";

const route = useRoute();
const address = computed(() => route.params.address);

// State vars
const overviewLoading = ref(true);
const overviewData = ref(null);
const notFound = ref(false);
const connectionError = ref(false);

const holdingsLoading = ref(true);
const holdings = ref([]);

const historyLoading = ref(true);
const historyRawData = ref({ portfolio: [], assets: [] });
const historyFilterAsset = ref("portfolio");
const historyData = computed(() => {
  const selected = historyFilterAsset.value;
  if (selected === 'portfolio') {
    return historyRawData.value.portfolio ?? [];
  } else {
    return (historyRawData.value.assets ?? []).filter(snap => snap.asset_code === selected);
  }
});
const timeframes = ["24H", "7D", "30D", "90D", "1Y", "ALL"];
const activeTimeframe = ref("ALL");

const activityLoading = ref(true);
const events = ref([]);
const activeActivityTab = ref("all");
const eventsPage = ref(1);
const hasMoreEvents = ref(false);

const metricsLoading = ref(true);
const metricsData = ref(null);

const copied = ref(false);
const holdingsSearchQuery = ref('');
const holdingsSortField = ref('value');
const holdingsSortDirection = ref('desc');
const isClaimsCollapsed = ref(true);

const trustlinesSearchQuery = ref('');
const trustlinesLimit = ref(10);
const issuerCopiedId = ref(null);
const activeAssetsTab = ref('holdings');

const poolsSearchQuery = ref('');
const poolsSortField = ref('value');
const poolsSortDirection = ref('desc');

function togglePoolsSort(field) {
  if (poolsSortField.value === field) {
    poolsSortDirection.value = poolsSortDirection.value === 'desc' ? 'asc' : 'desc';
  } else {
    poolsSortField.value = field;
    poolsSortDirection.value = 'desc';
  }
}

function copyIssuer(issuer, id) {
  navigator.clipboard.writeText(issuer);
  issuerCopiedId.value = id;
  setTimeout(() => {
    if (issuerCopiedId.value === id) {
      issuerCopiedId.value = null;
    }
  }, 2000);
}

function getTrustlineStatus(hold) {
  if (hold.asset_type === 'native') return 'Active';
  if (hold.is_authorized === undefined || hold.is_authorized === null) {
    return '--';
  }
  if (hold.is_authorized) {
    return 'Active';
  }
  if (hold.is_authorized_to_maintain_liabilities) {
    return 'Maintain Liabilities Only';
  }
  return 'Unauthorized';
}

let indexingPollTimer = null;
let chartInstance = null;
let lineSeriesInstance = null;
const chartContainer = ref(null);

// Lifecycle priorities
const isIndexing = computed(() => {
  return overviewData.value && !overviewData.value.historical_index_complete;
});

// Color generator for allocations
const donutColors = ["#12CBEE", "#F0189C", "#FF8A3D", "#A855F7", "#2ED47A", "#3B82F6", "#F59E0B", "#64748B"];

const sortedHoldings = computed(() => {
  return [...holdings.value].sort((a, b) => (b.value_usd || 0) - (a.value_usd || 0));
});

const holdingsOnly = computed(() => {
  return sortedHoldings.value.filter(h => h.asset_type !== 'claimable_balance' && h.asset_type !== 'liquidity_pool_shares' && h.balance > 0);
});

const trustlinesOnly = computed(() => {
  return sortedHoldings.value.filter(h => h.asset_type !== 'claimable_balance' && h.asset_type !== 'native' && h.asset_type !== 'liquidity_pool_shares');
});

const poolsOnly = computed(() => {
  return sortedHoldings.value.filter(h => h.asset_type === 'liquidity_pool_shares' && h.balance > 0);
});

const holdingsCount = computed(() => holdingsOnly.value.length);
const trustlinesCount = computed(() => trustlinesOnly.value.length);
const poolsCount = computed(() => poolsOnly.value.length);

const poolsValued = computed(() => {
  let list = [...poolsOnly.value];

  // Apply Search
  const q = poolsSearchQuery.value.trim().toLowerCase();
  if (q) {
    list = list.filter(h => {
      const code = (h.asset_code || '').toLowerCase();
      const poolId = (h.pool_id || '').toLowerCase();
      return code.includes(q) || poolId.includes(q);
    });
  }

  // Calculate Allocation
  const totalVal = list.reduce((sum, h) => sum + (h.value_usd || 0), 0);
  let mapped = list.map(h => ({
    ...h,
    local_allocation_percentage: totalVal > 0 ? ((h.value_usd || 0) / totalVal) * 100 : 0
  }));

  // Apply Sorting
  mapped.sort((a, b) => {
    let valA, valB;
    if (poolsSortField.value === 'balance') {
      valA = Number(a.balance || 0);
      valB = Number(b.balance || 0);
    } else if (poolsSortField.value === 'allocation') {
      valA = Number(a.local_allocation_percentage || 0);
      valB = Number(b.local_allocation_percentage || 0);
    } else {
      valA = Number(a.value_usd || 0);
      valB = Number(b.value_usd || 0);
    }

    return poolsSortDirection.value === 'desc' ? valB - valA : valA - valB;
  });

  return mapped;
});

const claimableHoldings = computed(() => {
  return sortedHoldings.value.filter(h => h.asset_type === 'claimable_balance');
});

const regularHoldingsValued = computed(() => {
  // 1. Get raw positive-balance holdings
  let list = [...holdingsOnly.value];

  // 2. Apply Search
  const q = holdingsSearchQuery.value.trim().toLowerCase();
  if (q) {
    list = list.filter(h => {
      const code = (h.asset_code || '').toLowerCase();
      const issuer = (h.asset_issuer || '').toLowerCase();
      return code.includes(q) || issuer.includes(q);
    });
  }

  // 3. Calculate Allocation
  const totalVal = list.reduce((sum, h) => sum + (h.value_usd || 0), 0);
  let mapped = list.map(h => ({
    ...h,
    local_allocation_percentage: totalVal > 0 ? ((h.value_usd || 0) / totalVal) * 100 : 0
  }));

  // 4. Apply Sorting
  mapped.sort((a, b) => {
    let valA = 0;
    let valB = 0;
    if (holdingsSortField.value === 'value') {
      valA = a.value_usd || 0;
      valB = b.value_usd || 0;
    } else if (holdingsSortField.value === 'balance') {
      valA = a.balance || 0;
      valB = b.balance || 0;
    } else if (holdingsSortField.value === 'allocation') {
      valA = a.local_allocation_percentage || 0;
      valB = b.local_allocation_percentage || 0;
    } else if (holdingsSortField.value === 'asset') {
      const codeA = (a.asset_code || '').toUpperCase();
      const codeB = (b.asset_code || '').toUpperCase();
      if (codeA < codeB) return holdingsSortDirection.value === 'asc' ? -1 : 1;
      if (codeA > codeB) return holdingsSortDirection.value === 'asc' ? 1 : -1;
      return 0;
    }

    if (holdingsSortDirection.value === 'desc') {
      return valB - valA;
    } else {
      return valA - valB;
    }
  });

  return mapped;
});

const trustlinesFiltered = computed(() => {
  let list = [...trustlinesOnly.value];

  // Apply Search
  const q = trustlinesSearchQuery.value.trim().toLowerCase();
  if (q) {
    list = list.filter(h => {
      const code = (h.asset_code || '').toLowerCase();
      const issuer = (h.asset_issuer || '').toLowerCase();
      return code.includes(q) || issuer.includes(q);
    });
  }

  // Sort by asset code alphabetically
  list.sort((a, b) => {
    const codeA = (a.asset_code || '').toUpperCase();
    const codeB = (b.asset_code || '').toUpperCase();
    if (codeA < codeB) return -1;
    if (codeA > codeB) return 1;
    return 0;
  });

  return list;
});

const trustlinesPaginated = computed(() => {
  return trustlinesFiltered.value.slice(0, trustlinesLimit.value);
});

const hasMoreTrustlines = computed(() => {
  return trustlinesFiltered.value.length > trustlinesLimit.value;
});

function loadMoreTrustlines() {
  trustlinesLimit.value += 10;
}

const nonXlmHoldings = computed(() => {
  return holdingsOnly.value.filter(h => h.asset_code !== 'XLM' && h.asset_type !== 'liquidity_pool_shares');
});

// Group allocations for donut chart: top 5 + "Others"
const allocationSlices = computed(() => {
  const list = holdingsOnly.value;
  if (list.length === 0) return [];
  
  // Sort by local allocation percentage descending
  const totalVal = list.reduce((sum, h) => sum + (h.value_usd || 0), 0);
  const mapped = list.map(h => ({
    ...h,
    alloc: totalVal > 0 ? ((h.value_usd || 0) / totalVal) * 100 : 0
  }));
  const sorted = mapped.sort((a, b) => b.alloc - a.alloc);
  
  // Take top 5 assets
  const topAssets = sorted.slice(0, 5);
  const remainingAssets = sorted.slice(5);
  
  const slices = [];
  let otherSum = 0;
  
  topAssets.forEach((hold, idx) => {
    const percentage = hold.alloc || 0;
    if (percentage > 0) {
      slices.push({
        label: (hold.asset_code === 'XLM' && hold.asset_type === 'native') ? 'XLM (Native)' : hold.asset_code,
        percentage: percentage,
        color: donutColors[idx % donutColors.length]
      });
    }
  });
  
  remainingAssets.forEach(hold => {
    otherSum += hold.alloc || 0;
  });
  
  if (otherSum > 0.05) {
    slices.push({
      label: 'Others',
      percentage: otherSum,
      color: '#475569' // slate-600
    });
  }
  
  return slices;
});

// Donut calculation
const donutSlices = computed(() => {
  let accumPercentage = 0;
  return allocationSlices.value.map((slice) => {
    const percentage = slice.percentage;
    const offset = 100 - accumPercentage;
    accumPercentage += percentage;
    return {
      percentage,
      color: slice.color,
      offset,
    };
  });
});

const legendSlices = computed(() => {
  return allocationSlices.value;
});

function toggleHoldingsSort(field) {
  if (holdingsSortField.value === field) {
    holdingsSortDirection.value = holdingsSortDirection.value === 'desc' ? 'asc' : 'desc';
  } else {
    holdingsSortField.value = field;
    holdingsSortDirection.value = 'desc';
  }
}

const historyChange = computed(() => {
  const list = historyRawData.value.portfolio ?? [];
  if (list.length < 2) return null;
  
  const earliest = list[0];
  const latest = list[list.length - 1];
  
  const earliestVal = parseFloat(earliest.total_value_usd || 0);
  const latestVal = parseFloat(latest.total_value_usd || 0);
  
  const absChange = latestVal - earliestVal;
  const pctChange = earliestVal > 0 ? (absChange / earliestVal) * 100 : 0;
  
  return {
    latestValue: latestVal,
    absolute: absChange,
    percentage: pctChange,
    isPositive: absChange >= 0
  };
});

// Metrics derived properties
const mostTradedAsset = computed(() => {
  if (events.value.length === 0) return "--";
  const trades = events.value.filter(e => e.event_type === 'BUY' || e.event_type === 'SELL');
  if (trades.length === 0) return "--";
  
  const counts = {};
  trades.forEach(t => {
    const code = t.asset_code || 'XLM';
    counts[code] = (counts[code] || 0) + 1;
  });

  let maxCode = "";
  let maxCount = 0;
  Object.keys(counts).forEach(k => {
    if (counts[k] > maxCount) {
      maxCount = counts[k];
      maxCode = k;
    }
  });

  return maxCode || "--";
});

// Helper formatting
function formatNumber(value, decimals = 2) {
  if (value === null || value === undefined) return "--";
  return new Intl.NumberFormat("en-US", {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals
  }).format(value);
}

function formatDate(isoString) {
  if (!isoString) return "";
  const d = new Date(isoString);
  return d.toISOString().replace('T', ' ').substring(0, 16);
}

function formatRelativeTime(dateString) {
  const diffMs = new Date() - new Date(dateString);
  const diffSec = Math.floor(diffMs / 1000);
  const diffMin = Math.floor(diffSec / 60);
  const diffHour = Math.floor(diffMin / 60);
  const diffDay = Math.floor(diffHour / 24);

  if (diffSec < 60) return "just now";
  if (diffMin < 60) return `${diffMin}m ago`;
  if (diffHour < 24) return `${diffHour}h ago`;
  return `${diffDay}d ago`;
}

function shortenAddress(addr) {
  if (!addr) return "";
  return addr.substring(0, 6) + "..." + addr.substring(addr.length - 4);
}

function copyAddress() {
  navigator.clipboard.writeText(address.value);
  copied.value = true;
  setTimeout(() => (copied.value = false), 2000);
}

// Activity tabs filtering configuration
const activityTabs = [
  { label: "All", value: "all" },
  { label: "Buys", value: "BUYS" },
  { label: "Sells", value: "SELLS" },
  { label: "Payments", value: "PAYMENTS" },
  { label: "Liquidity", value: "LIQUIDITY" },
  { label: "Trustlines", value: "TRUSTLINES" }
];

const filteredEvents = computed(() => {
  if (activeActivityTab.value === 'all') return events.value;
  return events.value.filter(event => {
    const type = event.event_type;
    switch (activeActivityTab.value) {
      case 'BUYS': return type === 'BUY';
      case 'SELLS': return type === 'SELL';
      case 'PAYMENTS': return type === 'PAYMENT_IN' || type === 'PAYMENT_OUT';
      case 'LIQUIDITY': return type === 'LP_ADD' || type === 'LP_REMOVE';
      case 'TRUSTLINES': return type === 'TRUSTLINE_ADD' || type === 'TRUSTLINE_REMOVE';
      default: return true;
    }
  });
});



function getEventName(type) {
  switch (type) {
    case 'BUY': return 'DEX Buy';
    case 'SELL': return 'DEX Sell';
    case 'PAYMENT_IN': return 'Payment In';
    case 'PAYMENT_OUT': return 'Payment Out';
    case 'LP_ADD': return 'LP Deposit';
    case 'LP_REMOVE': return 'LP Withdrawal';
    case 'TRUSTLINE_ADD': return 'Trustline Add';
    case 'TRUSTLINE_REMOVE': return 'Trustline Remove';
    case 'CLAIMABLE_BALANCE_CLAIM': return 'Claim Claimable Balance';
    case 'OFFER_CREATE': return 'Create DEX Offer';
    case 'OFFER_UPDATE': return 'Update DEX Offer';
    case 'OFFER_CANCEL': return 'Cancel DEX Offer';
    case 'ACCOUNT_MERGE': return 'Account Merge';
    case 'OTHER': return 'Other Operation';
    default: return type;
  }
}

// Data loaders
async function loadOverview() {
  try {
    overviewLoading.value = true;
    notFound.value = false;
    connectionError.value = false;
    const { data } = await axios.get(`/api/wallet/${address.value}/overview`);
    if (data.status === 'success') {
      overviewData.value = data.data;
      
      // Start polling for index preparation if still running
      if (!overviewData.value.historical_index_complete) {
        startPollingIndexState();
      }
    } else {
      notFound.value = true;
    }
  } catch (err) {
    const errorType = err.response?.data?.error_type;
    if (errorType === 'connection_error') {
      connectionError.value = true;
    } else {
      notFound.value = true;
    }
    console.error(err);
  } finally {
    overviewLoading.value = false;
  }
}

function retryLoad() {
  connectionError.value = false;
  notFound.value = false;
  overviewLoading.value = true;
  holdingsLoading.value = true;
  activityLoading.value = true;
  metricsLoading.value = true;
  historyLoading.value = true;
  
  Promise.all([
    loadOverview(),
    loadHoldings(),
    loadLazyBlocks()
  ]);
}

async function retryIndexing() {
  try {
    const { data } = await axios.get(`/api/wallet/${address.value}/overview?retry=1`);
    if (data.status === 'success') {
      overviewData.value = data.data;
      startPollingIndexState();
    }
  } catch (err) {
    console.error(err);
  }
}

async function loadHoldings() {
  try {
    holdingsLoading.value = true;
    const { data } = await axios.get(`/api/wallet/${address.value}/holdings`);
    if (data.status === 'success') {
      holdings.value = data.data ?? [];
    }
  } catch (err) {
    console.error(err);
  } finally {
    holdingsLoading.value = false;
  }
}

// Lazy loaded data blocks
async function loadLazyBlocks() {
  loadHistoryData();
  loadActivityEvents();
  loadMetrics();
}

async function loadMetrics() {
  try {
    metricsLoading.value = true;
    const { data } = await axios.get(`/api/wallet/${address.value}/metrics`);
    if (data.status === 'success') {
      metricsData.value = data.data;
    }
  } catch (err) {
    console.error(err);
  } finally {
    metricsLoading.value = false;
  }
}

async function loadActivityEvents(append = false) {
  try {
    if (!append) {
      activityLoading.value = true;
      eventsPage.value = 1;
    }
    const { data } = await axios.get(`/api/wallet/${address.value}/activity`, {
      params: { page: eventsPage.value }
    });
    
    const records = data.data ?? [];
    events.value = append ? [...events.value, ...records] : records;
    hasMoreEvents.value = !!data.next_page_url;
  } catch (err) {
    console.error(err);
  } finally {
    activityLoading.value = false;
  }
}

function loadMoreEvents() {
  eventsPage.value++;
  loadActivityEvents(true);
}

function changeActivityTab(tab) {
  activeActivityTab.value = tab;
}

// Historical Snapshots & Charts
const historyStartDate = computed(() => {
  const list = historyRawData.value.portfolio ?? [];
  return list.length > 0 ? list[0].snapshot_at : null;
});

async function loadHistoryData() {
  try {
    historyLoading.value = true;
    const { data } = await axios.get(`/api/wallet/${address.value}/portfolio-history`);
    if (data.status === 'success') {
      historyRawData.value = data.data;
      nextTick(() => {
        renderHistoryChart();
      });
    }
  } catch (err) {
    console.error(err);
  } finally {
    historyLoading.value = false;
  }
}

function changeTimeframe(tf) {
  activeTimeframe.value = tf;
  renderHistoryChart();
}

// Chart Rendering via Lightweight Charts
function renderHistoryChart() {
  if (!chartContainer.value) return;
  
  // Clean container
  chartContainer.value.innerHTML = '';
  
  let sourceList = [];
  const selected = historyFilterAsset.value;

  if (selected === 'portfolio') {
    // Total USD value
    sourceList = (historyRawData.value.portfolio ?? []).map(snap => ({
      time: new Date(snap.snapshot_at).getTime() / 1000,
      value: Number(snap.total_value_usd ?? 0.0)
    }));
  } else {
    // Asset snapshot value (filter by asset_code)
    sourceList = (historyRawData.value.assets ?? [])
      .filter(snap => snap.asset_code === selected)
      .map(snap => ({
        time: new Date(snap.snapshot_at).getTime() / 1000,
        value: Number(snap.value_usd ?? 0.0)
      }));
  }

  // Filter list by timeframe
  if (sourceList.length === 0) return;

  const nowTime = new Date().getTime() / 1000;
  let cutTime = 0;
  switch (activeTimeframe.value) {
    case '24H': cutTime = nowTime - 24 * 3600; break;
    case '7D': cutTime = nowTime - 7 * 24 * 3600; break;
    case '30D': cutTime = nowTime - 30 * 24 * 3600; break;
    case '90D': cutTime = nowTime - 90 * 24 * 3600; break;
    case '1Y': cutTime = nowTime - 365 * 24 * 3600; break;
  }

  if (cutTime > 0) {
    sourceList = sourceList.filter(item => item.time >= cutTime);
  }

  if (sourceList.length === 0) return;

  // Sort by time
  sourceList.sort((a, b) => a.time - b.time);

  // Group duplicate timestamps (if any)
  const uniqueItems = [];
  const timesSeen = new Set();
  for (const item of sourceList) {
    const truncatedTime = Math.floor(item.time / 60) * 60; // Group to nearest minute
    if (!timesSeen.has(truncatedTime)) {
      timesSeen.add(truncatedTime);
      uniqueItems.push({ time: truncatedTime, value: item.value });
    }
  }

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
      rightPriceScale: {
        borderColor: '#1D2531',
      },
      timeScale: {
        borderColor: '#1D2531',
        timeVisible: true,
      },
    });

    lineSeriesInstance = chartInstance.addLineSeries({
      color: '#12CBEE',
      lineWidth: 2,
      priceFormat: {
        type: 'volume',
      },
    });

    lineSeriesInstance.setData(uniqueItems);
    chartInstance.timeScale().fitContent();
  } catch (err) {
    console.error("Chart render error:", err);
  }
}

// Index State Polling
function startPollingIndexState() {
  stopPollingIndexState();
  indexingPollTimer = setInterval(async () => {
    try {
      const { data } = await axios.get(`/api/wallet/${address.value}/overview`);
      if (data.status === 'success') {
        overviewData.value = data.data;
        
        // Dynamically update UI sections in real-time as background indexing progresses
        loadHoldings();
        loadActivityEvents();
        loadMetrics();
        loadHistoryData();

        if (overviewData.value.historical_index_complete) {
          stopPollingIndexState();
        }
      }
    } catch (err) {
      console.error(err);
    }
  }, 5000);
}

function stopPollingIndexState() {
  if (indexingPollTimer) {
    clearInterval(indexingPollTimer);
    indexingPollTimer = null;
  }
}

// Handle resizing of historical chart
function handleResize() {
  if (chartInstance && chartContainer.value) {
    chartInstance.resize(chartContainer.value.clientWidth, 320);
  }
}

// Watch routing parameters change (e.g. searching new address from header)
watch(address, async (newAddr) => {
  if (newAddr) {
    stopPollingIndexState();
    
    // Reset state to show skeletons and clear old data
    overviewData.value = null;
    holdings.value = [];
    events.value = [];
    metricsData.value = null;
    historyRawData.value = { portfolio: [], assets: [] };
    
    notFound.value = false;
    connectionError.value = false;
    overviewLoading.value = true;
    holdingsLoading.value = true;
    activityLoading.value = true;
    metricsLoading.value = true;
    historyLoading.value = true;

    await Promise.all([
      loadOverview(),
      loadHoldings(),
      loadLazyBlocks()
    ]);
  }
});

onMounted(async () => {
  await Promise.all([
    loadOverview(),
    loadHoldings(),
    loadLazyBlocks()
  ]);
  window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
  stopPollingIndexState();
  window.removeEventListener('resize', handleResize);
  if (chartInstance) {
    chartInstance.remove();
    chartInstance = null;
  }
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
  background-image: radial-gradient(900px 460px at 84% -12%, rgba(18, 203, 238, .09), transparent 62%), radial-gradient(760px 420px at 6% -8%, rgba(240, 24, 156, .07), transparent 60%);
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
  background: var(--cyan);
  box-shadow: 0 0 8px var(--cyan);
  display: inline-block;
  margin-right: 6px;
}

.asset-hero {
  border-color: rgba(18, 203, 238, 0.15);
}

.hero-glow {
  position: absolute;
  top: 0;
  right: 0;
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(18, 203, 238, 0.08) 0%, transparent 70%);
  z-index: 1;
  pointer-events: none;
}

/* Donut chart css animations */
.donut-segment {
  transform-origin: center;
}

.donut-segment:hover {
  stroke-width: 4.5;
  cursor: pointer;
}

/* Custom scrollbar */
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;  /* Firefox */
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: var(--panel2);
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: var(--line2);
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: var(--dim);
}
</style>
