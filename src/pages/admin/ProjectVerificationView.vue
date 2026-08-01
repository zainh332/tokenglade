<template>
  <div class="space-y-6 text-gray-300">
    <!-- Summary stats cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Claims</span>
        <span class="text-3xl font-extrabold text-white mt-2">{{ totalCount }}</span>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-green-500 font-bold uppercase tracking-wider">Approved</span>
        <span class="text-3xl font-extrabold text-green-400 mt-2">{{ approvedCount }}</span>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-purple-500 font-bold uppercase tracking-wider">Pending Review</span>
        <span class="text-3xl font-extrabold text-purple-400 mt-2">{{ pendingCount }}</span>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-red-500 font-bold uppercase tracking-wider">Rejected</span>
        <span class="text-3xl font-extrabold text-red-400 mt-2">{{ rejectedCount }}</span>
      </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-xl">
      <div class="p-6 border-b border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="space-y-1">
          <h3 class="text-base font-bold text-gray-100">Project Verification Claims</h3>
          <p class="text-xs text-gray-500">Approve or reject projects that have applied for Stellar ecosystem verification badges.</p>
        </div>
        <div class="flex items-center gap-4">
          <button @click="loadData" class="text-xs text-purple-400 hover:text-purple-300 font-semibold">
            Refresh Claims
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="p-6 border-b border-gray-800 flex flex-col sm:flex-row gap-4 items-center justify-between bg-gray-950/20">
        <div class="flex gap-2">
          <button 
            v-for="status in ['all', 'pending', 'approved', 'rejected']" 
            :key="status" 
            @click="filterStatus = status"
            class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border transition"
            :class="filterStatus === status ? 'bg-purple-600 border-purple-500 text-white' : 'border-gray-800 text-gray-400 hover:text-white'"
          >
            {{ status }}
          </button>
        </div>
        <div class="w-full sm:w-64">
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Search by Code or Project..." 
            class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2 text-xs focus:outline-none focus:border-purple-500 text-white"
          />
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-950/40 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-855 select-none">
              <th @click="sortBy('name')" class="py-4 px-6 cursor-pointer hover:text-gray-300 transition">
                Project Details <span class="text-[9px]" v-if="sortKey === 'name'">{{ sortAsc ? '▲' : '▼' }}</span>
              </th>
              <th class="py-4 px-6">Issuer</th>
              <th class="py-4 px-6">Sender Wallet</th>
              <th @click="sortBy('payment_asset')" class="py-4 px-6 cursor-pointer hover:text-gray-300 transition">
                Payment Asset <span class="text-[9px]" v-if="sortKey === 'payment_asset'">{{ sortAsc ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('payment_amount')" class="py-4 px-6 cursor-pointer hover:text-gray-300 transition">
                Amount <span class="text-[9px]" v-if="sortKey === 'payment_amount'">{{ sortAsc ? '▲' : '▼' }}</span>
              </th>
              <th class="py-4 px-6">Payment Tx Link</th>
              <th @click="sortBy('created_at')" class="py-4 px-6 cursor-pointer hover:text-gray-300 transition">
                Submitted At <span class="text-[9px]" v-if="sortKey === 'created_at'">{{ sortAsc ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('updated_at')" class="py-4 px-6 cursor-pointer hover:text-gray-300 transition">
                Updated At <span class="text-[9px]" v-if="sortKey === 'updated_at'">{{ sortAsc ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('status')" class="py-4 px-6 cursor-pointer hover:text-gray-300 transition">
                Status <span class="text-[9px]" v-if="sortKey === 'status'">{{ sortAsc ? '▲' : '▼' }}</span>
              </th>
              <th class="py-4 px-6 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-850 text-sm text-gray-300">
            <tr v-for="claim in filteredItems" :key="claim.id" class="hover:bg-gray-850/30 transition">
              <!-- Project Details -->
              <td class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-gray-950 border border-gray-800 overflow-hidden flex items-center justify-center flex-shrink-0">
                    <img v-if="claim.logo_url" :src="claim.logo_url" class="w-full h-full object-contain p-1" />
                    <span v-else class="text-[10px] font-black text-cyan-400">
                      {{ claim.asset_code?.slice(0, 2).toUpperCase() }}
                    </span>
                  </div>
                  <div>
                    <span class="font-bold text-white block">{{ claim.name }}</span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black tracking-wider uppercase bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                      {{ claim.asset_code }}
                    </span>
                  </div>
                </div>
              </td>
              <!-- Issuer -->
              <td class="py-4 px-6 font-mono text-xs text-gray-400 select-all" :title="claim.asset_issuer">
                {{ shortAddr(claim.asset_issuer) }}
              </td>
              <!-- Sender Wallet -->
              <td class="py-4 px-6 font-mono text-xs text-gray-400 select-all" :title="claim.sender_wallet">
                {{ shortAddr(claim.sender_wallet) }}
              </td>
              <!-- Payment Asset -->
              <td class="py-4 px-6">
                <span class="font-bold text-white font-mono">{{ claim.payment_asset || 'TKG' }}</span>
              </td>
              <!-- Payment Amount -->
              <td class="py-4 px-6 font-mono font-bold text-gray-100">
                {{ claim.payment_amount ? claim.payment_amount.toLocaleString() : '500' }}
              </td>
              <!-- Payment Tx Link -->
              <td class="py-4 px-6 text-xs">
                <a 
                  :href="`https://stellar.expert/explorer/public/tx/${claim.payment_tx}`" 
                  target="_blank" 
                  class="inline-flex items-center gap-1 text-cyan-400 hover:text-cyan-300 font-semibold"
                >
                  <span>{{ shortHash(claim.payment_tx) }}</span>
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                  </svg>
                </a>
              </td>
              <!-- Submitted At -->
              <td class="py-4 px-6 text-xs text-gray-400 font-mono">
                {{ formatDate(claim.created_at) }}
              </td>
              <!-- Updated At -->
              <td class="py-4 px-6 text-xs text-gray-400 font-mono">
                {{ formatDate(claim.updated_at) }}
              </td>
              <!-- Status -->
              <td class="py-4 px-6">
                <div class="flex flex-col items-start gap-1">
                  <span 
                    class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider border"
                    :class="{
                      'bg-green-500/10 border-green-500/20 text-green-400': claim.status === 'approved',
                      'bg-purple-500/10 border-purple-500/20 text-purple-400': claim.status === 'pending',
                      'bg-red-500/10 border-red-500/20 text-red-400': claim.status === 'rejected',
                    }"
                  >
                    {{ claim.status }}
                  </span>
                  <span v-if="claim.status === 'rejected' && claim.rejection_reason" class="text-[10px] text-gray-500 italic max-w-[160px] truncate" :title="claim.rejection_reason">
                    Reason: {{ claim.rejection_reason }}
                  </span>
                </div>
              </td>
              <!-- Actions -->
              <td class="py-4 px-6 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="openDetails(claim)" class="px-2.5 py-1.5 bg-gray-800 hover:bg-gray-700 border border-gray-700 hover:border-gray-600 text-xs font-semibold text-white rounded-xl transition flex-shrink-0">
                    Details
                  </button>
                  <select 
                    v-if="claim.status === 'pending'"
                    @change="triggerAction(claim, $event)"
                    class="bg-gray-950 border border-gray-800 rounded-xl px-3 py-1.5 text-xs text-gray-300 focus:outline-none focus:border-purple-500 cursor-pointer flex-shrink-0"
                  >
                    <option value="" disabled selected>Action</option>
                    <option value="approved">Approve</option>
                    <option value="rejected">Reject</option>
                  </select>
                  <span v-else class="text-xs text-gray-550 italic font-semibold flex-shrink-0">Processed</span>
                </div>
              </td>
            </tr>
            <tr v-if="!filteredItems.length && !loading">
              <td colspan="10" class="py-12 text-center text-gray-500">
                No verification claims matching your criteria.
              </td>
            </tr>
            <tr v-if="loading">
              <td colspan="10" class="py-12 text-center">
                <span class="w-6 h-6 border-2 border-purple-500/30 border-t-purple-500 rounded-full animate-spin inline-block"></span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Details & Edit Modal Overlay -->
    <div v-if="activeDetailClaim" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4 overflow-y-auto">
      <div class="bg-gray-900 border border-gray-800 rounded-[28px] w-full max-w-2xl shadow-2xl overflow-hidden my-8 max-h-[90vh] flex flex-col">
        
        <!-- Header / Banner preview -->
        <div class="relative h-32 bg-gray-950 border-b border-gray-800/80 flex-shrink-0">
          <img v-if="activeDetailClaim.banner_url" :src="activeDetailClaim.banner_url" class="w-full h-full object-cover" />
          <div v-else class="w-full h-full bg-gradient-to-r from-purple-950/40 to-cyan-950/40 flex items-center justify-center">
            <span class="text-xs text-gray-600 font-mono">No banner image submitted</span>
          </div>
          <!-- Logo absolute overlap -->
          <div class="absolute -bottom-6 left-6 w-16 h-16 rounded-2xl bg-gray-950 border-2 border-gray-800 overflow-hidden flex items-center justify-center shadow-lg">
            <img v-if="activeDetailClaim.logo_url" :src="activeDetailClaim.logo_url" class="w-full h-full object-contain p-1" />
            <span v-else class="text-xs font-black text-cyan-400">{{ activeDetailClaim.asset_code?.slice(0, 2).toUpperCase() }}</span>
          </div>
        </div>

        <div class="px-6 pt-8 pb-4 flex-shrink-0 border-b border-gray-800/80">
          <div class="flex items-start justify-between">
            <div>
              <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <span>{{ activeDetailClaim.name }}</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                  {{ activeDetailClaim.asset_code }}
                </span>
              </h3>
              <p class="text-[10px] font-mono text-gray-500 mt-1 select-all">{{ activeDetailClaim.asset_issuer }}</p>
            </div>
            <div>
              <button 
                @click="isEditing = !isEditing"
                class="px-4 py-1.5 rounded-xl text-xs font-bold transition border"
                :class="isEditing ? 'border-amber-500/30 bg-amber-950/20 text-amber-400' : 'border-gray-800 bg-gray-850 text-gray-300 hover:bg-gray-800'"
              >
                {{ isEditing ? 'Cancel Edit' : 'Edit Details' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Scrollable Details/Form container -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">

          <div v-if="editError" class="p-3.5 bg-rose-950/20 border border-rose-500/20 rounded-2xl text-xs text-rose-400 font-mono">
            {{ editError }}
          </div>

          <!-- Edit Mode Form -->
          <div v-if="isEditing" class="space-y-4 text-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-mono font-bold text-gray-500 uppercase mb-1.5">Project Name</label>
                <input type="text" v-model="editForm.name" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500" />
              </div>
              <div>
                <label class="block text-[10px] font-mono font-bold text-gray-500 uppercase mb-1.5">Category</label>
                <input type="text" v-model="editForm.category" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500" />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-mono font-bold text-gray-500 uppercase mb-1.5">Launch Date</label>
                <input type="text" v-model="editForm.launch_date" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500" />
              </div>
              <div>
                <label class="block text-[10px] font-mono font-bold text-gray-500 uppercase mb-1.5">Logo URL</label>
                <input type="text" v-model="editForm.logo_url" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white font-mono text-[10px] focus:outline-none focus:border-purple-500" />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-mono font-bold text-gray-500 uppercase mb-1.5">Banner URL</label>
                <input type="text" v-model="editForm.banner_url" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white font-mono text-[10px] focus:outline-none focus:border-purple-500" />
              </div>
              <div>
                <label class="block text-[10px] font-mono font-bold text-gray-500 uppercase mb-1.5">Official Email</label>
                <input type="email" v-model="editForm.official_email" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500" />
              </div>
            </div>

            <div>
              <label class="block text-[10px] font-mono font-bold text-gray-500 uppercase mb-1.5">Short Description</label>
              <input type="text" v-model="editForm.short_description" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500" />
            </div>

            <div>
              <label class="block text-[10px] font-mono font-bold text-gray-500 uppercase mb-1.5">Full Description</label>
              <textarea v-model="editForm.full_description" rows="3" class="w-full bg-gray-950 border border-gray-800 rounded-xl p-3 text-white focus:outline-none focus:border-purple-500 resize-none"></textarea>
            </div>

            <!-- Links -->
            <div class="border-t border-gray-800/80 pt-4 space-y-3">
              <h4 class="font-bold text-gray-400 uppercase tracking-wider text-[10px]">Official Links</h4>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <input type="text" v-model="editForm.website_link" placeholder="Website Link" class="bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500" />
                <input type="text" v-model="editForm.documentation_link" placeholder="Documentation Link" class="bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500" />
                <input type="text" v-model="editForm.whitepaper_link" placeholder="Whitepaper Link" class="bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500" />
                <input type="text" v-model="editForm.github_link" placeholder="GitHub Repository" class="bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500" />
                <input type="text" v-model="editForm.medium_link" placeholder="Medium / Blog" class="bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500" />
              </div>
            </div>

            <!-- Socials -->
            <div class="border-t border-gray-800/80 pt-4 space-y-3">
              <h4 class="font-bold text-gray-400 uppercase tracking-wider text-[10px]">Social Channels</h4>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <input type="text" v-model="editForm.twitter_link" placeholder="Twitter (X) Link" class="bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500" />
                <input type="text" v-model="editForm.telegram_link" placeholder="Telegram Community" class="bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500" />
                <input type="text" v-model="editForm.discord_link" placeholder="Discord Link" class="bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500" />
                <input type="text" v-model="editForm.linkedin_link" placeholder="LinkedIn Page" class="bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500" />
                <input type="text" v-model="editForm.reddit_link" placeholder="Reddit Subreddit" class="bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500" />
                <input type="text" v-model="editForm.youtube_link" placeholder="YouTube Channel" class="bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500" />
              </div>
            </div>

            <!-- Wallets Edit -->
            <div class="border-t border-gray-800/80 pt-4 space-y-3">
              <div class="flex items-center justify-between">
                <h4 class="font-bold text-gray-400 uppercase tracking-wider text-[10px]">Official Wallets</h4>
                <button @click="addEditWallet" type="button" class="px-2.5 py-1 bg-cyan-500/10 hover:bg-cyan-500/20 border border-cyan-500/20 text-cyan-400 rounded-lg font-bold text-[10px] transition">
                  + Add Wallet
                </button>
              </div>
              <div class="space-y-2">
                <div v-for="(w, idx) in editForm.wallets" :key="idx" class="flex gap-2 items-center">
                  <input type="text" v-model="w.label" placeholder="Label" class="w-1/4 bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500" />
                  <input type="text" v-model="w.wallet_address" placeholder="Stellar Wallet Address" class="flex-1 bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-white font-mono text-[10px] focus:outline-none focus:border-purple-500" />
                  <button @click="removeEditWallet(idx)" type="button" class="px-3 py-1.5 text-rose-500 border border-gray-850 hover:bg-rose-500/10 rounded-xl transition">✕</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Read-only Details View -->
          <div v-else class="space-y-5 text-sm">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <span class="block text-[10px] font-mono font-bold text-gray-500 uppercase tracking-wider mb-0.5">Category</span>
                <span class="text-white font-semibold">{{ activeDetailClaim.category || '—' }}</span>
              </div>
              <div>
                <span class="block text-[10px] font-mono font-bold text-gray-500 uppercase tracking-wider mb-0.5">Launch Date</span>
                <span class="text-white font-semibold">{{ activeDetailClaim.launch_date || '—' }}</span>
              </div>
            </div>

            <div>
              <span class="block text-[10px] font-mono font-bold text-gray-500 uppercase tracking-wider mb-0.5">Official Email</span>
              <span class="text-white font-semibold font-mono">{{ activeDetailClaim.official_email || '—' }}</span>
            </div>

            <div>
              <span class="block text-[10px] font-mono font-bold text-gray-500 uppercase tracking-wider mb-0.5">Short Description</span>
              <p class="text-gray-300 leading-relaxed">{{ activeDetailClaim.short_description || '—' }}</p>
            </div>

            <div>
              <span class="block text-[10px] font-mono font-bold text-gray-500 uppercase tracking-wider mb-0.5">Full Description</span>
              <p class="text-gray-300 leading-relaxed whitespace-pre-wrap">{{ activeDetailClaim.full_description || '—' }}</p>
            </div>

            <!-- Links list -->
            <div class="border-t border-gray-800/80 pt-4 space-y-2">
              <span class="block text-[10px] font-mono font-bold text-gray-500 uppercase tracking-wider">Submitted Links</span>
              <div class="flex flex-wrap gap-2.5 pt-1">
                <a v-if="activeDetailClaim.website_link" :href="activeDetailClaim.website_link" target="_blank" class="px-3 py-1.5 rounded-xl border border-gray-800 hover:border-gray-700 bg-gray-950/40 text-xs text-cyan-400 hover:text-cyan-300 transition">Website</a>
                <a v-if="activeDetailClaim.documentation_link" :href="activeDetailClaim.documentation_link" target="_blank" class="px-3 py-1.5 rounded-xl border border-gray-800 hover:border-gray-700 bg-gray-950/40 text-xs text-cyan-400 hover:text-cyan-300 transition">Documentation</a>
                <a v-if="activeDetailClaim.whitepaper_link" :href="activeDetailClaim.whitepaper_link" target="_blank" class="px-3 py-1.5 rounded-xl border border-gray-800 hover:border-gray-700 bg-gray-950/40 text-xs text-cyan-400 hover:text-cyan-300 transition">Whitepaper</a>
                <a v-if="activeDetailClaim.github_link" :href="activeDetailClaim.github_link" target="_blank" class="px-3 py-1.5 rounded-xl border border-gray-800 hover:border-gray-700 bg-gray-950/40 text-xs text-cyan-400 hover:text-cyan-300 transition">GitHub</a>
                <a v-if="activeDetailClaim.medium_link" :href="activeDetailClaim.medium_link" target="_blank" class="px-3 py-1.5 rounded-xl border border-gray-800 hover:border-gray-700 bg-gray-950/40 text-xs text-cyan-400 hover:text-cyan-300 transition">Medium/Blog</a>
              </div>
            </div>

            <!-- Socials -->
            <div class="border-t border-gray-800/80 pt-4 space-y-2">
              <span class="block text-[10px] font-mono font-bold text-gray-500 uppercase tracking-wider">Social Channels</span>
              <div class="flex flex-wrap gap-2.5 pt-1">
                <a v-if="activeDetailClaim.twitter_link" :href="activeDetailClaim.twitter_link" target="_blank" class="px-3 py-1.5 rounded-xl border border-gray-800 hover:border-gray-700 bg-gray-950/40 text-xs text-purple-400 hover:text-purple-300 transition">X (Twitter)</a>
                <a v-if="activeDetailClaim.telegram_link" :href="activeDetailClaim.telegram_link" target="_blank" class="px-3 py-1.5 rounded-xl border border-gray-800 hover:border-gray-700 bg-gray-950/40 text-xs text-purple-400 hover:text-purple-300 transition">Telegram</a>
                <a v-if="activeDetailClaim.discord_link" :href="activeDetailClaim.discord_link" target="_blank" class="px-3 py-1.5 rounded-xl border border-gray-800 hover:border-gray-700 bg-gray-950/40 text-xs text-purple-400 hover:text-purple-300 transition">Discord</a>
                <a v-if="activeDetailClaim.linkedin_link" :href="activeDetailClaim.linkedin_link" target="_blank" class="px-3 py-1.5 rounded-xl border border-gray-800 hover:border-gray-700 bg-gray-950/40 text-xs text-purple-400 hover:text-purple-300 transition">LinkedIn</a>
                <a v-if="activeDetailClaim.reddit_link" :href="activeDetailClaim.reddit_link" target="_blank" class="px-3 py-1.5 rounded-xl border border-gray-800 hover:border-gray-700 bg-gray-950/40 text-xs text-purple-400 hover:text-purple-300 transition">Reddit</a>
                <a v-if="activeDetailClaim.youtube_link" :href="activeDetailClaim.youtube_link" target="_blank" class="px-3 py-1.5 rounded-xl border border-gray-800 hover:border-gray-700 bg-gray-950/40 text-xs text-purple-400 hover:text-purple-300 transition">YouTube</a>
              </div>
            </div>

            <!-- Official Wallets -->
            <div class="border-t border-gray-800/80 pt-4 space-y-2">
              <span class="block text-[10px] font-mono font-bold text-gray-500 uppercase tracking-wider mb-2">Official Wallets</span>
              <div class="space-y-1.5 max-h-[160px] overflow-y-auto pr-1">
                <div v-for="w in activeDetailClaim.wallets" :key="w.id" class="flex items-center justify-between p-2.5 bg-gray-950/50 border border-gray-800 rounded-xl">
                  <span class="font-bold text-xs text-white uppercase tracking-wide bg-gray-900 border border-gray-800 px-2 py-0.5 rounded-md">{{ w.label }}</span>
                  <span class="font-mono text-xs text-gray-400 select-all">{{ w.wallet_address }}</span>
                </div>
                <div v-if="!activeDetailClaim.wallets || activeDetailClaim.wallets.length === 0" class="text-xs text-gray-500 italic py-2 text-center border border-dashed border-gray-800 rounded-xl">
                  No official wallets configured.
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer actions -->
        <div class="px-6 py-4 bg-gray-950/50 border-t border-gray-800/80 flex justify-end gap-3 flex-shrink-0">
          <button 
            @click="activeDetailClaim = null" 
            class="px-5 py-2 rounded-xl text-xs font-bold text-gray-400 border border-gray-805 hover:bg-gray-850 transition"
          >
            Close
          </button>
          <button 
            v-if="isEditing"
            @click="saveDetails" 
            :disabled="savingEdit"
            class="px-5 py-2 bg-purple-600 hover:bg-purple-500 disabled:opacity-50 text-xs font-bold text-white rounded-xl transition shadow-lg shadow-purple-500/10"
          >
            {{ savingEdit ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>

      </div>
    </div>

    <!-- Custom Confirmation Dialog Modal Overlay -->
    <div v-if="activeClaimForAction" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-6 animate-fade-in">
        <div class="space-y-2 text-center">
          <h3 class="text-lg font-bold text-white uppercase tracking-wider">
            Confirm {{ chosenAction === 'approved' ? 'Approval' : 'Rejection' }}
          </h3>
          <p class="text-xs text-gray-400">
            Are you sure you want to 
            <span :class="chosenAction === 'approved' ? 'text-green-400 font-bold' : 'text-red-400 font-bold'">
              {{ chosenAction }}
            </span> 
            verification status for project <strong>{{ activeClaimForAction.name }}</strong> ({{ activeClaimForAction.asset_code }})?
          </p>
        </div>

        <!-- Rejection Reason input box -->
        <div v-if="chosenAction === 'rejected'" class="space-y-2">
          <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest block">Rejection Reason</label>
          <textarea 
            v-model="rejectionReason" 
            placeholder="Provide a brief explanation for rejection (e.g. Insufficient pool depth, mismatched asset details)..."
            class="w-full bg-gray-950 border border-gray-800 rounded-2xl p-3 text-xs focus:outline-none focus:border-red-500 text-white h-20 resize-none"
          ></textarea>
        </div>

        <!-- Actions -->
        <div class="flex gap-4">
          <button 
            @click="cancelAction" 
            class="flex-1 py-3 text-xs font-bold uppercase tracking-widest text-gray-400 border border-gray-800 rounded-xl hover:bg-gray-800 transition"
          >
            Cancel
          </button>
          <button 
            @click="confirmAction" 
            class="flex-1 py-3 text-xs font-bold uppercase tracking-widest text-white rounded-xl transition shadow-lg"
            :class="chosenAction === 'approved' ? 'bg-green-600 hover:bg-green-500 shadow-green-500/10' : 'bg-red-600 hover:bg-red-500 shadow-red-500/10'"
          >
            Confirm
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const items = ref([]);
const loading = ref(false);
const filterStatus = ref('all');
const searchQuery = ref('');

const sortKey = ref('created_at');
const sortAsc = ref(false);

function sortBy(key) {
  if (sortKey.value === key) {
    sortAsc.value = !sortAsc.value;
  } else {
    sortKey.value = key;
    sortAsc.value = true;
  }
}

// Dialog logic states
const activeClaimForAction = ref(null);
const chosenAction = ref('');
const rejectionReason = ref('');

// Details & Editing refs
const activeDetailClaim = ref(null);
const isEditing = ref(false);
const savingEdit = ref(false);
const editError = ref('');
const editForm = ref({
  name: '',
  official_email: '',
  short_description: '',
  full_description: '',
  category: '',
  launch_date: '',
  logo_url: '',
  banner_url: '',
  website_link: '',
  documentation_link: '',
  whitepaper_link: '',
  github_link: '',
  medium_link: '',
  twitter_link: '',
  telegram_link: '',
  discord_link: '',
  linkedin_link: '',
  reddit_link: '',
  youtube_link: '',
  wallets: []
});

function openDetails(claim) {
  activeDetailClaim.value = claim;
  isEditing.value = false;
  editError.value = '';
  editForm.value = {
    name: claim.name || '',
    official_email: claim.official_email || '',
    short_description: claim.short_description || '',
    full_description: claim.full_description || '',
    category: claim.category || '',
    launch_date: claim.launch_date || '',
    logo_url: claim.logo_url || '',
    banner_url: claim.banner_url || '',
    website_link: claim.website_link || '',
    documentation_link: claim.documentation_link || '',
    whitepaper_link: claim.whitepaper_link || '',
    github_link: claim.github_link || '',
    medium_link: claim.medium_link || '',
    twitter_link: claim.twitter_link || '',
    telegram_link: claim.telegram_link || '',
    discord_link: claim.discord_link || '',
    linkedin_link: claim.linkedin_link || '',
    reddit_link: claim.reddit_link || '',
    youtube_link: claim.youtube_link || '',
    wallets: claim.wallets ? JSON.parse(JSON.stringify(claim.wallets)) : []
  };
}

function addEditWallet() {
  editForm.value.wallets.push({ wallet_address: '', label: 'Treasury' });
}

function removeEditWallet(idx) {
  editForm.value.wallets.splice(idx, 1);
}

async function saveDetails() {
  savingEdit.value = true;
  editError.value = '';
  try {
    const res = await axios.post(`/api/admin/verifications/${activeDetailClaim.value.id}/edit`, editForm.value);
    if (res.data.status === 'success') {
      const idx = items.value.findIndex(i => i.id === activeDetailClaim.value.id);
      if (idx !== -1) {
        items.value[idx] = {
          ...items.value[idx],
          ...editForm.value
        };
        activeDetailClaim.value = items.value[idx];
      }
      isEditing.value = false;
    } else {
      editError.value = res.data.message || 'Failed to update details.';
    }
  } catch (err) {
    console.error(err);
    editError.value = err.response?.data?.message || 'Connection error while saving.';
  } finally {
    savingEdit.value = false;
  }
}

// Dynamic counts
const totalCount = computed(() => items.value.length);
const approvedCount = computed(() => items.value.filter(i => i.status === 'approved').length);
const pendingCount = computed(() => items.value.filter(i => i.status === 'pending').length);
const rejectedCount = computed(() => items.value.filter(i => i.status === 'rejected').length);

const defaultClaims = [
  {
    id: 1,
    name: 'Aquarius',
    asset_code: 'AQUA',
    asset_issuer: 'GBNZILSTVQZ4R7IKQDGHYGY2QXL5QOFJYQMXPKWRRM5PAV7Y4M67AQUA',
    sender_wallet: 'GD3U7Z3...Z7AQUA',
    payment_tx: '8c65b1d5bfceef19f96bca82f48f49ecff72a5a54db52cf7c1c1f1fcf5432abc',
    logo_url: 'https://aqua.network/logo.png',
    payment_asset: 'TKG',
    payment_amount: 500,
    status: 'approved',
    created_at: '2026-06-25T12:00:00Z',
    updated_at: '2026-06-25T14:30:00Z'
  },
  {
    id: 2,
    name: 'Yield XLM',
    asset_code: 'yXLM',
    asset_issuer: 'GARDNV3Q7YGT4AKSDF25LT32YSCCW4EV22Y2TV3I2PU2MMXJTEDL5T55',
    sender_wallet: 'GCT2YK3...PU2MM',
    payment_tx: 'e8e19b5b9f7c1d7bfd19bc82f48f49ecff72a5a54db52cf7c1c1f1fcf543cdef',
    logo_url: 'https://ultracapital.xyz/yXLM-logo.png',
    payment_asset: 'XLM',
    payment_amount: 150,
    status: 'pending',
    created_at: '2026-07-04T15:30:00Z',
    updated_at: '2026-07-04T15:30:00Z'
  },
  {
    id: 3,
    name: 'Stronghold Token',
    asset_code: 'SHX',
    asset_issuer: 'GDSTRSHXHGJ7ZIVRBXEYE5Q74XUVCUSEKEBR7UCHEUUEK72N7I7KJ6JH',
    sender_wallet: 'GA8SHX7...7KJ6J',
    payment_tx: '16af91ba42617f1a3028d7a8f4c2c5ecff72a5a54db52cf7c1c1f1fcf543defb',
    logo_url: 'https://stronghold.co/shx.png',
    payment_asset: 'USDC',
    payment_amount: 25,
    status: 'pending',
    created_at: '2026-07-06T09:12:00Z',
    updated_at: '2026-07-06T09:12:00Z'
  },
  {
    id: 4,
    name: 'Spam Token Plus',
    asset_code: 'SPAM',
    asset_issuer: 'GSPAMNZILSTVQZ4R7IKQDGHYGY2QXL5QOFJYQMXPKWRRM5PAV7Y4M67SPAM',
    sender_wallet: 'GSPAMZ3...Z7SPM',
    payment_tx: '49da10eb4261fef193fbc82f48f49ecff72a5a54db52cf7c1c1f1fcf543abcd',
    logo_url: '',
    payment_asset: 'TKG',
    payment_amount: 500,
    status: 'rejected',
    rejection_reason: 'Domain mismatch and high risk factor detected.',
    created_at: '2026-06-20T10:15:00Z',
    updated_at: '2026-06-21T08:00:00Z'
  }
];

const filteredItems = computed(() => {
  let list = items.value;
  if (filterStatus.value !== 'all') {
    list = list.filter(i => i.status === filterStatus.value);
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter(i => 
      i.name.toLowerCase().includes(q) || 
      i.asset_code.toLowerCase().includes(q)
    );
  }

  return [...list].sort((a, b) => {
    let valA = a[sortKey.value];
    let valB = b[sortKey.value];

    if (valA === null || valA === undefined) return sortAsc.value ? -1 : 1;
    if (valB === null || valB === undefined) return sortAsc.value ? 1 : -1;

    if (sortKey.value === 'created_at' || sortKey.value === 'updated_at') {
      valA = new Date(valA).getTime();
      valB = new Date(valB).getTime();
    }

    if (valA < valB) return sortAsc.value ? -1 : 1;
    if (valA > valB) return sortAsc.value ? 1 : -1;
    return 0;
  });
});

async function loadData() {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/admin/verifications');
    if (data.status === 'success') {
      items.value = data.data;
    } else {
      items.value = defaultClaims;
    }
  } catch (err) {
    console.warn('API /api/admin/verifications unavailable, falling back to default claims list.');
    items.value = defaultClaims;
  } finally {
    loading.value = false;
  }
}

function triggerAction(claim, event) {
  const selected = event.target.value;
  if (!selected) return;
  activeClaimForAction.value = claim;
  chosenAction.value = selected;
  rejectionReason.value = '';
}

function cancelAction() {
  activeClaimForAction.value = null;
  chosenAction.value = '';
  rejectionReason.value = '';
  // Force reset target elements in select fields
  const selects = document.querySelectorAll('select');
  selects.forEach(s => { s.value = ''; });
}

async function confirmAction() {
  if (!activeClaimForAction.value) return;
  const id = activeClaimForAction.value.id;
  const newStatus = chosenAction.value;
  const reason = rejectionReason.value;

  try {
    const response = await axios.post(`/api/admin/verifications/${id}/status`, {
      status: newStatus,
      rejection_reason: reason
    });
    if (response.data.status === 'success') {
      updateLocalClaim(id, newStatus, reason);
    }
  } catch (err) {
    console.warn('Status update API call failed. Simulating status update locally.');
    updateLocalClaim(id, newStatus, reason);
  } finally {
    cancelAction();
  }
}

function updateLocalClaim(id, newStatus, reason) {
  const idx = items.value.findIndex(item => item.id === id);
  if (idx !== -1) {
    items.value[idx].status = newStatus;
    items.value[idx].updated_at = new Date().toISOString();
    if (newStatus === 'rejected') {
      items.value[idx].rejection_reason = reason;
    }
  }
}

function formatDate(isoStr) {
  if (!isoStr) return '—';
  return new Date(isoStr).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function shortAddr(addr) {
  if (!addr) return '—';
  return addr.length > 12 ? `${addr.slice(0, 6)}...${addr.slice(-6)}` : addr;
}

function shortHash(hash) {
  if (!hash) return '—';
  return hash.length > 10 ? `${hash.slice(0, 5)}...${hash.slice(-5)}` : hash;
}

onMounted(() => {
  loadData();
});
</script>
