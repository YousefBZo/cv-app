import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/modules/auth/stores/auth";
import homeRoutes from "@/modules/home/routes";
import authRoutes from "@/modules/auth/routes";
import cvRoutes from "@/modules/cv/routes";
import exploreRoutes from "@/modules/explore/routes";

const routes = [
  ...homeRoutes,
  ...authRoutes,
  ...cvRoutes,
  ...exploreRoutes,
];

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
    scrollBehavior(to) {
      if (to.hash) {
        return { el: to.hash, behavior: 'smooth' }
      }
      return { top: 0 }
    },
});

// Navigation guard — protect routes that require auth
router.beforeEach((to) => {
  const auth = useAuthStore();

  // Guest-only pages (login, register) — redirect to home if already logged in
  if (to.meta.guest && auth.isAuthenticated) {
    return { name: 'HomeView' };
  }

  // Public pages (homepage, public CV viewer) — accessible by anyone
  if (to.meta.guest || to.meta.public || to.name === 'HomeView') {
    return;
  }

  // Protected pages — redirect to login if not authenticated
  if (!auth.isAuthenticated) {
    return { name: 'Login' };
  }
});

export default router;