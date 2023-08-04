// main.js

import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import '../resources/css/app.css';

const app = createApp(App);

// Use the router instance
app.use(router);

app.mount("#app");
