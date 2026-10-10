import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import SpaPage from '../spa/SpaPage.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/:pathMatch(.*)*',
            name: 'spa',
            component: SpaPage,
        },
    ],
});

let booted = false;

router.beforeEach(async () => {
    if (booted) {
        return;
    }

    booted = true;
    await useAuthStore().fetch();
});

export default router;
