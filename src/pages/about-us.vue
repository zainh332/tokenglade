<template>
    <Header />
    <Layout>
    <div class="responsive-container">
        <div class="max-w-2xl px-4 sm:px-16 mx-auto flex items-center flex-col space-y-4">
            <div class="space-y-2 py-8">
                <h1 class="text-t34 text-center font-semibold">About TokenGlade</h1>
                <p class="text-t16 text-center font-normal">
                    One Platform. Every Chain. Infinite Tokens
                </p>
            </div>
        </div>
         <div class="py-4">
            <h2 class="text-t24 font-semibold">Our Mission</h2>
            <p class="text-t16">
            TokenGlade exists to make blockchain tokenization accessible to everyone. Our mission is to remove complexity from blockchain tools by enabling users to launch tokens on Stellar and soon, many other blockchains with zero code and zero friction.
            </p>
        </div>
        <div class="py-4">
            <h2 class="text-t24 font-semibold">Our Vision</h2>
            <p class="text-t16">
            We envision a world where anyone from developers to creators to small businesses can tap into the power of blockchain technology effortlessly. By building a no-code, multi-chain token creation platform, we're unlocking access to the tools of the future, starting with Stellar and expanding to Ripple, Ethereum, and beyond.
            </p>
        </div>
        <div class="py-4">
        <h2 class="text-t24 font-semibold">Our Values</h2>
        <p class="text-t16">
          We believe in building tools that anyone can use regardless of their technical background. We lead with transparency, value open ecosystems, and are committed to empowering users globally with blockchain-enabled opportunities.
        </p>
      </div>

         <div class="py-4">
        <h2 class="text-t24 font-semibold">Why TokenGlade?</h2>
        <p class="text-t16">
          Most blockchain tools are built for developers. TokenGlade is different. We designed a platform that speaks to creators, innovators, and entrepreneurs not just coders. With support for Stellar (live) and multi-chain expansion underway, TokenGlade is the easiest way to turn your token idea into reality.
        </p>
      </div>

      <div class="py-4">
        <h2 class="text-t24 font-semibold">Join Us</h2>
        <p class="text-t16">
          Whether you're launching your first token or scaling your Web3 idea, TokenGlade is here to help. We're building a community-first platform that grows with you. Start your journey today and be part of the future of blockchain token creation.
        </p>
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
    /* Default styles for all screen sizes */
    max-width: 100%;
    margin-left: auto;
    margin-right: auto;
    padding: 2rem;
    /* Adjust padding as needed */

    /* Responsive adjustments */
    @media screen and (min-width: 640px) {
        padding: 4rem;
        /* Adjust padding for medium screens and above */
    }

    @media screen and (min-width: 1024px) {
        padding: 6rem;
        /* Adjust padding for large screens and above */
    }
}</style>