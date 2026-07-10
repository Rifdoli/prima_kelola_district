export default [
    {
        path: "/",
        redirect: "/dashboard",
    },
    {
        path: "/dashboard",
        name: "dashboard",
        meta: { title: "Home" },
        component: () => import("../views/pages/dashboard.vue"),
    },
    {
        path: "/roles",
        name: "roles",
        meta: { title: "Roles", group: "Administration" },
        component: () => import("../views/pages/roles.vue"),
    },
    {
        path: "/users",
        name: "users",
        meta: { title: "Users", group: "Administration" },
        component: () => import("../views/pages/users.vue"),
    },
    {
        path: "/assessment/self",
        name: "assessment-self",
        meta: { title: "Self Assessment", group: "Assessment" },
        component: () => import("../views/pages/self-assessment.vue"),
    },
    {
        path: "/assessment/on-desk",
        name: "assessment-on-desk",
        meta: { title: "On Desk Assessment", group: "Assessment" },
        component: () => import("../views/pages/placeholder.vue"),
    },
    {
        path: "/assessment/on-site",
        name: "assessment-on-site",
        meta: { title: "On Site Assessment", group: "Assessment" },
        component: () => import("../views/pages/placeholder.vue"),
    },
    {
        path: "/organizations",
        name: "organizations",
        meta: { title: "Organization Management", group: "Administration" },
        component: () => import("../views/pages/organizations.vue"),
    },
    {
        path: "/locations",
        name: "locations",
        meta: { title: "Location Management", group: "Administration" },
        component: () => import("../views/pages/placeholder.vue"),
    },

    // Auth
    {
        path: "/login-v1",
        name: "login-v1",
        meta: { title: "Login", public: true },
        component: () => import("../views/auth/login.vue"),
    },
    {
        path: "/register-v1",
        name: "register-v1",
        meta: { title: "Register", public: true },
        component: () => import("../views/auth/register.vue"),
    },
    {
        path: "/forgot-password-v1",
        name: "forgot-password-v1",
        meta: { title: "Forgot Password", public: true },
        component: () => import("../views/auth/forgot-password.vue"),
    },
    {
        path: "/reset-password-v1",
        name: "reset-password-v1",
        meta: { title: "Reset Password", public: true },
        component: () => import("../views/auth/reset-password.vue"),
    },
    {
        path: "/code-verification-v1",
        name: "code-verification-v1",
        meta: { title: "code-verification", public: true },
        component: () => import("../views/auth/code-verification.vue"),
    },
]
