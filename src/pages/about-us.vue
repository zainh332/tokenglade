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
                At TokenGlade, we are driven by a singular mission - to make blockchain technology accessible and user-friendly for everyone. We believe in the transformative power of blockchain, especially in the Stellar ecosystem, and we are committed to harnessing this power to simplify complex processes.
            </p>
        </div>
        <div class="py-4">
            <h2 class="text-t24 font-semibold">Our Vision</h2>
            <p class="text-t16">
                Our vision is to create a platform that empowers individuals and businesses to unlock the full potential of the Stellar blockchain. We aspire to be the go-to destination for anyone looking to harness the advantages of blockchain technology, whether it's token creation, smart contract deployment, or exploring the richness of the Stellar network.
            </p>
        </div>
        <div class="py-4">
            <h2 class="text-t24 font-semibold">Our Values</h2>
            <p class="text-t16">
                Integrity, innovation, and inclusivity are the cornerstones of our values. We are committed to maintaining the highest ethical standards in everything we do. We constantly innovate to bring you cutting-edge solutions. Above all, we embrace inclusivity, ensuring that blockchain's benefits are accessible to individuals and organizations worldwide.
            </p>
        </div>
        <div class="py-4">
            <h2 class="text-t24 font-semibold">Why TokenGlade?</h2>
            <p class="text-t16">
                With years of experience in blockchain technology, a passion for innovation, and a commitment to open-source principles, TokenGlade is uniquely positioned to simplify your journey into the Stellar universe. Our team comprises experts in software development, blockchain, and customer service, all dedicated to providing you with the best possible experience.
            </p>
        </div>
        <div class="py-4">
            <h2 class="text-t24 font-semibold">Join Us</h2>
            <p class="text-t16">
                We invite you to join us on this exciting journey into the world of Stellar blockchain. Whether you're a developer, a business owner, or simply someone curious about blockchain, TokenGlade is here to empower you. Together, we can shape the future of blockchain technology.
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