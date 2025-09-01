
import { createRouter, createWebHistory } from "vue-router";
import Home from "./pages/Home.vue";
import TokenGenerator from "./pages/TokenGenerator.vue";
import TokenTransfer from "./pages/TokenTransfer.vue";
import ClaimableBalance from "./pages/ClaimableBalance.vue";
import ReclaimClaimableBalance from "./pages/ReclaimClaimableBalance.vue";
import TomlFileGenerator from "./pages/TomlFileGenerator.vue";
import aboutus from "./pages/about-us.vue";
import privacypolicy from "./pages/privacy-policy.vue";
import termsofservice from "./pages/terms-of-service.vue";

function hasRequiredCookies() {
    const public_key = getCookie('public_key');
    const accessToken = getCookie('accessToken');
    return public_key && accessToken;
}

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}


const routes = [
    {
        path: "/",
        component: Home,
        meta: {
            title: "TokenGlade — Create Tokens on Stellar (More Chains Coming Soon)",
            description: "TokenGlade is a no-code platform that lets you create and launch tokens instantly on the Stellar blockchain. More blockchains like Ripple and Ethereum coming soon. Fast, secure, and developer-free token generation."
        },
    },
    // {
    //     path: "/token-generator",
    //     component: TokenGenerator,
    //     meta: {
    //         title: "Token Generator",
    //         description: "Create custom tokens effortlessly with TokenGlade's intuitive token generator. Make your mark in the token economy on the Stellar blockchain."
    //     },
    //     beforeEnter: (to, from, next) => {
    //         if (hasRequiredCookies()) {
    //             next(); // allow access if cookies exist
    //         } else {
    //             next('/'); // redirect to home page if cookies are missing
    //         }
    //     }
    // },
    // {
    //     path: "/claimable-balance",
    //     component: ClaimableBalance,
    //     meta: {
    //         title: "Claimable Balance",
    //         description: "Explore claimable balances on TokenGlade. Control token distributions, airdrops, and rewards with precision on the Stellar blockchain.",
    //     },
    //     beforeEnter: (to, from, next) => {
    //         if (hasRequiredCookies()) {
    //             next(); // allow access if cookies exist
    //         } else {
    //             next('/'); // redirect to home page if cookies are missing
    //         }
    //     }
    // },
    // {
    //     path: "/reclaim-claimable-balance",
    //     component: ReclaimClaimableBalance,
    //     meta: {
    //         title: "Reclaim Claimable Balance",
    //         description: "Easily reclaim your claimable balances in bulk for specific assets on the Stellar network. Streamline the process with our simple and efficient claim feature.",
    //     },
    //     beforeEnter: (to, from, next) => {
    //         if (hasRequiredCookies()) {
    //             next(); // allow access if cookies exist
    //         } else {
    //             next('/'); // redirect to home page if cookies are missing
    //         }
    //     }
    // },
    {
        path: "/about-us",
        component: aboutus,
        meta: {
            title: "About TokenGlade — No-Code Blockchain Token Creation Platform",
            description: "Learn more about TokenGlade, a no-code multi-chain token creation platform. Discover our mission to simplify blockchain, starting with Stellar and expanding to Ripple, Ethereum, and more."
        },
    },
    {
        path: "/privacy-policy",
        component: privacypolicy,
        meta: {
            title: "Privacy Policy — TokenGlade",
            description: "Review TokenGlade’s Privacy Policy to learn how we collect, use, and protect your personal data while using our blockchain token creation platform."
        },
    },
    {
        path: "/terms-service",
        component: termsofservice,
        meta: {
            title: "Terms of Service — TokenGlade",
            description: "Read the Terms of Service for TokenGlade. Understand your rights, responsibilities, and the rules that govern the use of our blockchain token generation platform."
        },
    },
];


const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Set the page title dynamically when the route changes
router.beforeEach((to, from, next) => {
    if (to.meta && to.meta.title) {
        document.title = to.meta.title;
    }

    // Optional: Set meta description if needed
    if (to.meta && to.meta.description) {
        let descriptionTag = document.querySelector('meta[name="description"]');
        if (descriptionTag) {
            descriptionTag.setAttribute('content', to.meta.description);
        } else {
            descriptionTag = document.createElement('meta');
            descriptionTag.setAttribute('name', 'description');
            descriptionTag.setAttribute('content', to.meta.description);
            document.head.appendChild(descriptionTag);
        }
    }

    next();
});


export default router;
