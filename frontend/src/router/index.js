import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/modules/auth/stores/auth";
import homeRoutes from "@/modules/home/routes";
import authRoutes from "@/modules/auth/routes";
import cvRoutes from "@/modules/cv/routes";

const routes = [
  ...homeRoutes,
  ...authRoutes,
  ...cvRoutes,
];

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
});

// Navigation guard — protect routes that require auth
router.beforeEach((to) => {
  const auth = useAuthStore();

  // Guest-only pages (login, register) — redirect to home if already logged in
  if (to.meta.guest && auth.isAuthenticated) {
    return { name: 'HomeView' };
  }

  // Protected pages (anything that isn't guest and isn't home) — redirect to login
  if (!to.meta.guest && to.name !== 'HomeView' && !auth.isAuthenticated) {
    return { name: 'Login' };
  }
});

export default router;