<template>
    <Header />
    <Layout>
        <div class="responsive-container">
            <div class="max-w-2xl px-4 sm:px-16 mx-auto flex items-center flex-col space-y-4">
                <div class="space-y-2 py-8">
                    <h1 class="text-t34 text-center font-semibold">Cookies Policy</h1>
                </div>
            </div>
            <div class="py-4">
                <p class="text-t16">
                    Welcome to the Cookies Policy of TokenGlade. This policy outlines how we use cookies and similar tracking technologies ("cookies") on our platform. By accessing or using TokenGlade, you consent to the practices described in this Cookies Policy.
                </p>
            </div>
            <div class="py-4">
                <h2 class="text-t24 font-semibold">What Are Cookies?</h2>
                <p class="text-t16">
                    Cookies are small text files that are placed on your device when you access our platform. They serve various purposes, such as remembering your preferences, analyzing your usage patterns, and providing personalized experiences.
                </p>
            </div>
            <div class="py-4">
                <h2 class="text-t24 font-semibold">How We Use Cookies</h2>
                <p class="text-t16">
                    We use cookies for the following purposes:
                </p>
            </div>
            <ul class="list-disc pl-6">
                <li>Essential Cookies: These cookies are necessary for the basic functioning of TokenGlade and enable you to navigate our platform and use its features.</li>
                <li>Analytics Cookies: We use these cookies to gather information about your usage patterns, which helps us improve our services and user experience.</li>
                <li>Functionality Cookies: These cookies allow us to remember your preferences and provide enhanced, personalized features.</li>
                <li>Advertising and Targeting Cookies: We may work with third-party advertising partners to display advertisements that are relevant to your interests based on your interactions with TokenGlade.</li>
            </ul>
            
            <div class="py-4">
                <p class="text-t16">
                    If you have any questions or concerns about this Cookies Policy, please contact us at info@tokenglade.com
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