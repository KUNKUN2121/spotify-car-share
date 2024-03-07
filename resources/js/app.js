import "./bootstrap";
import "../css/app.css";

import { createApp } from "vue";
import App from "./App.vue";
// Vue Routerを使用する場合はrouter.jsを作成しインポートする
// import router from "./router";
// Vuexをを使用する場合はstoreフォルダ以下にファイルを作成しインポートする
// import store from "./store";

const buildApp = async () => {
    const app = createApp(App);
    // Vue Routerを使用する場合
    // app.use(router);
    // Vuexをを使用する場合
    // app.use(store);

    app.mount("#app");
};

buildApp();
