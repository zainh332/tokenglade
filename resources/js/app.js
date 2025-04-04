import { createApp } from "vue";
import App from "../../src/App.vue";
import router from "../../src/router";

const app = createApp(App);

// Use the router instance
app.use(router);

app.mount("#app");
