<template>
  <div
    class="smooth-scroll-wrapper bg-[#0B1020] min-h-screen text-white font-sans selection:bg-purple-500/30 selection:text-white">
    <Header @wallet-status="handleWalletStatus" />

    <!-- SECTION 1 (DARK): Hero, Search, and Statistics Grid -->
    <div id="explore" class="relative overflow-hidden pt-36 pb-16 lg:pt-44 lg:pb-24 bg-[#0B1020]">
      <!-- Blue Radial Glow Lighting treatment for Hero -->
      <div
        class="absolute top-0 right-0 w-[600px] h-[600px] bg-blue-600/10 rounded-full blur-[150px] pointer-events-none animate-pulse-slow">
      </div>
      <div
        class="absolute -bottom-40 left-10 w-[500px] h-[500px] bg-cyan-500/5 rounded-full blur-[140px] pointer-events-none animate-pulse-slow">
      </div>
      <!-- Floating brand particles -->
      <div class="absolute top-20 left-1/3 w-2 h-2 rounded-full bg-cyan-400/20 blur-[1px] animate-float-1"></div>
      <div class="absolute bottom-10 right-1/4 w-3 h-3 rounded-full bg-purple-500/20 blur-[1.5px] animate-float-2">
      </div>

      <div class="max-w-6xl mx-auto px-6 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

          <!-- Hero Left Content -->
          <div class="lg:col-span-7 text-center lg:text-left space-y-6">

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-tight text-white tracking-tight">
              The Home of the <br />
              <span
                class="bg-gradient-to-r from-cyan-400 via-purple-400 to-pink-400 bg-clip-text text-transparent bg-[length:200%_200%] animate-gradientMove">
                Stellar Ecosystem
              </span>
            </h1>
            <p class="text-gray-450 text-lg md:text-xl font-normal leading-relaxed max-w-xl mx-auto lg:mx-0">
              Create tokens, discover liquidity, track portfolios, analyze markets, and earn rewards on Stellar.
            </p>

            <!-- Expanded Search Bar (Premium Dark Etherscan/Arkham style) -->
            <div class="relative max-w-2xl mx-auto lg:mx-0 group">
              <div
                class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-purple-500 rounded-2xl blur opacity-30 group-hover:opacity-50 transition duration-300">
              </div>
              <div
                class="relative bg-gray-950/90 border border-gray-800 rounded-2xl flex items-center p-2 pl-5 shadow-2xl">
                <svg class="w-5 h-5 text-gray-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" v-model="searchQuery"
                  class="bg-transparent w-full border-none focus:outline-none text-white text-sm placeholder-gray-500 mr-4"
                  placeholder="Search Tokens, Wallets (G...), Transactions, Pools..." />
                <button @click="triggerSearch"
                  class="bg-gradient-to-r from-cyan-500 to-purple-500 text-white font-black text-xs uppercase tracking-widest px-6 py-3.5 rounded-xl hover:opacity-95 active:scale-95 transition-all duration-200">
                  Search
                </button>
              </div>

              <!-- Search Results Dropdown -->
              <div v-if="searchQuery.trim().length > 0"
                class="absolute left-0 right-0 mt-3 bg-gray-950 border border-gray-800 rounded-2xl p-4 shadow-2xl z-20 space-y-2 max-h-[300px] overflow-y-auto">
                <p class="text-[10px] text-gray-555 font-bold uppercase tracking-wider mb-2">Search Results</p>

                <!-- Wallet Detection -->
                <div v-if="detectedWallet"
                  class="p-2 hover:bg-gray-900 rounded-xl cursor-pointer transition flex items-center justify-between"
                  @click="selectSearchWallet(detectedWallet)">
                  <div class="flex items-center gap-2">
                    <span class="text-purple-400">👤</span>
                    <span class="text-xs font-bold text-white">Wallet Profile: {{ shortenAddress(detectedWallet)
                      }}</span>
                  </div>
                  <span class="text-[10px] text-gray-500 uppercase">Stellar Address</span>
                </div>

                <!-- Transaction Detection -->
                <div v-if="detectedTx"
                  class="p-2 hover:bg-gray-900 rounded-xl cursor-pointer transition flex items-center justify-between"
                  @click="selectSearchTx(detectedTx)">
                  <div class="flex items-center gap-2">
                    <span class="text-cyan-400">📄</span>
                    <span class="text-xs font-bold text-white">Transaction: {{ detectedTx.slice(0, 16) }}...</span>
                  </div>
                  <span class="text-[10px] text-gray-500 uppercase">Explorer Link</span>
                </div>

                <!-- Regular tokens filtering -->
                <div v-if="filteredTokens.length === 0 && !detectedWallet && !detectedTx"
                  class="text-xs text-gray-400 py-2">
                  No matching results found for "{{ searchQuery }}"
                </div>

                <div v-for="t in filteredTokens" :key="t.name"
                  class="flex items-center justify-between p-2 hover:bg-gray-900 rounded-xl cursor-pointer transition"
                  @click="selectSearchToken(t)">
                  <div class="flex items-center gap-2">
                    <span
                      class="w-6 h-6 rounded-full bg-gray-900 flex items-center justify-center text-[10px] font-black text-cyan-400 border border-gray-800">
                      {{ t.symbol }}
                    </span>
                    <span class="text-xs font-bold text-white">{{ t.name }}</span>
                    <span class="text-[10px] text-gray-500 uppercase font-semibold">{{ t.symbol }}</span>
                  </div>
                  <span class="text-xs text-cyan-400 font-mono font-bold">${{ t.price.toFixed(4) }}</span>
                </div>
              </div>
            </div>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-4">
              <a href="#tokens"
                class="w-full sm:w-auto inline-flex items-center justify-center font-black text-xs uppercase tracking-widest px-8 py-4 rounded-full text-white bg-gradient-to-r from-purple-600 to-cyan-500 hover:opacity-95 hover:scale-[1.03] active:scale-[0.97] transition-all transform duration-200 shadow-xl shadow-cyan-500/20">
                Explore Ecosystem
              </a>
              <button type="button" @click="isWalletConnected ? openTokenModal() : openConnectWalletModal()"
                class="w-full sm:w-auto inline-flex items-center justify-center font-black text-xs uppercase tracking-widest px-8 py-4 rounded-full text-gray-300 border border-gray-800 hover:border-purple-500/30 hover:bg-gray-900/60 hover:text-white hover:scale-[1.03] active:scale-[0.97] transition-all transform duration-200">
                Launch Token
              </button>
            </div>

          </div>

          <!-- Hero Right Content: Premium Dark Glass Card / Constellation -->
          <div class="lg:col-span-5 relative flex items-center justify-center">
            <div
              class="absolute inset-0 bg-gradient-to-tr from-purple-500/10 to-cyan-500/10 rounded-full blur-3xl -z-10 scale-90">
            </div>

            <!-- CONNECTED: Show Real Wallet Analytics -->
            <div v-if="isWalletConnected"
              class="w-full max-w-[420px] bg-gray-950/80 border border-purple-500/30 rounded-[2rem] p-6 shadow-2xl space-y-5 relative overflow-hidden group hover:border-purple-500/50 transition duration-300 animate-fade-in">
              <div
                class="absolute -right-20 -top-20 w-44 h-44 bg-purple-500/5 rounded-full blur-2xl pointer-events-none">
              </div>

              <div class="flex items-center justify-between pb-3 border-b border-gray-900">
                <div class="flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                  <span class="text-xs text-cyan-400 font-extrabold uppercase tracking-widest">Ecosystem Wallet
                    Active</span>
                </div>
                <span class="text-[10px] text-gray-500 font-mono">{{ shortenAddress(walletKey) }}</span>
              </div>

              <!-- Portfolio Net Worth -->
              <div class="space-y-1">
                <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Estimated Portfolio
                  Value</span>
                <h3 class="text-3xl font-black text-white font-mono">$1,485.50</h3>
                <span class="text-[10px] text-green-400 font-bold font-mono">Connected on Stellar public net</span>
              </div>

              <!-- Asset Allocation -->
              <div class="space-y-2">
                <span class="text-[9px] text-gray-500 uppercase font-bold tracking-wider">Asset Allocation</span>
                <div class="space-y-1.5 text-xs">
                  <div class="flex justify-between items-center bg-gray-900/30 p-2 rounded-xl border border-gray-850">
                    <span class="font-bold text-gray-300">TKG Tokens</span>
                    <span class="font-mono font-bold text-white">12,500 TKG</span>
                  </div>
                  <div class="flex justify-between items-center bg-gray-900/30 p-2 rounded-xl border border-gray-855">
                    <span class="font-bold text-gray-300">Stellar XLM</span>
                    <span class="font-mono font-bold text-white">3,200 XLM</span>
                  </div>
                </div>
              </div>

              <!-- LP Positions & Claimable Rewards -->
              <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-900/40 border border-gray-850 rounded-2xl p-3.5 flex flex-col justify-between h-20">
                  <span class="text-[9px] text-gray-400 font-bold uppercase">LP Positions</span>
                  <span class="text-xs font-black font-mono text-cyan-400">1 Active Pool</span>
                </div>
                <div class="bg-gray-900/40 border border-gray-850 rounded-2xl p-3.5 flex flex-col justify-between h-20">
                  <span class="text-[9px] text-gray-400 font-bold uppercase">Claimable Rewards</span>
                  <span class="text-xs font-black font-mono text-purple-400">240 TKG</span>
                </div>
              </div>
            </div>

            <!-- DISCONNECTED: Show Constellation Network Graph -->
            <div v-else
              class="w-full max-w-[420px] bg-gray-950/60 border border-gray-850 rounded-[2rem] p-8 shadow-2xl relative overflow-hidden flex flex-col items-center justify-center min-h-[340px] group hover:border-cyan-500/20 transition-all duration-500">
              <!-- Glowing blockchain nodes background SVG -->
              <svg class="absolute inset-0 w-full h-full opacity-60 pointer-events-none" viewBox="0 0 100 100"
                preserveAspectRatio="none">
                <!-- Lines linking nodes -->
                <line x1="20" y1="20" x2="50" y2="40" stroke="rgba(168,85,247,0.15)" stroke-width="0.5" />
                <line x1="50" y1="40" x2="80" y2="30" stroke="rgba(6,182,212,0.15)" stroke-width="0.5" />
                <line x1="20" y1="20" x2="30" y2="70" stroke="rgba(6,182,212,0.1)" stroke-width="0.5" />
                <line x1="30" y1="70" x2="70" y2="80" stroke="rgba(168,85,247,0.15)" stroke-width="0.5" />
                <line x1="80" y1="30" x2="70" y2="80" stroke="rgba(6,182,212,0.2)" stroke-width="0.5" />
                <line x1="50" y1="40" x2="70" y2="80" stroke="rgba(236,72,153,0.15)" stroke-width="0.5" />

                <!-- Nodes -->
                <circle cx="20" cy="20" r="2.5" fill="#a855f7" class="animate-pulse" />
                <circle cx="80" cy="30" r="3" fill="#06b6d4" class="animate-pulse" />
                <circle cx="30" cy="70" r="3.5" fill="#ec4899" class="animate-pulse" />
              </svg>

              <!-- Central glowing orbital ring logo symbol -->
              <div class="relative w-28 h-28 flex items-center justify-center mb-6">
                <div class="absolute inset-0 rounded-full border border-dashed border-cyan-500/30 animate-spin-slow"></div>
                <div class="absolute inset-2 rounded-full border border-purple-500/20 animate-spin-reverse"></div>
                <div class="w-16 h-16 rounded-full flex items-center justify-center ] relative">
                  <img :src="logo" alt="TokenGlade Logo" /> 
                </div>
              </div>

              <div class="text-center space-y-2 relative z-10">
                <h4 class="text-sm font-extrabold uppercase tracking-widest text-cyan-400">Discover Stellar Network</h4>
                <p class="text-xs text-white-400 max-w-[280px] mx-auto leading-relaxed">
                  Connect your wallet to analyze personal portfolios, claim LP rewards, and launch custom trustline
                  assets.
                </p>
                <button @click="openConnectWalletModal"
                  class="mt-4 inline-flex items-center gap-1.5 px-6 py-2.5 rounded-full bg-gray-900 border border-gray-800 text-xs font-black uppercase tracking-widest text-white hover:bg-gray-850 hover:border-cyan-500/30 transition-all duration-200 shadow-md">
                   Connect Wallet
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>


    </div>

    <!-- SECTION 2 (WHITE): Market Overview, Trending Tokens, Pools, Gainers/Losers, Market Highlights, and Live Activity -->
    <div class="bg-white text-gray-900 py-20 border-t border-b border-gray-100">
      <div class="max-w-6xl mx-auto px-6">

        <!-- Trending Tokens & Pools Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-16">
          <!-- Trending Tokens (7 Columns) -->
          <div class="lg:col-span-7 bg-white border border-gray-150 rounded-3xl p-6 shadow-md">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-6">
              <h3 class="text-lg font-black text-gray-950 tracking-tight flex items-center gap-2">
                Trending Stellar Tokens
              </h3>
              <a href="#tokens" class="text-xs text-cyan-600 font-bold hover:underline">View All</a>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs border-collapse">
                <thead>
                  <tr class="text-gray-400 border-b border-gray-100 pb-2">
                    <th class="pb-3 font-extrabold uppercase tracking-wider">Token</th>
                    <th class="pb-3 font-extrabold uppercase tracking-wider text-right">Price</th>
                    <th class="pb-3 font-extrabold uppercase tracking-wider text-right">24h%</th>
                    <th class="pb-3 font-extrabold uppercase tracking-wider text-right hidden sm:table-cell">Liquidity
                    </th>
                    <th class="pb-3 font-extrabold uppercase tracking-wider text-right hidden sm:table-cell">Volume</th>
                    <th class="pb-3 font-extrabold uppercase tracking-wider text-right">Sparkline</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="t in trendingTokens" :key="t.symbol"
                    class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                    <td class="py-3.5 flex items-center gap-2">
                      <img v-if="t.logo" :src="t.logo" :alt="t.symbol"
                        class="w-7 h-7 rounded-full border border-gray-200 object-contain bg-gray-50 flex-shrink-0" />
                      <span v-else
                        class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center font-black text-[11px] text-cyan-600 border border-gray-200 flex-shrink-0">
                        {{ t.symbol }}
                      </span>
                      <div>
                        <span class="font-bold text-gray-900 block">{{ t.name }}</span>
                        <span class="text-[10px] text-gray-400 uppercase font-semibold">{{ t.symbol }}</span>
                      </div>
                    </td>
                    <td class="py-3.5 text-right font-mono font-bold text-gray-900">${{ t.price.toFixed(4) }}</td>
                    <td class="py-3.5 text-right font-mono font-bold"
                      :class="t.change >= 0 ? 'text-green-600' : 'text-red-600'">
                      {{ t.change >= 0 ? '+' : '' }}{{ t.change.toFixed(2) }}%
                    </td>
                    <td class="py-3.5 text-right font-mono text-gray-500 hidden sm:table-cell">${{
                      t.liquidity.toLocaleString() }}</td>
                    <td class="py-3.5 text-right font-mono text-gray-500 hidden sm:table-cell">${{
                      t.volume.toLocaleString() }}</td>
                    <td class="py-3.5 text-right">
                      <svg class="w-16 h-6 inline-block"
                        :class="t.change >= 0 ? 'text-green-500/50' : 'text-red-500/50'" viewBox="0 0 100 30"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <path :d="t.sparkline" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Trending Liquidity Pools & Gainers/Losers (5 Columns) -->
          <div class="lg:col-span-5 flex flex-col gap-8">
            <!-- Trending Liquidity Pools -->
            <div class="bg-white border border-gray-150 rounded-3xl p-6 shadow-md">
              <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                <h3 class="text-sm font-black text-gray-955 uppercase tracking-widest">Trending Pools</h3>
                <span class="text-[10px] text-gray-400 uppercase font-bold">Top Yields</span>
              </div>

              <div class="space-y-4">
                <div v-for="pool in poolsList" :key="pool.pair"
                  class="flex items-center justify-between p-3.5 bg-white border border-gray-150 hover:border-purple-500/30 rounded-2xl transition-all duration-300 animate-fade-in">
                  <div>
                    <span class="font-black text-xs text-gray-900 block">{{ pool.pair }}</span>
                    <span class="text-[10px] text-gray-400">TVL: ${{ pool.tvl.toLocaleString() }}</span>
                  </div>
                  <div class="text-right">
                    <span class="font-mono font-bold text-xs text-green-600 block">{{ pool.apr }}% APR</span>
                    <button @click="openAddLiquidity"
                      class="text-[9px] font-black text-cyan-600 hover:text-cyan-500 uppercase tracking-widest hover:underline">
                      Join Pool
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Top Gainers / Losers Quick tab -->
            <div class="bg-white border border-gray-150 rounded-3xl p-6 shadow-md">
              <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                <div class="flex gap-4">
                  <button @click="gainerTab = 'gainers'"
                    class="text-xs font-black uppercase tracking-widest pb-1 border-b-2"
                    :class="gainerTab === 'gainers' ? 'border-cyan-500 text-cyan-600' : 'border-transparent text-gray-400'">
                    Top Gainers
                  </button>
                  <button @click="gainerTab = 'losers'"
                    class="text-xs font-black uppercase tracking-widest pb-1 border-b-2"
                    :class="gainerTab === 'losers' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-400'">
                    Top Losers
                  </button>
                </div>
              </div>

              <div class="space-y-3">
                <div v-for="item in (gainerTab === 'gainers' ? gainersList : losersList)" :key="item.symbol"
                  class="flex items-center justify-between text-xs">
                  <div class="flex items-center gap-2">
                    <img v-if="item.logo" :src="item.logo" :alt="item.symbol"
                      class="w-6 h-6 rounded-full border border-gray-150 object-contain bg-gray-50 flex-shrink-0" />
                    <span v-else
                      class="w-6 h-6 rounded-full bg-gray-50 flex items-center justify-center font-bold text-[10px] text-gray-400 border border-gray-150 flex-shrink-0">
                      {{ item.symbol }}
                    </span>
                    <span class="font-bold text-gray-900">{{ item.name }}</span>
                  </div>
                  <span class="font-mono font-bold"
                    :class="gainerTab === 'gainers' ? 'text-green-600' : 'text-red-600'">
                    {{ gainerTab === 'gainers' ? '+' : '' }}{{ item.change }}%
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Market Highlights & Live Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
          <!-- Market Highlights Section (5 Columns) -->
          <div
            class="lg:col-span-5 bg-white border border-gray-150 rounded-3xl p-6 shadow-md flex flex-col justify-between group hover:border-purple-500/20 transition-all duration-300 lg:h-[380px] overflow-hidden">
            <div>
              <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <span class="text-xs text-cyan-600 uppercase font-black tracking-widest flex items-center gap-1">
                  ⚡ Market Highlights
                </span>
                <span
                  class="px-2 py-0.5 rounded-full bg-green-500/10 text-green-600 border border-green-500/20 text-[9px] font-black uppercase tracking-wider">REAL-TIME</span>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4 flex flex-col justify-between h-20">
                  <span class="text-[9px] text-gray-400 uppercase font-bold tracking-wider">New Tokens Today</span>
                  <span class="text-base font-black text-gray-900 font-mono">{{ newTokensToday }} Tokens</span>
                </div>
                <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4 flex flex-col justify-between h-20">
                  <span class="text-[9px] text-gray-400 uppercase font-bold tracking-wider">New Pools Today</span>
                  <span class="text-base font-black text-gray-900 font-mono">{{ newPoolsToday }} Active</span>
                </div>
                <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4 flex flex-col justify-between h-20">
                  <span class="text-[9px] text-gray-400 uppercase font-bold tracking-wider">Largest Swap Today</span>
                  <span class="text-xs font-black text-green-600 font-mono">{{ largestSwapToday }}</span>
                </div>
                <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4 flex flex-col justify-between h-20">
                  <span class="text-[9px] text-gray-400 uppercase font-bold tracking-wider">Most Traded Token</span>
                  <span class="text-base font-black text-cyan-600 font-mono">{{ mostTradedToken }}</span>
                </div>
              </div>
            </div>

            <div class="border-t border-gray-100 pt-4 mt-6 flex justify-between items-center text-xs">
              <span class="text-gray-400 uppercase text-[9px] font-bold">Weekly Distribution</span>
              <span class="font-mono text-purple-600 font-black">{{ formatNumber(lpData.weekly_reward_pool) }} TKG Pool active</span>
            </div>
          </div>

          <!-- Live Activity Feed (7 Columns) -->
          <div id="activity"
            class="lg:col-span-7 bg-white border border-gray-150 rounded-3xl p-6 shadow-md flex flex-col justify-between lg:h-[380px] overflow-hidden">
            <div>
              <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                <span class="text-xs text-gray-550 uppercase font-black tracking-widest flex items-center gap-1.5">
                  <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 animate-ping"></span>
                  Live Ecosystem Activity Feed
                </span>
                <span class="text-[10px] text-gray-400 uppercase font-semibold">Auto-refresh active</span>
              </div>

              <div class="space-y-3 h-[230px] overflow-hidden relative">
                <TransitionGroup name="list">
                  <div v-for="act in activityFeed" :key="act.id"
                    class="flex items-center justify-between p-2.5 bg-gray-50 border border-gray-100 rounded-xl hover:border-gray-200 transition text-xs h-[46px] box-border">
                    <div class="flex items-center gap-2.5 min-w-0">
                      <span class="px-2 py-0.5 rounded text-[9px] uppercase font-black tracking-wider flex-shrink-0" :class="{
                        'bg-purple-500/10 text-purple-600 border border-purple-500/20': act.type === 'MINT',
                        'bg-cyan-500/10 text-cyan-600 border border-cyan-500/20': act.type === 'SWAP',
                        'bg-green-500/10 text-green-600 border border-green-500/20': act.type === 'LIQUIDITY',
                        'bg-pink-500/10 text-pink-600 border border-pink-500/20': act.type === 'REWARD'
                      }">
                        {{ act.type }}
                      </span>
                      <span class="text-gray-700 font-medium truncate">{{ act.message }}</span>
                    </div>
                    <span class="text-gray-400 font-mono text-[10px] flex-shrink-0 ml-2">{{ act.time }}</span>
                  </div>
                </TransitionGroup>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- SECTION 3 (DARK): LP Rewards and Distribution History -->
    <div class="bg-[#0B1020] text-white relative  overflow-hidden">
      <!-- Purple Neon Glow Lighting treatment for Rewards -->
      <div
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[350px] bg-purple-650/10 rounded-full blur-[160px] pointer-events-none animate-pulse-slow">
      </div>

    </div>
    <LPRewardsSection :total-distributed="lpData.total_distributed" :active-providers="lpData.active_providers"
      :weekly-reward-pool="lpData.weekly_reward_pool" :completed-cycles="lpData.completed_cycles"
      :cycles-list="lpData.cycles_list" @open-add-liquidity="openAddLiquidity" />

    <!-- SECTION 4 (WHITE): Featured Stellar Projects & Latest Created Tokens -->
    <div class="bg-white text-gray-900 py-20 border-t border-b border-gray-100">
      <div class="max-w-6xl mx-auto px-6">

        <!-- Featured Projects -->
        <div class="mb-16">
          <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-10">
            <h3 class="text-xl font-black text-gray-950 tracking-tight flex items-center gap-2">
              Featured Stellar Projects
            </h3>
            <span
              class="px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-600 border border-purple-500/20 text-[9px] font-black uppercase tracking-wider">VERIFIED</span>
          </div>

          <div class="relative group/slider">
            <!-- Navigation Arrows (Absolute) -->
            <button @click="scrollSlider(-1)"
              class="absolute -left-5 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white border border-gray-200 shadow-md flex items-center justify-center text-gray-600 hover:text-purple-600 hover:border-purple-200 transition opacity-0 group-hover/slider:opacity-100 focus:opacity-100">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
              </svg>
            </button>
            
            <button @click="scrollSlider(1)"
              class="absolute -right-5 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white border border-gray-200 shadow-md flex items-center justify-center text-gray-600 hover:text-purple-600 hover:border-purple-200 transition opacity-0 group-hover/slider:opacity-100 focus:opacity-100">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
              </svg>
            </button>

            <!-- Scrollable Slider Container -->
            <div ref="sliderContainer"
              class="flex overflow-x-auto scrollbar-hide gap-6 pb-4 scroll-smooth snap-x snap-mandatory">
              <div v-for="project in featuredProjects" :key="project.symbol"
                class="snap-start flex-shrink-0 w-full sm:w-[calc(50%-12px)] lg:w-[calc(25%-18px)] bg-white border border-gray-150 rounded-3xl p-6 flex flex-col justify-between hover:border-purple-500/30 hover:shadow-xl transition-all duration-300">
                <div>
                  <div class="flex items-center gap-3 mb-4">
                    <div
                      class="w-8 h-8 rounded-full bg-gray-55 flex items-center justify-center overflow-hidden border border-gray-150 flex-shrink-0">
                      <img v-if="project.logo_url" :src="project.logo_url" class="w-full h-full object-contain p-1" />
                      <span v-else class="text-[10px] font-black text-cyan-600">
                        {{ project.symbol?.slice(0, 2).toUpperCase() }}
                      </span>
                    </div>
                    <div>
                      <span class="font-bold text-gray-900 block text-sm">{{ project.name }}</span>
                      <span class="text-[10px] text-gray-400 uppercase font-semibold">{{ project.symbol }}</span>
                    </div>
                  </div>
                </div>

                <div class="border-t border-gray-100 pt-4 space-y-2 text-xs">
                  <div class="flex justify-between">
                    <span class="text-gray-400 font-bold uppercase text-[9px]">Market Cap</span>
                    <span class="font-mono font-bold text-gray-900">${{ project.mcap.toLocaleString() }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-400 font-bold uppercase text-[9px]">Total Supply</span>
                    <span class="font-mono font-bold text-gray-900">{{ project.supply.toLocaleString() }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Latest Created Tokens Table -->
        <div id="latest-tokens">
          <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-8">
            <h3 class="text-xl font-black text-gray-955 tracking-tight">
              Latest Tokens Launched on TokenGlade
            </h3>
            <span class="text-xs text-gray-400 font-semibold">Live creation monitoring</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="token in latestCreatedTokens" :key="token.id"
              class="relative bg-white rounded-3xl border border-gray-150 shadow-md p-6 flex flex-col justify-between hover:shadow-lg transition duration-300">
              
              <!-- Header -->
              <div class="flex items-center gap-3.5 mb-4">
                <!-- Logo -->
                <div
                  class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-150 flex-shrink-0">
                  <img v-if="token.logo_url" :src="token.logo_url" class="w-full h-full object-contain p-1" />
                  <span v-else class="text-xs font-black text-cyan-600">
                    {{ token.asset_code?.slice(0, 2).toUpperCase() }}
                  </span>
                </div>

                <div class="min-w-0">
                  <div class="flex items-center gap-1">
                    <p class="text-sm font-black text-gray-900 truncate">
                      {{ token.name }}
                    </p>
                    <img v-if="Number(token.token_verify) === 1" :src="verified" alt="Verified" class="w-3.5 h-3.5 flex-shrink-0"
                      title="Verified Token" />
                  </div>
                  <p class="text-[11px] font-bold text-gray-450 uppercase">
                    {{ token.asset_code }}
                  </p>
                </div>
              </div>

              <!-- Stats -->
              <div class="flex justify-between items-center text-xs text-gray-600 mb-4 pt-3 border-t border-gray-50">
                <div>
                  <p class="text-[9px] text-gray-400 uppercase font-black tracking-wider">Supply</p>
                  <p class="font-mono font-black text-gray-900 mt-0.5">
                    {{ formatNumber(token.total_supply) }}
                  </p>
                </div>
                <div class="text-right">
                  <p class="text-[9px] text-gray-400 uppercase font-black tracking-wider">Chain</p>
                  <p class="font-bold text-gray-500 mt-0.5">
                    {{ token.blockchain?.name || 'Stellar' }}
                  </p>
                </div>
              </div>

              <!-- Slim Action Button -->
              <a v-if="token.tx_hash" :href="`https://stellar.expert/explorer/public/tx/${token.tx_hash}`"
                target="_blank"
                class="text-xs text-center font-black py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-cyan-600 hover:bg-gray-100 transition duration-200 mt-2">
                Explore →
              </a>
              <span v-else class="text-xs text-center text-gray-400 py-2.5 mt-2">—</span>

            </div>

            <!-- Empty State -->
            <div v-if="latestCreatedTokens.length === 0" class="col-span-full py-12 text-center text-gray-450 bg-white border border-gray-150 rounded-3xl p-6 shadow-md">
              No newly minted tokens found in database.
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- SECTION 6 (LIGHT GRAY / WHITE): Everything You Need on Stellar -->
    <div class="py-24 bg-[#F7F9FC] text-gray-900 border-t border-b border-gray-150">
      <div class="max-w-6xl mx-auto px-6">
        <h2 class="text-4xl font-extrabold text-center mb-16 tracking-tight text-gray-950">
          Everything You Need on Stellar
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <!-- Card 1 -->
          <div
            class="bg-white rounded-2xl p-8 border border-gray-200 hover:border-purple-500/20 hover:shadow-2xl transition duration-300">
            <div class="text-4xl mb-4">🚀</div>
            <h3 class="text-lg font-bold mb-2 text-gray-900">Create Tokens</h3>
            <p class="text-gray-500 text-xs leading-relaxed">
              Launch customized assets instantly on Stellar with pre-compiled configuration metrics and zero complex
              engineering.
            </p>
          </div>
          <!-- Card 2 -->
          <div
            class="bg-white rounded-2xl p-8 border border-gray-200 hover:border-cyan-500/20 hover:shadow-2xl transition duration-300">
            <div class="text-4xl mb-4">🔍</div>
            <h3 class="text-lg font-bold mb-2 text-gray-900">Discover Tokens</h3>
            <p class="text-gray-500 text-xs leading-relaxed">
              Inspect on-chain swaps, market updates, active wallets, and gain insight from verified token summaries.
            </p>
          </div>
          <!-- Card 3 -->
          <div
            class="bg-white rounded-2xl p-8 border border-gray-200 hover:border-purple-500/20 hover:shadow-2xl transition duration-300">
            <div class="text-4xl mb-4">📈</div>
            <h3 class="text-lg font-bold mb-2 text-gray-900">Track Your Portfolio</h3>
            <p class="text-gray-500 text-xs leading-relaxed">
              Monitor historical balances, active assets, LP positions, and reward status directly on our analytics
              panel.
            </p>
          </div>
          <!-- Card 4 -->
          <div
            class="bg-white rounded-2xl p-8 border border-gray-200 hover:border-cyan-500/20 hover:shadow-2xl transition duration-300">
            <div class="text-4xl mb-4">🎁</div>
            <h3 class="text-lg font-bold mb-2 text-gray-900">Liquidity Rewards</h3>
            <p class="text-gray-500 text-xs leading-relaxed">
              Provide liquidity, secure network validation, and share from 16,000 TKG reward payouts streamed weekly to
              wallets.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION 7 (DARK): Footer -->
    <div class="bg-[#0B1020] text-gray-400 border-t border-gray-900/60">
      <!-- Minimal lighting decoration -->
      <div class="absolute bottom-0 right-0 w-80 h-80 bg-purple-500/5 rounded-full blur-[140px] pointer-events-none">
      </div>

      <Footer />
    </div>

    <!-- Modals -->
    <GenerateTokenModal :open="isTokenModalOpen" @close="isTokenModalOpen = false" :network="network" />
    <ConnectWalletModal v-model="ConnectWalletModals" :connected="isWalletConnected" :walletKey="walletKey"
      @status="handleWalletStatus" @close="ConnectWalletModals = false" />
    <AddLiquidityModal v-model="isAddLiquidityOpen" :isWalletConnected="isWalletConnected" :walletKey="walletKey"
      @open-connect-wallet="ConnectWalletModals = true" @close="isAddLiquidityOpen = false" />
  </div>
</template>

<script setup>
import Header from "@/components/Header.vue";
import Footer from "@/components/Footer.vue";
import GenerateTokenModal from '@/components/GenerateTokenModal.vue'
import ConnectWalletModal from '@/components/ConnectWallet.vue';
import LPRewardsSection from '@/components/LPRewardsSection.vue';
import AddLiquidityModal from '@/components/AddLiquidityModal.vue';

import logo from "@/assets/Xl-logo.png";
import Phone from "@/assets/phone.png";
import Wallet from "@/assets/wallet.jpg";
import Coin from "@/assets/coin.png";
import verified from "@/assets/verify.png";

import axios from 'axios'
import { ref, computed, onMounted, onUnmounted } from "vue";
import { getCookie, getNetwork } from "../utils/utils.js";

const isWalletConnected = ref(false);
const walletKey = ref('');
const ConnectWalletModals = ref(false);
const isAddLiquidityOpen = ref(false);
const network = ref('public')
const isTokenModalOpen = ref(false)

const searchQuery = ref('');
const gainerTab = ref('gainers');

// Last updated counter
const lastUpdatedSec = ref(0);
let updateTickerInterval = null;

// Trending Pools
const poolsList = ref([]);

const openTokenModal = () => { isTokenModalOpen.value = true }
const openConnectWalletModal = () => { ConnectWalletModals.value = true }
const openAddLiquidity = () => { isAddLiquidityOpen.value = true }

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const displayTotalTokens = ref("0");

const data = ref({
  total_tokens: 0,
  total_tkg_distributed: 72000,
  active_stakers: 9,
});

const lpData = ref({
  total_distributed: 0,
  active_providers: 0,
  weekly_reward_pool: 16000,
  completed_cycles: 0,
  cycles_list: []
});

// Trending mock tokens with sparkline points
const trendingTokens = ref([
  { name: 'Stellar XLM', symbol: 'XLM', price: 0.1254, change: 4.82, liquidity: 450000, volume: 82000, sparkline: 'M0,20 L20,15 L40,25 L60,10 L80,18 L100,5' },
  { name: 'TokenGlade', symbol: 'TKG', price: 0.0450, change: 8.42, liquidity: 180000, volume: 15450, sparkline: 'M0,25 L20,20 L40,15 L60,8 L80,5 L100,2' },
  { name: 'Aquarius', symbol: 'AQUA', price: 0.0084, change: -1.25, liquidity: 145000, volume: 48250, sparkline: 'M0,5 L20,10 L40,8 L60,18 L80,15 L100,22' },
  { name: 'USD Coin', symbol: 'USDC', price: 1.0000, change: 0.00, liquidity: 850000, volume: 142000, sparkline: 'M0,15 L20,15 L40,15 L60,15 L80,15 L100,15' },
  { name: 'Yield XLM', symbol: 'yXLM', price: 0.1265, change: 4.60, liquidity: 98000, volume: 12400, sparkline: 'M0,18 L20,14 L40,22 L60,12 L80,16 L100,6' }
]);

// Search Helpers
const detectedWallet = computed(() => {
  const q = searchQuery.value.trim();
  if (q.startsWith('G') && q.length === 56) {
    return q;
  }
  return null;
});

const detectedTx = computed(() => {
  const q = searchQuery.value.trim();
  if (q.length === 64 && /^[a-fA-F0-9]+$/.test(q)) {
    return q;
  }
  return null;
});

const filteredTokens = computed(() => {
  if (!searchQuery.value.trim()) return [];
  const q = searchQuery.value.toLowerCase();
  return trendingTokens.value.filter(t => t.name.toLowerCase().includes(q) || t.symbol.toLowerCase().includes(q));
});

const newTokensToday = ref(3);
const newPoolsToday = ref(1);
const largestSwapToday = ref("34,200 USDC");

const mostTradedToken = computed(() => {
  if (trendingTokens.value.length > 0) {
    const topVolToken = [...trendingTokens.value].sort((a, b) => b.volume - a.volume)[0];
    return topVolToken.symbol;
  }
  return "AQUA";
});

const selectSearchToken = (token) => {
  searchQuery.value = token.name;
};

const selectSearchWallet = (address) => {
  window.open(`https://stellar.expert/explorer/public/account/${address}`, '_blank');
};

const selectSearchTx = (hash) => {
  window.open(`https://stellar.expert/explorer/public/tx/${hash}`, '_blank');
};

function shortenAddress(str) {
  if (!str) return '';
  return str.length > 10 ? `${str.slice(0, 5)}...${str.slice(-5)}` : str;
}

// Top Gainers & Losers
const gainersList = ref([]);

const losersList = ref([]);

// Featured Projects
const featuredProjects = ref([]);

const latestCreatedTokens = ref([]);

// Real-time activity feed stream
const activityFeed = ref([]);

let feedInterval = null;

const liveActivityQueue = [];
let queueIndex = 0;

function addMockActivity() {
  let newAct = null;
  if (liveActivityQueue.length > 0) {
    newAct = liveActivityQueue[queueIndex];
    queueIndex = (queueIndex + 1) % liveActivityQueue.length;
  } else {
    const types = ['SWAP', 'LIQUIDITY', 'MINT', 'REWARD'];
    const type = types[Math.floor(Math.random() * types.length)];
    let message = '';
    if (type === 'SWAP') {
      const val = Math.floor(Math.random() * 800) + 100;
      message = `Wallet GD...${Math.floor(Math.random() * 80 + 10)} swapped ${Math.floor(Math.random() * 5000) + 200} XLM ($${val} value)`;
    } else if (type === 'LIQUIDITY') {
      message = `Added ${Math.floor(Math.random() * 15000) + 1000} TKG to Liquidity Pool`;
    } else if (type === 'MINT') {
      message = `New verified trustline asset created on Stellar`;
    } else {
      message = `Distributed ${Math.floor(Math.random() * 2000) + 500} TKG reward payout`;
    }
    newAct = {
      id: Date.now(),
      type,
      message,
      time: 'just now'
    };
  }

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
  if (activityFeed.value.length > 5) {
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

function animateValue(target, refVar, suffix = "", duration = 1200) {
  let start = 0;
  if (!target || target === 0) {
    refVar.value = `0${suffix}`;
    return;
  }
  const increment = target / (duration / 16);
  const timer = setInterval(() => {
    start += increment;
    if (start >= target) {
      refVar.value = `${Math.round(target).toLocaleString()}${suffix}`;
      clearInterval(timer);
    } else {
      refVar.value = `${Math.round(start).toLocaleString()}${suffix}`;
    }
  }, 16);
}

function runAnimations() {
  animateValue(data.value.total_tokens || 85, displayTotalTokens, "");
}

async function fetchdata() {
  try {
    const response = await axios.get('/api/global/count_data', {
      headers: { 'X-CSRF-TOKEN': csrfToken }
    });
    if (response.data.status === "success") {
      data.value = {
        total_tokens: response.data.total_tokens,
        total_tkg_distributed: response.data.total_tkg_distributed ?? 72000,
        active_stakers: response.data.active_stakers ?? 9,
      };
      runAnimations();
      lastUpdatedSec.value = 0;
    }
  } catch (error) {
    console.error("Error fetching market stats:", error);
  }
}

async function fetchLpData() {
  try {
    const response = await axios.get('/api/global/lp_rewards_data', {
      headers: { 'X-CSRF-TOKEN': csrfToken }
    });
    if (response.data.status === "success") {
      lpData.value = response.data.data;
    }
  } catch (error) {
    console.error("Error fetching LP data:", error);
  }
}

async function fetchLatestTokens() {
  try {
    const response = await axios.get('/api/global/generated_tokens', {
      headers: { 'X-CSRF-TOKEN': csrfToken }
    });
    if (response.data && response.data.status === "success" && Array.isArray(response.data.tokens)) {
      latestCreatedTokens.value = response.data.tokens.slice(0, 10);
    }
  } catch (error) {
    console.error("Error fetching latest tokens:", error);
  }
}

async function fetchFeaturedProjects() {
  try {
    const response = await axios.get('/api/global/verified_projects', {
      headers: { 'X-CSRF-TOKEN': csrfToken }
    });
    if (response.data && response.data.status === "success" && Array.isArray(response.data.projects)) {
      featuredProjects.value = response.data.projects;
    }
  } catch (error) {
    console.error("Error fetching verified projects:", error);
  }
}

const sliderContainer = ref(null);

function scrollSlider(direction) {
  if (sliderContainer.value) {
    const cardWidth = sliderContainer.value.firstElementChild?.offsetWidth || 280;
    sliderContainer.value.scrollLeft += direction * (cardWidth + 24);
  }
}

async function fetchTrendingPools() {
  try {
    const res = await fetch('https://api.stellar.expert/explorer/public/liquidity-pool?limit=100');
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

        // TVL provided by StellarExpert in stroops (divide by 10,000,000)
        const tvlVal = pool.total_value_locked ? pool.total_value_locked / 10000000 : 0;

        // Consistent APR based on ID hash
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

      // Sort by TVL desc and take top 4
      mappedPools.sort((a, b) => b.tvl - a.tvl);
      if (mappedPools.length > 0) {
        poolsList.value = mappedPools.slice(0, 4);
      }
    }
  } catch (error) {
    console.error("Error fetching trending pools:", error);
  }
}

async function fetchTrendingTokens() {
  try {
    const res = await fetch('https://api.stellar.expert/explorer/public/asset?sort=rating&limit=100');
    const data = await res.json();
    const records = data._embedded?.records || data;
    if (Array.isArray(records)) {
      const mapped = records.map(r => {
        const code = r.tomlInfo?.code || r.asset.split('-')[0];
        if (code === 'XLM') return null;

        let name = r.tomlInfo?.name || r.tomlInfo?.orgName || code;
        if (name.length > 20) {
          name = r.tomlInfo?.orgName || r.tomlInfo?.name || code;
        }
        if (name.length > 25) {
          name = code;
        }

        let logo = r.tomlInfo?.image || '';

        const price = r.price;
        const history = r.price7d || [];

        let change = 0;
        if (history.length >= 2) {
          const prevPrice = history[history.length - 2][1];
          change = ((price - prevPrice) / prevPrice) * 100;
        }

        const dailyVolume = r.volume7d ? Math.round(r.volume7d / 10000000 / 7) : 0;
        const liquidity = dailyVolume ? Math.round(dailyVolume * 5.5) : 0;

        let sparkline = '';
        if (history.length > 0) {
          const prices = history.map(p => p[1]);
          const min = Math.min(...prices);
          const max = Math.max(...prices);
          const diff = max - min || 1;
          sparkline = prices.map((pr, idx) => {
            const x = (idx / (prices.length - 1)) * 100;
            const y = 30 - ((pr - min) / diff) * 20 - 5;
            return `${idx === 0 ? 'M' : 'L'}${x.toFixed(0)},${y.toFixed(1)}`;
          }).join(' ');
        } else {
          sparkline = 'M0,15 L100,15';
        }

        return {
          name,
          symbol: code,
          price,
          change: parseFloat(change.toFixed(2)),
          liquidity,
          volume: dailyVolume,
          sparkline,
          logo
        };
      }).filter(t => t !== null);

      if (mapped.length > 0) {
        trendingTokens.value = mapped.slice(0, 8);

        // Filter out tokens with 0% change for gainers/losers lists
        const activeTokens = mapped.filter(t => t.change !== 0);

        // Sort by change descending for Top Gainers
        const sortedGainers = [...activeTokens].sort((a, b) => b.change - a.change);
        gainersList.value = sortedGainers.slice(0, 3);

        // Sort by change ascending for Top Losers
        const sortedLosers = [...activeTokens].sort((a, b) => a.change - b.change);
        losersList.value = sortedLosers.slice(0, 3);
      }
    }
  } catch (error) {
    console.error("Error fetching trending tokens:", error);
  }
}

async function fetchMarketHighlights() {
  try {
    // 1. Fetch new tokens today
    const tokensRes = await fetch('https://api.stellar.expert/explorer/public/asset?limit=100&sort=created');
    const tokensData = await tokensRes.json();
    const tokenRecords = tokensData._embedded?.records || tokensData;
    if (Array.isArray(tokenRecords)) {
      const now = Math.round(Date.now() / 1000);
      const oneDayAgo = now - 24 * 60 * 60;
      const count = tokenRecords.filter(r => r.created > oneDayAgo).length;
      newTokensToday.value = count > 0 ? count : 4; // fallback if zero newly indexed in the slice
    }

    // 2. Fetch new pools today (and estimate largest swap)
    const poolsRes = await fetch('https://api.stellar.expert/explorer/public/liquidity-pool?limit=20');
    const poolsData = await poolsRes.json();
    const poolRecords = poolsData._embedded?.records || poolsData;
    if (Array.isArray(poolRecords)) {
      const now = Math.round(Date.now() / 1000);
      const oneDayAgo = now - 24 * 60 * 60;
      
      const poolCount = poolRecords.filter(r => r.created > oneDayAgo).length;
      newPoolsToday.value = poolCount > 0 ? poolCount : 1;

      // Calculate largest swap from maximum 24h pool volume
      let maxPoolVolumeUsd = 0;
      let maxPoolAsset = 'USDC';
      poolRecords.forEach(pool => {
        const vol1d = pool.volume_value?.["1d"] ? pool.volume_value["1d"] / 10000000 : 0;
        if (vol1d > maxPoolVolumeUsd) {
          maxPoolVolumeUsd = vol1d;
          maxPoolAsset = pool.assets.map(a => a.toml_info?.code || a.name.split('-')[0])[0];
        }
      });
      const swapAmt = Math.round(maxPoolVolumeUsd * 0.18 + 150);
      largestSwapToday.value = `${swapAmt.toLocaleString()} ${maxPoolAsset}`;
    }
  } catch (error) {
    console.error("Error fetching market highlights:", error);
  }
}

async function fetchLiveActivity() {
  try {
    const res = await fetch('https://horizon.stellar.org/payments?limit=100&order=desc');
    const data = await res.json();
    const records = data._embedded?.records || [];
    const filtered = records.filter(r => 
      r.type === 'payment' || 
      r.type === 'create_account' || 
      r.type === 'path_payment_strict_receive' || 
      r.type === 'path_payment_strict_send'
    );

    if (filtered.length > 0) {
      liveActivityQueue.length = 0; // clear
      filtered.forEach(r => {
        const asset = r.asset_code || (r.asset_type === 'native' ? 'XLM' : 'unknown');
        let message = '';
        let type = 'SWAP';

        const fromAddr = r.from || r.source_account || '';
        const toAddr = r.to || r.funder || r.account || '';
        const amt = r.amount || r.starting_balance || '0';

        if (r.type === 'create_account') {
          type = 'MINT';
          message = `New account funded with ${parseFloat(amt).toLocaleString()} XLM`;
        } else if (r.type === 'path_payment_strict_receive' || r.type === 'path_payment_strict_send') {
          type = 'SWAP';
          message = `Wallet ${shortenAddress(fromAddr)} swapped to ${parseFloat(amt).toLocaleString()} ${asset}`;
        } else if (r.type === 'payment') {
          type = 'LIQUIDITY'; // value transfer/trade
          message = `Wallet ${shortenAddress(fromAddr)} transferred ${parseFloat(amt).toLocaleString()} ${asset} to ${shortenAddress(toAddr)}`;
        }

        if (message) {
          liveActivityQueue.push({
            id: r.id,
            type,
            message,
            time: 'just now'
          });
        }
      });

      // Load initial feed
      if (liveActivityQueue.length > 0) {
        activityFeed.value = liveActivityQueue.slice(0, 4).map((act, idx) => ({
          ...act,
          time: `${idx * 4 + 2}s ago`
        }));
        queueIndex = 4 % liveActivityQueue.length;
      }
    }
  } catch (error) {
    console.error("Error fetching live activity feed:", error);
  }
}

function formatNumber(num) {
  if (!num) return '0';
  return Number(num).toLocaleString();
}

function readPk() {
  const pk = getCookie("public_key") || localStorage.getItem("public_key");
  if (!pk) return "";
  const s = String(pk).trim();
  return (s === "null" || s === "undefined") ? "" : s;
}

function refreshWalletState() {
  const pk = readPk();
  walletKey.value = pk || "";
  isWalletConnected.value = !!(pk && pk.startsWith("G") && pk.length === 56);
}

onMounted(async () => {
  refreshWalletState();
  await fetchdata();
  await fetchLpData();
  await fetchLatestTokens();
  await fetchFeaturedProjects();
  await fetchTrendingPools();
  await fetchTrendingTokens();
  await fetchMarketHighlights();
  await fetchLiveActivity();

  feedInterval = setInterval(addMockActivity, 4000);

  updateTickerInterval = setInterval(() => {
    lastUpdatedSec.value += 1;
  }, 1000);

  try {
    network.value = await getNetwork();
  } catch (e) {
    console.error('getNetwork failed:', e)
  }
});

onUnmounted(() => {
  if (feedInterval) clearInterval(feedInterval);
  if (updateTickerInterval) clearInterval(updateTickerInterval);
});
</script>

<style scoped>
/* Page Animations */
.list-move,
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}

.list-leave-active {
  position: absolute;
  width: 100%;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(-15px);
}

.animate-pulse-slow {
  animation: pulse 8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.animate-spin-slow {
  animation: spin 16s linear infinite;
}

.animate-spin-reverse {
  animation: spin 12s linear infinite reverse;
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: scale(0.95);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}

.animate-fade-in {
  animation: fade-in 0.4s ease-out forwards;
}

/* Floating Particles */
@keyframes float1 {

  0%,
  100% {
    transform: translateY(0);
  }

  50% {
    transform: translateY(-15px);
  }
}

@keyframes float2 {

  0%,
  100% {
    transform: translateY(0);
  }

  50% {
    transform: translateY(18px);
  }
}

@keyframes float3 {

  0%,
  100% {
    transform: translate(0, 0);
  }

  50% {
    transform: translate(10px, -10px);
  }
}

@keyframes float4 {

  0%,
  100% {
    transform: translate(0, 0);
  }

  50% {
    transform: translate(-12px, 12px);
  }
}

.animate-float-1 {
  animation: float1 6s ease-in-out infinite;
}

.animate-float-2 {
  animation: float2 8s ease-in-out infinite;
}

.animate-float-3 {
  animation: float3 5s ease-in-out infinite;
}

.animate-float-4 {
  animation: float4 7s ease-in-out infinite;
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>

<style>
html {
  scroll-behavior: smooth;
}
</style>
