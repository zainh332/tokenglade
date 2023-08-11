<template>
    <Header />
    <Layout>
        <div class="responsive-container">
            <div class="max-w-2xl px-4 sm:px-16 mx-auto flex items-center flex-col space-y-4">
                <div class="space-y-2 py-8">
                    <h1 class="text-t34 text-center font-semibold">About TokenGlade</h1>
                    <p class="text-t16 text-center font-normal">
                        Empowering Your Journey into the Stellar Universe
                    </p>
                </div>
            </div>
            <div class="py-4">
                <h2 class="text-t24 font-semibold">Our Mission</h2>
                <p class="text-t16">
                    At TokenGlade, our mission is to make tokenization on the Stellar blockchain accessible to everyone. We
                    believe in simplifying the process, fostering transparency, and driving innovation in the digital asset
                    space.
                </p>
            </div>
            <div class="py-4">
                <h2 class="text-t24 font-semibold">User-Centric Approach</h2>
                <p class="text-t16">
                    We prioritize user-friendliness and aim to create a seamless experience for individuals and businesses
                    looking to harness the power of tokens. TokenGlade is designed with you in mind.
                </p>
            </div>
            <div class="py-4">
                <h2 class="text-t24 font-semibold">Transparency Matters</h2>
                <p class="text-t16">
                    We value transparency, which is why TokenGlade generates TOML files to enhance token credibility. We
                    believe that a transparent ecosystem benefits everyone involved.
                </p>
            </div>
            <div class="py-4">
                <h2 class="text-t24 font-semibold">Join the Revolution</h2>
                <p class="text-t16">
                    Be part of the TokenGlade revolution! Join us as we reshape tokenization, drive innovation, and empower
                    users to explore the limitless possibilities of the Stellar blockchain.
                </p>
            </div>

        </div>
    </Layout>
</template>
<script setup>
import Header from "@/components/Header.vue";
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