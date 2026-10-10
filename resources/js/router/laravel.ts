import routeMap from './laravelRoutes.json';

type RouteDef = { uri: string };
const routes = routeMap as Record<string, RouteDef>;

export function laravelRoute(name?: string, params?: unknown, absolute?: boolean): string {
    if (!name) {
        return '/';
    }

    if (typeof params === 'boolean') {
        absolute = params;
        params = undefined;
    }

    const definition = routes[name];
    if (!definition) {
        return `/${name.replace(/\./g, '/')}`;
    }

    let uri = definition.uri || '/';
    const leftovers: Record<string, string> = {};

    if (params != null && typeof params !== 'object') {
        uri = uri.replace(/\{[^}]+\}/, String(params));
    } else if (params && typeof params === 'object') {
        const values = params as Record<string, unknown>;
        Object.entries(values).forEach(([key, value]) => {
            const pattern = new RegExp(`\\{${key}\\??\\}`);
            if (pattern.test(uri)) {
                uri = uri.replace(pattern, value == null ? '' : String(value));
            } else if (value != null) {
                leftovers[key] = String(value);
            }
        });
    }

    uri = uri.replace(/\{[^}]+\?\}/g, '').replace(/\{[^}]+\}/g, '');
    uri = uri.replace(/\/+/g, '/');

    if (uri.length > 1 && uri.endsWith('/')) {
        uri = uri.slice(0, -1);
    }

    if (!uri.startsWith('/')) {
        uri = `/${uri}`;
    }

    const query = new URLSearchParams(leftovers).toString();
    if (query) {
        uri += `?${query}`;
    }

    return absolute ? `${window.location.origin}${uri}` : uri;
}

export function installRouteHelper(): void {
    window.route = laravelRoute;
}
