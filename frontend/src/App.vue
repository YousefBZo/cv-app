<script setup>
import NavBar from "@/shared/components/NavBar.vue";
import ToastNotification from "@/shared/components/ToastNotification.vue";
import { useCVStore } from "@/modules/cv/stores/cv";
import { useAuthStore } from "@/modules/auth/stores/auth";
import { computed } from "vue";
import { useRoute } from "vue-router";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const cvStore = useCVStore();
const authStore = useAuthStore();
const route = useRoute();

const currentYear = new Date().getFullYear();
const isAuthPage = computed(() => ['Login', 'Register'].includes(route.name));
</script>

<template>
  <div id="app" class="min-h-screen flex flex-col"
       style="background: radial-gradient(ellipse at top, #0f172a 0%, #020617 50%, #000 100%);">
    <!-- Nav -->
    <NavBar />
    <ToastNotification />

    <!-- Main Content -->
    <main class="flex-1">
      <RouterView />
    </main>

    <!-- Footer (hidden on auth pages) -->
    <footer v-if="!isAuthPage"
      class="mt-auto border-t border-white/5 bg-black/20 backdrop-blur-sm">
      <div class="max-w-6xl mx-auto px-6 py-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
          <!-- Brand -->
          <div>
            <div class="flex items-center gap-2 mb-3">
              <div class="w-8 h-8 rounded-lg bg-linear-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-black text-xs">
                {{ cvStore.userName?.charAt(0) || 'C' }}
              </div>
              <span class="text-white font-bold">{{ cvStore.userName || t('sidebar.cvBuilder') }}</span>
            </div>
            <p class="text-slate-500 text-sm leading-relaxed">
              {{ t('home.heroSubtitle') }}
            </p>
          </div>
          <!-- Quick Links -->
          <div>
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">{{ t('footer.quickLinks') }}</h4>
            <ul class="space-y-2">
              <li v-for="link in [{name: t('nav.home'), path:'/'}, {name: t('nav.cv'), path:'/cv'}, {name: t('sidebar.projects'), path:'/project'}]" :key="link.name">
                <router-link :to="link.path" class="text-slate-500 hover:text-blue-400 text-sm transition-colors">{{ link.name }}</router-link>
              </li>
            </ul>
          </div>
          <!-- CV Sections -->
          <div>
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">{{ t('footer.cvSections') }}</h4>
            <ul class="space-y-2">
              <li v-for="link in [{name: t('sidebar.profile'), path:'/profile'}, {name: t('sidebar.education'), path:'/education'}, {name: t('sidebar.skills'), path:'/skill'}, {name: t('sidebar.languages'), path:'/language'}]" :key="link.name">
                <router-link :to="link.path" class="text-slate-500 hover:text-blue-400 text-sm transition-colors">{{ link.name }}</router-link>
              </li>
            </ul>
          </div>
        </div>
        <div class="border-t border-white/5 pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
          <p class="text-slate-600 text-xs">{{ t('footer.allRights', { year: currentYear }) }}</p>
          <div class="flex items-center gap-1 text-xs text-slate-600">
            <span>{{ t('footer.builtWith') }}</span>
            <span class="text-red-400">♥</span>
            <span>{{ t('footer.usingTech') }}</span>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
#app {
  width: 100%;
  min-height: 100vh;
}
</style>
