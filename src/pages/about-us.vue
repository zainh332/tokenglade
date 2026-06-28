<template>
  <div class="smooth-scroll-wrapper bg-[#0B1020] min-h-screen text-white font-sans selection:bg-purple-500/30 selection:text-white">
    <Header />
    
    <!-- SECTION 1 (DARK): Hero Section -->
    <div class="relative overflow-hidden pt-40 pb-24 lg:pt-48 lg:pb-32 bg-[#0B1020]">
      <!-- Radial blue wash background glow -->
      <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-blue-600/10 rounded-full blur-[160px] pointer-events-none animate-pulse-slow"></div>
      <div class="absolute -bottom-40 left-10 w-[500px] h-[500px] bg-cyan-500/5 rounded-full blur-[140px] pointer-events-none"></div>

      <div class="max-w-6xl mx-auto px-6 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">
          
          <!-- Hero Left Content -->
          <div class="lg:col-span-7 text-center lg:text-left space-y-6">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gradient-to-r from-purple-500/10 to-cyan-500/10 border border-purple-500/20 text-xs font-black uppercase tracking-widest text-purple-400">
              The Story of TokenGlade
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-tight text-white tracking-tight">
              Building Better <br />
              <span class="bg-gradient-to-r from-cyan-400 via-purple-400 to-pink-400 bg-clip-text text-transparent bg-[length:200%_200%] animate-gradientMove">
                Blockchain Tools
              </span>
            </h1>
            <p class="text-gray-400 text-lg md:text-xl font-normal leading-relaxed max-w-xl mx-auto lg:mx-0">
              TokenGlade is building products that make blockchain simpler, more transparent, and easier to use for everyone.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-4">          
              <a 
                href="/whitepaper.pdf" target="_blank"
                class="w-full sm:w-auto inline-flex items-center justify-center font-black text-xs uppercase tracking-widest px-8 py-4 rounded-full text-gray-300 border border-gray-800 hover:border-purple-500/30 hover:bg-gray-900/60 hover:text-white hover:scale-[1.03] active:scale-[0.97] transition-all transform duration-200"
              >
                Read Documentation
              </a>
            </div>
          </div>

          <!-- Hero Right Content: Connected Ecosystem illustration (No Dashboard) -->
          <div class="lg:col-span-5 relative flex items-center justify-center">
            <div class="w-full max-w-[400px] aspect-square bg-[#0b0f19] border border-gray-850 rounded-[2.5rem] p-8 shadow-2xl relative overflow-hidden flex items-center justify-center group hover:border-cyan-500/30 transition-all duration-500">
              <svg class="w-4/5 h-4/5 text-purple-500/20" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="0.75">
                <!-- Outer abstract network circles -->
                <circle cx="50" cy="50" r="35" stroke-dasharray="2 3" class="animate-spin-slow" />
                <circle cx="50" cy="50" r="20" stroke-dasharray="3 4" class="animate-spin-reverse" />
                <!-- Connecting lines representing people and integration -->
                <line x1="50" y1="15" x2="50" y2="85" stroke-dasharray="2 2" />
                <line x1="15" y1="50" x2="85" y2="50" stroke-dasharray="2 2" />
                <path d="M25,25 Q50,35 75,25" stroke="rgba(6,182,212,0.4)" stroke-width="1" fill="none" />
                <path d="M25,75 Q50,65 75,75" stroke="rgba(168,85,247,0.4)" stroke-width="1" fill="none" />
                <!-- Connected Nodes -->
                <circle cx="50" cy="15" r="3.5" fill="#a855f7" />
                <circle cx="50" cy="85" r="3.5" fill="#a855f7" />
                <circle cx="15" cy="50" r="3.5" fill="#06b6d4" />
                <circle cx="85" cy="50" r="3.5" fill="#06b6d4" />
                <circle cx="50" cy="50" r="7" fill="url(#brandGrad)" />
              </svg>
              <!-- Gradient definitions -->
              <svg class="w-0 h-0">
                <defs>
                  <linearGradient id="brandGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#06b6d4" />
                    <stop offset="100%" stop-color="#8b5cf6" />
                  </linearGradient>
                </defs>
              </svg>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- SECTION 2 (WHITE): Our Story & Why We Started -->
    <div class="bg-white text-gray-900 py-28 border-t border-b border-gray-100">
      <div class="max-w-4xl mx-auto px-6 space-y-32">
        
        <!-- Our Story Narrative Blocks -->
        <div class="space-y-16">
          <div class="space-y-3">
            <span class="text-xs text-purple-600 font-extrabold uppercase tracking-widest block">Narrative</span>
            <h2 class="text-3xl sm:text-4xl font-black text-gray-950 tracking-tight">Our Story</h2>
          </div>

          <div class="space-y-12">
            <!-- Origin Block -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
              <div class="space-y-4">
                <h4 class="text-lg font-black text-gray-900">How TokenGlade Started</h4>
                <p class="text-gray-650 text-sm leading-relaxed">
                  In 2026, we noticed a persistent barrier holding back creators in blockchain. Deploying basic tokens, configuring trustlines, and setting up reward snapshots required complex developer setups. TokenGlade was conceived as a bridge to remove that friction.
                </p>
              </div>
              <div class="bg-gray-50 border border-gray-150 rounded-2xl p-6 aspect-[4/3] flex items-center justify-center shadow-inner">
                <span class="text-4xl">🌱</span>
              </div>
            </div>

            <!-- Problem Block -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center md:flex-row-reverse">
              <div class="bg-gray-55 border border-gray-150 rounded-2xl p-6 aspect-[4/3] flex items-center justify-center shadow-inner md:order-last">
                <span class="text-4xl">⚙️</span>
              </div>
              <div class="space-y-4">
                <h4 class="text-lg font-black text-gray-900">Why Blockchain is Too Complicated</h4>
                <p class="text-gray-650 text-sm leading-relaxed">
                  Existing tools are built by engineers, for engineers. Startup founders, local communities, and artists shouldn't need deep smart contract mastery just to distribute utility tokens or track stakers.
                </p>
              </div>
            </div>

            <!-- Vision Block -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
              <div class="space-y-4">
                <h4 class="text-lg font-black text-gray-900">Why TokenGlade Exists</h4>
                <p class="text-gray-650 text-sm leading-relaxed">
                  We exist to build user-centric interfaces. By combining visual asset creators, clean analytics streams, and transparent distribution models, we make blockchain integration immediate and reliable.
                </p>
              </div>
              <div class="bg-gray-50 border border-gray-150 rounded-2xl p-6 aspect-[4/3] flex items-center justify-center shadow-inner">
                <span class="text-4xl">✨</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Why We Started (Three Large Cards) -->
        <div class="space-y-12">
          <div class="text-center space-y-3">
            <span class="text-xs text-purple-600 font-extrabold uppercase tracking-widest block">Motivation</span>
            <h3 class="text-3xl font-black text-gray-950">Why We Started</h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Complexity Card -->
            <div class="bg-white border border-gray-150 rounded-2xl p-6 space-y-4 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between min-h-[220px]">
              <div class="w-10 h-10 rounded-full bg-cyan-50 flex items-center justify-center text-cyan-600 font-black">🧩</div>
              <div class="space-y-2">
                <h4 class="text-sm font-black text-gray-900">Complexity</h4>
                <p class="text-[11px] text-gray-500 leading-relaxed">Blockchain shouldn't require technical expertise. We simplify interactions so you can focus on building your project.</p>
              </div>
            </div>

            <!-- Transparency Card -->
            <div class="bg-white border border-gray-150 rounded-2xl p-6 space-y-4 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between min-h-[220px]">
              <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-600 font-black">👁️</div>
              <div class="space-y-2">
                <h4 class="text-sm font-black text-gray-900">Transparency</h4>
                <p class="text-[11px] text-gray-500 leading-relaxed">Everything should be verifiable on-chain. We display real-time transaction updates, trustlines, and reward cycles.</p>
              </div>
            </div>

            <!-- Accessibility Card -->
            <div class="bg-white border border-gray-150 rounded-2xl p-6 space-y-4 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between min-h-[220px]">
              <div class="w-10 h-10 rounded-full bg-pink-50 flex items-center justify-center text-pink-600 font-black">🌐</div>
              <div class="space-y-2">
                <h4 class="text-sm font-black text-gray-900">Accessibility</h4>
                <p class="text-[11px] text-gray-500 leading-relaxed">Professional blockchain tools should be available to everyone, regardless of funding, location, or programming background.</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- SECTION 3 (DARK): Our Philosophy & Journey Timeline -->
    <div class="bg-[#0B1020] text-white py-28 relative overflow-hidden">
      <!-- Purple Neon Glow Background -->
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[350px] bg-purple-650/10 rounded-full blur-[150px] pointer-events-none"></div>

      <div class="max-w-4xl mx-auto px-6 space-y-32 relative z-10">
        
        <!-- Philosophy (Quote on Left, Text on Right) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
          <div class="space-y-4">
            <span class="text-[3rem] leading-none text-purple-400 font-black block">“</span>
            <p class="text-2xl sm:text-3xl font-black text-white leading-snug">
              We believe blockchain succeeds when it becomes invisible to the user.
            </p>
          </div>
          <div class="space-y-4 text-gray-400 text-sm leading-relaxed">
            <h4 class="text-sm font-black text-cyan-400 uppercase tracking-widest">Our Philosophy</h4>
            <p>
              Tech should empower, not distract. When users have to copy-paste hexadecimal hashes, check network configuration settings, or wait for complex validation codes, products break.
            </p>
            <p>
              By engineering tools that operate behind intuitive, lightning-fast interfaces, TokenGlade hides the complexity of smart contracts while preserving complete, on-chain cryptographic validation.
            </p>
          </div>
        </div>

        <!-- Vertical Journey -->
        <div class="space-y-16">
          <div class="text-center space-y-3">
            <span class="text-xs text-cyan-400 font-extrabold uppercase tracking-widest block">Milestones</span>
            <h3 class="text-3xl font-black text-white">Our Journey</h3>
          </div>

          <!-- Vertical Journey Timeline -->
          <div class="relative max-w-xl mx-auto pl-8 border-l-2 border-dashed border-gray-800 space-y-12">
            
            <!-- Milestone 1 -->
            <div class="relative space-y-2">
              <span class="absolute left-[-39px] top-[4px] w-4.5 h-4.5 rounded-full bg-cyan-400 border-4 border-gray-950"></span>
              <span class="text-xs font-black text-cyan-400 block uppercase font-mono">2026 Early</span>
              <h4 class="text-base font-bold text-white">TokenGlade Idea</h4>
              <p class="text-xs text-gray-500 leading-normal max-w-md">Origin planning for simple asset management tools, aiming to eliminate complex smart contracts configs.</p>
            </div>

            <!-- Milestone 2 -->
            <div class="relative space-y-2">
              <span class="absolute left-[-39px] top-[4px] w-4.5 h-4.5 rounded-full bg-cyan-400 border-4 border-gray-950"></span>
              <span class="text-xs font-black text-cyan-400 block uppercase font-mono">2026 Spring</span>
              <h4 class="text-base font-bold text-white">First Token Launched</h4>
              <p class="text-xs text-gray-500 leading-normal max-w-md">No-code asset minting contracts successfully live on Stellar main network.</p>
            </div>

            <!-- Milestone 3 -->
            <div class="relative space-y-2">
              <span class="absolute left-[-39px] top-[4px] w-4.5 h-4.5 rounded-full bg-purple-500 border-4 border-gray-950"></span>
              <span class="text-xs font-black text-purple-400 block uppercase font-mono">2026 Summer</span>
              <h4 class="text-base font-bold text-white">LP Rewards Released</h4>
              <p class="text-xs text-gray-500 leading-normal max-w-md">Ecosystem weekly reward snaps activated, routing TKG allocations transparently to LP providers.</p>
            </div>

            <!-- Milestone 4 -->
            <div class="relative space-y-2">
              <span class="absolute left-[-39px] top-[4px] w-4.5 h-4.5 rounded-full bg-gray-800 border-4 border-gray-950"></span>
              <span class="text-xs font-black text-gray-550 block uppercase font-mono">2026 Autumn</span>
              <h4 class="text-base font-bold text-white">Analytics Platform</h4>
              <p class="text-xs text-gray-550 leading-normal max-w-md">Verified token trackers and market activity charts integrated to monitor chain movements.</p>
            </div>

            <!-- Milestone 5 -->
            <div class="relative space-y-2">
              <span class="absolute left-[-39px] top-[4px] w-4.5 h-4.5 rounded-full bg-gray-800 border-4 border-gray-950"></span>
              <span class="text-xs font-black text-gray-550 block uppercase font-mono">2026 Winter</span>
              <h4 class="text-base font-bold text-white">Portfolio Tools</h4>
              <p class="text-xs text-gray-550 leading-normal max-w-md">Personal asset allocation breakdowns and claimable rewards overview dashboards.</p>
            </div>

            <!-- Milestone 6 -->
            <div class="relative space-y-2">
              <span class="absolute left-[-39px] top-[4px] w-4.5 h-4.5 rounded-full bg-gray-800 border-4 border-gray-950"></span>
              <span class="text-xs font-black text-gray-550 block uppercase font-mono">2027</span>
              <h4 class="text-base font-bold text-white">Multi-Chain Expansion</h4>
              <p class="text-xs text-gray-550 leading-normal max-w-md">Bridging assets outside Stellar into Ethereum, XRPL, and neighboring networks.</p>
            </div>

          </div>
        </div>

      </div>
    </div>

    <!-- SECTION 4 (WHITE): What We Build & Core Principles -->
    <div class="bg-white text-gray-900 py-28 border-t border-b border-gray-100">
      <div class="max-w-4xl mx-auto px-6 space-y-32">
        
        <!-- What We Build Product Categories -->
        <div class="space-y-12">
          <div class="text-center space-y-3">
            <span class="text-xs text-purple-600 font-extrabold uppercase tracking-widest block">Portfolio</span>
            <h3 class="text-3xl font-black text-gray-950">What We Build</h3>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
            <!-- Category 1 -->
            <div class="bg-gray-50 border border-gray-150 p-8 rounded-2xl flex flex-col justify-between min-h-[220px] shadow-sm">
              <div class="w-12 h-12 rounded-full bg-cyan-100 flex items-center justify-center text-cyan-600 text-xl font-bold">🏗️</div>
              <div class="space-y-2">
                <h4 class="text-base font-black text-gray-900">Token Infrastructure</h4>
                <p class="text-xs text-gray-500 leading-relaxed">Launch and manage digital assets directly on-chain without coding. Ensure reliable parameters and custom trustline integrations.</p>
              </div>
            </div>

            <!-- Category 2 -->
            <div class="bg-gray-50 border border-gray-150 p-8 rounded-2xl flex flex-col justify-between min-h-[220px] shadow-sm">
              <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-xl font-bold">📈</div>
              <div class="space-y-2">
                <h4 class="text-base font-black text-gray-900">Analytics</h4>
                <p class="text-xs text-gray-500 leading-relaxed">Understand blockchain activity through meaningful insights. Track verified token movements and custom summaries instantly.</p>
              </div>
            </div>

            <!-- Category 3 -->
            <div class="bg-gray-50 border border-gray-150 p-8 rounded-2xl flex flex-col justify-between min-h-[220px] shadow-sm">
              <div class="w-12 h-12 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 text-xl font-bold">🎁</div>
              <div class="space-y-2">
                <h4 class="text-base font-black text-gray-900">Rewards</h4>
                <p class="text-xs text-gray-500 leading-relaxed">Enable transparent liquidity and staking programs. Lock assets to participate in automated yield Snapshot allocations.</p>
              </div>
            </div>

            <!-- Category 4 -->
            <div class="bg-gray-50 border border-gray-150 p-8 rounded-2xl flex flex-col justify-between min-h-[220px] shadow-sm">
              <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xl font-bold">🌍</div>
              <div class="space-y-2">
                <h4 class="text-base font-black text-gray-900">Multi-Chain Future</h4>
                <p class="text-xs text-gray-500 leading-relaxed">Expand beyond Stellar into additional blockchain ecosystems (Ethereum, XRPL) to unified cross-chain tooling.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Core Principles -->
        <div class="space-y-12">
          <div class="text-center space-y-3">
            <span class="text-xs text-purple-600 font-extrabold uppercase tracking-widest block">Philosophy</span>
            <h3 class="text-3xl font-black text-gray-950">Core Principles</h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white border border-gray-150 p-6 rounded-xl space-y-2 shadow-sm">
              <h4 class="text-sm font-black text-gray-900">Transparency</h4>
              <p class="text-xs text-gray-500 leading-relaxed font-normal">Everything verifiable. All staker rewards, token initial parameters, and cycles are verified directly on-chain.</p>
            </div>
            <div class="bg-white border border-gray-150 p-6 rounded-xl space-y-2 shadow-sm">
              <h4 class="text-sm font-black text-gray-900">Simplicity</h4>
              <p class="text-xs text-gray-500 leading-relaxed font-normal">Blockchain made understandable. We design interfaces that make operations obvious and clear to everyone.</p>
            </div>
            <div class="bg-white border border-gray-150 p-6 rounded-xl space-y-2 shadow-sm">
              <h4 class="text-sm font-black text-gray-900">Ownership</h4>
              <p class="text-xs text-gray-500 leading-relaxed font-normal">Users control their assets. Non-custodial interactions guarantee only you have access to your keys.</p>
            </div>
            <div class="bg-white border border-gray-150 p-6 rounded-xl space-y-2 shadow-sm">
              <h4 class="text-sm font-black text-gray-900">Long-Term Thinking</h4>
              <p class="text-xs text-gray-500 leading-relaxed font-normal">Build products that remain useful for years. Focus on core DeFi utility instead of chasing short-term speculation.</p>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- SECTION 5 (DARK): Meet the Founder & Looking Ahead Nodes Diagram -->
    <div class="bg-[#0B1020] text-white py-28 relative overflow-hidden">
      <!-- Blue & Cyan lighting treatment -->
      <div class="absolute -top-40 left-10 w-96 h-96 bg-cyan-500/10 rounded-full blur-[140px] pointer-events-none"></div>
      <div class="absolute -bottom-40 right-10 w-96 h-96 bg-blue-500/10 rounded-full blur-[140px] pointer-events-none"></div>

      <div class="max-w-4xl mx-auto px-6 space-y-32 relative z-10">
        
        <!-- Founder Profile Section (Q&A style) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
          <div class="lg:col-span-4 space-y-4">
            <div class="w-full bg-[#0b0f19] border border-gray-850 rounded-[2rem] p-6 text-center space-y-4 shadow-2xl relative overflow-hidden">
              <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-cyan-400 to-purple-500 mx-auto flex items-center justify-center text-3xl font-black text-white shadow-lg">
                ZH
              </div>
              <div>
                <h4 class="text-lg font-black text-white">Zain Haider</h4>
                <p class="text-xs text-cyan-400 font-bold uppercase tracking-wider">Software Engineer & Builder</p>
              </div>
              <div class="flex justify-center gap-4 text-xs font-bold text-gray-450 border-t border-gray-900 pt-4">
                <a href="https://github.com/zainh332" target="_blank" class="hover:text-white transition">GitHub</a>
                <a href="https://x.com/zainhaider332" target="_blank" class="hover:text-white transition">X</a>
              </div>
            </div>
          </div>

          <div class="lg:col-span-8 space-y-8">
            <div class="space-y-2">
              <span class="text-xs text-purple-400 font-extrabold uppercase tracking-widest block">Meet the Founder</span>
              <h3 class="text-3xl font-black text-white">Q&A with Zain Haider</h3>
            </div>
            
            <div class="space-y-6">
              <div class="space-y-1">
                <span class="text-[11px] text-cyan-400 font-black uppercase tracking-wider">Why did you build TokenGlade?</span>
                <p class="text-xs text-gray-450 leading-relaxed">
                  I wanted to build blockchain tools that emphasize core usability. Blockchain is incredibly powerful, but users shouldn't have to face complex CLI trustlines or script setups just to create or validate custom assets on Stellar.
                </p>
              </div>

              <div class="space-y-1">
                <span class="text-[11px] text-cyan-400 font-black uppercase tracking-wider">What are you obsessed with improving?</span>
                <p class="text-xs text-gray-450 leading-relaxed">
                  Making advanced DeFi metrics, like staker snap calculations and verified pools, load instantly and feel visually obvious to first-time developers and token creators.
                </p>
              </div>

              <div class="space-y-1">
                <span class="text-[11px] text-cyan-400 font-black uppercase tracking-wider">Where do you want TokenGlade to be in five years?</span>
                <p class="text-xs text-gray-450 leading-relaxed">
                  To serve as the primary no-code dashboard hub. We hope to support multiple chains (Ethereum, XRPL, and others), allowing users to bridge, mint, and review balances across ecosystems with identical simplicity.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Looking Ahead Nodes Map -->
        <div class="space-y-12">
          <div class="text-center space-y-3">
            <span class="text-xs text-cyan-400 font-extrabold uppercase tracking-widest block">Ecosystem Node Architecture</span>
            <h3 class="text-3xl font-black text-white">Looking Ahead</h3>
          </div>

          <div class="bg-gray-950/60 border border-gray-850 rounded-[2.5rem] p-12 min-h-[320px] flex items-center justify-center relative overflow-hidden shadow-2xl">
            <svg class="absolute inset-0 w-full h-full opacity-40 pointer-events-none" viewBox="0 0 100 100" preserveAspectRatio="none">
              <!-- Animated dash lines from center to outer nodes -->
              <line x1="50" y1="50" x2="15" y2="15" stroke="#06b6d4" stroke-width="0.75" stroke-dasharray="2 2" />
              <line x1="50" y1="50" x2="85" y2="15" stroke="#a855f7" stroke-width="0.75" stroke-dasharray="2 2" />
              <line x1="50" y1="50" x2="15" y2="85" stroke="#a855f7" stroke-width="0.75" stroke-dasharray="2 2" />
              <line x1="50" y1="50" x2="85" y2="85" stroke="#06b6d4" stroke-width="0.75" stroke-dasharray="2 2" />
              <line x1="50" y1="50" x2="50" y2="15" stroke="#8b5cf6" stroke-width="0.75" stroke-dasharray="2 2" />
              <line x1="50" y1="50" x2="50" y2="85" stroke="#8b5cf6" stroke-width="0.75" stroke-dasharray="2 2" />
            </svg>
            
            <div class="grid grid-cols-3 gap-8 sm:gap-24 relative z-10 w-full max-w-3xl items-center text-center">
              <!-- Left node column -->
              <div class="space-y-8 text-right">
                <div><span class="inline-block px-3 py-1.5 rounded-full bg-gray-900 border border-gray-800 text-[10px] font-black uppercase text-cyan-400">Portfolio</span></div>
                <div><span class="inline-block px-3 py-1.5 rounded-full bg-gray-900 border border-gray-800 text-[10px] font-black uppercase text-purple-400">Analytics</span></div>
                <div><span class="inline-block px-3 py-1.5 rounded-full bg-gray-900 border border-gray-800 text-[10px] font-black uppercase text-cyan-400">Explorer</span></div>
              </div>
              <!-- Center -->
              <div class="flex flex-col items-center justify-center">
                <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-cyan-400 to-purple-500 flex items-center justify-center shadow-lg shadow-cyan-500/20 mb-2">
                  <img :src="logo" alt="TokenGlade Logo" />
                </div>
              </div>
              <!-- Right node column -->
              <div class="space-y-8 text-left">
                <div><span class="inline-block px-3 py-1.5 rounded-full bg-gray-900 border border-gray-800 text-[10px] font-black uppercase text-purple-400">Rewards</span></div>
                <div><span class="inline-block px-3 py-1.5 rounded-full bg-gray-900 border border-gray-800 text-[10px] font-black uppercase text-cyan-400">XRPL / ETH</span></div>
                <div><span class="inline-block px-3 py-1.5 rounded-full bg-gray-900 border border-gray-800 text-[10px] font-black uppercase text-purple-400">Developer APIs</span></div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- SECTION 6 (WHITE): Final CTA -->
    <div class="bg-white text-gray-900 py-24 border-t border-b border-gray-100">
      <div class="max-w-4xl mx-auto px-6 text-center space-y-6">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-50 text-[10px] font-black uppercase tracking-widest text-purple-600 border border-purple-100">
          🚀 Get Started
        </span>
        <h3 class="text-3xl font-black text-gray-950 tracking-tight">Join Us in Building Better Blockchain Tools</h3>
        <p class="text-gray-550 text-sm max-w-xl mx-auto leading-relaxed">
          Create, analyze, and manage assets using clean parameters. Explore our non-custodial tools now.
        </p>  
      </div>
    </div>

    <!-- SECTION 7 (DARK): Footer -->
    <div class="bg-[#0B1020] text-gray-400 border-t border-gray-900/60">
      <Footer />
    </div>
  </div>
</template>

<script setup>
import Header from "@/components/Header.vue";
import Footer from "@/components/Footer.vue";
import logo from "@/assets/Xl-logo.png";
</script>

<style scoped>
.animate-pulse-slow {
  animation: pulse 8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.animate-spin-slow {
  animation: spin 16s linear infinite;
}

.animate-spin-reverse {
  animation: spin 12s linear infinite reverse;
}

/* Floating Animations */
@keyframes float1 {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-15px); }
}
@keyframes float2 {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(18px); }
}

.animate-float-1 { animation: float1 6s ease-in-out infinite; }
.animate-float-2 { animation: float2 8s ease-in-out infinite; }
</style>