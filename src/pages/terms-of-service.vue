<template>
    <Header />
    <Layout>
        <div class="responsive-container">
            <div class="max-w-2xl px-4 sm:px-16 mx-auto flex items-center flex-col space-y-4">
                <div class="space-y-2 py-8">
                    <h1 class="text-t34 text-center font-semibold">Terms of Service</h1>
                </div>
            </div>
            <div class="py-4">
                <p class="text-t16">
                    Welcome to the Terms of Service ("Terms") of TokenGlade. These Terms outline the rules and guidelines for using our platform and services. By accessing or using TokenGlade, you agree to abide by these Terms. If you do not agree with any part of these Terms, you may not access or use our services.
                </p>
            </div>
            <div class="py-4">
                <h2 class="text-t24 font-semibold">Use of Our Platform</h2>
                <p class="text-t16">
                    TokenGlade provides a platform for creating, managing, and interacting with tokens on the Stellar blockchain. By using our platform, you agree to:
                </p>
            </div>
            <ul class="list-disc pl-6">
                <li>Abide by all applicable laws and regulations while using TokenGlade.</li>
                <li>Provide accurate and up-to-date information when using our services.</li>
                <li>Not engage in any unauthorized or illegal activities on our platform.</li>
                <li>Respect the rights and privacy of other users.</li>
            </ul>
            <div class="py-4">
                <h2 class="text-t24 font-semibold">Intellectual Property</h2>
                <p class="text-t16">
                    The content and features available on TokenGlade, including but not limited to text, graphics, logos, and software, are the intellectual property of TokenGlade and its licensors. You may not use, reproduce, or distribute our intellectual property without explicit permission.
                </p>
            </div>
            <div class="py-4">
                <h2 class="text-t24 font-semibold">Disclaimer of Liability</h2>
                <p class="text-t16">
                    TokenGlade is provided "as is" and we make no warranties or representations about the accuracy, reliability, or availability of our platform. We shall not be liable for any direct, indirect, incidental, special, or consequential damages resulting from your use of TokenGlade.
                </p>
            </div>
            <div class="py-4">
                <h2 class="text-t24 font-semibold">Changes to Terms</h2>
                <p class="text-t16">
                    We may update these Terms from time to time. Any changes will be effective immediately upon posting on our platform. It's your responsibility to review these Terms periodically. Your continued use of TokenGlade after changes constitutes acceptance of the modified Terms.
                </p>
            </div>
            <div class="py-4">
                <p class="text-t16">
                    If you have any questions or concerns about these Terms of Service, please contact us at info@tokenglade.com
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

<style lang="scss" scoped>.responsive-container {
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