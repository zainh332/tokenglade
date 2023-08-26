<template>
    <Header />
    <Layout>
        <div class="responsive-container">
            <div class="max-w-2xl px-4 sm:px-16 mx-auto flex items-center flex-col space-y-4">
                <div class="space-y-2 py-8">
                    <h1 class="text-t34 text-center font-semibold">Privacy Policy</h1>
                </div>
            </div>
            <div class="py-4">
                <p class="text-t16">
                    Welcome to TokenGlade's Privacy Policy. This policy outlines how TokenGlade ("we," "our," or "us")
                    collects, uses, discloses, and safeguards your personal information when you use our platform and
                    services. By accessing or using TokenGlade, you consent to the practices described in this Privacy
                    Policy.
                </p>
            </div>
            <div class="py-4">
                <h2 class="text-t24 font-semibold">Information We Collect</h2>
                <p class="text-t16">
                    We collect both personal and non-personal information to provide and improve our services. The types of
                    information we collect include:
                </p>
            </div>
            <ul class="list-disc pl-6">
                <li>Personal Information: This includes information that can identify you personally, such as your name,
                    email address, contact details, etc.</li>
                <li>Transaction Information: Details of transactions involving tokens, including sender and recipient
                    addresses, transaction amounts, timestamps.</li>
                <li>Usage Information: Data about how you use TokenGlade, interactions, features, pages visited.</li>
                <li>Device Information: Information about devices used to access TokenGlade, device type, operating system,
                    browser type, etc.</li>
                <li>Log Data: Automatically recorded data including IP addresses, access times, and usage patterns.</li>
            </ul>
            <div class="py-4">
                <h2 class="text-t24 font-semibold">How We Use Your Information</h2>
                <p class="text-t16">
                    We use your information for various purposes, including:</p>
            </div>
            <ul class="list-disc pl-6">
                <li>Providing and Improving Services: We use your information to deliver and enhance our platform, develop
                    new features, and optimize user experience.</li>
                <li>Communication: We may contact you to provide updates, information about our services, or respond to your
                    inquiries.</li>
                <li>Personalization: We personalize your experience on TokenGlade by recommending relevant content, tokens,
                    and features based on your interactions.</li>
                <li>Security: Your information helps us monitor and enhance the security of our platform and services.</li>
                <li>Legal and Regulatory Compliance: We may use your information to comply with legal obligations and
                    respond to law enforcement requests.</li>
            </ul>
            <div class="py-4">
                <h2 class="text-t24 font-semibold">How We Share Your Information</h2>
                <p class="text-t16">
                    We may share your information under certain circumstances:
                </p>
            </div>
            <ul class="list-disc pl-6">
                <li>With Service Providers: We share information with trusted third-party service providers who assist us in
                    delivering our services, such as hosting, analytics, and customer support.</li>
                <li>Legal Compliance: We may disclose information to comply with legal obligations, protect our rights, privacy,
                    safety, or property, and respond to law enforcement requests.</li>
                <li>Business Transfers: In the event of a merger, acquisition, or sale of all or a portion of our assets, your
                    information may be transferred to the acquiring entity.</li>
                <li>Consent: We may share your information with your consent.</li>
            </ul>

            <!-- Your Choices -->
            <div class="py-4">
                <h2 class="text-t24 font-semibold">Your Choices</h2>
                <p class="text-t16">
                    You have several choices regarding your information:
                </p>
            </div>
            <ul class="list-disc pl-6">
                <li>Account Information: You can access and update your account information by logging into your TokenGlade
                    account.</li>
                <li>Communication Preferences: You can opt out of receiving promotional emails from us by following the
                    instructions provided in the email.</li>
                <li>Cookies: Most web browsers are set to accept cookies by default. You can usually modify your browser
                    settings to decline cookies if you prefer. However, this may impact your ability to use TokenGlade
                    effectively.</li>
            </ul>

            <!-- Changes to this Privacy Policy -->
            <div class="py-4">
                <h2 class="text-t24 font-semibold">Changes to this Privacy Policy</h2>
                <p class="text-t16">
                    We may update this Privacy Policy periodically to reflect changes in our practices or for legal or
                    regulatory reasons. We will notify you of any significant changes through the platform or via email.
                </p>
            </div>

            <!-- Contact Us -->
            <div class="py-4">
                <p class="text-t16">
                    If you have any questions or concerns about this Privacy Policy, please contact us at info@tokenglade.com
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