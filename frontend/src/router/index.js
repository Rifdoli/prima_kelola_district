import { createWebHistory, createRouter } from "vue-router";
import routes from './routes';
import appConfig from "../../app.config";


const router = createRouter({
    history: createWebHistory("/"),
    routes,

});

const ADMIN_ONLY_PATHS = ['/users', '/roles', '/organizations', '/locations'];

router.beforeEach((to, _from, next) => {
    if (to.meta.public) {
        return next();
    }

    const isAuthenticated = !!sessionStorage.getItem('authToken');

    if (!isAuthenticated) {
        return next({ name: 'login-v1' });
    }

    if (ADMIN_ONLY_PATHS.includes(to.path)) {
        const sname = JSON.parse(sessionStorage.getItem('authUser') || '{}')?.role?.sname;
        if (sname !== 'admin_sup') {
            return next({ path: '/dashboard' });
        }
    }

    next();
});

router.beforeResolve(async (routeTo, routeFrom, next) => {
    // Create a `beforeResolve` hook, which fires whenever
    // `beforeRouteEnter` and `beforeRouteUpdate` would. This
    // allows us to ensure data is fetched even when params change,
    // but the resolved route does not. We put it in `meta` to
    // indicate that it's a hook we created, rather than part of
    // Vue Router (yet?).
    try {
        // For each matched route...
        for (const route of routeTo.matched) {
            await new Promise((resolve, reject) => {
                // If a `beforeResolve` hook is defined, call it with
                // the same arguments as the `beforeEnter` hook.
                if (route.meta && route.meta.beforeResolve) {
                    route.meta.beforeResolve(routeTo, routeFrom, (...args) => {
                        // If the user chose to redirect...
                        if (args.length) {
                            // If redirecting to the same route we're coming from...
                            // Complete the redirect.
                            next(...args);
                            reject(new Error('Redirected'));
                        } else {
                            resolve();
                        }
                    });
                } else {
                    // Otherwise, continue resolving the route.
                    resolve();
                }
            });
        }
        // If a `beforeResolve` hook chose to redirect, just return.
    } catch (error) {
        return;
    }
    document.title = routeTo.meta.title + ' | ' + appConfig.title;
    // If we reach this point, continue resolving the route.
    next();
});

export default router;