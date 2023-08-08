// src/router.js

import { createRouter, createWebHistory } from "vue-router";
import Home from "./pages/Home.vue";
import TokenGenerator from "./pages/TokenGenerator.vue";
import TokenTransfer from "./pages/TokenTransfer.vue";
import ClaimableBalance from "./pages/ClaimableBalance.vue";
import TomlFileGenerator from "./pages/TomlFileGenerator.vue";
import SmartContract from "./pages/SmartContract.vue";

const routes = [
  {
    path: "/",
    component: Home,
    meta: {
      title: "Home",
      description: "Welcome to TokenGalde - Home Page",
    },
  },
  {
    path: "/token-generator",
    component: TokenGenerator,
    meta: {
      title: "Token Generator",
      description: "Welcome to TokenGlade - Home Page",
    },
  },
  {
    path: "/token-transfer",
    component: TokenTransfer,
    meta: {
      title: "Token Transfer",
      description: "Welcome to TokenGlade - Home Page",
    },
  },
  {
    path: "/claimable-balance",
    component: ClaimableBalance,
    meta: {
      title: "Claimable Balance",
      description: "Welcome to TokenGlade - Home Page",
    },
    // beforeEnter: conditionalNext('isAdmin'),
  },
  {
    path: "/toml-file-generator",
    component: TomlFileGenerator,
    meta: {
      title: "Toml File Generator",
      description: "Welcome to TokenGlade - Home Page",
    },
    // beforeEnter: conditionalNext('isAdmin'),
  },
  {
    path: "/smart-contract",
    component: SmartContract,
    meta: {
      title: "Smart Contract",
      description: "Welcome to TokenGlade - Home Page",
    },
    // beforeEnter: conditionalNext('isAdmin'),
  },
  {
    path: "/tokenization-assets",
    component: TomlFileGenerator,
    meta: {
      title: "Tokenization of Assets",
      description: "Welcome to TokenGlade - Home Page",
    },
    // beforeEnter: conditionalNext('isAdmin'),
  },
  {
    path: "/about-us",
    component: TomlFileGenerator,
    meta: {
      title: "About Us | TokenGlade",
      description: "About Us | TokenGlade",
    },
    // beforeEnter: conditionalNext('isAdmin'),
  },
  // Add more routes as needed
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
