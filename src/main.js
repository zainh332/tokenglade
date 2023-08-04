// main.js

import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";

const app = createApp(App);

// Use the router instance
app.use(router);

app.mount("#app");

// import "./bootstrap"
// import { createApp } from "vue";
// import App from './App.vue';
// import router from "/src/router";
// import { Field, Form, ErrorMessage, defineRule as defineFieldRule } from "vee-validate";

// const app = createApp(App);

// // Use the router instance
// app.use(router);
// app.component("Form", Form);
// app.component("Field", Field);
// app.component("ErrorMessage", ErrorMessage);

// app.mount("#app");
