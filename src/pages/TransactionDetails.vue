<template>
  <div class="tx-page-wrapper">
    <Header />

    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-16 space-y-6">

      <!-- LOADING STATE SKELETON -->
      <div v-if="loading" class="space-y-6">
        <!-- Hero Skeleton -->
        <div class="card p-6 sm:p-8 space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-24 h-6 bg-theme-line animate-pulse rounded-full"></div>
            <div class="w-32 h-6 bg-theme-line animate-pulse rounded-full"></div>
          </div>
          <div class="w-full max-w-xl h-8 bg-theme-line animate-pulse rounded-lg"></div>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4">
            <div v-for="i in 4" :key="i" class="h-16 bg-theme-panel2 border border-theme-line rounded-xl animate-pulse"></div>
          </div>
        </div>

        <!-- Operations Skeleton -->
        <div class="card p-6 space-y-4">
          <div class="w-48 h-6 bg-theme-line animate-pulse rounded"></div>
          <div v-for="i in 3" :key="i" class="h-24 bg-theme-panel2 border border-theme-line rounded-xl animate-pulse"></div>
        </div>
      </div>

      <!-- ERROR / NOT FOUND STATE -->
      <div v-else-if="error || !txData" class="card p-12 text-center space-y-4 max-w-xl mx-auto my-12">
        <div class="w-14 h-14 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">
          ✕
        </div>
        <h2 class="text-xl font-bold text-theme-ink font-disp">Transaction Not Found</h2>
        <p class="text-xs text-theme-dim max-w-md mx-auto leading-relaxed">
          {{ error || 'Unable to retrieve transaction details for this hash. The transaction may not exist on the public Stellar ledger or was dropped.' }}
        </p>
        <div class="pt-4 flex items-center justify-center gap-3">
          <button @click="fetchTransaction" class="px-5 py-2 rounded-lg bg-theme-panel2 border border-theme-line text-xs font-bold text-theme-ink hover:border-theme-line2 transition">
            Retry
          </button>
          <router-link to="/" class="px-5 py-2 rounded-lg bg-cyan-500/10 border border-cyan-500/20 text-xs font-bold text-cyan-400 hover:bg-cyan-500/20 transition">
            Back to Home
          </router-link>
        </div>
      </div>

      <!-- LOADED TRANSACTION CONTENT -->
      <div v-else class="space-y-6">

        <!-- HERO BANNER: Status, Hash & Quick Stats -->
        <section class="card p-6 sm:p-8 relative overflow-hidden">
          <!-- Background glow -->
          <div class="absolute -top-24 -right-24 w-80 h-80 bg-cyan-500/5 rounded-full blur-3xl pointer-events-none"></div>

          <div class="space-y-6 relative z-10">
            <!-- Badges Bar -->
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div class="flex flex-wrap items-center gap-2.5">
                <!-- Status Badge -->
                <span v-if="txData.successful" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                  <CheckCircle2 class="w-3.5 h-3.5" />
                  Successful
                </span>
                <span v-else class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                  <XCircle class="w-3.5 h-3.5" />
                  Failed
                </span>

                <!-- Network Badge -->
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-mono font-bold uppercase tracking-wider bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20">
                  <div class="w-2.5 h-2.5"><XlmLogo /></div>
                  Stellar Public
                </span>

                <!-- Time Ago -->
                <span class="text-xs text-theme-dim font-mono">
                  {{ formatRelativeTime(txData.created_at) }}
                </span>
              </div>

              <!-- Timestamp -->
              <div class="text-xs font-mono text-theme-faint">
                {{ formatFullDate(txData.created_at) }}
              </div>
            </div>

            <!-- Hash Title and Action Buttons -->
            <div class="space-y-2">
              <div class="text-xs font-semibold text-slate-400 font-sans">Transaction Hash</div>
              <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                <h1 class="text-xs sm:text-sm md:text-base font-mono font-bold text-theme-ink break-all select-all flex-1 min-w-0" :title="txData.hash">
                  {{ txData.hash }}
                </h1>
                <div class="flex items-center gap-2 flex-shrink-0">
                  <button @click="copyText(txData.hash, 'hash')" 
                          class="px-3 py-1.5 bg-theme-panel2 border border-theme-line hover:border-theme-line2 rounded-lg text-xs font-sans font-medium text-theme-ink flex items-center gap-1.5 transition cursor-pointer shadow-sm">
                    <Check v-if="copiedField === 'hash'" class="w-3.5 h-3.5 text-emerald-400" />
                    <Copy v-else class="w-3.5 h-3.5 text-theme-dim" />
                    <span>{{ copiedField === 'hash' ? 'Copied' : 'Copy Hash' }}</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Key Info Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 font-sans">
              <!-- Ledger -->
              <div class="p-3.5 bg-theme-panel2 border border-theme-line rounded-xl space-y-1">
                <div class="text-xs font-semibold text-slate-400">Ledger</div>
                <div class="text-sm font-bold text-cyan-600 dark:text-cyan-400 font-mono">
                  {{ txData.ledger ? '#' + txData.ledger : '--' }}
                </div>
              </div>

              <!-- Operations Count -->
              <div class="p-3.5 bg-theme-panel2 border border-theme-line rounded-xl space-y-1">
                <div class="text-xs font-semibold text-slate-400">Operations</div>
                <div class="text-sm font-bold text-theme-ink font-mono">
                  {{ txData.operation_count ?? txData.operations?.length ?? 0 }}
                </div>
              </div>

              <!-- Fee Charged -->
              <div class="p-3.5 bg-theme-panel2 border border-theme-line rounded-xl space-y-1">
                <div class="text-xs font-semibold text-slate-400">Fee Charged</div>
                <div class="text-sm font-bold text-theme-ink font-mono" :title="`${txData.fee_charged_stroops} Stroops`">
                  {{ txData.fee_charged_xlm }} XLM
                </div>
              </div>

              <!-- Transaction Size -->
              <div class="p-3.5 bg-theme-panel2 border border-theme-line rounded-xl space-y-1">
                <div class="text-xs font-semibold text-slate-400">Size</div>
                <div class="text-sm font-bold text-theme-ink font-mono">
                  {{ txData.tx_size_bytes ? txData.tx_size_bytes + ' B' : '--' }}
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- TRANSACTION DETAILS & SUMMARY -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- Left Column: Operations & Effects (2 Cols) -->
          <div class="lg:col-span-2 space-y-6">

            <!-- SECTION: OPERATIONS LIST -->
            <section class="card">
              <!-- Card Header -->
              <div class="card-hd border-b border-theme-line pb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <h3 class="font-sans font-semibold text-sm text-theme-ink">Operations</h3>
                  <span class="px-2 py-0.5 rounded-full text-xs font-sans font-medium bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20">
                    {{ txData.operations?.length ?? 0 }}
                  </span>
                </div>
                <div class="text-xs font-sans text-theme-dim">Executed in order</div>
              </div>

              <!-- Operations List -->
              <div class="divide-y divide-theme-line">
                <div v-for="(op, idx) in visibleOperations" :key="op.id || idx" class="p-4 sm:p-5 hover:bg-theme-panel2/40 transition-colors space-y-3">
                  <!-- Op Header -->
                  <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                      <span class="w-5 h-5 rounded bg-theme-panel2 border border-theme-line flex items-center justify-center text-[10px] font-mono font-bold text-theme-dim">
                        #{{ idx + 1 }}
                      </span>
                      <span class="text-xs font-bold text-theme-ink uppercase font-sans">
                        {{ formatOpName(op.type) }}
                      </span>
                    </div>
                    <span class="text-[10px] font-mono text-theme-faint">ID: {{ op.id }}</span>
                  </div>

                  <!-- OP TYPE DETAILS -->
                  <!-- 1. PAYMENT -->
                  <div v-if="op.type === 'payment' || op.type === 'path_payment_strict_send' || op.type === 'path_payment_strict_receive'" class="space-y-2.5 bg-theme-panel2/70 p-3.5 rounded-xl border border-theme-line text-xs font-mono">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                      <div class="flex items-center gap-2">
                        <img v-if="op.token_meta?.logo" :src="op.token_meta.logo" class="w-5 h-5 rounded-full object-cover border border-theme-line" />
                        <div v-else-if="!op.asset_code" class="w-5 h-5 flex items-center justify-center"><XlmLogo /></div>
                        <div v-else class="w-5 h-5 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-[9px] font-bold text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                          {{ op.asset_code.slice(0, 2) }}
                        </div>
                        <span class="text-xs font-semibold text-slate-400 font-sans">Amount:</span>
                        <span class="text-sm font-extrabold text-theme-ink font-mono">{{ formatNumber(op.amount, 7) }}</span>
                        <router-link v-if="op.asset_code && op.asset_issuer" 
                                     :to="{ path: '/token-insight', query: { asset_code: op.asset_code, issuer: op.asset_issuer } }" 
                                     class="text-cyan-600 dark:text-cyan-400 hover:underline font-bold uppercase">
                          {{ op.asset_code }}
                        </router-link>
                        <span v-else class="text-cyan-600 dark:text-cyan-400 font-bold uppercase">XLM</span>
                      </div>
                      <div v-if="op.token_meta?.usd_price && parseFloat(op.amount)" class="text-theme-faint text-[11px] font-mono">
                        ≈ ${{ formatNumber(parseFloat(op.amount) * op.token_meta.usd_price, 2) }}
                      </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1 border-t border-theme-line/60 text-xs font-sans">
                      <div>
                        <span class="text-theme-dim">From:</span>
                        <router-link :to="`/wallet/${op.from || op.source_account}`" class="text-cyan-600 dark:text-cyan-400 hover:underline ml-1 font-mono font-medium">
                          {{ shortenAddress(op.from || op.source_account) }}
                        </router-link>
                      </div>
                      <div>
                        <span class="text-theme-dim">To:</span>
                        <router-link :to="`/wallet/${op.to}`" class="text-cyan-600 dark:text-cyan-400 hover:underline ml-1 font-mono font-medium">
                          {{ shortenAddress(op.to) }}
                        </router-link>
                      </div>
                    </div>
                  </div>

                  <!-- 2. CREATE CLAIMABLE BALANCE -->
                  <div v-else-if="op.type === 'create_claimable_balance'" class="space-y-2 bg-theme-panel2/70 p-3.5 rounded-xl border border-theme-line text-xs font-mono">
                    <div class="flex items-center gap-2">
                      <span class="text-xs font-semibold text-slate-400 font-sans">Locked:</span>
                      <span class="text-sm font-extrabold text-amber-500 font-mono">{{ formatNumber(op.amount, 7) }}</span>
                      <span class="text-theme-ink font-bold">{{ op.asset?.split(':')[0] || op.asset_code || 'XLM' }}</span>
                    </div>
                    <div class="space-y-1 text-xs pt-1 border-t border-theme-line/60 font-sans" v-if="op.claimants?.length">
                      <span class="text-theme-dim block">Eligible Claimants:</span>
                      <div v-for="(c, cIdx) in op.claimants" :key="cIdx" class="flex items-center gap-2 font-mono">
                        <router-link :to="`/wallet/${c.destination}`" class="text-cyan-600 dark:text-cyan-400 hover:underline">
                          {{ shortenAddress(c.destination) }}
                        </router-link>
                        <span class="text-theme-dim text-[11px] font-sans">Predicate: {{ formatPredicate(c.predicate) }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- 3. CLAIM CLAIMABLE BALANCE -->
                  <div v-else-if="op.type === 'claim_claimable_balance'" class="space-y-2 bg-theme-panel2/70 p-3.5 rounded-xl border border-theme-line text-xs font-mono">
                    <div>
                      <span class="text-theme-dim font-sans">Balance ID:</span>
                      <span class="text-theme-ink font-semibold ml-1 truncate block font-mono">{{ op.balance_id }}</span>
                    </div>
                    <div v-if="op.claimant">
                      <span class="text-theme-dim font-sans">Claimant:</span>
                      <router-link :to="`/wallet/${op.claimant}`" class="text-cyan-600 dark:text-cyan-400 hover:underline ml-1 font-mono font-medium">
                        {{ shortenAddress(op.claimant) }}
                      </router-link>
                    </div>
                  </div>

                  <!-- 4. CHANGE TRUST -->
                  <div v-else-if="op.type === 'change_trust'" class="space-y-2 bg-theme-panel2/70 p-3.5 rounded-xl border border-theme-line text-xs font-mono">
                    <div class="flex items-center justify-between gap-2">
                      <div class="flex items-center gap-1.5">
                        <span class="text-theme-dim font-sans">Asset:</span>
                        <router-link v-if="op.asset_code && op.asset_issuer" 
                                     :to="{ path: '/token-insight', query: { asset_code: op.asset_code, issuer: op.asset_issuer } }" 
                                     class="text-cyan-600 dark:text-cyan-400 hover:underline font-bold uppercase">
                          {{ op.asset_code }}
                        </router-link>
                        <span v-else class="text-theme-ink font-bold">{{ op.asset_code || 'LP' }}</span>
                      </div>
                      <div class="text-theme-dim text-xs font-sans">
                        Limit: <span class="text-theme-ink font-mono font-semibold">{{ op.limit ? formatNumber(op.limit, 2) : 'No Limit' }}</span>
                      </div>
                    </div>
                    <div v-if="op.asset_issuer" class="text-xs text-theme-dim font-sans">
                      Issuer: 
                      <router-link :to="`/wallet/${op.asset_issuer}`" class="text-theme-dim hover:text-cyan-400 ml-1 font-mono">
                        {{ shortenAddress(op.asset_issuer) }}
                      </router-link>
                    </div>
                  </div>

                  <!-- 5. CREATE ACCOUNT -->
                  <div v-else-if="op.type === 'create_account'" class="space-y-2 bg-theme-panel2/70 p-3.5 rounded-xl border border-theme-line text-xs font-mono">
                    <div class="flex items-center gap-2">
                      <span class="text-xs font-semibold text-slate-400 font-sans">Starting Balance:</span>
                      <span class="text-sm font-extrabold text-emerald-400 font-mono">{{ formatNumber(op.starting_balance, 2) }} XLM</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1 border-t border-theme-line/60 text-xs font-sans">
                      <div>
                        <span class="text-theme-dim">Funder:</span>
                        <router-link :to="`/wallet/${op.funder}`" class="text-cyan-600 dark:text-cyan-400 hover:underline ml-1 font-mono font-medium">
                          {{ shortenAddress(op.funder) }}
                        </router-link>
                      </div>
                      <div>
                        <span class="text-theme-dim">Account Created:</span>
                        <router-link :to="`/wallet/${op.account}`" class="text-cyan-600 dark:text-cyan-400 hover:underline ml-1 font-mono font-medium">
                          {{ shortenAddress(op.account) }}
                        </router-link>
                      </div>
                    </div>
                  </div>

                  <!-- 6. MANAGE DEX OFFER -->
                  <div v-else-if="op.type === 'manage_buy_offer' || op.type === 'manage_sell_offer' || op.type === 'create_passive_sell_offer'" class="space-y-2 bg-theme-panel2/70 p-3.5 rounded-xl border border-theme-line text-xs font-mono">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                      <div>
                        <span class="text-theme-faint">Amount:</span>
                        <span class="text-theme-ink font-bold ml-1">{{ formatNumber(op.amount, 4) }} {{ op.buying_asset_code || op.selling_asset_code || 'XLM' }}</span>
                      </div>
                      <div>
                        <span class="text-theme-faint">Price:</span>
                        <span class="text-theme-ink font-bold ml-1">{{ op.price }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- 7. SMART CONTRACT / SOROBAN -->
                  <div v-else-if="op.type === 'invoke_host_function'" class="space-y-2 bg-theme-panel2/70 p-3.5 rounded-xl border border-theme-line text-xs font-mono">
                    <div class="flex items-center gap-2">
                      <span class="px-2 py-0.5 rounded text-[10px] bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 uppercase font-bold">Soroban Host Call</span>
                      <span class="text-theme-ink font-semibold" v-if="op.function">{{ op.function }}</span>
                    </div>
                  </div>

                  <!-- 8. GENERIC FALLBACK -->
                  <div v-else class="space-y-1.5 bg-theme-panel2/70 p-3.5 rounded-xl border border-theme-line text-xs font-mono">
                    <div class="text-theme-dim" v-if="op.source_account">
                      Source: <router-link :to="`/wallet/${op.source_account}`" class="text-cyan-600 dark:text-cyan-400 hover:underline">{{ shortenAddress(op.source_account) }}</router-link>
                    </div>
                    <div v-if="op.amount" class="text-theme-ink">
                      Amount: <span class="font-bold">{{ op.amount }}</span> {{ op.asset_code || 'XLM' }}
                    </div>
                  </div>
                </div>

                <!-- Empty operations -->
                <div v-if="!txData.operations?.length" class="p-8 text-center text-xs text-theme-dim">
                  No operation records found for this transaction.
                </div>
              </div>

              <!-- LOAD MORE OPERATIONS (5 PER CLICK WITH DOWN BUTTON) -->
              <div v-if="hasMoreOperations" class="p-4 text-center border-t border-theme-line bg-theme-panel2/50">
                <button @click="loadMoreOperations" 
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-theme-panel border border-theme-line hover:border-theme-line2 text-xs font-mono font-bold text-cyan-600 dark:text-cyan-400 hover:text-cyan-500 rounded-xl transition cursor-pointer shadow-sm group">
                  <span>Show More Operations (+{{ Math.min(5, remainingOpsCount) }})</span>
                  <ChevronDown class="w-4 h-4 transition-transform group-hover:translate-y-0.5" />
                </button>
                <div class="mt-2 text-[10px] font-mono text-theme-faint">
                  Showing {{ visibleOperations.length }} of {{ txData.operations?.length }} operations
                </div>
              </div>
            </section>

            <!-- SECTION: STATE CHANGES & EFFECTS -->
            <section class="card">
              <div class="card-hd border-b border-theme-line pb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <h3 class="font-sans font-semibold text-sm text-theme-ink">State Changes & Effects</h3>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-theme-panel2 text-theme-dim border border-theme-line">
                    {{ txData.effects?.length ?? 0 }}
                  </span>
                </div>
              </div>

              <div class="p-4 sm:p-5">
                <div v-if="txData.effects?.length" class="divide-y divide-theme-line">
                  <div v-for="(eff, eIdx) in visibleEffects" :key="eff.id || eIdx" class="py-2.5 flex items-center justify-between text-xs font-mono">
                    <div class="flex items-center gap-2.5 min-w-0">
                      <span class="w-2 h-2 rounded-full flex-shrink-0" 
                            :class="eff.type.includes('credited') ? 'bg-emerald-400' : (eff.type.includes('debited') ? 'bg-rose-400' : 'bg-cyan-400')"></span>
                      <span class="font-bold text-theme-ink uppercase text-[11px]">{{ formatOpName(eff.type) }}</span>
                      <router-link v-if="eff.account" :to="`/wallet/${eff.account}`" class="text-theme-dim hover:text-cyan-400 truncate max-w-[150px] sm:max-w-[220px]">
                        {{ shortenAddress(eff.account) }}
                      </router-link>
                    </div>
                    <div v-if="eff.amount" class="font-bold font-mono text-right flex-shrink-0"
                         :class="eff.type.includes('credited') ? 'text-emerald-500 dark:text-emerald-400' : (eff.type.includes('debited') ? 'text-rose-500 dark:text-rose-400' : 'text-theme-ink')">
                      {{ eff.type.includes('credited') ? '+' : (eff.type.includes('debited') ? '-' : '') }}{{ formatNumber(eff.amount, 4) }} {{ eff.asset_code || 'XLM' }}
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-6 text-xs text-theme-dim font-mono">
                  No effects recorded for this transaction.
                </div>
              </div>

              <!-- LOAD MORE EFFECTS (5 PER CLICK WITH DOWN BUTTON) -->
              <div v-if="hasMoreEffects" class="p-4 text-center border-t border-theme-line bg-theme-panel2/50">
                <button @click="loadMoreEffects" 
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-theme-panel border border-theme-line hover:border-theme-line2 text-xs font-mono font-bold text-cyan-600 dark:text-cyan-400 hover:text-cyan-500 rounded-xl transition cursor-pointer shadow-sm group">
                  <span>Show More Effects (+{{ Math.min(5, remainingEffectsCount) }})</span>
                  <ChevronDown class="w-4 h-4 transition-transform group-hover:translate-y-0.5" />
                </button>
                <div class="mt-2 text-[10px] font-mono text-theme-faint">
                  Showing {{ visibleEffects.length }} of {{ txData.effects?.length }} effects
                </div>
              </div>
            </section>

          </div>

          <!-- Right Column: Technical Details, Signatures & XDR (1 Col) -->
          <div class="space-y-6">

            <!-- CARD: TRANSACTION METADATA -->
            <section class="card">
              <div class="card-hd border-b border-theme-line pb-3">
                <h3 class="font-sans font-semibold text-sm text-theme-ink">Details</h3>
              </div>

              <div class="p-5 space-y-4 text-xs font-sans">
                <!-- Source Account -->
                <div class="space-y-1">
                  <div class="text-xs font-semibold text-slate-400">Source Account</div>
                  <div class="flex items-center justify-between gap-2">
                    <router-link :to="`/wallet/${txData.source_account}`" class="text-cyan-600 dark:text-cyan-400 hover:underline font-mono font-semibold truncate" :title="txData.source_account">
                      {{ shortenAddress(txData.source_account) }}
                    </router-link>
                    <button @click="copyText(txData.source_account, 'source')" class="p-1 text-theme-dim hover:text-theme-ink transition">
                      <Check v-if="copiedField === 'source'" class="w-3 h-3 text-emerald-400" />
                      <Copy v-else class="w-3 h-3" />
                    </button>
                  </div>
                </div>

                <!-- Fee Account -->
                <div class="space-y-1 pt-2 border-t border-theme-line" v-if="txData.fee_account && txData.fee_account !== txData.source_account">
                  <div class="text-xs font-semibold text-slate-400">Fee Account</div>
                  <router-link :to="`/wallet/${txData.fee_account}`" class="text-cyan-600 dark:text-cyan-400 hover:underline font-mono font-semibold block truncate">
                    {{ shortenAddress(txData.fee_account) }}
                  </router-link>
                </div>

                <!-- Sequence Number -->
                <div class="space-y-1 pt-2 border-t border-theme-line">
                  <div class="text-xs font-semibold text-slate-400">Sequence Number</div>
                  <div class="text-theme-ink font-semibold font-mono select-all">{{ txData.source_account_sequence }}</div>
                </div>

                <!-- Max Fee -->
                <div class="space-y-1 pt-2 border-t border-theme-line">
                  <div class="text-xs font-semibold text-slate-400">Max Fee / Bid</div>
                  <div class="text-theme-ink font-semibold font-mono">{{ txData.max_fee_xlm }} XLM <span class="text-theme-faint text-[10px]">({{ txData.max_fee_stroops }} Stroops)</span></div>
                </div>

                <!-- Memo -->
                <div class="space-y-1 pt-2 border-t border-theme-line">
                  <div class="flex items-center justify-between">
                    <div class="text-xs font-semibold text-slate-400">Memo</div>
                    <span class="text-[10px] px-2 py-0.5 bg-theme-panel2 text-theme-dim border border-theme-line rounded font-medium">
                      {{ txData.memo_type }}
                    </span>
                  </div>
                  <div class="text-theme-ink font-semibold font-mono select-all break-all">
                    {{ txData.memo || (txData.memo_type === 'none' ? 'None' : '--') }}
                  </div>
                </div>

                <!-- Preconditions -->
                <div class="space-y-1 pt-2 border-t border-theme-line" v-if="txData.preconditions">
                  <div class="text-xs font-semibold text-slate-400">Preconditions</div>
                  <div v-if="txData.preconditions.timebounds" class="text-xs text-theme-dim space-y-0.5 font-sans">
                    <div v-if="txData.preconditions.timebounds.max_time">
                      Valid before: <span class="text-theme-ink font-semibold font-mono">{{ formatTimestamp(txData.preconditions.timebounds.max_time) }}</span>
                    </div>
                    <div v-if="txData.preconditions.timebounds.min_time && txData.preconditions.timebounds.min_time !== '0'">
                      Valid after: <span class="text-theme-ink font-semibold font-mono">{{ formatTimestamp(txData.preconditions.timebounds.min_time) }}</span>
                    </div>
                  </div>
                  <div v-else class="text-xs text-theme-dim font-sans">No specific conditions</div>
                </div>
              </div>
            </section>

            <!-- CARD: SIGNATURES -->
            <section class="card">
              <div class="card-hd border-b border-theme-line pb-3 flex items-center justify-between">
                <h3 class="font-sans font-semibold text-sm text-theme-ink">Signatures</h3>
                <span class="text-[10px] font-mono text-theme-dim">{{ txData.signatures?.length ?? 0 }}</span>
              </div>

              <div class="p-4 space-y-2 text-xs font-mono">
                <div v-for="(sig, sIdx) in txData.signatures" :key="sIdx" 
                     class="p-2.5 bg-theme-panel2 border border-theme-line rounded-lg flex items-center justify-between gap-2">
                  <span class="text-theme-ink break-all text-[11px] select-all">{{ sig }}</span>
                  <button @click="copyText(sig, `sig_${sIdx}`)" class="p-1 text-theme-dim hover:text-theme-ink flex-shrink-0">
                    <Check v-if="copiedField === `sig_${sIdx}`" class="w-3 h-3 text-emerald-400" />
                    <Copy v-else class="w-3 h-3" />
                  </button>
                </div>
              </div>
            </section>

            <!-- CARD: RAW XDR TOOLS -->
            <section class="card">
              <div class="card-hd border-b border-theme-line pb-3 flex items-center justify-between">
                <h3 class="font-sans font-semibold text-sm text-theme-ink">Raw XDR</h3>
                <!-- XDR Tabs -->
                <div class="flex items-center gap-1">
                  <button @click="activeXdrTab = 'envelope'" 
                          class="px-2 py-0.5 text-[10px] font-mono font-bold uppercase rounded border transition"
                          :class="activeXdrTab === 'envelope' ? 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30' : 'bg-transparent border-transparent text-theme-dim hover:text-theme-ink'">
                    Envelope
                  </button>
                  <button @click="activeXdrTab = 'result'" 
                          class="px-2 py-0.5 text-[10px] font-mono font-bold uppercase rounded border transition"
                          :class="activeXdrTab === 'result' ? 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30' : 'bg-transparent border-transparent text-theme-dim hover:text-theme-ink'">
                    Result
                  </button>
                </div>
              </div>

              <div class="p-4 space-y-3">
                <div class="relative">
                  <textarea readonly 
                            :value="currentXdrContent" 
                            rows="4" 
                            class="w-full bg-theme-panel2 border border-theme-line rounded-xl p-3 text-[11px] font-mono text-theme-dim focus:outline-none resize-none select-all custom-scrollbar"></textarea>
                </div>
                <button @click="copyText(currentXdrContent, 'xdr')" 
                        class="w-full py-2 bg-theme-panel2 border border-theme-line hover:border-theme-line2 rounded-xl text-xs font-mono font-bold text-cyan-600 dark:text-cyan-400 flex items-center justify-center gap-2 transition cursor-pointer">
                  <Check v-if="copiedField === 'xdr'" class="w-3.5 h-3.5 text-emerald-400" />
                  <Copy v-else class="w-3.5 h-3.5" />
                  <span>{{ copiedField === 'xdr' ? 'Copied XDR' : 'Copy ' + activeXdrTab.toUpperCase() + ' XDR' }}</span>
                </button>
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
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';
import XlmLogo from '@/components/icons/XlmLogo.vue';
import { Copy, Check, CheckCircle2, XCircle, ChevronDown } from 'lucide-vue-next';

const route = useRoute();
const hash = computed(() => route.params.hash);

const txData = ref(null);
const loading = ref(true);
const error = ref(null);
const copiedField = ref(null);
const activeXdrTab = ref('envelope');
const displayedOpsCount = ref(5);
const displayedEffectsCount = ref(5);

const visibleOperations = computed(() => {
  if (!txData.value?.operations) return [];
  return txData.value.operations.slice(0, displayedOpsCount.value);
});

const hasMoreOperations = computed(() => {
  const total = txData.value?.operations?.length || 0;
  return total > displayedOpsCount.value;
});

const remainingOpsCount = computed(() => {
  const total = txData.value?.operations?.length || 0;
  return Math.max(0, total - displayedOpsCount.value);
});

function loadMoreOperations() {
  displayedOpsCount.value += 5;
}

const visibleEffects = computed(() => {
  if (!txData.value?.effects) return [];
  return txData.value.effects.slice(0, displayedEffectsCount.value);
});

const hasMoreEffects = computed(() => {
  const total = txData.value?.effects?.length || 0;
  return total > displayedEffectsCount.value;
});

const remainingEffectsCount = computed(() => {
  const total = txData.value?.effects?.length || 0;
  return Math.max(0, total - displayedEffectsCount.value);
});

function loadMoreEffects() {
  displayedEffectsCount.value += 5;
}

const currentXdrContent = computed(() => {
  if (!txData.value) return '';
  return activeXdrTab.value === 'envelope' 
    ? (txData.value.envelope_xdr || '') 
    : (txData.value.result_xdr || '');
});

async function fetchTransaction() {
  if (!hash.value) return;
  loading.value = true;
  error.value = null;
  displayedOpsCount.value = 5;
  displayedEffectsCount.value = 5;

  try {
    const res = await axios.get(`/api/tx/${hash.value}`);
    if (res.data && res.data.status === 'success') {
      txData.value = res.data.data;
      document.title = `Tx ${shortenAddress(hash.value)} | TokenGlade`;
    } else {
      error.value = res.data?.message || 'Failed to load transaction.';
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Transaction could not be found on Horizon.';
  } finally {
    loading.value = false;
  }
}

function shortenAddress(str) {
  if (!str) return '';
  if (str.length <= 12) return str;
  return `${str.slice(0, 6)}...${str.slice(-6)}`;
}

function formatNumber(val, decimals = 2) {
  if (val === null || val === undefined || isNaN(val)) return '0';
  const num = parseFloat(val);
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: decimals,
  }).format(num);
}

function formatRelativeTime(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const now = new Date();
  const diffSec = Math.floor((now - date) / 1000);

  if (diffSec < 60) return `${diffSec}s ago`;
  const diffMin = Math.floor(diffSec / 60);
  if (diffMin < 60) return `${diffMin}m ago`;
  const diffHours = Math.floor(diffMin / 60);
  if (diffHours < 24) return `${diffHours}h ago`;
  const diffDays = Math.floor(diffHours / 24);
  return `${diffDays}d ago`;
}

function formatFullDate(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toUTCString();
}

function formatTimestamp(unixTimeStr) {
  if (!unixTimeStr || unixTimeStr === '0') return 'No limit';
  const timeSec = parseInt(unixTimeStr, 10);
  if (isNaN(timeSec)) return unixTimeStr;
  const d = new Date(timeSec * 1000);
  return d.toUTCString();
}

function formatOpName(type) {
  if (!type) return 'Operation';
  return type.replace(/_/g, ' ');
}

function formatPredicate(pred) {
  if (!pred) return 'Unconditional';
  if (pred.unconditional) return 'Unconditional';
  if (pred.rel_before) return `Before ${pred.rel_before}s`;
  if (pred.abs_before) {
    const d = new Date(pred.abs_before);
    return `Before ${d.toLocaleDateString()}`;
  }
  return JSON.stringify(pred);
}

async function copyText(text, fieldKey) {
  if (!text) return;
  try {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(text);
    } else {
      const textarea = document.createElement('textarea');
      textarea.value = text;
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand('copy');
      document.body.removeChild(textarea);
    }
    copiedField.value = fieldKey;
    setTimeout(() => {
      if (copiedField.value === fieldKey) copiedField.value = null;
    }, 2000);
  } catch (e) {
    console.error('Failed to copy text', e);
  }
}

watch(hash, () => {
  fetchTransaction();
});

onMounted(() => {
  fetchTransaction();
});
</script>

<style scoped>
.tx-page-wrapper {
  --mono: var(--font-mono, "JetBrains Mono", ui-monospace, monospace);
  --disp: var(--font-disp, "Space Grotesk", sans-serif);
  --body: var(--font-sans, "Inter", sans-serif);

  background: var(--bg);
  color: var(--ink);
  font-family: var(--body);
  font-size: 14px;
  line-height: 1.45;
  min-height: 100vh;
  background-image: radial-gradient(900px 460px at 84% -12%, rgba(18, 203, 238, .07), transparent 62%), radial-gradient(760px 420px at 6% -8%, rgba(240, 24, 156, .05), transparent 60%);
}

html.light .tx-page-wrapper {
  background: var(--bg);
  color: var(--ink);
  background-image: radial-gradient(900px 460px at 84% -12%, rgba(18, 203, 238, .03), transparent 62%), radial-gradient(760px 420px at 6% -8%, rgba(240, 24, 156, .02), transparent 60%);
}

.card {
  background: var(--panel);
  border: 1px solid var(--line);
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.card-hd {
  padding: 16px 20px;
}
</style>
