
import { createRouter, createWebHistory } from "vue-router";
import Home from "./pages/Home.vue";
import TokenGenerator from "./pages/TokenGenerator.vue";
import TokenTransfer from "./pages/TokenTransfer.vue";
import ClaimableBalance from "./pages/ClaimableBalance.vue";
import TomlFileGenerator from "./pages/TomlFileGenerator.vue";
import aboutus from "./pages/about-us.vue";
import privacypolicy from "./pages/privacy-policy.vue";
import termsofservice from "./pages/terms-of-service.vue";
import SmartContract from "./pages/SmartContract.vue";

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
  },
  {
    path: "/token-transfer",
    component: TokenTransfer,
    meta: {
      title: "Token Transfer",
      description: "Effortlessly transfer tokens across wallets on the Stellar blockchain with TokenGlade. Simplify token interactions and explore decentralized possibilities."
    },
  },
  {
    path: "/claimable-balance",
    component: ClaimableBalance,
    meta: {
      title: "Claimable Balance",
      description: "Explore claimable balances on TokenGlade. Control token distributions, airdrops, and rewards with precision on the Stellar blockchain."
    },
    // beforeEnter: conditionalNext('isAdmin'),
  },
  {
    path: "/toml-file-generator",
    component: TomlFileGenerator,
    meta: {
      title: "TOML File Generator",
      description: "Generate TOML files for your tokens with ease on TokenGlade. Enhance transparency, trust, and credibility for your token projects on the Stellar blockchain."
    },
    // beforeEnter: conditionalNext('isAdmin'),
  },
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
    path: "/smart-contract",
    component: SmartContract,
    meta: {
      title: "Smart Contract Deployment",
      description: "This feature guides users through the process of deploying a smart contract on the Stellar blockchain. This feature will simplify the deployment process and ensure users follow the correct sequence of steps."
    },
  },
  {
    path: "/ledger-entry",
    component: SmartContract,
    meta: {
      title: "Ledger Entry Inspector",
      description: "This feature allows users to inspect the current state of ledger entries directly on the Stellar blockchain. This feature empowers users to view contract details, contract code, and other ledger entry information in a user-friendly interface."
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
