<template>
  <Layout v-if="showShell">
    <component
      :is="pageComponent"
      v-if="pageComponent"
      :key="loadedPath"
      v-bind="pageData"
    />
    <div v-else class="spa-loading">Загрузка...</div>
  </Layout>
  <component
    :is="pageComponent"
    v-else-if="pageComponent"
    :key="loadedPath"
    v-bind="pageData"
  />
  <div v-else class="spa-loading">Загрузка...</div>
</template>

<script setup lang="ts">
import { computed, ref, shallowRef, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api, toSpaPath } from '../api/client';
import { signalPageLoaded, usePageStore } from '../stores/page';
import Layout from '../Pages/Layout.vue';

const currentRoute = useRoute();
const vueRouter = useRouter();
const pageStore = usePageStore();
const pages = import.meta.glob('../Pages/**/*.vue');

const pageComponent = shallowRef<unknown>(null);
const loadedPath = ref('');

const barePaths = ['/login', '/password', '/change-password', '/privacy', '/terms'];

function isBarePage(path: string, component: string): boolean {
    if (component.startsWith('Auth/') || component.startsWith('Errors/') || component.startsWith('Legal/')) {
        return true;
    }

    return barePaths.some((prefix) => path === prefix || path.startsWith(`${prefix}/`));
}

const showShell = computed(() => !isBarePage(currentRoute.path, pageStore.component));

const pageData = computed(() => {
    const shared = new Set(['auth', 'flash', 'errors', 'locale', 'unreadChatsCount', 'translations']);
    const data: Record<string, unknown> = {};

    Object.entries(pageStore.props).forEach(([key, value]) => {
        if (!shared.has(key)) {
            data[key] = value;
        }
    });

    return data;
});

async function resolveComponent(name: string) {
    const loader = pages[`../Pages/${name}.vue`] as (() => Promise<{ default: unknown }>) | undefined;

    if (!loader) {
        const fallback = pages['../Pages/Errors/404.vue'] as () => Promise<{ default: unknown }>;
        return (await fallback()).default;
    }

    return (await loader()).default;
}

async function loadPage() {
    const path = currentRoute.path || '/';

    try {
        const response = await api.get(path, { params: currentRoute.query });
        const data = response.data ?? {};

        if (data.redirect && !data.component) {
            const target = toSpaPath(data.redirect);
            if (target !== currentRoute.fullPath && target !== path) {
                await vueRouter.replace(target);
            }
            return;
        }

        const componentName = data.component || 'Errors/404';
        const props = data.props && !Array.isArray(data.props) ? data.props : {};
        const component = await resolveComponent(componentName);
        pageStore.setPage(componentName, props);
        pageComponent.value = component;
        loadedPath.value = path;
    } catch (error: unknown) {
        const response = (error as { response?: { status?: number; data?: { component?: string; props?: Record<string, unknown>; message?: string } } }).response;
        const status = response?.status;
        const body = response?.data;

        if (status === 401) {
            return;
        }

        const componentName = body?.component || (status === 403 ? 'Errors/403' : status === 419 ? 'Errors/419' : 'Errors/500');
        const component = await resolveComponent(componentName);
        pageStore.setPage(componentName, body?.props || { message: body?.message || '' });
        pageComponent.value = component;
        loadedPath.value = path;
    } finally {
        signalPageLoaded();
    }
}

watch(
    () => [currentRoute.fullPath, pageStore.reloadToken] as const,
    () => {
        loadPage();
    },
    { immediate: true },
);
</script>

<style scoped>
.spa-loading {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: sans-serif;
    color: #445;
}
</style>
