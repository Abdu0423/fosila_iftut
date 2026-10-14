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
import { toSpaPath } from '../api/client';
import { signalPageLoaded, usePageStore } from '../stores/page';
import Layout from '../Pages/Layout.vue';
import { fetchPage, forgetPage, isRedirect, readPage, resolvePageComponent, type CachedPage } from './pageCache';

const currentRoute = useRoute();
const vueRouter = useRouter();
const pageStore = usePageStore();

const pageComponent = shallowRef<unknown>(null);
const loadedPath = ref('');
let generation = 0;

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

async function showPage(page: CachedPage, path: string) {
    const component = await resolvePageComponent(page.component);
    pageStore.setPage(page.component, page.props);
    pageComponent.value = component;
    loadedPath.value = path;
}

async function loadPage(fresh: boolean) {
    const path = currentRoute.path || '/';
    const key = currentRoute.fullPath;
    const requestId = ++generation;
    let showedCache = false;

    if (fresh) {
        forgetPage(key);
    } else {
        const cached = readPage(key);
        if (cached) {
            await showPage(cached, path);
            if (requestId !== generation) {
                return;
            }
            showedCache = true;
        }
    }

    try {
        const result = await fetchPage(path, currentRoute.query as Record<string, unknown>, key, fresh);
        if (requestId !== generation) {
            return;
        }

        if (isRedirect(result)) {
            const target = toSpaPath(result.redirect);
            if (target !== currentRoute.fullPath && target !== path) {
                await vueRouter.replace(target);
            }
            return;
        }

        await showPage(result, path);
    } catch (error: unknown) {
        if (requestId !== generation || showedCache) {
            return;
        }

        const response = (error as { response?: { status?: number; data?: { component?: string; props?: Record<string, unknown>; message?: string } } }).response;
        const status = response?.status;
        const body = response?.data;

        if (status === 401) {
            return;
        }

        const componentName = body?.component || (status === 403 ? 'Errors/403' : status === 419 ? 'Errors/419' : 'Errors/500');
        await showPage({
            component: componentName,
            props: body?.props || { message: body?.message || '' },
        }, path);
    } finally {
        if (requestId === generation) {
            signalPageLoaded();
        }
    }
}

watch(
    () => [currentRoute.fullPath, pageStore.reloadToken] as const,
    (value, previous) => {
        const fresh = Boolean(previous && value[0] === previous[0] && value[1] !== previous[1]);
        loadPage(fresh);
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
