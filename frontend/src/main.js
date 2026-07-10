import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import store from "./state/store";
import i18n from "./i18n";
import BootstrapVueNext from 'bootstrap-vue-next';
import VueApexCharts from "vue3-apexcharts";
import PhosphorIcons from "@phosphor-icons/vue";
import Wizard from 'form-wizard-vue3';
import { initAuthBackend } from './authutils';
// import CoolLightBox from 'vue-cool-lightbox';

initAuthBackend();

// Harmless browser warning triggered by ResizeObserver-based components
// (e.g. simplebar) during rapid layout changes like breakpoint/sidebar
// toggles. Doesn't indicate a real error — suppress only this message
// so it doesn't trip the dev-server error overlay.
window.addEventListener('error', (e) => {
    if (e.message === 'ResizeObserver loop completed with undelivered notifications.') {
        e.stopImmediatePropagation();
    }
});

// Packages CSS import
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue-next/dist/bootstrap-vue-next.css';
import '@vueform/slider/themes/default.css';
import 'form-wizard-vue3/dist/form-wizard-vue3.css'
import 'simplebar-vue/dist/simplebar.min.css';

import '@/assets/scss/style.scss';

// bootstrap.bundle.js
import 'bootstrap/dist/js/bootstrap.bundle.js';

createApp(App)
.use(store)
.use(router)
.use(i18n)
.use(BootstrapVueNext)
.use(VueApexCharts)
.use(PhosphorIcons)
// .use(CoolLightBox)
.component('Wizard', Wizard)
.mount('#app')