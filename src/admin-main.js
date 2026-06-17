import { createApp } from "vue";
import AdminApp from "./AdminApp.vue";
import adminRouter from "./adminRouter";
import { setUp } from './hearing.js';

const app = createApp(AdminApp);
app.use(adminRouter);
app.mount("#admin-app");
setUp();
