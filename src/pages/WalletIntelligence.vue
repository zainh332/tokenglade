<template>
  <div class="asset-page-wrapper min-h-screen selection:bg-cyan-500/20 selection:text-white">
    <Header />

    <!-- MAIN CONTAINER -->
    <div class="wrap space-y-6 pb-24 sm:pb-32 pt-4">

      <!-- ERROR / NOT FOUND STATE -->
      <div v-if="notFound" class="card p-12 text-center max-w-xl mx-auto space-y-4 my-12">
        <AlertCircle class="w-12 h-12 text-rose-500 mx-auto" />
        <h2 class="text-xl font-bold text-theme-ink">Wallet Address Not Found</h2>
        <p class="text-xs text-theme-dim font-mono break-all">{{ address }}</p>
        <p class="text-sm text-theme-faint">
          This address does not appear to exist on the Stellar network or is invalid. Please verify the address and try again.
        </p>
        <router-link to="/" class="inline-block text-xs uppercase tracking-wider font-extrabold px-6 py-2.5 bg-theme-panel border border-theme-line hover:border-theme-line2 transition rounded-lg text-theme-ink">
          Back to Home
        </router-link>
      </div>

      <!-- CONNECTION / NETWORK ERROR STATE -->
      <div v-else-if="connectionError" class="card p-12 text-center max-w-xl mx-auto space-y-4 my-12">
        <AlertCircle class="w-12 h-12 text-amber-500 mx-auto animate-pulse" />
        <h2 class="text-xl font-bold text-theme-ink">Horizon Node Connection Refused</h2>
        <p class="text-xs text-theme-dim font-mono break-all">{{ address }}</p>
        <p class="text-sm text-theme-faint">
          Stellar Horizon nodes are temporarily refusing connection or rate-limiting requests. Please try again.
        </p>
        <div class="flex items-center justify-center gap-3">
          <button @click="retryLoad" class="text-xs uppercase tracking-wider font-extrabold px-6 py-2.5 bg-theme-panel border border-theme-line hover:border-theme-line2 transition rounded-lg text-theme-ink">
            Retry Connection
          </button>
          <router-link to="/" class="text-xs uppercase tracking-wider font-extrabold px-6 py-2.5 bg-transparent text-theme-dim hover:text-theme-ink transition">
            Back to Home
          </router-link>
        </div>
      </div>

      <!-- MAIN PAGE LAYOUT -->
      <div v-else class="space-y-6">

        <!-- SECTION 1: WALLET HERO -->
        <section class="card asset-hero p-6 relative overflow-hidden">
          <div class="hero-glow"></div>
          
          <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <!-- Left Side: Address and Meta -->
            <div class="space-y-3.5 flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <span class="text-[10px] font-mono tracking-widest text-cyan-500 dark:text-cyan-400 uppercase bg-cyan-500/10 border border-cyan-500/20 px-2 py-0.5 rounded font-bold flex items-center gap-1.5">
                  <div class="w-3 h-3 flex-shrink-0"><XlmLogo /></div>
                  Stellar Wallet
                </span>
                <span v-if="overviewData" class="text-[10px] font-mono uppercase px-2 py-0.5 rounded font-bold"
                      :class="overviewData.is_connected_wallet ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-theme-panel2 text-theme-dim border border-theme-line'">
                  {{ overviewData.is_connected_wallet ? 'CONNECTED' : (overviewData.is_official_wallet ? 'OFFICIAL' : 'PUBLIC') }}
                </span>
                <span v-if="overviewData?.home_domain" class="text-xs font-sans font-medium text-cyan-600 dark:text-cyan-300 bg-cyan-500/10 border border-cyan-500/20 px-2 py-0.5 rounded-md">
                  🌐 {{ overviewData.home_domain }}
                </span>
              </div>
              
              <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                <h1 class="text-xs sm:text-sm md:text-base font-mono text-theme-ink break-all font-semibold select-all" :title="address">
                  {{ address }}
                </h1>
                <button @click="copyAddress" class="p-1.5 sm:p-2 bg-theme-panel2 border border-theme-line rounded-lg hover:border-theme-line2 hover:text-theme-ink transition flex-shrink-0 cursor-pointer" title="Copy Address">
                  <Check v-if="copied" class="w-4 h-4 text-emerald-500 dark:text-emerald-400" />
                  <Copy v-else class="w-4 h-4 text-theme-dim hover:text-theme-ink" />
                </button>
              </div>

              <!-- Mini stats -->
              <div v-if="overviewData" class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs font-mono text-theme-dim pt-1">
                <div v-if="overviewData.sequence">
                  Sequence: <span class="text-theme-ink font-semibold">{{ overviewData.sequence }}</span>
                </div>
                <div v-if="overviewData.subentry_count !== undefined">
                  Subentries: <span class="text-theme-ink font-semibold">{{ overviewData.subentry_count }}</span>
                </div>
                <div v-if="overviewData.signers">
                  Signers: <span class="text-theme-ink font-semibold">{{ overviewData.signers.length }}</span>
                </div>
              </div>
            </div>

            <!-- Right Side: Value & Key balances -->
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-4 lg:text-right w-full lg:w-auto font-sans">
              <!-- Highlighted Portfolio Value Card -->
              <div class="bg-gradient-to-br from-cyan-500/10 to-purple-500/10 border border-cyan-500/30 p-4 rounded-xl flex-1 sm:flex-none min-w-[170px] space-y-1 shadow-lg shadow-cyan-500/5">
                <div class="text-xs font-semibold text-cyan-600 dark:text-cyan-400">Portfolio Value</div>
                <div class="text-lg font-bold text-theme-ink font-mono leading-tight py-0.5">
                  <span v-if="overviewLoading" class="inline-block w-24 h-6 bg-theme-line animate-pulse rounded"></span>
                  <span v-else>${{ formatNumber(overviewData?.portfolio_value_usd, 2) }}</span>
                </div>
                <div class="text-xs text-theme-dim font-mono">
                  <span v-if="overviewLoading" class="inline-block w-16 h-3 bg-theme-line animate-pulse rounded"></span>
                  <span v-else>{{ formatNumber(overviewData?.portfolio_value_xlm, 2) }} XLM</span>
                </div>
              </div>

              <!-- XLM Balance -->
              <div class="bg-theme-panel2 border border-theme-line p-4 rounded-xl flex-1 sm:flex-none min-w-[110px] space-y-1">
                <div class="text-xs font-semibold text-slate-400">XLM Balance</div>
                <div class="text-base font-bold text-theme-ink font-mono leading-tight py-0.5">
                  <span v-if="overviewLoading" class="inline-block w-14 h-5 bg-theme-line animate-pulse rounded"></span>
                  <span v-else>{{ formatNumber(overviewData?.xlm_balance, 2) }}</span>
                </div>
                <div class="text-xs text-theme-dim font-medium">Native XLM</div>
              </div>

              <!-- Assets Held -->
              <div class="bg-theme-panel2 border border-theme-line p-4 rounded-xl flex-1 sm:flex-none min-w-[100px] space-y-1">
                <div class="text-xs font-semibold text-slate-400">Assets Held</div>
                <div class="text-base font-bold text-theme-ink font-mono leading-tight py-0.5">
                  <span v-if="overviewLoading" class="inline-block w-8 h-5 bg-theme-line animate-pulse rounded"></span>
                  <span v-else>{{ overviewData?.assets_held ?? 0 }}</span>
                </div>
                <div class="text-xs text-theme-dim font-medium">Positions</div>
              </div>

              <!-- Trustlines -->
              <div class="bg-theme-panel2 border border-theme-line p-4 rounded-xl flex-1 sm:flex-none min-w-[100px] space-y-1">
                <div class="text-xs font-semibold text-slate-400">Trustlines</div>
                <div class="text-base font-bold text-cyan-500 dark:text-cyan-400 font-mono leading-tight py-0.5">
                  <span v-if="overviewLoading" class="inline-block w-8 h-5 bg-theme-line animate-pulse rounded"></span>
                  <span v-else>{{ overviewData?.trustlines_count ?? 0 }}</span>
                </div>
                <div class="text-xs text-theme-dim font-medium">Established</div>
              </div>

              <!-- Pools -->
              <div class="bg-theme-panel2 border border-theme-line p-4 rounded-xl flex-1 sm:flex-none min-w-[100px] space-y-1">
                <div class="text-xs font-semibold text-slate-400">Pools</div>
                <div class="text-base font-bold text-amber-500 dark:text-amber-400 font-mono leading-tight py-0.5">
                  <span v-if="overviewLoading" class="inline-block w-8 h-5 bg-theme-line animate-pulse rounded"></span>
                  <span v-else>{{ overviewData?.pools_count ?? 0 }}</span>
                </div>
                <div class="text-xs text-theme-dim font-medium">Participated</div>
              </div>
            </div>
          </div>
        </section>

        <!-- PORTFOLIO GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- SECTION 2: PORTFOLIO OVERVIEW (LEFT 2 COLS) -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Assets card with Holdings, Trustlines, and Pools tabs -->
            <section class="card">
              <div class="card-hd border-b border-theme-line pb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h3 class="flex items-center gap-2 font-sans font-semibold text-sm text-theme-ink">
                  Account Assets
                </h3>
                
                <!-- TABS -->
                <div class="flex items-center gap-1.5">
                  <button @click="activeAssetsTab = 'holdings'" 
                          class="text-xs uppercase tracking-wider font-extrabold px-3 py-1 rounded border transition-colors cursor-pointer"
                          :class="activeAssetsTab === 'holdings' ? 'bg-theme-panel2 border-theme-line2 text-theme-ink shadow-sm' : 'bg-transparent border-transparent text-theme-dim hover:text-theme-ink'">
                    Holdings <span class="ml-1 text-[10px] font-mono text-cyan-500 dark:text-cyan-400 font-semibold">{{ holdingsCount }}</span>
                  </button>
                  <button @click="activeAssetsTab = 'trustlines'" 
                          class="text-xs uppercase tracking-wider font-extrabold px-3 py-1 rounded border transition-colors cursor-pointer"
                          :class="activeAssetsTab === 'trustlines' ? 'bg-theme-panel2 border-theme-line2 text-theme-ink shadow-sm' : 'bg-transparent border-transparent text-theme-dim hover:text-theme-ink'">
                    Trustlines <span class="ml-1 text-[10px] font-mono text-cyan-500 dark:text-cyan-400 font-semibold">{{ trustlinesCount }}</span>
                  </button>
                  <button @click="activeAssetsTab = 'pools'" 
                          class="text-xs uppercase tracking-wider font-extrabold px-3 py-1 rounded border transition-colors cursor-pointer"
                          :class="activeAssetsTab === 'pools' ? 'bg-theme-panel2 border-theme-line2 text-theme-ink shadow-sm' : 'bg-transparent border-transparent text-theme-dim hover:text-theme-ink'">
                    Pools <span class="ml-1 text-[10px] font-mono text-cyan-500 dark:text-cyan-400 font-semibold">{{ poolsCount }}</span>
                  </button>
                </div>
              </div>

              <!-- SKELETON LOADER -->
              <div v-if="holdingsLoading" class="p-6 space-y-4 animate-pulse">
                <div class="h-8 bg-theme-line rounded-lg w-full" v-for="i in 4" :key="i"></div>
              </div>

              <!-- HOLDINGS TAB -->
              <div v-else-if="activeAssetsTab === 'holdings'">
                <!-- SEARCH AND SORT CONTROLS -->
                <div class="px-6 py-3 flex flex-col sm:flex-row gap-3 items-center justify-between border-b border-theme-line bg-theme-panel2/50">
                  <div class="relative w-full sm:max-w-xs">
                    <input type="text" 
                           v-model="holdingsSearchQuery" 
                           placeholder="Search assets..." 
                           class="w-full bg-theme-panel border border-theme-line rounded-lg px-3 py-1.5 text-xs text-theme-ink placeholder-theme-faint placeholder:font-sans font-sans focus:outline-none focus:border-cyan-500 transition" />
                  </div>
                  
                  <div class="flex items-center gap-2 self-stretch sm:self-auto justify-between sm:justify-start">
                    <span class="text-xs font-sans text-theme-dim font-medium">Sort by:</span>
                    <div class="flex items-center gap-1">
                      <button v-for="field in ['value', 'balance', 'allocation']" :key="field"
                              @click="toggleHoldingsSort(field)"
                              class="text-xs font-sans font-medium capitalize px-2.5 py-1 rounded transition border cursor-pointer"
                              :class="holdingsSortField === field ? 'bg-theme-panel border-theme-line2 text-theme-ink shadow-sm' : 'bg-transparent border-transparent text-theme-dim hover:text-theme-ink'">
                        {{ field }}
                        <span v-if="holdingsSortField === field">
                          {{ holdingsSortDirection === 'desc' ? '▼' : '▲' }}
                        </span>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- EMPTY STATE -->
                <div v-if="regularHoldingsValued.length === 0" class="p-12 text-center text-theme-dim">
                  <Coins class="w-8 h-8 mx-auto mb-2 text-theme-faint" />
                  <p class="text-sm font-semibold text-theme-ink">No token holdings found.</p>
                  <p class="text-xs text-theme-dim font-mono mt-1">This wallet currently has no funded asset balances.</p>
                </div>

                <!-- TABLE -->
                <div v-else class="overflow-auto max-h-[380px] custom-scrollbar">
                  <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead>
                      <tr class="border-b border-theme-line text-[10px] font-mono uppercase tracking-wider text-theme-dim bg-theme-panel2/50">
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
                    <tbody class="divide-y divide-theme-line text-xs font-mono">
                      <tr v-for="hold in regularHoldingsValued" :key="hold.id" class="hover:bg-theme-panel2/60 transition-colors">
                        <td class="py-3.5 px-4 flex items-center gap-3">
                          <div class="w-7 h-7 rounded-full bg-theme-panel2 border border-theme-line flex items-center justify-center text-[10px] font-bold text-theme-dim select-none flex-shrink-0 overflow-hidden shadow-sm">
                            <div v-if="hold.asset_code === 'XLM' || hold.asset_type === 'native'" class="w-full h-full p-1.5 flex items-center justify-center">
                              <XlmLogo />
                            </div>
                            <img v-else-if="hold.logo_url" :src="hold.logo_url" class="w-full h-full object-cover" @error="hold.logo_url = null" />
                            <span v-else-if="hold.asset_type === 'liquidity_pool_shares'" class="text-[9px] uppercase font-bold text-amber-500">LP</span>
                            <span v-else class="text-[10px] uppercase font-bold text-theme-dim">{{ hold.asset_code?.slice(0, 3) }}</span>
                          </div>
                          <div>
                            <div class="font-sans font-bold text-theme-ink uppercase text-xs flex items-center gap-1.5">
                              <router-link v-if="hold.asset_code !== 'XLM' && hold.asset_type !== 'liquidity_pool_shares'"
                                           :to="{ path: '/token-insight', query: { asset_code: hold.asset_code, issuer: hold.asset_issuer } }"
                                           class="text-cyan-600 dark:text-cyan-400 hover:text-cyan-500 transition-colors">
                                {{ hold.asset_code }}
                              </router-link>
                              <span v-else>{{ hold.asset_code }}</span>
                              <span v-if="hold.asset_type === 'liquidity_pool_shares'" class="text-[9px] lowercase bg-theme-panel2 text-theme-faint px-1 py-0.2 rounded border border-theme-line">pool</span>
                            </div>
                            <div class="text-[10px] text-theme-faint break-all select-all max-w-[200px] truncate" :title="hold.asset_issuer">
                              <router-link v-if="hold.asset_issuer" :to="`/wallet/${hold.asset_issuer}`" class="text-theme-faint hover:text-cyan-600 dark:hover:text-cyan-400 hover:underline transition-colors">
                                {{ shortenAddress(hold.asset_issuer) }}
                              </router-link>
                              <span v-else>Stellar Network</span>
                            </div>
                          </div>
                        </td>
                        <td class="py-3.5 px-4 text-right text-theme-ink font-semibold">
                          {{ formatNumber(hold.balance, 4) }}
                        </td>
                        <td class="py-3.5 px-4 text-right text-theme-dim">
                          {{ hold.price_usd ? '$' + formatNumber(hold.price_usd, 6) : '--' }}
                        </td>
                        <td class="py-3.5 px-4 text-right text-theme-ink font-bold">
                          {{ hold.value_usd ? '$' + formatNumber(hold.value_usd, 2) : '--' }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                          <div class="flex items-center justify-end gap-2.5">
                            <span class="text-cyan-600 dark:text-cyan-400 font-semibold">{{ formatNumber(hold.local_allocation_percentage, 1) }}%</span>
                            <div class="w-12 h-1.5 bg-theme-line rounded overflow-hidden hidden sm:block">
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
                <div class="px-6 py-3 flex flex-col sm:flex-row gap-3 items-center justify-between border-b border-theme-line bg-theme-panel2/50">
                  <div class="relative w-full sm:max-w-xs">
                    <input type="text" 
                           v-model="trustlinesSearchQuery" 
                           placeholder="Search by code or issuer..." 
                           class="w-full bg-theme-panel border border-theme-line rounded-lg px-3 py-1.5 text-xs text-theme-ink placeholder-theme-faint placeholder:font-sans font-sans focus:outline-none focus:border-cyan-500 transition" />
                  </div>
                  <div class="text-[10px] text-theme-dim font-mono">
                    Showing {{ Math.min(trustlinesLimit, trustlinesFiltered.length) }} of {{ trustlinesFiltered.length }} trustlines
                  </div>
                </div>

                <!-- EMPTY STATE -->
                <div v-if="trustlinesFiltered.length === 0" class="p-12 text-center text-theme-dim">
                  <Coins class="w-8 h-8 mx-auto mb-2 text-theme-faint" />
                  <p class="text-sm font-semibold text-theme-ink">No trustlines match your query.</p>
                </div>

                <!-- TABLE -->
                <div v-else class="overflow-auto max-h-[380px] custom-scrollbar">
                  <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead>
                      <tr class="border-b border-theme-line text-[10px] font-mono uppercase tracking-wider text-theme-dim bg-theme-panel2/50">
                        <th class="py-3.5 px-4 font-semibold">ASSET</th>
                        <th class="py-3.5 px-4 font-semibold">ISSUER</th>
                        <th class="py-3.5 px-4 font-semibold text-right">BALANCE</th>
                        <th class="py-3.5 px-4 font-semibold text-right">LIMIT</th>
                        <th class="py-3.5 px-4 font-semibold text-center">STATUS</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-theme-line text-xs font-mono">
                      <tr v-for="hold in trustlinesPaginated" :key="hold.id" class="hover:bg-theme-panel2/60 transition-colors">
                        <td class="py-3.5 px-4">
                          <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-theme-panel2 border border-theme-line flex items-center justify-center text-[10px] font-bold text-theme-dim select-none flex-shrink-0 overflow-hidden shadow-sm">
                              <div v-if="hold.asset_code === 'XLM' || hold.asset_type === 'native'" class="w-full h-full p-1.5 flex items-center justify-center">
                                <XlmLogo />
                              </div>
                              <img v-else-if="hold.logo_url" :src="hold.logo_url" class="w-full h-full object-cover" @error="hold.logo_url = null" />
                              <span v-else-if="hold.asset_type === 'liquidity_pool_shares'" class="text-[9px] uppercase font-bold text-amber-500">LP</span>
                              <span v-else class="text-[10px] uppercase font-bold text-theme-dim">{{ hold.asset_code?.slice(0, 3) }}</span>
                            </div>
                            <div>
                              <div class="font-sans font-bold text-theme-ink uppercase text-xs flex items-center gap-1.5">
                                <router-link v-if="hold.asset_code !== 'XLM' && hold.asset_type !== 'liquidity_pool_shares'"
                                             :to="{ path: '/token-insight', query: { asset_code: hold.asset_code, issuer: hold.asset_issuer } }"
                                             class="text-cyan-600 dark:text-cyan-400 hover:text-cyan-500 transition-colors">
                                  {{ hold.asset_code }}
                                </router-link>
                                <span v-else>{{ hold.asset_code }}</span>
                                <span v-if="hold.asset_type === 'liquidity_pool_shares'" class="text-[9px] lowercase bg-theme-panel2 text-theme-faint px-1 py-0.2 rounded border border-theme-line">pool</span>
                              </div>
                              <div class="text-[10.5px] text-theme-faint uppercase tracking-widest font-mono">
                                {{ hold.asset_type === 'liquidity_pool_shares' ? 'pool shares' : (hold.asset_type?.replace('credit_alphanum', 'alpha') || 'issued') }}
                              </div>
                            </div>
                          </div>
                        </td>

                        <td class="py-3.5 px-4">
                          <div v-if="hold.asset_issuer" class="flex items-center gap-2">
                            <router-link :to="`/wallet/${hold.asset_issuer}`" class="text-xs text-cyan-600 dark:text-cyan-400 hover:underline select-all font-mono transition-colors" :title="hold.asset_issuer">
                              {{ shortenAddress(hold.asset_issuer) }}
                            </router-link>
                            <button @click="copyIssuer(hold.asset_issuer, hold.id)" class="p-1 bg-theme-panel border border-theme-line hover:border-theme-line2 rounded text-theme-dim hover:text-theme-ink transition flex-shrink-0 cursor-pointer">
                              <Check v-if="issuerCopiedId === hold.id" class="w-3 h-3 text-emerald-500 dark:text-emerald-400" />
                              <Copy v-else class="w-3 h-3 text-theme-dim" />
                            </button>
                          </div>
                          <span v-else class="text-theme-faint">--</span>
                        </td>

                        <td class="py-3.5 px-4 text-right text-theme-ink font-semibold">
                          {{ formatNumber(hold.balance, 4) }} <span class="text-[10px] text-theme-dim font-normal uppercase">{{ hold.asset_code }}</span>
                        </td>

                        <td class="py-3.5 px-4 text-right text-theme-dim font-mono">
                          {{ hold.limit !== null && hold.limit !== undefined ? formatNumber(hold.limit, 7) : '--' }}
                        </td>

                        <td class="py-3.5 px-4 text-center">
                          <span class="text-[10.5px] font-mono px-2 py-0.5 rounded font-semibold"
                                :class="{
                                  'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20': getTrustlineStatus(hold) === 'Active',
                                  'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20': getTrustlineStatus(hold) === 'Maintain Liabilities Only',
                                  'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20': getTrustlineStatus(hold) === 'Unauthorized',
                                  'bg-theme-panel2 text-theme-faint border border-theme-line': getTrustlineStatus(hold) === '--'
                                }">
                            {{ getTrustlineStatus(hold) }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- LOAD MORE TRUSTLINES PAGINATION -->
                <div v-if="hasMoreTrustlines" class="p-4 text-center border-t border-theme-line bg-theme-panel2/50">
                  <button @click="loadMoreTrustlines" 
                          class="text-[10px] font-mono font-bold uppercase tracking-wider px-5 py-2 bg-theme-panel border border-theme-line hover:border-theme-line2 transition rounded-lg text-theme-ink cursor-pointer">
                    Load More Trustlines
                  </button>
                </div>
              </div>

              <!-- POOLS TAB -->
              <div v-else-if="activeAssetsTab === 'pools'">
                <!-- SEARCH AND SORT CONTROLS -->
                <div class="px-6 py-3 flex flex-col sm:flex-row gap-3 items-center justify-between border-b border-theme-line bg-theme-panel2/50">
                  <div class="relative w-full sm:max-w-xs">
                    <input type="text" 
                           v-model="poolsSearchQuery" 
                           placeholder="Search pools..." 
                           class="w-full bg-theme-panel border border-theme-line rounded-lg px-3 py-1.5 text-xs text-theme-ink placeholder-theme-faint placeholder:font-sans placeholder:font-medium font-sans focus:outline-none focus:border-cyan-500 transition" />
                  </div>
                  
                  <div class="flex items-center gap-2 self-stretch sm:self-auto justify-between sm:justify-start">
                    <span class="text-xs font-sans text-theme-dim font-medium">Sort by:</span>
                    <div class="flex items-center gap-1">
                      <button v-for="field in ['value', 'balance', 'allocation']" :key="field"
                              @click="togglePoolsSort(field)"
                              class="text-xs font-sans font-medium capitalize px-2.5 py-1 rounded transition border cursor-pointer"
                              :class="poolsSortField === field ? 'bg-theme-panel border-theme-line2 text-theme-ink shadow-sm' : 'bg-transparent border-transparent text-theme-dim hover:text-theme-ink'">
                        {{ field }}
                        <span v-if="poolsSortField === field">
                          {{ poolsSortDirection === 'desc' ? '▼' : '▲' }}
                        </span>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- POOLS TABLE -->
                <div class="overflow-auto max-h-[380px] custom-scrollbar">
                  <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead>
                      <tr class="border-b border-theme-line text-[10px] text-theme-dim font-mono uppercase tracking-wider bg-theme-panel2/50">
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
                    <tbody class="divide-y divide-theme-line text-xs font-mono">
                      <tr v-for="hold in poolsValued" :key="hold.id" class="hover:bg-theme-panel2/60 transition-colors">
                        <td class="py-3.5 px-4 flex items-center gap-3">
                          <div class="w-7 h-7 rounded-full bg-theme-panel2 border border-theme-line flex items-center justify-center text-[10px] font-bold text-theme-dim select-none flex-shrink-0 overflow-hidden shadow-sm">
                            <span class="text-[9px] uppercase font-bold text-amber-500">LP</span>
                          </div>
                          <div>
                            <div class="font-sans font-bold text-theme-ink uppercase text-xs flex items-center gap-1.5">
                              {{ hold.asset_code }}
                              <span class="text-[9px] lowercase bg-theme-panel2 text-theme-faint px-1 py-0.2 rounded border border-theme-line">pool</span>
                            </div>
                            <div class="text-[10px] text-theme-faint break-all select-all max-w-[200px] truncate" :title="hold.pool_id">
                              ID: {{ hold.pool_id ? shortenAddress(hold.pool_id) : 'Unknown Pool' }}
                            </div>
                          </div>
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-theme-ink">{{ formatNumber(hold.balance, 4) }}</td>
                        <td class="py-3.5 px-4 text-right font-mono text-theme-dim">${{ formatNumber(hold.price_usd, 4) }}</td>
                        <td class="py-3.5 px-4 text-right font-mono text-cyan-600 dark:text-cyan-400 font-bold">${{ formatNumber(hold.value_usd, 2) }}</td>
                        <td class="py-3.5 px-4 text-right font-mono text-theme-dim">{{ formatNumber(hold.local_allocation_percentage, 1) }}%</td>
                      </tr>
                      <tr v-if="poolsValued.length === 0">
                        <td colspan="5" class="py-8 px-4 text-center text-xs font-mono text-theme-dim">
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
                <h3 class="font-sans font-semibold text-sm text-theme-ink">
                  Asset Allocation
                </h3>
                <span class="text-xs font-sans font-medium text-theme-dim">Live Portfolio</span>
              </div>
              
              <div class="p-6 flex-1 flex flex-col justify-center items-center space-y-6">
                <!-- SVG Donut Chart -->
                <div class="relative w-48 h-48 sm:w-56 sm:h-56 flex items-center justify-center flex-shrink-0">
                  <svg class="w-full h-full transform -rotate-90" viewBox="0 0 42 42">
                    <circle class="donut-hole" cx="21" cy="21" r="15.91549430918954" fill="transparent"></circle>
                    <circle class="donut-ring" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="var(--line)" stroke-width="3"></circle>
                    
                    <circle v-for="(slice, index) in donutSlices" :key="index"
                            cx="21" cy="21" r="15.91549430918954" fill="transparent"
                            :stroke="slice.color" stroke-width="3"
                            :stroke-dasharray="`${slice.percentage} ${100 - slice.percentage}`"
                            :stroke-dashoffset="slice.offset"
                            class="donut-segment transition-all duration-300"></circle>
                  </svg>
                  
                  <div class="absolute inset-0 flex flex-col items-center justify-center p-3 text-center select-none pointer-events-none max-w-[130px] sm:max-w-[155px] mx-auto">
                    <span class="text-[10px] font-sans font-semibold text-slate-400 dark:text-slate-400 tracking-wider uppercase block">PORTFOLIO</span>
                    <span 
                      class="font-extrabold text-theme-ink font-mono leading-tight tracking-tight block truncate w-full mt-0.5"
                      :class="formattedPortfolioDonutValue.length > 13 ? 'text-xs sm:text-sm' : (formattedPortfolioDonutValue.length > 9 ? 'text-sm sm:text-base' : 'text-base sm:text-lg')"
                      :title="formattedPortfolioDonutValue"
                    >
                      {{ formattedPortfolioDonutValue }}
                    </span>
                  </div>
                </div>

                <!-- Donut Legend -->
                <div class="w-full space-y-2">
                  <div v-for="slice in legendSlices" :key="slice.label" class="flex items-center justify-between text-xs font-mono p-1 rounded hover:bg-theme-panel2/60">
                    <div class="flex items-center gap-2">
                      <span class="w-2.5 h-2.5 rounded-sm" :style="`background-color: ${slice.color}`"></span>
                      <span class="text-theme-ink font-semibold">{{ slice.label }}</span>
                    </div>
                    <span class="text-theme-dim">{{ formatNumber(slice.percentage, 1) }}%</span>
                  </div>
                  <div v-if="legendSlices.length === 0" class="text-center text-xs text-theme-dim py-2">
                    No positive token balances to allocate
                  </div>
                </div>
              </div>
            </section>
          </div>

        </div>

        <!-- SECTION 3: CLAIMABLE BALANCES (COLLAPSIBLE) -->
        <section class="card">
          <button @click="isClaimsCollapsed = !isClaimsCollapsed" 
                  class="w-full flex items-center justify-between p-4 hover:bg-theme-panel2/50 transition-colors focus:outline-none select-none cursor-pointer">
            <div class="flex items-center gap-3">
              <span class="font-sans font-semibold text-theme-ink text-sm">Claimable Balances</span>
              <span class="text-xs bg-theme-panel2 border border-theme-line text-theme-dim px-2 py-0.5 rounded font-mono">
                {{ overviewData?.claimable_count ?? 0 }} Claims
              </span>
              <span class="text-xs text-theme-dim font-mono hidden sm:inline">
                | Claimable Value: ${{ overviewData?.claimable_value_usd ? formatNumber(overviewData.claimable_value_usd, 2) : '0.00' }}
              </span>
            </div>
            <div class="flex items-center gap-1 text-xs text-cyan-600 dark:text-cyan-400 hover:text-cyan-500 font-mono">
              <span>{{ isClaimsCollapsed ? 'View Details' : 'Hide Details' }}</span>
              <span>{{ isClaimsCollapsed ? '→' : '↓' }}</span>
            </div>
          </button>

          <!-- COLLAPSIBLE CONTENT -->
          <div v-show="!isClaimsCollapsed" class="border-t border-theme-line">
            <!-- Empty state -->
            <div v-if="claimableHoldings.length === 0" class="p-8 text-center text-theme-dim">
              <p class="text-sm font-semibold text-theme-ink">No pending claimable balances found.</p>
            </div>
            <!-- Claims table -->
            <div v-else class="overflow-auto max-h-[350px] custom-scrollbar">
              <table class="w-full text-left border-collapse min-w-[600px]">
                <thead>
                  <tr class="border-b border-theme-line text-xs font-sans font-semibold text-theme-dim bg-theme-panel2/50">
                    <th class="py-3.5 px-4">Asset</th>
                    <th class="py-3.5 px-4 text-right">Balance</th>
                    <th class="py-3.5 px-4 text-right">Price (USD)</th>
                    <th class="py-3.5 px-4 text-right">Value (USD)</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-theme-line text-xs font-mono">
                  <tr v-for="hold in claimableHoldings" :key="hold.id" class="hover:bg-theme-panel2/60 transition-colors">
                    <td class="py-3.5 px-4 flex items-center gap-3">
                      <div class="w-7 h-7 rounded-full bg-theme-panel2 border border-theme-line flex items-center justify-center text-[10px] font-bold text-theme-dim select-none flex-shrink-0 overflow-hidden shadow-sm">
                        <div v-if="hold.asset_code === 'XLM' || hold.asset_type === 'native'" class="w-full h-full p-1.5 flex items-center justify-center">
                          <XlmLogo />
                        </div>
                        <img v-else-if="hold.logo_url" :src="hold.logo_url" class="w-full h-full object-cover" @error="hold.logo_url = null" />
                        <span v-else class="text-[10px] uppercase font-bold text-theme-dim">{{ hold.asset_code?.slice(0, 3) }}</span>
                      </div>
                      <div>
                        <div class="font-sans font-bold text-theme-ink uppercase text-xs flex items-center gap-1.5">
                          <router-link v-if="hold.asset_code !== 'XLM' && hold.asset_type !== 'liquidity_pool_shares'"
                                       :to="{ path: '/token-insight', query: { asset_code: hold.asset_code, issuer: hold.asset_issuer } }"
                                       class="text-cyan-600 dark:text-cyan-400 hover:text-cyan-500 transition-colors">
                            {{ hold.asset_code }}
                          </router-link>
                          <span v-else>{{ hold.asset_code }}</span>
                          <span class="text-[9px] lowercase bg-amber-500/10 text-amber-600 dark:text-amber-400 px-1 py-0.2 rounded border border-amber-500/20">claimable</span>
                        </div>
                        <div class="text-[10px] text-theme-faint break-all select-all max-w-[200px] truncate" :title="hold.asset_issuer">
                          <router-link v-if="hold.asset_issuer" :to="`/wallet/${hold.asset_issuer}`" class="text-theme-faint hover:text-cyan-600 dark:hover:text-cyan-400 hover:underline transition-colors">
                            {{ shortenAddress(hold.asset_issuer) }}
                          </router-link>
                          <span v-else>Stellar Network</span>
                        </div>
                      </div>
                    </td>
                    <td class="py-3.5 px-4 text-right text-theme-ink font-semibold">
                      {{ formatNumber(hold.balance, 4) }}
                    </td>
                    <td class="py-3.5 px-4 text-right text-theme-dim">
                      {{ hold.price_usd ? '$' + formatNumber(hold.price_usd, 6) : '--' }}
                    </td>
                    <td class="py-3.5 px-4 text-right text-theme-ink font-bold">
                      {{ hold.value_usd ? '$' + formatNumber(hold.value_usd, 2) : '--' }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- ACTIVITY AND ACCOUNT DETAILS GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- SECTION 4: REAL-TIME HORIZON ACTIVITY (LEFT 2 COLS) -->
          <div class="lg:col-span-2 space-y-6">
            <section class="card">
              <div class="card-hd">
                <h3 class="font-sans font-semibold text-sm text-theme-ink">
                  Live Operations & Activity
                </h3>
                <span class="text-xs font-sans font-medium text-theme-dim">Real-Time Horizon</span>
              </div>

              <!-- Filter tabs -->
              <div class="flex items-center gap-1 border-b border-theme-line bg-theme-panel2/50 p-2 overflow-x-auto whitespace-nowrap scrollbar-none">
                <button v-for="tab in activityTabs" :key="tab.value"
                        @click="changeActivityTab(tab.value)"
                        class="text-xs font-sans font-medium px-3.5 py-1.5 rounded-lg transition cursor-pointer"
                        :class="activeActivityTab === tab.value ? 'bg-theme-panel border border-theme-line2 text-theme-ink font-semibold shadow-sm' : 'text-theme-dim hover:text-theme-ink'">
                  {{ tab.label }}
                </button>
              </div>

              <!-- SKELETON LOADER -->
              <div v-if="activityLoading && events.length === 0" class="p-6 space-y-4 animate-pulse">
                <div class="h-10 bg-theme-line rounded-lg w-full" v-for="i in 10" :key="i"></div>
              </div>

              <!-- EMPTY STATE -->
              <div v-else-if="filteredEvents.length === 0" class="p-16 text-center text-theme-dim">
                <Activity class="w-10 h-10 mx-auto mb-2 text-theme-faint animate-pulse" />
                <p class="text-sm font-semibold text-theme-ink">No recent activity events found.</p>
                <p class="text-xs text-theme-dim font-mono mt-1">This wallet does not have transactions in this category.</p>
              </div>

              <!-- EVENTS LIST -->
              <div v-else class="divide-y divide-theme-line">
                <div v-for="event in filteredEvents" :key="event.id" class="p-4 flex items-center justify-between gap-4 hover:bg-theme-panel2/50 transition-colors">
                  <!-- Left & Center details -->
                  <div class="flex items-center gap-4 min-w-0">
                    <div class="min-w-0">
                      <!-- Human-Readable Cards -->
                      <div class="font-sans text-xs text-theme-dim">
                        <!-- BUY -->
                        <div v-if="event.event_type === 'BUY'" class="space-y-0.5">
                          <div>
                            <span class="text-[11px] font-sans font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20 mr-2">BUY</span>
                            <span class="font-bold text-theme-ink">Bought {{ formatNumber(event.amount, 2) }} {{ event.asset_code }}</span>
                          </div>
                          <div class="text-xs text-theme-dim font-sans mt-0.5">
                            Spent <span class="font-mono font-medium text-theme-ink">{{ formatNumber(event.counter_amount, 2) }}</span> {{ event.counter_asset_code || 'XLM' }}
                          </div>
                        </div>

                        <!-- SELL -->
                        <div v-else-if="event.event_type === 'SELL'" class="space-y-0.5">
                          <div>
                            <span class="text-[11px] font-sans font-semibold text-rose-600 dark:text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded border border-rose-500/20 mr-2">SELL</span>
                            <span class="font-bold text-theme-ink">Sold {{ formatNumber(event.amount, 2) }} {{ event.asset_code }}</span>
                          </div>
                          <div class="text-xs text-theme-dim font-sans mt-0.5">
                            Received <span class="font-mono font-medium text-theme-ink">{{ formatNumber(event.counter_amount, 2) }}</span> {{ event.counter_asset_code || 'XLM' }}
                          </div>
                        </div>

                        <!-- PAYMENT IN -->
                        <div v-else-if="event.event_type === 'PAYMENT_IN'" class="space-y-0.5">
                          <div>
                            <span class="text-[11px] font-sans font-semibold text-teal-600 dark:text-teal-400 bg-teal-500/10 px-2 py-0.5 rounded border border-teal-500/20 mr-2">INCOMING</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">+{{ formatNumber(event.amount, 2) }} {{ event.asset_code || 'XLM' }}</span>
                          </div>
                          <div class="text-xs text-theme-dim font-sans mt-0.5">
                            Received from <router-link v-if="event.counterparty_address" :to="`/wallet/${event.counterparty_address}`" class="text-cyan-600 dark:text-cyan-400 hover:underline select-all font-mono font-medium transition-colors">{{ shortenAddress(event.counterparty_address) }}</router-link><span v-else class="text-theme-ink select-all font-medium">Unknown</span>
                          </div>
                        </div>

                        <!-- PAYMENT OUT -->
                        <div v-else-if="event.event_type === 'PAYMENT_OUT'" class="space-y-0.5">
                          <div>
                            <span class="text-[11px] font-sans font-semibold text-purple-600 dark:text-purple-400 bg-purple-500/10 px-2 py-0.5 rounded border border-purple-500/20 mr-2">OUTGOING</span>
                            <span class="font-bold text-rose-600 dark:text-rose-400">-{{ formatNumber(event.amount, 2) }} {{ event.asset_code || 'XLM' }}</span>
                          </div>
                          <div class="text-xs text-theme-dim font-sans mt-0.5">
                            Sent to <router-link v-if="event.counterparty_address" :to="`/wallet/${event.counterparty_address}`" class="text-cyan-600 dark:text-cyan-400 hover:underline select-all font-mono font-medium transition-colors">{{ shortenAddress(event.counterparty_address) }}</router-link><span v-else class="text-theme-ink select-all font-medium">Unknown</span>
                          </div>
                        </div>

                        <!-- LP ADD -->
                        <div v-else-if="event.event_type === 'LP_ADD'" class="space-y-0.5">
                          <div>
                            <span class="text-[11px] font-sans font-semibold text-cyan-600 dark:text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded border border-cyan-500/20 mr-2">LP ADD</span>
                            <span class="font-bold text-theme-ink">Added Liquidity</span>
                          </div>
                          <div class="text-xs text-theme-dim font-sans mt-0.5">
                            Provided <span class="font-mono font-medium text-theme-ink">{{ formatNumber(event.amount, 2) }}</span> {{ event.asset_code || 'LP' }}
                          </div>
                        </div>

                        <!-- LP REMOVE -->
                        <div v-else-if="event.event_type === 'LP_REMOVE'" class="space-y-0.5">
                          <div>
                            <span class="text-[11px] font-sans font-semibold text-cyan-600 dark:text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded border border-cyan-500/20 mr-2">LP REMOVE</span>
                            <span class="font-bold text-theme-ink">Removed Liquidity</span>
                          </div>
                          <div class="text-xs text-theme-dim font-sans mt-0.5">
                            Withdrew <span class="font-mono font-medium text-theme-ink">{{ formatNumber(event.amount, 2) }}</span> {{ event.asset_code || 'LP' }}
                          </div>
                        </div>

                        <!-- TRUSTLINE ADD -->
                        <div v-else-if="event.event_type === 'TRUSTLINE_ADD'" class="space-y-0.5">
                          <div>
                            <span class="text-[11px] font-sans font-semibold text-theme-dim bg-theme-panel2 border border-theme-line px-2 py-0.5 rounded mr-2">TRUSTLINE</span>
                            <span class="font-bold text-theme-ink">Added Trustline</span>
                          </div>
                          <div class="text-xs text-theme-dim font-sans mt-0.5">
                            Opened trustline for token <span class="text-theme-ink font-semibold">{{ event.asset_code }}</span>
                          </div>
                        </div>

                        <!-- TRUSTLINE REMOVE -->
                        <div v-else-if="event.event_type === 'TRUSTLINE_REMOVE'" class="space-y-0.5">
                          <div>
                            <span class="text-[11px] font-sans font-semibold text-theme-dim bg-theme-panel2 border border-theme-line px-2 py-0.5 rounded mr-2">TRUSTLINE</span>
                            <span class="font-bold text-theme-ink">Removed Trustline</span>
                          </div>
                          <div class="text-xs text-theme-dim font-sans mt-0.5">
                            Closed trustline for token <span class="text-theme-ink font-semibold">{{ event.asset_code }}</span>
                          </div>
                        </div>

                        <!-- CLAIMABLE BALANCE CLAIM -->
                        <div v-else-if="event.event_type === 'CLAIMABLE_BALANCE_CLAIM'" class="space-y-0.5">
                          <div>
                            <span class="text-[11px] font-sans font-semibold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20 mr-2">CLAIM</span>
                            <span class="font-bold text-theme-ink">Claimed Pending Balance</span>
                          </div>
                          <div class="text-xs text-theme-dim font-sans mt-0.5" v-if="event.counterparty_address">
                            Claimed from <router-link :to="`/wallet/${event.counterparty_address}`" class="text-cyan-600 dark:text-cyan-400 hover:underline select-all font-mono font-medium transition-colors">{{ shortenAddress(event.counterparty_address) }}</router-link>
                          </div>
                        </div>

                        <!-- CLAIMABLE BALANCE CREATE -->
                        <div v-else-if="event.event_type === 'CLAIMABLE_BALANCE_CREATE'" class="space-y-0.5">
                          <div>
                            <span class="text-[11px] font-sans font-semibold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20 mr-2">CLAIMABLE</span>
                            <span class="font-bold text-theme-ink">Created Claimable Balance</span>
                          </div>
                          <div class="text-xs text-theme-dim font-sans mt-0.5">
                            <span>Locked <span class="font-mono font-medium text-theme-ink">{{ formatNumber(event.amount, 5) }}</span> {{ event.asset_code }}</span>
                            <span v-if="event.counterparty_address"> for <router-link :to="`/wallet/${event.counterparty_address}`" class="text-cyan-600 dark:text-cyan-400 hover:underline select-all font-mono font-medium transition-colors">{{ shortenAddress(event.counterparty_address) }}</router-link></span>
                          </div>
                        </div>

                        <!-- CLAIMABLE BALANCE RECEIVED -->
                        <div v-else-if="event.event_type === 'CLAIMABLE_BALANCE_RECEIVED'" class="space-y-0.5">
                          <div>
                            <span class="text-[11px] font-sans font-semibold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20 mr-2">CLAIMABLE</span>
                            <span class="font-bold text-theme-ink">Received Claimable Balance</span>
                          </div>
                          <div class="text-xs text-theme-dim font-sans mt-0.5">
                            <span><span class="font-mono font-medium text-theme-ink">{{ formatNumber(event.amount, 5) }}</span> {{ event.asset_code }} available to claim</span>
                          </div>
                        </div>

                        <!-- ACCOUNT MERGE -->
                        <div v-else-if="event.event_type === 'ACCOUNT_MERGE'" class="space-y-0.5">
                          <div>
                            <span class="text-[11px] font-sans font-semibold text-purple-600 dark:text-purple-400 bg-purple-500/10 px-2 py-0.5 rounded border border-purple-500/20 mr-2">MERGE</span>
                            <span class="font-bold text-theme-ink">Account Merged</span>
                          </div>
                          <div class="text-xs text-theme-dim font-sans mt-0.5">
                            Merged into <router-link v-if="event.counterparty_address" :to="`/wallet/${event.counterparty_address}`" class="text-cyan-600 dark:text-cyan-400 hover:underline select-all font-mono font-medium transition-colors">{{ shortenAddress(event.counterparty_address) }}</router-link>
                          </div>
                        </div>

                        <!-- CLAWBACK -->
                        <div v-else-if="event.event_type === 'CLAWBACK'" class="space-y-0.5">
                          <div>
                            <span class="text-[11px] font-sans font-semibold text-rose-600 dark:text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded border border-rose-500/20 mr-2">CLAWBACK</span>
                            <span class="font-bold text-theme-ink">Clawback Asset</span>
                          </div>
                          <div class="text-xs text-theme-dim font-sans mt-0.5">
                            <span><span class="font-mono font-medium text-theme-ink">{{ formatNumber(event.amount, 2) }}</span> {{ event.asset_code }}</span>
                            <span v-if="event.counterparty_address"> from <router-link :to="`/wallet/${event.counterparty_address}`" class="text-cyan-600 dark:text-cyan-400 hover:underline select-all font-mono font-medium transition-colors">{{ shortenAddress(event.counterparty_address) }}</router-link></span>
                          </div>
                        </div>

                        <!-- TRUSTLINE FLAGS -->
                        <div v-else-if="event.event_type === 'TRUSTLINE_FLAGS'" class="space-y-0.5">
                          <div>
                            <span class="text-[11px] font-sans font-semibold text-theme-dim bg-theme-panel2 border border-theme-line px-2 py-0.5 rounded mr-2">FLAGS</span>
                            <span class="font-bold text-theme-ink">Trustline Flags</span>
                          </div>
                          <div class="text-xs text-theme-dim font-sans mt-0.5">
                            <span>Token {{ event.asset_code }}</span>
                            <span v-if="event.counterparty_address"> for <router-link :to="`/wallet/${event.counterparty_address}`" class="text-cyan-600 dark:text-cyan-400 hover:underline select-all font-mono font-medium transition-colors">{{ shortenAddress(event.counterparty_address) }}</router-link></span>
                          </div>
                        </div>

                        <!-- SMART CONTRACT / SOROBAN -->
                        <div v-else-if="event.event_type === 'INVOKE_HOST_FUNCTION'" class="space-y-0.5">
                          <div>
                            <span class="text-[11px] font-sans font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-500/20 mr-2">CONTRACT</span>
                            <span class="font-bold text-theme-ink">Smart Contract Invocation</span>
                          </div>
                          <div class="text-xs text-theme-dim font-sans mt-0.5" v-if="event.counterparty_address">
                            Function: <span class="font-mono">{{ event.counterparty_address }}</span>
                          </div>
                        </div>

                        <!-- SET OPTIONS / MANAGE DATA -->
                        <div v-else-if="event.event_type === 'SET_OPTIONS' || event.event_type === 'MANAGE_DATA'" class="space-y-0.5">
                          <div>
                            <span class="text-[11px] font-sans font-semibold text-theme-dim bg-theme-panel2 border border-theme-line px-2 py-0.5 rounded mr-2">CONFIG</span>
                            <span class="font-bold text-theme-ink">{{ getEventName(event.event_type) }}</span>
                          </div>
                          <div class="text-xs text-theme-dim font-sans mt-0.5">
                            <span v-if="event.asset_code">Key: <span class="font-mono">{{ event.asset_code }}</span></span>
                            <span v-if="event.counterparty_address && /^G[A-Z2-7]{55}$/.test(event.counterparty_address)"> | Signer: <router-link :to="`/wallet/${event.counterparty_address}`" class="text-cyan-600 dark:text-cyan-400 hover:underline select-all font-mono font-medium transition-colors">{{ shortenAddress(event.counterparty_address) }}</router-link></span>
                          </div>
                        </div>

                        <!-- DEFAULT -->
                        <div v-else class="space-y-0.5">
                          <div>
                            <span class="text-[11px] font-sans font-semibold text-theme-dim bg-theme-panel2 border border-theme-line px-2 py-0.5 rounded mr-2">OP</span>
                            <span class="font-bold text-theme-ink">{{ getEventName(event.event_type) }}</span>
                          </div>
                          <div class="text-xs text-theme-dim font-sans mt-0.5">
                            <span v-if="event.amount">Amount: <span class="font-mono font-medium text-theme-ink">{{ formatNumber(event.amount, 2) }}</span> {{ event.asset_code }}</span>
                            <span v-if="event.counterparty_address && /^G[A-Z2-7]{55}$/.test(event.counterparty_address)"> with <router-link :to="`/wallet/${event.counterparty_address}`" class="text-cyan-600 dark:text-cyan-400 hover:underline select-all font-mono font-medium transition-colors">{{ shortenAddress(event.counterparty_address) }}</router-link></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Right details: Value & link -->
                  <div class="text-right flex-shrink-0 space-y-1 font-mono">
                    <div class="text-xs text-theme-ink font-extrabold">
                      {{ event.value_usd ? '$' + formatNumber(event.value_usd, 2) : '--' }}
                    </div>
                    <div class="text-[10px] text-theme-dim flex items-center justify-end gap-1.5">
                      <span>{{ formatRelativeTime(event.occurred_at) }}</span>
                      <router-link v-if="event.transaction_hash" 
                                   :to="`/tx/${event.transaction_hash}`" 
                                   class="p-1 bg-theme-panel border border-theme-line hover:border-theme-line2 rounded text-theme-dim hover:text-cyan-500 dark:hover:text-cyan-400 transition"
                                   title="View Transaction Details">
                        <ArrowUpRight class="w-3 h-3" />
                      </router-link>
                    </div>
                  </div>
                </div>
              </div>

              <!-- REAL-TIME PAGINATION: LOAD MORE VIA HORIZON CURSOR -->
              <div v-if="hasMoreEvents && filteredEvents.length > 0" class="p-4 text-center border-t border-theme-line bg-theme-panel2/50">
                <button @click="loadMoreEvents" 
                        :disabled="activityLoading"
                        class="text-xs font-sans font-semibold px-5 py-2 bg-theme-panel border border-theme-line hover:border-theme-line2 transition rounded-lg text-theme-ink cursor-pointer disabled:opacity-50 shadow-sm">
                  <span v-if="activityLoading">Loading...</span>
                  <span v-else>Load More Activity</span>
                </button>
              </div>
            </section>
          </div>

          <!-- SECTION 5: ACCOUNT DETAILS & SIGNERS (RIGHT 1 COL) -->
          <div class="space-y-6">
            <section class="card">
              <div class="card-hd">
                <h3 class="font-sans font-semibold text-sm text-theme-ink">
                  Account Configuration
                </h3>
                <span class="text-xs font-sans font-medium text-theme-dim">Security & Keys</span>
              </div>

              <div class="p-5 space-y-5">
                <!-- Signers List -->
                <div class="space-y-2 font-sans">
                  <div class="text-xs font-semibold text-slate-400">Signers & Weights</div>
                  <div class="space-y-1.5">
                    <div v-for="signer in sortedSigners" :key="signer.key" 
                         class="p-2.5 bg-theme-panel2 border border-theme-line rounded-lg flex items-center justify-between text-xs font-mono">
                      <div class="flex items-center gap-2 min-w-0 font-sans">
                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" :class="signer.key === address ? 'bg-cyan-500' : 'bg-theme-faint'"></span>
                        <router-link v-if="signer.key !== address" :to="`/wallet/${signer.key}`" class="text-cyan-600 dark:text-cyan-400 hover:underline truncate max-w-[170px] transition-colors font-mono" :title="signer.key">
                          {{ shortenAddress(signer.key) }}
                        </router-link>
                        <span v-else class="text-theme-ink truncate max-w-[170px] font-mono" :title="signer.key">{{ shortenAddress(signer.key) }}</span>
                        <span v-if="signer.key === address" class="text-[10px] px-2 py-0.5 bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 rounded font-semibold">Master</span>
                      </div>
                      <span class="text-theme-ink font-semibold bg-theme-panel border border-theme-line px-2.5 py-0.5 rounded text-xs font-sans">Weight: <span class="font-mono font-bold">{{ signer.weight }}</span></span>
                    </div>
                    <div v-if="!overviewData?.signers?.length" class="text-xs text-theme-dim font-sans py-1">
                      No signers found
                    </div>
                  </div>
                </div>

                <!-- Thresholds -->
                <div class="space-y-2 pt-2 border-t border-theme-line font-sans">
                  <div class="text-xs font-semibold text-slate-400">Operation Thresholds</div>
                  <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="p-2 bg-theme-panel2 border border-theme-line rounded-lg">
                      <div class="text-[11px] text-theme-dim">Low</div>
                      <div class="text-sm font-bold text-theme-ink font-mono">{{ overviewData?.thresholds?.low_threshold ?? 0 }}</div>
                    </div>
                    <div class="p-2 bg-theme-panel2 border border-theme-line rounded-lg">
                      <div class="text-[11px] text-theme-dim">Medium</div>
                      <div class="text-sm font-bold text-cyan-600 dark:text-cyan-400 font-mono">{{ overviewData?.thresholds?.med_threshold ?? 0 }}</div>
                    </div>
                    <div class="p-2 bg-theme-panel2 border border-theme-line rounded-lg">
                      <div class="text-[11px] text-theme-dim">High</div>
                      <div class="text-sm font-bold text-rose-600 dark:text-rose-400 font-mono">{{ overviewData?.thresholds?.high_threshold ?? 0 }}</div>
                    </div>
                  </div>
                </div>

                <!-- Flags -->
                <div class="space-y-2 pt-2 border-t border-theme-line font-sans">
                  <div class="text-xs font-semibold text-slate-400">Account Flags</div>
                  <div class="grid grid-cols-2 gap-1.5 text-xs">
                    <div class="p-2 bg-theme-panel2 border border-theme-line rounded flex items-center justify-between">
                      <span class="text-theme-dim">Auth Required</span>
                      <span class="font-semibold" :class="overviewData?.flags?.auth_required ? 'text-emerald-600 dark:text-emerald-400' : 'text-theme-faint'">{{ overviewData?.flags?.auth_required ? 'Yes' : 'No' }}</span>
                    </div>
                    <div class="p-2 bg-theme-panel2 border border-theme-line rounded flex items-center justify-between">
                      <span class="text-theme-dim">Auth Revocable</span>
                      <span class="font-semibold" :class="overviewData?.flags?.auth_revocable ? 'text-emerald-600 dark:text-emerald-400' : 'text-theme-faint'">{{ overviewData?.flags?.auth_revocable ? 'Yes' : 'No' }}</span>
                    </div>
                    <div class="p-2 bg-theme-panel2 border border-theme-line rounded flex items-center justify-between">
                      <span class="text-theme-dim">Auth Immutable</span>
                      <span class="font-semibold" :class="overviewData?.flags?.auth_immutable ? 'text-emerald-600 dark:text-emerald-400' : 'text-theme-faint'">{{ overviewData?.flags?.auth_immutable ? 'Yes' : 'No' }}</span>
                    </div>
                    <div class="p-2 bg-theme-panel2 border border-theme-line rounded flex items-center justify-between">
                      <span class="text-theme-dim">Clawback</span>
                      <span class="font-semibold" :class="overviewData?.flags?.auth_clawback_enabled ? 'text-emerald-600 dark:text-emerald-400' : 'text-theme-faint'">{{ overviewData?.flags?.auth_clawback_enabled ? 'Yes' : 'No' }}</span>
                    </div>
                  </div>
                </div>

                <!-- Additional Details -->
                <div class="space-y-2 pt-2 border-t border-theme-line text-xs font-mono">
                  <div class="flex items-center justify-between">
                    <span class="text-theme-dim font-sans">Subentries</span>
                    <span class="text-theme-ink font-semibold">{{ overviewData?.subentry_count ?? 0 }}</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-theme-dim font-sans">Sequence #</span>
                    <span class="text-theme-dim font-semibold">{{ overviewData?.sequence ?? '--' }}</span>
                  </div>
                  <div v-if="overviewData?.home_domain" class="flex items-center justify-between">
                    <span class="text-theme-dim font-sans">Home Domain</span>
                    <span class="text-cyan-600 dark:text-cyan-400 font-semibold">{{ overviewData.home_domain }}</span>
                  </div>
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
import { ref, computed, onMounted, watch } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";
import { Copy, Check, AlertCircle, Coins, Activity, ArrowUpRight } from "lucide-vue-next";

import Header from "@/components/Header.vue";
import Footer from "@/components/Footer.vue";
import XlmLogo from "@/components/icons/XlmLogo.vue";

const route = useRoute();
const address = computed(() => route.params.address);

// State vars
const overviewLoading = ref(true);
const overviewData = ref(null);
const notFound = ref(false);
const connectionError = ref(false);

const holdingsLoading = ref(true);
const holdings = ref([]);

const activityLoading = ref(true);
const events = ref([]);
const activeActivityTab = ref("all");
const nextCursor = ref(null);
const hasMoreEvents = ref(false);

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

function loadMoreTrustlines() {
  trustlinesLimit.value += 10;
}

const trustlinesCount = computed(() => {
  return holdings.value.filter(h => h.asset_type !== 'native' && h.asset_type !== 'claimable_balance').length;
});

const holdingsCount = computed(() => {
  return holdings.value.filter(h => h.asset_type !== 'claimable_balance' && (h.balance > 0 || h.asset_type === 'native')).length;
});

const poolsCount = computed(() => {
  return holdings.value.filter(h => h.asset_type === 'liquidity_pool_shares' && h.balance > 0).length;
});

// Grouped and sorted holdings
const regularHoldings = computed(() => {
  return holdings.value.filter(h => h.asset_type !== 'claimable_balance');
});

const poolsOnly = computed(() => {
  return holdings.value.filter(h => h.asset_type === 'liquidity_pool_shares' && h.balance > 0);
});

const trustlinesOnly = computed(() => {
  return holdings.value.filter(h => h.asset_type !== 'native' && h.asset_type !== 'claimable_balance');
});

const claimableHoldings = computed(() => {
  return holdings.value.filter(h => h.asset_type === 'claimable_balance');
});

function toggleHoldingsSort(field) {
  if (holdingsSortField.value === field) {
    holdingsSortDirection.value = holdingsSortDirection.value === 'desc' ? 'asc' : 'desc';
  } else {
    holdingsSortField.value = field;
    holdingsSortDirection.value = 'desc';
  }
}

const regularHoldingsValued = computed(() => {
  let list = [...regularHoldings.value];

  // Apply Search
  const q = holdingsSearchQuery.value.trim().toLowerCase();
  if (q) {
    list = list.filter(h => {
      const code = (h.asset_code || '').toLowerCase();
      const issuer = (h.asset_issuer || '').toLowerCase();
      return code.includes(q) || issuer.includes(q);
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

  return list;
});

const trustlinesPaginated = computed(() => {
  return trustlinesFiltered.value.slice(0, trustlinesLimit.value);
});

const hasMoreTrustlines = computed(() => {
  return trustlinesFiltered.value.length > trustlinesLimit.value;
});

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
    let valA = 0;
    let valB = 0;
    if (poolsSortField.value === 'value') {
      valA = a.value_usd || 0;
      valB = b.value_usd || 0;
    } else if (poolsSortField.value === 'balance') {
      valA = a.balance || 0;
      valB = b.balance || 0;
    } else if (poolsSortField.value === 'allocation') {
      valA = a.local_allocation_percentage || 0;
      valB = b.local_allocation_percentage || 0;
    }

    if (poolsSortDirection.value === 'desc') {
      return valB - valA;
    } else {
      return valA - valB;
    }
  });

  return mapped;
});

function getTrustlineStatus(hold) {
  if (hold.is_authorized === false) {
    return hold.is_authorized_to_maintain_liabilities ? 'Maintain Liabilities Only' : 'Unauthorized';
  }
  if (hold.is_authorized) return 'Active';
  return '--';
}

const sortedSigners = computed(() => {
  const signers = overviewData.value?.signers ?? [];
  if (!signers.length) return [];
  return [...signers].sort((a, b) => {
    if (a.key === address.value) return -1;
    if (b.key === address.value) return 1;
    return (b.weight || 0) - (a.weight || 0);
  });
});

// Donut Chart slices calculation
const donutColors = ['#12CBEE', '#A78BFA', '#F472B6', '#34D399', '#FBBF24', '#60A5FA', '#94A3B8'];

const donutData = computed(() => {
  const list = regularHoldings.value.filter(h => (h.value_usd || 0) > 0);
  const totalVal = list.reduce((sum, h) => sum + (h.value_usd || 0), 0);
  
  if (totalVal <= 0) return [];

  // Sort descending
  const sorted = [...list].sort((a, b) => (b.value_usd || 0) - (a.value_usd || 0));

  // Top 5 + Others
  let slices = [];
  let otherVal = 0;

  sorted.forEach((item, index) => {
    if (index < 5) {
      slices.push({
        label: item.asset_code || 'Unknown',
        value: item.value_usd || 0,
        percentage: ((item.value_usd || 0) / totalVal) * 100,
        color: donutColors[index % donutColors.length]
      });
    } else {
      otherVal += (item.value_usd || 0);
    }
  });

  if (otherVal > 0) {
    slices.push({
      label: 'Other Tokens',
      value: otherVal,
      percentage: (otherVal / totalVal) * 100,
      color: donutColors[5]
    });
  }

  return slices;
});

const donutSlices = computed(() => {
  let accumulated = 0;
  return donutData.value.map(slice => {
    const offset = 100 - accumulated;
    accumulated += slice.percentage;
    return {
      ...slice,
      offset
    };
  });
});

const legendSlices = computed(() => donutData.value);

const formattedPortfolioDonutValue = computed(() => {
  if (overviewData.value?.portfolio_value_usd === null || overviewData.value?.portfolio_value_usd === undefined) {
    return '--';
  }
  const val = Number(overviewData.value.portfolio_value_usd);
  if (isNaN(val)) return '$0.00';
  return '$' + formatNumber(val, 2);
});

// Helper formatting
function formatNumber(value, decimals = 2) {
  if (value === null || value === undefined) return "--";
  return new Intl.NumberFormat("en-US", {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals
  }).format(value);
}

function formatRelativeTime(dateString) {
  if (!dateString) return "";
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
      case 'PAYMENTS': return type === 'PAYMENT_IN' || type === 'PAYMENT_OUT' || type === 'ACCOUNT_MERGE';
      case 'LIQUIDITY': return type === 'LP_ADD' || type === 'LP_REMOVE';
      case 'TRUSTLINES': return type === 'TRUSTLINE_ADD' || type === 'TRUSTLINE_REMOVE';
      default: return true;
    }
  });
});

function changeActivityTab(tab) {
  activeActivityTab.value = tab;
}

function getEventName(type) {
  const map = {
    'BUY': 'Buy Token',
    'SELL': 'Sell Token',
    'PAYMENT_IN': 'Incoming Payment',
    'PAYMENT_OUT': 'Outgoing Payment',
    'ACCOUNT_MERGE': 'Account Merge',
    'LP_ADD': 'Liquidity Deposit',
    'LP_REMOVE': 'Liquidity Withdrawal',
    'TRUSTLINE_ADD': 'Add Trustline',
    'TRUSTLINE_REMOVE': 'Remove Trustline',
    'CLAIMABLE_BALANCE_CREATE': 'Create Claimable Balance',
    'CLAIMABLE_BALANCE_RECEIVED': 'Received Claimable Balance',
    'CLAIMABLE_BALANCE_CLAIM': 'Claim Balance',
    'OFFER_CREATE': 'Create Offer',
    'OFFER_UPDATE': 'Update Offer',
    'OFFER_CANCEL': 'Cancel Offer',
    'INVOKE_HOST_FUNCTION': 'Smart Contract Call',
    'SET_OPTIONS': 'Account Configuration',
    'MANAGE_DATA': 'Manage Data Entry',
    'BUMP_SEQUENCE': 'Bump Sequence',
    'CLAWBACK': 'Clawback Asset',
    'TRUSTLINE_FLAGS': 'Trustline Flags',
  };
  return map[type] || (type ? type.replace(/_/g, ' ') : 'Operation');
}

// API Loader functions
async function loadOverview() {
  try {
    overviewLoading.value = true;
    notFound.value = false;
    connectionError.value = false;
    const { data } = await axios.get(`/api/wallet/${address.value}/overview`);
    if (data.status === 'success') {
      overviewData.value = data.data;
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

async function resolveMissingLogos() {
  const targets = holdings.value.filter(h => 
    h.asset_code !== 'XLM' && 
    h.asset_type !== 'liquidity_pool_shares' && 
    h.asset_issuer
  );

  if (targets.length === 0) return;

  targets.forEach(async (hold) => {
    const cacheKey = `tg_asset_logo_v2_${hold.asset_code}_${hold.asset_issuer}`;
    const cached = sessionStorage.getItem(cacheKey);
    if (cached) {
      if (cached !== 'none') hold.logo_url = cached;
      return;
    }

    try {
      const res = await fetch(`/api/token/stellar-proxy?endpoint=explorer/public/asset/${hold.asset_code}-${hold.asset_issuer}`);
      if (res.ok) {
        const json = await res.json();
        const logo = json.toml_info?.image || json.toml_info?.orgLogo || json.tomlInfo?.image || json.image || null;
        if (logo) {
          hold.logo_url = logo;
          sessionStorage.setItem(cacheKey, logo);
        } else {
          sessionStorage.setItem(cacheKey, 'none');
        }
      }
    } catch (e) {
      // Ignore network errors
    }
  });
}

async function loadHoldings() {
  try {
    holdingsLoading.value = true;
    const { data } = await axios.get(`/api/wallet/${address.value}/holdings`);
    if (data.status === 'success') {
      holdings.value = data.data ?? [];
      resolveMissingLogos();
    }
  } catch (err) {
    console.error(err);
  } finally {
    holdingsLoading.value = false;
  }
}

async function loadActivityEvents(append = false) {
  try {
    activityLoading.value = true;
    const params = { limit: 10 };
    if (append && nextCursor.value) {
      params.cursor = nextCursor.value;
    }

    const { data } = await axios.get(`/api/wallet/${address.value}/activity`, { params });
    
    if (data.status === 'success') {
      const records = data.data ?? [];
      events.value = append ? [...events.value, ...records] : records;
      nextCursor.value = data.next_cursor;
      hasMoreEvents.value = !!data.has_more;
    }
  } catch (err) {
    console.error(err);
  } finally {
    activityLoading.value = false;
  }
}

function loadMoreEvents() {
  if (nextCursor.value) {
    loadActivityEvents(true);
  }
}

function retryLoad() {
  connectionError.value = false;
  notFound.value = false;
  overviewLoading.value = true;
  holdingsLoading.value = true;
  activityLoading.value = true;
  
  Promise.all([
    loadOverview(),
    loadHoldings(),
    loadActivityEvents()
  ]);
}

// Watch routing parameters change
watch(address, async (newAddr) => {
  if (newAddr) {
    overviewData.value = null;
    holdings.value = [];
    events.value = [];
    nextCursor.value = null;
    hasMoreEvents.value = false;
    
    notFound.value = false;
    connectionError.value = false;
    overviewLoading.value = true;
    holdingsLoading.value = true;
    activityLoading.value = true;

    await Promise.all([
      loadOverview(),
      loadHoldings(),
      loadActivityEvents()
    ]);
  }
});

onMounted(async () => {
  await Promise.all([
    loadOverview(),
    loadHoldings(),
    loadActivityEvents()
  ]);
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap');

.asset-page-wrapper {
  --cyan: #12CBEE;
  --mono: var(--font-mono, "JetBrains Mono", ui-monospace, monospace);
  --disp: var(--font-disp, "Space Grotesk", sans-serif);
  --body: var(--font-sans, "Inter", sans-serif);

  background: var(--bg);
  color: var(--ink);
  font-family: var(--body);
  font-size: 14px;
  line-height: 1.45;
  min-height: 100vh;
  background-image: radial-gradient(900px 460px at 84% -12%, rgba(18, 203, 238, .09), transparent 62%), radial-gradient(760px 420px at 6% -8%, rgba(240, 24, 156, .07), transparent 60%);
}

html.light .asset-page-wrapper {
  background: var(--bg);
  color: var(--ink);
  background-image: radial-gradient(900px 460px at 84% -12%, rgba(18, 203, 238, .03), transparent 62%), radial-gradient(760px 420px at 6% -8%, rgba(240, 24, 156, .02), transparent 60%);
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
  box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
}

.card-hd {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 13px 16px;
  border-bottom: 1px solid var(--line);
  background: var(--panel);
}

.card-hd h3 {
  margin: 0;
  font-family: var(--disp);
  font-weight: 600;
  font-size: 14px;
  color: var(--ink);
}

.asset-hero {
  border-color: rgba(18, 203, 238, 0.2);
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

html.light .hero-glow {
  background: radial-gradient(circle, rgba(18, 203, 238, 0.05) 0%, transparent 70%);
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
  -ms-overflow-style: none;
  scrollbar-width: none;
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
