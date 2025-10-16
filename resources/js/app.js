import "./bootstrap";
import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import { Form } from "vform";

import "vuetify/dist/vuetify.min.css";

// Vuetify
import "vuetify/styles";
import { createVuetify } from "vuetify";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";
import { aliases, mdi } from 'vuetify/iconsets/mdi'

import {
    formatDate,
    getCountries,
    getPropertyTypes,
    roomTypes,
    showSuccessToast,
    showErrorToast,
    getAllAmenities,
    propertyTypeGroups,
    propertyTypeCategories,
    listingViewOptions,
    checkInOptionCategories
} from "./globalProperties";

const vuetify = createVuetify({
    components,
    directives,
    icons: {
        defaultSet: "mdi",
        aliases,
        sets: {
            mdi,
        },
    },
});

//Form
window.form = Form;
const app = createApp({});

app.config.globalProperties.$formatDate = formatDate;
app.config.globalProperties.$getCountries = getCountries;
app.config.globalProperties.$getPropertyTypes = getPropertyTypes;
app.config.globalProperties.$roomTypes = roomTypes;
app.config.globalProperties.$getAllAmenities = getAllAmenities;
app.config.globalProperties.$showSuccessToast = showSuccessToast;
app.config.globalProperties.$showErrorToast = showErrorToast;
app.config.globalProperties.$propertyTypeGroups = propertyTypeGroups;
app.config.globalProperties.$propertyTypeCategories = propertyTypeCategories;
app.config.globalProperties.$listingViewOptions = listingViewOptions;
app.config.globalProperties.$checkInOptionCategories = checkInOptionCategories;

app.component("App", App);
app.use(router);
app.use(vuetify);
app.mount("#vue-app");