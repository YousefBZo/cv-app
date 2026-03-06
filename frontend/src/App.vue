<script setup>
import NavBar from "@/shared/components/NavBar.vue";
import ToastNotification from "@/shared/components/ToastNotification.vue";
import { useCVStore } from "@/modules/cv/stores/cv";
import { useAuthStore } from "@/modules/auth/stores/auth";
import { computed } from "vue";
import { useRoute } from "vue-router";

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
              <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-black text-xs">
                {{ cvStore.userName?.charAt(0) || 'C' }}
              </div>
              <span class="text-white font-bold">{{ cvStore.userName || 'CV Builder' }}</span>
            </div>
            <p class="text-slate-500 text-sm leading-relaxed">
              Build and showcase your professional CV with a modern, interactive experience.
            </p>
          </div>
          <!-- Quick Links -->
          <div>
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Quick Links</h4>
            <ul class="space-y-2">
              <li v-for="link in [{name:'Home', path:'/'}, {name:'CV', path:'/cv'}, {name:'Projects', path:'/project'}]" :key="link.name">
                <router-link :to="link.path" class="text-slate-500 hover:text-blue-400 text-sm transition-colors">{{ link.name }}</router-link>
              </li>
            </ul>
          </div>
          <!-- CV Sections -->
          <div>
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">CV Sections</h4>
            <ul class="space-y-2">
              <li v-for="link in [{name:'Profile', path:'/profile'}, {name:'Education', path:'/education'}, {name:'Skills', path:'/skill'}, {name:'Languages', path:'/language'}]" :key="link.name">
                <router-link :to="link.path" class="text-slate-500 hover:text-blue-400 text-sm transition-colors">{{ link.name }}</router-link>
              </li>
            </ul>
          </div>
        </div>
        <div class="border-t border-white/5 pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
          <p class="text-slate-600 text-xs">© {{ currentYear }} CV Builder. All rights reserved.</p>
          <div class="flex items-center gap-1 text-xs text-slate-600">
            <span>Built with</span>
            <span class="text-red-400">♥</span>
            <span>using Vue & Laravel</span>
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
