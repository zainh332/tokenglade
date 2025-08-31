
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
import Staking from "./pages/staking.vue";

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
            title: "Home",
            description: "Explore the world of tokens with TokenGlade. Empower your journey into the Stellar universe and join the revolution in digital assets."
        },
    },
    {
        path: "/token-generator",
        component: TokenGenerator,
        meta: {
            title: "Token Generator",
            description: "Create custom tokens effortlessly with TokenGlade's intuitive token generator. Make your mark in the token economy on the Stellar blockchain."
        },
        beforeEnter: (to, from, next) => {
            if (hasRequiredCookies()) {
                next(); // allow access if cookies exist
            } else {
                next('/'); // redirect to home page if cookies are missing
            }
        }
    },
    {
        path: "/claimable-balance",
        component: ClaimableBalance,
        meta: {
            title: "Claimable Balance",
            description: "Explore claimable balances on TokenGlade. Control token distributions, airdrops, and rewards with precision on the Stellar blockchain.",
        },
        beforeEnter: (to, from, next) => {
            if (hasRequiredCookies()) {
                next(); // allow access if cookies exist
            } else {
                next('/'); // redirect to home page if cookies are missing
            }
        }
    },
    {
        path: "/reclaim-claimable-balance",
        component: ReclaimClaimableBalance,
        meta: {
            title: "Reclaim Claimable Balance",
            description: "Easily reclaim your claimable balances in bulk for specific assets on the Stellar network. Streamline the process with our simple and efficient claim feature.",
        },
        beforeEnter: (to, from, next) => {
            if (hasRequiredCookies()) {
                next(); // allow access if cookies exist
            } else {
                next('/'); // redirect to home page if cookies are missing
            }
        }
    },
    // {
    //   path: "/token-transfer",
    //   component: TokenTransfer,
    //   meta: {
    //     title: "Token Transfer",
    //     description: "Effortlessly transfer tokens across wallets on the Stellar blockchain with TokenGlade. Simplify token interactions and explore decentralized possibilities."
    //   },
    // },
    //
    // {
    //   path: "/toml-file-generator",
    //   component: TomlFileGenerator,
    //   meta: {
    //     title: "TOML File Generator",
    //     description: "Generate TOML files for your tokens with ease on TokenGlade. Enhance transparency, trust, and credibility for your token projects on the Stellar blockchain."
    //   },
    //   // beforeEnter: conditionalNext('isAdmin'),
    // },
    {
        path: "/about-us",
        component: aboutus,
        meta: {
            title: "About Us",
            description: "Discover the mission of TokenGlade. We prioritize user-friendliness, transparency, and innovation in tokenization on the Stellar blockchain. Join the revolution!"
        },
        // beforeEnter: conditionalNext('isAdmin'),
    },
    {
        path: "/privacy-policy",
        component: privacypolicy,
        meta: {
            title: "Privacy Policy",
            description: "Explore TokenGlade's Privacy Policy to discover how we handle your personal information. From data collection and usage to your choices, our policy ensures transparency and protection. Learn more about your privacy rights while using our platform."
        },
        // beforeEnter: conditionalNext('isAdmin'),
    },
    {
        path: "/terms-service",
        component: termsofservice,
        meta: {
            title: "Terms of Service",
            description: ""
        },
    },
    {
        path: "/buy-stake",
        component: Staking,
        meta: {
            title: "Buy Stake",
            description: ""
        },
    },
];


const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Set the page title dynamically when the route changes
router.beforeEach((to, from, next) => {
    document.title = to.meta.title + " | TokenGlade";
    next();
});

export default router;
