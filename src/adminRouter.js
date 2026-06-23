import { createRouter, createWebHistory } from "vue-router";
import AdminLayout from "./AdminLayout.vue";
import WalletsView from "./pages/admin/WalletsView.vue";
import TokensView from "./pages/admin/TokensView.vue";
import StakingView from "./pages/admin/StakingView.vue";

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
