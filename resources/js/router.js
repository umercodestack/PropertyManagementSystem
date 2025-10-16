// resources/js/router.js
import { createRouter, createWebHistory } from 'vue-router';
import Listings from './pages/listings/Listings.vue';
import AddListing from './pages/listings/AddListing.vue';
import EditListing from './pages/listings/EditListing.vue';
import Templates from './pages/shceduled-messages/Templates.vue';

const routes = [
  {
    path: '/admin/listings',
    name: 'Listings',
    component: Listings,
  },
  {
    path: '/admin/listing/create',
    name: 'AddListing',
    component: AddListing,
  },
  {
    path: '/admin/listing/edit/:id',
    name: 'EditListing',
    component: EditListing,
  },
  {
    path: '/admin/messages/templates',
    name: 'Templates',
    component: Templates,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router; 