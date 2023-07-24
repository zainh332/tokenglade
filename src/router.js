// src/router.js

import { createRouter, createWebHistory } from "vue-router";
import Home from "./pages/Home.vue";
import TokenGenerator from "./pages/TokenGenerator.vue";
import TokenTransfer from "./pages/TokenTransfer.vue";
import ClaimableBalance from "./pages/ClaimableBalance.vue";

const routes = [
  {
    path: "/",
    component: Home,
    meta: {
      title: "Home",
      description: "Welcome to Sorostellar - Home Page",
    },
  },
  {
    path: "/tokengenerator",
    component: TokenGenerator,
    meta: {
      title: "Token Generator",
      description: "Welcome to Sorostellar - Home Page",
    },
  },
  {
    path: "/tokentransfer",
    component: TokenTransfer,
    meta: {
      title: "Token Transfer",
      description: "Welcome to Sorostellar - Home Page",
    },
  },
  {
    path: "/claimable-balance",
    component: ClaimableBalance,
    meta: {
      title: "Claimable Balance",
      description: "Welcome to Sorostellar - Home Page",
    },
  },
  // Add more routes as needed
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
