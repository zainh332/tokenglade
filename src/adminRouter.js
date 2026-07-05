import { createRouter, createWebHistory } from "vue-router";
import AdminLayout from "./AdminLayout.vue";
import WalletsView from "./pages/admin/WalletsView.vue";
import TokensView from "./pages/admin/TokensView.vue";
import StakingView from "./pages/admin/StakingView.vue";
import LpParticipantsView from "./pages/admin/LpParticipantsView.vue";
import LpHistoryView from "./pages/admin/LpHistoryView.vue";
import VerificationFeesView from "./pages/admin/VerificationFeesView.vue";

const routes = [
    {
        path: "/admin",
        component: AdminLayout,
        children: [
            {
                path: "",
                redirect: "/admin/wallets"
            },
            {
                path: "wallets",
                component: WalletsView,
                meta: { title: "Admin — Wallets Base Registry" }
            },
            {
                path: "tokens",
                component: TokensView,
                meta: { title: "Admin — Minted Assets Inventory" }
            },
            {
                path: "staking",
                component: StakingView,
                meta: { title: "Admin — Staking Analytics Snapshot" }
            },
            {
                path: "lp-participants",
                component: LpParticipantsView,
                meta: { title: "Admin — Liquidity Pool Participants" }
            },
            {
                path: "lp-history",
                component: LpHistoryView,
                meta: { title: "Admin — LP Reward Payout History" }
            },
            {
                path: "verification-fees",
                component: VerificationFeesView,
                meta: { title: "Admin — Verification Project Fees" }
            }
        ]
    }
];

const adminRouter = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    }
});

adminRouter.beforeEach((to, from, next) => {
    if (to.meta && to.meta.title) {
        document.title = to.meta.title;
    }
    next();
});

export default adminRouter;
