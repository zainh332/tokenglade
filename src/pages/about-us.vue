<template>
    <Header />
    <Layout>
        <div class="responsive-container">
            <div class="max-w-2xl px-4 sm:px-16 mx-auto flex items-center flex-col space-y-4">
                <div class="space-y-2 py-8">
                    <h1 class="text-t34 text-center font-semibold">About TokenGlade</h1>
                    <p class="text-t16 text-center font-normal">
                        One Platform. Multiple Chains. Infinite Possibilities.
                    </p>
                </div>
            </div>

            <div class="py-4">
                <h2 class="text-t24 font-semibold">Our Mission</h2>
                <p class="text-t16">
                    TokenGlade is building tools that make blockchain tokenization simpler,
                    more transparent, and accessible to everyone. From token creation to
                    analytics and staking, the goal is to remove unnecessary complexity
                    and make blockchain tools usable for real people.
                </p>
            </div>

            <div class="py-4">
                <h2 class="text-t24 font-semibold">Our Vision</h2>
                <p class="text-t16">
                    We believe blockchain adoption grows when tools become easier to use.
                    TokenGlade envisions a future where anyone can launch, explore,
                    analyze, and manage tokens without needing deep technical knowledge.
                    Starting with Stellar and expanding toward multi-chain support,
                    TokenGlade is focused on building a more open and accessible ecosystem.
                </p>
            </div>

            <div class="py-4">
                <h2 class="text-t24 font-semibold">What TokenGlade Offers</h2>
                <p class="text-t16">
                    TokenGlade provides a growing ecosystem of blockchain tools including:
                </p>

                <ul class="list-disc pl-6 pt-3 space-y-2 text-t16">
                    <li>No-code token creation</li>
                    <li>Real-time Token Insight analytics</li>
                    <li>Token Health Scores</li>
                    <li>Liquidity and trading activity tracking</li>
                    <li>Community-driven token voting</li>
                    <li>TKG staking with flexible rewards</li>
                    <li>Verified token profiles</li>
                </ul>
            </div>

            <div class="py-4">
                <h2 class="text-t24 font-semibold">Why TokenGlade?</h2>
                <p class="text-t16">
                    Most blockchain platforms focus only on developers.
                    TokenGlade focuses on usability, transparency, and practical tools
                    for creators, traders, startups, and communities.
                    Instead of overwhelming users with complexity, TokenGlade helps
                    simplify how people interact with blockchain ecosystems.
                </p>
            </div>

            <div class="py-4">
                <h2 class="text-t24 font-semibold">Building Beyond Stellar</h2>
                <p class="text-t16">
                    Stellar is the foundation of TokenGlade today because of its speed,
                    low transaction costs, and efficient infrastructure.
                    As the platform evolves, support for additional blockchain ecosystems
                    will continue expanding to create a more connected multi-chain experience.
                </p>
            </div>

            <div class="py-4">
                <h2 class="text-t24 font-semibold">About the Founder</h2>

                <p class="text-t16">
                    TokenGlade was founded by Zain Haider<a href="https://x.com/zainhaider332" target="_blank"
                        class="text-blue-500 hover:underline">
                        @ZainHaider332
                    </a>, a software developer and entrepreneur
                    focused on building practical blockchain tools for real users.
                    With a strong interest in the Stellar ecosystem, Zain started TokenGlade
                    with the vision of making tokenization, analytics, and blockchain infrastructure
                    simpler and more accessible.
                </p>

                <p class="text-t16 pt-4">
                    Instead of focusing on hype, the goal behind TokenGlade is to build tools
                    that provide real utility, transparency, and long-term value for the community.
                    From Token Insight to staking and multi-chain expansion, the platform continues
                    evolving with a product-first approach.
                </p>
            </div>

            <div class="py-4">
                <h2 class="text-t24 font-semibold">Join the Journey</h2>
                <p class="text-t16">
                    TokenGlade is still early, and new features are being developed rapidly.
                    The vision goes beyond token creation. The goal is to build one of the
                    most useful and transparent token ecosystems in blockchain.
                </p>
            </div>
            <div class="py-4">
                <h2 class="text-t24 font-semibold">Follow TokenGlade</h2>

                <p class="text-t16">
                    Stay updated with new features, ecosystem updates, and TokenGlade developments.
                </p>

                <div class="flex flex-col pt-4 space-y-2">
                    <a href="https://x.com/TokenGlade" target="_blank" class="text-blue-500 hover:underline">
                        Twitter / X
                    </a>

                    <a href="https://medium.com/@tokenglade" target="_blank" class="text-blue-500 hover:underline">
                        Medium
                    </a>
                </div>
            </div>
        </div>

        <Footer />
    </Layout>
</template>

<script setup>
import Header from "@/components/Header.vue";
import Footer from "@/components/Footer.vue";
</script>

<style lang="scss" scoped>
.responsive-container {
    max-width: 100%;
    margin-left: auto;
    margin-right: auto;
    padding: 2rem;

    @media screen and (min-width: 640px) {
        padding: 4rem;
    }

    @media screen and (min-width: 1024px) {
        padding: 6rem;
    }
}
</style>