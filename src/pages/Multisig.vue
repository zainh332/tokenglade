<template>
  <div class="bg-theme-bg min-h-screen text-theme-ink font-sans antialiased selection:bg-cyan-500/20 selection:text-white">
    <Header />

    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-10 pb-20 relative z-10">
      
      <!-- HERO / HEADER SECTION -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-8 border-b border-theme-line">
        <div>
          <div class="flex items-center gap-3">
            <h1 class="text-2xl sm:text-3xl font-black text-theme-ink tracking-tight font-display uppercase">
              Multisig <span class="text-cyan-500 dark:text-cyan-400">Vault</span>
            </h1>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider"
                  :class="isTestnet ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'">
              {{ networkName.toUpperCase() }}
            </span>
          </div>
          <p class="text-xs sm:text-sm text-theme-dim mt-1.5 max-w-2xl">
            Configure native Stellar multi-signature thresholds, manage authorized signers, coordinate co-signing workflows, and broadcast fully authorized transactions.
          </p>
        </div>

        <!-- Quick Action / Refresh (only shown when wallet is connected) -->
        <div v-if="connectedWalletKey" class="flex items-center gap-3">
          <button 
            @click="loadAccount(connectedWalletKey)" 
            :disabled="accountLoading"
            class="px-3.5 py-2 bg-theme-panel hover:bg-theme-panel2 border border-theme-line hover:border-cyan-500/40 rounded-xl text-xs font-mono text-cyan-400 flex items-center gap-2 transition select-none cursor-pointer disabled:opacity-50"
            title="Refresh Account Data"
          >
            <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': accountLoading }" />
            <span>Refresh</span>
          </button>
        </div>
      </div>

      <!-- MAIN CONTENT -->
      <!-- 1. NOT CONNECTED STATE (Don't show unless wallet is connected) -->
      <div v-if="!connectedWalletKey" class="py-24 text-center max-w-md mx-auto space-y-4">
        <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 flex items-center justify-center mx-auto text-3xl shadow-lg shadow-cyan-500/5">
          <Shield class="w-8 h-8" />
        </div>
        <h2 class="text-xl font-bold text-theme-ink font-display uppercase tracking-tight">Wallet Not Connected</h2>
        <p class="text-xs sm:text-sm text-theme-dim leading-relaxed">
          Please connect your Stellar wallet using the button in the top navigation header to configure multisig thresholds, manage signers, and sign transactions.
        </p>
      </div>

      <!-- 2. LOADING STATE -->
      <div v-else-if="accountLoading" class="py-24 text-center space-y-3">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-cyan-400 border-t-transparent mx-auto"></div>
        <p class="text-xs font-mono text-theme-dim">Querying Stellar Horizon ledger...</p>
      </div>

      <!-- 3. ERROR STATE -->
      <div v-else-if="accountError" class="py-16 text-center max-w-md mx-auto space-y-4">
        <div class="w-14 h-14 rounded-2xl bg-rose-500/10 text-rose-400 border border-rose-500/20 flex items-center justify-center mx-auto text-2xl">⚠️</div>
        <h3 class="text-lg font-bold text-theme-ink">Account Not Found</h3>
        <p class="text-xs text-theme-dim leading-relaxed">{{ accountError }}</p>
        <button 
          @click="loadAccount(connectedWalletKey)"
          class="px-4 py-2 bg-theme-panel2 hover:bg-theme-panel border border-theme-line rounded-xl text-xs font-bold text-cyan-400 transition cursor-pointer"
        >
          Retry My Wallet
        </button>
      </div>

      <div v-else-if="accountInfo" class="mt-8 space-y-8">

        <!-- ACCOUNT OVERVIEW CARD -->
        <div class="bg-theme-panel border border-theme-line rounded-2xl p-5 sm:p-6 shadow-xl relative overflow-hidden">
          <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            
            <div class="space-y-2 flex-1 min-w-0">
              <div class="flex items-center gap-2.5 flex-wrap">
                <span class="text-xs font-mono text-theme-faint">Account:</span>
                <span class="font-mono text-sm sm:text-base font-bold text-theme-ink select-all break-all">
                  {{ accountInfo.account_id }}
                </span>
                <button 
                  @click="copyText(accountInfo.account_id)" 
                  class="p-1 hover:text-cyan-400 text-theme-dim transition cursor-pointer"
                  title="Copy Account Address"
                >
                  <Copy class="w-3.5 h-3.5" />
                </button>
                <a 
                  :href="getExplorerAccountUrl(accountInfo.account_id)" 
                  target="_blank" 
                  rel="noopener"
                  class="p-1 hover:text-cyan-400 text-theme-dim transition"
                  title="View on StellarExpert Explorer"
                >
                  <ExternalLink class="w-3.5 h-3.5" />
                </a>
              </div>

              <div class="flex items-center gap-3 text-xs text-theme-dim flex-wrap">
                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold"
                      :class="accountInfo.is_multisig ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-slate-500/10 text-slate-400 border border-slate-500/20'">
                  {{ accountInfo.summary }}
                </span>
                <span>·</span>
                <span>Balance: <strong class="text-theme-ink font-mono">{{ formatNum(accountInfo.xlm_balance) }} XLM</strong></span>
                <span>·</span>
                <span>Available: <strong class="text-emerald-400 font-mono">{{ formatNum(accountInfo.available_xlm_balance) }} XLM</strong></span>
                <span>·</span>
                <span>Min Reserve: <span class="font-mono text-theme-faint">{{ formatNum(accountInfo.minimum_reserve_xlm) }} XLM ({{ accountInfo.subentry_count }} subentries)</span></span>
              </div>
            </div>

            <!-- Fast Action Buttons -->
            <div class="flex items-center gap-2.5 flex-wrap flex-shrink-0">
              <button 
                @click="openAddSignerModal()"
                class="px-3.5 py-2 bg-theme-panel2 hover:bg-theme-panel3 border border-theme-line hover:border-cyan-500/30 rounded-xl text-xs font-semibold text-theme-ink flex items-center gap-1.5 transition cursor-pointer"
              >
                <UserPlus class="w-3.5 h-3.5 text-cyan-400" />
                <span>Add Signer</span>
              </button>

              <button 
                @click="openNewProposalModal()"
                class="px-3.5 py-2 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold rounded-xl text-xs flex items-center gap-1.5 transition shadow-lg shadow-cyan-500/15 cursor-pointer"
              >
                <Send class="w-3.5 h-3.5" />
                <span>New Transaction</span>
              </button>
            </div>
          </div>

          <!-- Quick Threshold Metrics Bar -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-5 border-t border-theme-line/60">
            <div class="bg-theme-panel2/60 border border-theme-line/60 p-3 rounded-xl">
              <span class="text-[10px] font-mono uppercase tracking-wider text-theme-dim block">Master Weight</span>
              <div class="text-base font-mono font-bold text-theme-ink mt-0.5">
                {{ accountInfo.master_key_weight }}
                <span class="text-[10px] font-sans font-normal text-theme-dim">/ 255</span>
              </div>
              <span class="text-[10px] text-theme-faint block mt-0.5">Primary Key Power</span>
            </div>

            <div class="bg-theme-panel2/60 border border-theme-line/60 p-3 rounded-xl">
              <span class="text-[10px] font-mono uppercase tracking-wider text-theme-dim block">Low Threshold</span>
              <div class="text-base font-mono font-bold text-theme-ink mt-0.5">
                {{ accountInfo.thresholds.low }}
              </div>
              <span class="text-[10px] text-theme-faint block mt-0.5">Sequence bumps</span>
            </div>

            <div class="bg-theme-panel2/60 border border-theme-line/60 p-3 rounded-xl">
              <span class="text-[10px] font-mono uppercase tracking-wider text-theme-dim block">Medium Threshold</span>
              <div class="text-base font-mono font-bold text-cyan-400 mt-0.5">
                {{ accountInfo.thresholds.med }}
              </div>
              <span class="text-[10px] text-theme-faint block mt-0.5">Payments & Offers</span>
            </div>

            <div class="bg-theme-panel2/60 border border-theme-line/60 p-3 rounded-xl">
              <span class="text-[10px] font-mono uppercase tracking-wider text-theme-dim block">High Threshold</span>
              <div class="text-base font-mono font-bold text-purple-400 mt-0.5">
                {{ accountInfo.thresholds.high }}
              </div>
              <span class="text-[10px] text-theme-faint block mt-0.5">Signers & Thresholds</span>
            </div>
          </div>
        </div>

        <!-- TABS BAR -->
        <div class="flex items-center justify-between border-b border-theme-line">
          <div class="flex items-center gap-1 sm:gap-4 overflow-x-auto">
            <button 
              @click="activeTab = 'signers'"
              class="px-4 py-3 text-xs sm:text-sm font-bold tracking-wide transition border-b-2 flex items-center gap-2 cursor-pointer"
              :class="activeTab === 'signers' ? 'border-cyan-400 text-cyan-400' : 'border-transparent text-theme-dim hover:text-theme-ink'"
            >
              <Users class="w-4 h-4" />
              <span>Signers & Weights ({{ accountInfo.signers.length }})</span>
            </button>

            <button 
              @click="activeTab = 'thresholds'"
              class="px-4 py-3 text-xs sm:text-sm font-bold tracking-wide transition border-b-2 flex items-center gap-2 cursor-pointer"
              :class="activeTab === 'thresholds' ? 'border-cyan-400 text-cyan-400' : 'border-transparent text-theme-dim hover:text-theme-ink'"
            >
              <Sliders class="w-4 h-4" />
              <span>Threshold Settings</span>
            </button>

            <button 
              @click="activeTab = 'transactions'"
              class="px-4 py-3 text-xs sm:text-sm font-bold tracking-wide transition border-b-2 flex items-center gap-2 cursor-pointer"
              :class="activeTab === 'transactions' ? 'border-cyan-400 text-cyan-400' : 'border-transparent text-theme-dim hover:text-theme-ink'"
            >
              <FileSignature class="w-4 h-4" />
              <span>Multisig Transactions ({{ transactions.length }})</span>
            </button>
          </div>

          <button 
            @click="loadAccount(accountInfo.account_id)" 
            class="p-2 text-theme-dim hover:text-cyan-400 transition" 
            title="Refresh Account Data"
          >
            <RefreshCw class="w-4 h-4" />
          </button>
        </div>

        <!-- TAB 1: SIGNERS LIST -->
        <div v-if="activeTab === 'signers'" class="space-y-6">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-base font-bold text-theme-ink">Configured Account Signers</h2>
              <p class="text-xs text-theme-dim mt-0.5">
                Every signer contributes weight toward meeting operational thresholds. Adding each signer requires 0.5 XLM account reserve.
              </p>
            </div>
            <button 
              @click="openAddSignerModal()"
              class="px-3 py-1.5 bg-theme-panel2 hover:bg-theme-panel3 border border-theme-line rounded-lg text-xs font-semibold text-cyan-400 flex items-center gap-1.5 transition"
            >
              <Plus class="w-3.5 h-3.5" />
              <span>Add Signer</span>
            </button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
              v-for="signer in accountInfo.signers" 
              :key="signer.key"
              class="bg-theme-panel border border-theme-line rounded-2xl p-4 flex flex-col justify-between hover:border-theme-line2 transition"
            >
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-[10px] font-mono uppercase tracking-wider px-2 py-0.5 rounded"
                        :class="signer.is_master ? 'bg-cyan-500/10 text-cyan-400 font-bold border border-cyan-500/20' : 'bg-slate-500/10 text-theme-dim border border-theme-line'">
                    {{ signer.is_master ? 'Master Key' : signer.type }}
                  </span>
                  <div class="flex items-center gap-1">
                    <span class="text-[11px] font-mono text-theme-faint">Weight:</span>
                    <span class="text-xs font-mono font-black text-theme-ink bg-theme-panel2 px-2 py-0.5 rounded border border-theme-line">
                      {{ signer.weight }}
                    </span>
                  </div>
                </div>

                <div class="pt-1">
                  <div class="flex items-center gap-1.5 text-xs font-mono text-theme-ink font-semibold select-all break-all">
                    <span>{{ signer.key }}</span>
                  </div>
                  <div class="flex items-center gap-2 mt-1">
                    <button 
                      @click="copyText(signer.key)" 
                      class="text-[10px] text-theme-dim hover:text-cyan-400 transition flex items-center gap-1"
                    >
                      <Copy class="w-3 h-3" />
                      <span>Copy</span>
                    </button>
                    <a 
                      :href="getExplorerAccountUrl(signer.key)" 
                      target="_blank" 
                      class="text-[10px] text-theme-dim hover:text-cyan-400 transition flex items-center gap-1"
                    >
                      <ExternalLink class="w-3 h-3" />
                      <span>Explorer</span>
                    </a>
                  </div>
                </div>
              </div>

              <!-- Signer Actions -->
              <div class="pt-4 mt-4 border-t border-theme-line flex items-center justify-end gap-2">
                <button 
                  v-if="!signer.is_master"
                  @click="openEditSignerModal(signer)"
                  class="px-2.5 py-1 text-[11px] font-semibold text-theme-dim hover:text-cyan-400 hover:bg-theme-panel2 rounded-md transition"
                >
                  Edit Weight
                </button>
                <button 
                  v-if="!signer.is_master"
                  @click="openRemoveSignerModal(signer)"
                  class="px-2.5 py-1 text-[11px] font-semibold text-rose-400 hover:bg-rose-500/10 rounded-md transition"
                >
                  Remove
                </button>
                <span v-else class="text-[11px] text-theme-faint italic">
                  Manage via Thresholds
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 2: THRESHOLD SETTINGS -->
        <div v-if="activeTab === 'thresholds'" class="space-y-6 max-w-2xl">
          <div>
            <h2 class="text-base font-bold text-theme-ink">Threshold & Master Key Configuration</h2>
            <p class="text-xs text-theme-dim mt-0.5">
              Configure minimum cumulative signing weight required for each operation level.
            </p>
          </div>

          <!-- Lockout Safety Alert Box -->
          <div 
            class="p-4 rounded-xl border text-xs leading-relaxed"
            :class="isLockoutRisk ? 'bg-rose-500/10 border-rose-500/30 text-rose-300' : 'bg-theme-panel2 border-theme-line text-theme-dim'"
          >
            <div class="flex items-start gap-2.5">
              <AlertTriangle class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" />
              <div>
                <strong class="text-theme-ink block">Lockout Prevention Guard</strong>
                <span>Total Available Signer Weight: <strong class="text-cyan-400 font-mono">{{ totalPotentialWeight }}</strong>. </span>
                <span v-if="isLockoutRisk" class="text-rose-400 font-bold block mt-1">
                  DANGER: High Threshold ({{ thresholdForm.high }}) exceeds Total Weight ({{ totalPotentialWeight }}). Applying this will permanently lock this account!
                </span>
                <span v-else>
                  Authorization requires the sum of weights from participating signers to reach or exceed the threshold for the executed operation.
                </span>
              </div>
            </div>
          </div>

          <div class="bg-theme-panel border border-theme-line rounded-2xl p-6 space-y-6">
            <!-- Master Key Weight -->
            <div class="space-y-2">
              <div class="flex justify-between items-center">
                <label class="text-xs font-bold text-theme-ink uppercase font-mono">Master Key Weight</label>
                <span class="text-xs font-mono font-bold text-cyan-400">{{ thresholdForm.masterWeight }}</span>
              </div>
              <input 
                v-model.number="thresholdForm.masterWeight" 
                type="number" min="0" max="255" 
                class="w-full px-3.5 py-2 bg-theme-panel2 border border-theme-line rounded-xl text-xs font-mono text-theme-ink focus:outline-none focus:border-cyan-400"
              />
              <p class="text-[11px] text-theme-faint">Weight of the primary public key. Setting to 0 revokes primary key signing power.</p>
            </div>

            <!-- Low Threshold -->
            <div class="space-y-2">
              <div class="flex justify-between items-center">
                <label class="text-xs font-bold text-theme-ink uppercase font-mono">Low Threshold</label>
                <span class="text-xs font-mono font-bold text-theme-ink">{{ thresholdForm.low }}</span>
              </div>
              <input 
                v-model.number="thresholdForm.low" 
                type="number" min="0" max="255" 
                class="w-full px-3.5 py-2 bg-theme-panel2 border border-theme-line rounded-xl text-xs font-mono text-theme-ink focus:outline-none focus:border-cyan-400"
              />
              <p class="text-[11px] text-theme-faint">Required for low-risk operations (sequence bumps, allow trust).</p>
            </div>

            <!-- Medium Threshold -->
            <div class="space-y-2">
              <div class="flex justify-between items-center">
                <label class="text-xs font-bold text-theme-ink uppercase font-mono">Medium Threshold</label>
                <span class="text-xs font-mono font-bold text-cyan-400">{{ thresholdForm.med }}</span>
              </div>
              <input 
                v-model.number="thresholdForm.med" 
                type="number" min="0" max="255" 
                class="w-full px-3.5 py-2 bg-theme-panel2 border border-theme-line rounded-xl text-xs font-mono text-theme-ink focus:outline-none focus:border-cyan-400"
              />
              <p class="text-[11px] text-theme-faint">Required for standard operations (Payments, DEX Trades, Liquidity Pool Deposits, Trustlines).</p>
            </div>

            <!-- High Threshold -->
            <div class="space-y-2">
              <div class="flex justify-between items-center">
                <label class="text-xs font-bold text-theme-ink uppercase font-mono">High Threshold</label>
                <span class="text-xs font-mono font-bold text-purple-400">{{ thresholdForm.high }}</span>
              </div>
              <input 
                v-model.number="thresholdForm.high" 
                type="number" min="0" max="255" 
                class="w-full px-3.5 py-2 bg-theme-panel2 border border-theme-line rounded-xl text-xs font-mono text-theme-ink focus:outline-none focus:border-cyan-400"
              />
              <p class="text-[11px] text-theme-faint">Required for critical operations (Account Signers, Threshold adjustments, Account Merge).</p>
            </div>

            <button 
              @click="applyThresholds()"
              :disabled="isLockoutRisk || actionLoading"
              class="w-full py-3 bg-gradient-to-r from-purple-600 to-cyan-500 hover:opacity-95 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition shadow-lg shadow-cyan-500/10 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
            >
              {{ actionLoading ? 'Generating Transaction...' : 'Generate & Sign Threshold Update' }}
            </button>
          </div>
        </div>

        <!-- TAB 3: MULTISIG TRANSACTIONS -->
        <div v-if="activeTab === 'transactions'" class="space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <h2 class="text-base font-bold text-theme-ink">Multisig Proposals & Signing Queue</h2>
              <p class="text-xs text-theme-dim mt-0.5">
                Propose payments, collect signatures from co-signers, and submit when threshold is satisfied.
              </p>
            </div>

            <div class="flex items-center gap-2">
              <button 
                @click="openImportXdrModal()"
                class="px-3 py-1.5 bg-theme-panel2 hover:bg-theme-panel3 border border-theme-line rounded-lg text-xs font-semibold text-theme-ink flex items-center gap-1.5 transition"
              >
                <FileCode class="w-3.5 h-3.5 text-cyan-400" />
                <span>Import XDR</span>
              </button>
              <button 
                @click="openNewProposalModal()"
                class="px-3.5 py-1.5 bg-cyan-500 hover:bg-cyan-400 text-slate-950 rounded-lg text-xs font-bold flex items-center gap-1.5 transition cursor-pointer"
              >
                <Plus class="w-3.5 h-3.5" />
                <span>Create Proposal</span>
              </button>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="transactions.length === 0" class="py-16 text-center bg-theme-panel border border-theme-line rounded-2xl p-6 space-y-3">
            <FileSignature class="w-10 h-10 text-theme-dim mx-auto stroke-1" />
            <p class="text-sm font-bold text-theme-ink">No Multisig Transactions</p>
            <p class="text-xs text-theme-dim max-w-sm mx-auto">
              Create a proposal or import an existing transaction XDR to begin collecting required signatures.
            </p>
            <button 
              @click="openNewProposalModal()"
              class="px-4 py-2 bg-theme-panel2 hover:bg-theme-panel3 border border-theme-line rounded-xl text-xs font-bold text-cyan-400 mt-2 transition"
            >
              Propose Payment
            </button>
          </div>

          <!-- Transaction List -->
          <div v-else class="space-y-4">
            <div 
              v-for="tx in transactions" 
              :key="tx.id"
              class="bg-theme-panel border border-theme-line hover:border-theme-line2 rounded-2xl p-5 transition space-y-4 shadow-lg"
            >
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-theme-ink">{{ tx.title || 'Multisig Transaction' }}</h3>
                    <span 
                      class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider"
                      :class="getStatusBadgeClass(tx.status)"
                    >
                      {{ tx.status }}
                    </span>
                  </div>
                  <div class="flex items-center gap-3 text-xs text-theme-dim flex-wrap font-mono text-[11px]">
                    <span>Type: <strong class="text-theme-ink uppercase">{{ tx.operation_type }}</strong></span>
                    <span>·</span>
                    <span>Threshold: <strong class="text-theme-ink uppercase">{{ tx.threshold_type }}</strong></span>
                    <span>·</span>
                    <span>Created by: {{ shorten(tx.created_by) }}</span>
                    <span>·</span>
                    <span>{{ formatDate(tx.created_at) }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <button 
                    @click="viewTransactionDetails(tx)"
                    class="px-3.5 py-1.5 bg-theme-panel2 hover:bg-theme-panel3 border border-theme-line rounded-xl text-xs font-semibold text-theme-ink flex items-center gap-1.5 transition"
                  >
                    <Eye class="w-3.5 h-3.5 text-cyan-400" />
                    <span>Workspace & Sign</span>
                  </button>
                </div>
              </div>

              <!-- Signing Progress Bar -->
              <div class="space-y-1.5 bg-theme-panel2/50 p-3 rounded-xl border border-theme-line/60">
                <div class="flex justify-between text-xs font-mono">
                  <span class="text-theme-dim">Signing Weight: <strong class="text-cyan-400">{{ tx.current_weight }}</strong> / {{ tx.required_weight }}</span>
                  <span :class="tx.current_weight >= tx.required_weight ? 'text-emerald-400 font-bold' : 'text-amber-400'">
                    {{ tx.current_weight >= tx.required_weight ? 'Threshold Met (Ready)' : `Need +${tx.required_weight - tx.current_weight} more weight` }}
                  </span>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden">
                  <div 
                    class="h-full rounded-full transition-all duration-300"
                    :class="tx.current_weight >= tx.required_weight ? 'bg-emerald-400' : 'bg-gradient-to-r from-purple-500 to-cyan-400'"
                    :style="{ width: `${Math.min(100, Math.round((tx.current_weight / (tx.required_weight || 1)) * 100))}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- MODAL: ADD SIGNER -->
    <div v-if="addSignerModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
      <div class="bg-theme-panel border border-theme-line rounded-2xl max-w-md w-full p-6 space-y-5 shadow-2xl">
        <div class="flex justify-between items-center">
          <h3 class="text-base font-bold text-theme-ink flex items-center gap-2">
            <UserPlus class="w-4 h-4 text-cyan-400" />
            <span>Add Additional Signer</span>
          </h3>
          <button @click="addSignerModalOpen = false" class="text-theme-dim hover:text-white transition">✕</button>
        </div>

        <div class="space-y-4 text-xs">
          <div class="space-y-1.5">
            <label class="font-bold text-theme-dim uppercase font-mono text-[10px]">Stellar Public Key (G...)</label>
            <input 
              v-model="signerForm.publicKey" 
              type="text" 
              placeholder="GXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX" 
              class="w-full px-3 py-2 bg-theme-panel2 border border-theme-line rounded-xl font-mono text-xs text-theme-ink focus:outline-none focus:border-cyan-400"
            />
          </div>

          <div class="space-y-1.5">
            <label class="font-bold text-theme-dim uppercase font-mono text-[10px]">Signer Weight (1 - 255)</label>
            <input 
              v-model.number="signerForm.weight" 
              type="number" min="1" max="255" 
              class="w-full px-3 py-2 bg-theme-panel2 border border-theme-line rounded-xl font-mono text-xs text-theme-ink focus:outline-none focus:border-cyan-400"
            />
          </div>

          <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-xl text-amber-300 text-[11px] leading-relaxed">
            ⚠️ <strong>On-Chain Configuration Change</strong>: Adding a signer modifies account state on Stellar and consumes 1 subentry (+0.5 XLM reserve requirement). You will be prompted to sign this transaction in your connected wallet.
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
          <button 
            @click="addSignerModalOpen = false" 
            class="px-4 py-2 bg-theme-panel2 hover:bg-theme-panel3 text-theme-dim rounded-xl text-xs font-semibold"
          >
            Cancel
          </button>
          <button 
            @click="submitAddSigner()" 
            :disabled="actionLoading || !signerForm.publicKey || signerForm.weight < 1"
            class="px-4 py-2 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold rounded-xl text-xs transition disabled:opacity-40"
          >
            {{ actionLoading ? 'Signing...' : 'Sign & Add Signer' }}
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL: REMOVE SIGNER -->
    <div v-if="removeSignerModalOpen && targetSigner" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
      <div class="bg-theme-panel border border-theme-line rounded-2xl max-w-md w-full p-6 space-y-5 shadow-2xl">
        <div class="flex justify-between items-center">
          <h3 class="text-base font-bold text-rose-400 flex items-center gap-2">
            <AlertTriangle class="w-4 h-4" />
            <span>Remove Account Signer</span>
          </h3>
          <button @click="removeSignerModalOpen = false" class="text-theme-dim hover:text-white transition">✕</button>
        </div>

        <div class="space-y-3 text-xs leading-relaxed">
          <p class="text-theme-ink">
            Are you sure you want to remove signer <strong class="font-mono text-cyan-400">{{ shorten(targetSigner.key) }}</strong>?
          </p>

          <div class="p-3.5 bg-rose-500/10 border border-rose-500/20 rounded-xl text-rose-300 text-[11px] space-y-1.5">
            <strong>Security Warning:</strong>
            <p>
              "Removing this signer changes who can authorize transactions from this Stellar account."
            </p>
            <p>
              Its weight ({{ targetSigner.weight }}) will be set to 0 and permanently removed from the ledger.
            </p>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
          <button 
            @click="removeSignerModalOpen = false" 
            class="px-4 py-2 bg-theme-panel2 hover:bg-theme-panel3 text-theme-dim rounded-xl text-xs font-semibold"
          >
            Cancel
          </button>
          <button 
            @click="submitRemoveSigner()" 
            :disabled="actionLoading"
            class="px-4 py-2 bg-rose-500 hover:bg-rose-400 text-white font-bold rounded-xl text-xs transition disabled:opacity-40"
          >
            {{ actionLoading ? 'Processing...' : 'Confirm & Remove Signer' }}
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL: TRANSACTION DETAIL / SIGNING WORKSPACE -->
    <div v-if="detailModalOpen && selectedTx" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm overflow-y-auto">
      <div class="bg-theme-panel border border-theme-line rounded-2xl max-w-2xl w-full p-6 space-y-6 shadow-2xl my-8">
        <div class="flex justify-between items-start">
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-lg font-bold text-theme-ink">{{ selectedTx.title }}</h3>
              <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider" :class="getStatusBadgeClass(selectedTx.status)">
                {{ selectedTx.status }}
              </span>
            </div>
            <p class="text-xs font-mono text-theme-dim mt-0.5">Hash: {{ selectedTx.transaction_hash }}</p>
          </div>
          <button @click="detailModalOpen = false" class="text-theme-dim hover:text-white transition">✕</button>
        </div>

        <!-- Weight Progress Summary -->
        <div class="p-4 bg-theme-panel2 rounded-xl border border-theme-line space-y-2">
          <div class="flex justify-between text-xs font-mono">
            <span class="text-theme-dim">Required Threshold ({{ selectedTx.threshold_type.toUpperCase() }}): <strong class="text-theme-ink">{{ selectedTx.required_weight }}</strong></span>
            <span class="text-theme-dim">Collected Weight: <strong class="text-cyan-400">{{ selectedTx.current_weight }}</strong></span>
          </div>
          <div class="w-full bg-slate-800 rounded-full h-2.5 overflow-hidden">
            <div 
              class="h-full rounded-full transition-all duration-300"
              :class="selectedTx.current_weight >= selectedTx.required_weight ? 'bg-emerald-400' : 'bg-gradient-to-r from-purple-500 to-cyan-400'"
              :style="{ width: `${Math.min(100, Math.round((selectedTx.current_weight / (selectedTx.required_weight || 1)) * 100))}%` }"
            ></div>
          </div>
        </div>

        <!-- Signatures Table -->
        <div class="space-y-2">
          <h4 class="text-xs font-bold uppercase tracking-wider text-theme-dim font-mono">Verified Signers</h4>
          <div class="bg-theme-panel2/60 border border-theme-line rounded-xl divide-y divide-theme-line">
            <div 
              v-for="sig in (selectedTx.signatures_json || [])" 
              :key="sig.public_key" 
              class="p-3 flex items-center justify-between text-xs font-mono"
            >
              <div class="flex items-center gap-2">
                <CheckCircle2 class="w-4 h-4 text-emerald-400 flex-shrink-0" />
                <span class="font-bold text-theme-ink">{{ sig.public_key }}</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-cyan-400 font-bold">+{{ sig.weight }} Weight</span>
              </div>
            </div>
            <div v-if="!selectedTx.signatures_json || selectedTx.signatures_json.length === 0" class="p-4 text-center text-xs text-theme-dim">
              No signatures attached yet.
            </div>
          </div>
        </div>

        <!-- Raw XDR Accordion -->
        <div class="space-y-1.5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase font-mono text-theme-dim">Transaction Envelope XDR</span>
            <button @click="copyText(selectedTx.signed_xdr || selectedTx.unsigned_xdr)" class="text-xs text-cyan-400 hover:underline flex items-center gap-1">
              <Copy class="w-3 h-3" />
              <span>Copy XDR</span>
            </button>
          </div>
          <textarea 
            :value="selectedTx.signed_xdr || selectedTx.unsigned_xdr" 
            readonly 
            rows="3" 
            class="w-full px-3 py-2 bg-theme-panel2 border border-theme-line rounded-xl font-mono text-[10px] text-theme-dim select-all focus:outline-none"
          ></textarea>
        </div>

        <!-- Import / Paste Co-Signed XDR from external signer -->
        <div class="space-y-2 pt-2 border-t border-theme-line">
          <label class="text-xs font-bold text-theme-ink flex items-center gap-1.5">
            <Download class="w-3.5 h-3.5 text-cyan-400" />
            <span>Import Signature from Co-Signer</span>
          </label>
          <div class="flex gap-2">
            <input 
              v-model="importCoSignedXdrInput" 
              type="text" 
              placeholder="Paste co-signed XDR here..." 
              class="flex-1 px-3 py-2 bg-theme-panel2 border border-theme-line rounded-xl text-xs font-mono text-theme-ink placeholder:text-theme-faint focus:outline-none focus:border-cyan-400"
            />
            <button 
              @click="submitCoSignedXdr(selectedTx.id)" 
              :disabled="!importCoSignedXdrInput || actionLoading"
              class="px-4 py-2 bg-theme-panel2 hover:bg-theme-panel3 border border-theme-line rounded-xl text-xs font-bold text-cyan-400 transition cursor-pointer disabled:opacity-40"
            >
              Merge Signature
            </button>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-theme-line">
          <button 
            @click="signWithConnectedWallet(selectedTx)" 
            :disabled="actionLoading || selectedTx.status === 'submitted'"
            class="w-full sm:w-auto px-4 py-2.5 bg-theme-panel2 hover:bg-theme-panel3 border border-theme-line hover:border-cyan-500/40 rounded-xl text-xs font-bold text-cyan-400 flex items-center justify-center gap-1.5 transition cursor-pointer disabled:opacity-40"
          >
            <Shield class="w-4 h-4" />
            <span>Sign with Connected Wallet</span>
          </button>

          <button 
            @click="broadcastTransaction(selectedTx.id)" 
            :disabled="actionLoading || selectedTx.current_weight < selectedTx.required_weight || selectedTx.status === 'submitted'"
            class="w-full sm:w-auto px-5 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black rounded-xl text-xs uppercase tracking-wider flex items-center justify-center gap-1.5 transition shadow-lg shadow-emerald-500/20 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
          >
            <Send class="w-4 h-4" />
            <span>{{ selectedTx.status === 'submitted' ? 'Already Submitted' : 'Broadcast to Stellar' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL: PROPOSE NEW TRANSACTION -->
    <div v-if="newProposalModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
      <div class="bg-theme-panel border border-theme-line rounded-2xl max-w-lg w-full p-6 space-y-5 shadow-2xl">
        <div class="flex justify-between items-center">
          <h3 class="text-base font-bold text-theme-ink flex items-center gap-2">
            <Send class="w-4 h-4 text-cyan-400" />
            <span>Create Multisig Proposal</span>
          </h3>
          <button @click="newProposalModalOpen = false" class="text-theme-dim hover:text-white transition">✕</button>
        </div>

        <!-- Form fields -->
        <div class="space-y-4 text-xs">
          <div class="space-y-1">
            <label class="font-bold text-theme-dim uppercase font-mono text-[10px]">Proposal Title</label>
            <input 
              v-model="proposalForm.title" 
              type="text" 
              placeholder="e.g. Treasury Reserve Transfer" 
              class="w-full px-3 py-2 bg-theme-panel2 border border-theme-line rounded-xl font-mono text-xs text-theme-ink focus:outline-none focus:border-cyan-400"
            />
          </div>

          <div class="space-y-1">
            <label class="font-bold text-theme-dim uppercase font-mono text-[10px]">Destination Stellar Address (G...)</label>
            <input 
              v-model="proposalForm.destination" 
              type="text" 
              placeholder="GXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX" 
              class="w-full px-3 py-2 bg-theme-panel2 border border-theme-line rounded-xl font-mono text-xs text-theme-ink focus:outline-none focus:border-cyan-400"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1">
              <label class="font-bold text-theme-dim uppercase font-mono text-[10px]">Amount</label>
              <input 
                v-model="proposalForm.amount" 
                type="number" step="0.0000001" min="0.0000001" 
                placeholder="100.0" 
                class="w-full px-3 py-2 bg-theme-panel2 border border-theme-line rounded-xl font-mono text-xs text-theme-ink focus:outline-none focus:border-cyan-400"
              />
            </div>

            <div class="space-y-1">
              <label class="font-bold text-theme-dim uppercase font-mono text-[10px]">Asset Code</label>
              <input 
                v-model="proposalForm.assetCode" 
                type="text" 
                placeholder="XLM" 
                class="w-full px-3 py-2 bg-theme-panel2 border border-theme-line rounded-xl font-mono text-xs text-theme-ink focus:outline-none focus:border-cyan-400 uppercase"
              />
            </div>
          </div>

          <div v-if="proposalForm.assetCode && proposalForm.assetCode.toUpperCase() !== 'XLM'" class="space-y-1">
            <label class="font-bold text-theme-dim uppercase font-mono text-[10px]">Asset Issuer Address (G...)</label>
            <input 
              v-model="proposalForm.assetIssuer" 
              type="text" 
              placeholder="G..." 
              class="w-full px-3 py-2 bg-theme-panel2 border border-theme-line rounded-xl font-mono text-xs text-theme-ink focus:outline-none focus:border-cyan-400"
            />
          </div>

          <div class="space-y-1">
            <label class="font-bold text-theme-dim uppercase font-mono text-[10px]">Memo (Optional)</label>
            <input 
              v-model="proposalForm.memo" 
              type="text" maxlength="28" 
              placeholder="Up to 28 characters" 
              class="w-full px-3 py-2 bg-theme-panel2 border border-theme-line rounded-xl font-mono text-xs text-theme-ink focus:outline-none focus:border-cyan-400"
            />
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
          <button 
            @click="newProposalModalOpen = false" 
            class="px-4 py-2 bg-theme-panel2 hover:bg-theme-panel3 text-theme-dim rounded-xl text-xs font-semibold"
          >
            Cancel
          </button>
          <button 
            @click="submitCreateProposal()" 
            :disabled="actionLoading || !proposalForm.destination || !proposalForm.amount"
            class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-cyan-500 hover:opacity-95 text-white font-bold rounded-xl text-xs transition disabled:opacity-40 cursor-pointer"
          >
            {{ actionLoading ? 'Building Proposal...' : 'Create Proposal & Sign' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Connect Wallet Modal -->
    <ConnectWalletModal v-model="connectWalletModalOpen" />
    <Footer />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import Swal from 'sweetalert2'
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'
import ConnectWalletModal from '@/components/ConnectWallet.vue'
import { getCookie, signXdrWithWallet } from '../utils/utils.js'
import {
  Shield,
  Wallet,
  Copy,
  ExternalLink,
  UserPlus,
  Users,
  Sliders,
  FileSignature,
  AlertTriangle,
  Send,
  Plus,
  Eye,
  CheckCircle2,
  RefreshCw,
  FileCode,
  Download
} from 'lucide-vue-next'

const route = useRoute()

const networkName = ref('public')
const isTestnet = computed(() => networkName.value !== 'public')

const connectWalletModalOpen = ref(false)
const connectedWalletKey = ref('')

const accountLoading = ref(false)
const accountError = ref('')
const accountInfo = ref(null)

const activeTab = ref('signers')
const actionLoading = ref(false)

// Modals
const addSignerModalOpen = ref(false)
const removeSignerModalOpen = ref(false)
const targetSigner = ref(null)

const newProposalModalOpen = ref(false)
const detailModalOpen = ref(false)
const selectedTx = ref(null)
const importCoSignedXdrInput = ref('')

// Forms
const signerForm = reactive({
  publicKey: '',
  weight: 1
})

const thresholdForm = reactive({
  masterWeight: 1,
  low: 0,
  med: 1,
  high: 1
})

const proposalForm = reactive({
  title: '',
  destination: '',
  amount: '',
  assetCode: 'XLM',
  assetIssuer: '',
  memo: ''
})

const transactions = ref([])

const totalPotentialWeight = computed(() => {
  if (!accountInfo.value) return 0
  let total = thresholdForm.masterWeight || 0
  accountInfo.value.signers.forEach(s => {
    if (!s.is_master) {
      total += s.weight
    }
  })
  return total
})

const isLockoutRisk = computed(() => {
  return thresholdForm.high > totalPotentialWeight.value
})

const fetchEnv = async () => {
  try {
    const res = await axios.get('/api/env')
    networkName.value = res.data?.stellar_env || 'public'
  } catch (e) {
    networkName.value = 'public'
  }
}

const loadAccount = async (address) => {
  if (!address || !address.trim()) {
    accountInfo.value = null
    accountLoading.value = false
    return
  }
  accountLoading.value = true
  accountError.value = ''
  try {
    const res = await axios.get(`/api/multisig/account/${address.trim()}`)
    if (res.data?.status === 'success') {
      accountInfo.value = res.data.data
      thresholdForm.masterWeight = res.data.data.master_key_weight
      thresholdForm.low = res.data.data.thresholds.low
      thresholdForm.med = res.data.data.thresholds.med
      thresholdForm.high = res.data.data.thresholds.high

      await fetchTransactions(address.trim())
    } else {
      accountError.value = res.data?.message || 'Failed to load account.'
    }
  } catch (err) {
    accountError.value = err.response?.data?.message || 'Account not found or inactive on Stellar.'
  } finally {
    accountLoading.value = false
  }
}

const fetchTransactions = async (accountId) => {
  try {
    const res = await axios.get('/api/multisig/transactions', {
      params: { account_id: accountId }
    })
    if (res.data?.status === 'success') {
      transactions.value = res.data.data || []
    }
  } catch (e) {
    console.error('Failed to load multisig transactions:', e)
  }
}

const openAddSignerModal = () => {
  signerForm.publicKey = ''
  signerForm.weight = 1
  addSignerModalOpen.value = true
}

const submitAddSigner = async () => {
  actionLoading.value = true
  try {
    // 1. Build XDR on backend
    const buildRes = await axios.post('/api/multisig/build-signer-xdr', {
      account_id: accountInfo.value.account_id,
      signer_public_key: signerForm.publicKey.trim(),
      weight: signerForm.weight
    })

    const unsignedXdr = buildRes.data?.data?.unsigned_xdr

    // 2. Sign client-side via connected wallet
    const signedXdr = await signXdrWithWallet(
      localStorage.getItem('wallet_key') || 'freighter',
      unsignedXdr,
      isTestnet.value
    )

    // 3. Submit directly or save proposal
    const submitRes = await axios.post('/api/token/submit-trustline-xdr', {
      signedXdr
    })

    if (submitRes.data?.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Signer Added!',
        text: 'The signer configuration has been successfully updated on Stellar.',
        confirmButtonColor: '#06b6d4'
      })
      addSignerModalOpen.value = false
      await loadAccount(accountInfo.value.account_id)
    } else {
      Swal.fire('Error', submitRes.data?.message || 'Transaction submission failed.', 'error')
    }
  } catch (err) {
    Swal.fire('Failed', err.response?.data?.message || err.message || 'Failed to add signer.', 'error')
  } finally {
    actionLoading.value = false
  }
}

const openEditSignerModal = (signer) => {
  signerForm.publicKey = signer.key
  signerForm.weight = signer.weight
  addSignerModalOpen.value = true
}

const openRemoveSignerModal = (signer) => {
  targetSigner.value = signer
  removeSignerModalOpen.value = true
}

const submitRemoveSigner = async () => {
  actionLoading.value = true
  try {
    const buildRes = await axios.post('/api/multisig/build-signer-xdr', {
      account_id: accountInfo.value.account_id,
      signer_public_key: targetSigner.value.key,
      weight: 0
    })

    const unsignedXdr = buildRes.data?.data?.unsigned_xdr

    const signedXdr = await signXdrWithWallet(
      localStorage.getItem('wallet_key') || 'freighter',
      unsignedXdr,
      isTestnet.value
    )

    const submitRes = await axios.post('/api/token/submit-trustline-xdr', {
      signedXdr
    })

    if (submitRes.data?.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Signer Removed',
        text: 'The signer has been removed from the account.',
        confirmButtonColor: '#06b6d4'
      })
      removeSignerModalOpen.value = false
      await loadAccount(accountInfo.value.account_id)
    } else {
      Swal.fire('Error', submitRes.data?.message || 'Transaction submission failed.', 'error')
    }
  } catch (err) {
    Swal.fire('Failed', err.response?.data?.message || err.message || 'Failed to remove signer.', 'error')
  } finally {
    actionLoading.value = false
  }
}

const applyThresholds = async () => {
  actionLoading.value = true
  try {
    const buildRes = await axios.post('/api/multisig/build-thresholds-xdr', {
      account_id: accountInfo.value.account_id,
      master_weight: thresholdForm.masterWeight,
      low: thresholdForm.low,
      med: thresholdForm.med,
      high: thresholdForm.high
    })

    const unsignedXdr = buildRes.data?.data?.unsigned_xdr

    const signedXdr = await signXdrWithWallet(
      localStorage.getItem('wallet_key') || 'freighter',
      unsignedXdr,
      isTestnet.value
    )

    const submitRes = await axios.post('/api/token/submit-trustline-xdr', {
      signedXdr
    })

    if (submitRes.data?.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Thresholds Updated!',
        text: 'Account thresholds successfully saved on-chain.',
        confirmButtonColor: '#06b6d4'
      })
      await loadAccount(accountInfo.value.account_id)
    } else {
      Swal.fire('Error', submitRes.data?.message || 'Submission failed.', 'error')
    }
  } catch (err) {
    Swal.fire('Failed', err.response?.data?.message || err.message || 'Failed to update thresholds.', 'error')
  } finally {
    actionLoading.value = false
  }
}

const openNewProposalModal = () => {
  proposalForm.title = ''
  proposalForm.destination = ''
  proposalForm.amount = ''
  proposalForm.assetCode = 'XLM'
  proposalForm.assetIssuer = ''
  proposalForm.memo = ''
  newProposalModalOpen.value = true
}

const submitCreateProposal = async () => {
  actionLoading.value = true
  try {
    // 1. Build payment XDR
    const buildRes = await axios.post('/api/multisig/build-payment-xdr', {
      source_account: accountInfo.value.account_id,
      destination: proposalForm.destination.trim(),
      amount: proposalForm.amount,
      asset_code: proposalForm.assetCode.trim(),
      asset_issuer: proposalForm.assetIssuer?.trim() || null,
      memo: proposalForm.memo || null
    })

    const unsignedXdr = buildRes.data?.data?.unsigned_xdr

    // 2. Initial signer signs via wallet
    const signedXdr = await signXdrWithWallet(
      localStorage.getItem('wallet_key') || 'freighter',
      unsignedXdr,
      isTestnet.value
    )

    // 3. Persist proposal to backend
    const createRes = await axios.post('/api/multisig/transactions', {
      account_id: accountInfo.value.account_id,
      xdr: signedXdr,
      created_by: connectedWalletKey.value || accountInfo.value.account_id,
      title: proposalForm.title || buildRes.data?.data?.title,
      operation_type: 'payment'
    })

    Swal.fire({
      icon: 'success',
      title: 'Multisig Proposal Created!',
      text: 'Transaction created and signed with your wallet. Co-signers can now add signatures.',
      confirmButtonColor: '#06b6d4'
    })

    newProposalModalOpen.value = false
    await fetchTransactions(accountInfo.value.account_id)
  } catch (err) {
    Swal.fire('Failed', err.response?.data?.message || err.message || 'Failed to create proposal.', 'error')
  } finally {
    actionLoading.value = false
  }
}

const viewTransactionDetails = async (tx) => {
  try {
    const res = await axios.get(`/api/multisig/transactions/${tx.id}`)
    selectedTx.value = res.data?.data
    detailModalOpen.value = true
  } catch (e) {
    selectedTx.value = tx
    detailModalOpen.value = true
  }
}

const signWithConnectedWallet = async (tx) => {
  actionLoading.value = true
  try {
    const xdrToSign = tx.signed_xdr || tx.unsigned_xdr
    const newSignedXdr = await signXdrWithWallet(
      localStorage.getItem('wallet_key') || 'freighter',
      xdrToSign,
      isTestnet.value
    )

    const res = await axios.post(`/api/multisig/transactions/${tx.id}/sign`, {
      signed_xdr: newSignedXdr
    })

    selectedTx.value = res.data?.data
    Swal.fire({
      icon: 'success',
      title: 'Signed Successfully!',
      text: 'Your signature has been merged. Updated weight: ' + res.data?.data?.current_weight,
      confirmButtonColor: '#06b6d4'
    })
    await fetchTransactions(accountInfo.value.account_id)
  } catch (err) {
    Swal.fire('Sign Failed', err.response?.data?.message || err.message || 'Could not sign transaction.', 'error')
  } finally {
    actionLoading.value = false
  }
}

const submitCoSignedXdr = async (txId) => {
  if (!importCoSignedXdrInput.value) return
  actionLoading.value = true
  try {
    const res = await axios.post(`/api/multisig/transactions/${txId}/sign`, {
      signed_xdr: importCoSignedXdrInput.value.trim()
    })

    selectedTx.value = res.data?.data
    importCoSignedXdrInput.value = ''
    Swal.fire('Success', 'External signature merged successfully!', 'success')
    await fetchTransactions(accountInfo.value.account_id)
  } catch (err) {
    Swal.fire('Error', err.response?.data?.message || 'Invalid XDR or signature.', 'error')
  } finally {
    actionLoading.value = false
  }
}

const broadcastTransaction = async (txId) => {
  actionLoading.value = true
  try {
    const res = await axios.post(`/api/multisig/transactions/${txId}/submit`)
    if (res.data?.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Transaction Confirmed!',
        text: 'Successfully broadcast and confirmed on Stellar ledger.',
        confirmButtonColor: '#06b6d4'
      })
      detailModalOpen.value = false
      await loadAccount(accountInfo.value.account_id)
    } else {
      Swal.fire('Submission Failed', res.data?.message || 'Horizon rejected transaction.', 'error')
    }
  } catch (err) {
    Swal.fire('Broadcast Error', err.response?.data?.message || 'Submission error.', 'error')
  } finally {
    actionLoading.value = false
  }
}

const openImportXdrModal = async () => {
  const { value: xdrText } = await Swal.fire({
    title: 'Import Transaction XDR',
    input: 'textarea',
    inputLabel: 'Paste transaction base64 XDR',
    inputPlaceholder: 'AAAAAgAAAA...',
    showCancelButton: true,
    confirmButtonText: 'Inspect & Import',
    confirmButtonColor: '#06b6d4'
  })

  if (xdrText) {
    try {
      const res = await axios.post('/api/multisig/transactions', {
        account_id: accountInfo.value.account_id,
        xdr: xdrText.trim(),
        created_by: connectedWalletKey.value || accountInfo.value.account_id,
        title: 'Imported Multisig Transaction'
      })
      Swal.fire('Imported', 'Multisig proposal created from XDR.', 'success')
      await fetchTransactions(accountInfo.value.account_id)
    } catch (e) {
      Swal.fire('Error', e.response?.data?.message || 'Failed to import XDR.', 'error')
    }
  }
}

const copyText = (text) => {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(text)
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      title: 'Copied to clipboard',
      showConfirmButton: false,
      timer: 1500
    })
  }
}

const shorten = (str) => {
  if (!str) return '-'
  return str.slice(0, 5) + '...' + str.slice(-4)
}

const formatNum = (num) => {
  return new Intl.NumberFormat('en-US', { maximumFractionDigits: 2 }).format(num || 0)
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleString()
}

const getExplorerAccountUrl = (address) => {
  const netPrefix = isTestnet.value ? 'testnet' : 'public'
  return `https://stellar.expert/explorer/${netPrefix}/account/${address}`
}

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'ready':
      return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
    case 'submitted':
      return 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20'
    case 'failed':
      return 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
    default:
      return 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
  }
}

const onWalletChanged = async (e) => {
  const pk = e?.detail?.publicKey || getCookie('public_key') || localStorage.getItem('public_key') || ''
  connectedWalletKey.value = pk
  if (pk) {
    await loadAccount(pk)
  } else {
    accountInfo.value = null
    accountError.value = ''
    accountLoading.value = false
    transactions.value = []
  }
}

onMounted(async () => {
  await fetchEnv()
  connectedWalletKey.value = getCookie('public_key') || localStorage.getItem('public_key') || localStorage.getItem('wallet_key') || ''
  
  const targetAddress = route.query.address || connectedWalletKey.value || ''
  if (targetAddress) {
    await loadAccount(targetAddress)
  } else {
    accountLoading.value = false
    accountInfo.value = null
  }

  window.addEventListener('tokenglade-wallet-changed', onWalletChanged)
})

onUnmounted(() => {
  window.removeEventListener('tokenglade-wallet-changed', onWalletChanged)
})
</script>
