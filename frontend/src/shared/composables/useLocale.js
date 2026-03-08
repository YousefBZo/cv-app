import { computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'

/**
 * useLocale — composable for switching between languages.
 *
 * WHY a dedicated composable?
 *   - Centralizes locale-switching logic (change i18n locale, save to
 *     localStorage, toggle RTL on <html>).
 *   - Any component can call `toggleLocale()` without knowing the details.
 *   - The watcher ensures <html dir="rtl" lang="ar"> is always in sync.
 *
 * RTL HANDLING:
 *   Arabic is right-to-left. We set `dir="rtl"` on <html> so that:
 *     - Tailwind's `rtl:` variants activate automatically
 *     - Browser native form controls flip direction
 *     - The entire document flow reverses
 */
export function useLocale() {
  const { locale, t } = useI18n()

  const isRTL = computed(() => locale.value === 'ar')
  const currentLang = computed(() => locale.value)

  /**
   * Apply dir and lang attributes to <html>.
   * Called on initial load and whenever locale changes.
   */
  function applyDirection(lang) {
    const dir = lang === 'ar' ? 'rtl' : 'ltr'
    document.documentElement.setAttribute('dir', dir)
    document.documentElement.setAttribute('lang', lang)
  }

  /**
   * Toggle between English ↔ Arabic.
   */
  function toggleLocale() {
    const newLocale = locale.value === 'en' ? 'ar' : 'en'
    locale.value = newLocale
    localStorage.setItem('locale', newLocale)
    applyDirection(newLocale)
  }

  /**
   * Set a specific locale.
   */
  function setLocale(lang) {
    locale.value = lang
    localStorage.setItem('locale', lang)
    applyDirection(lang)
  }

  // Apply direction on first use (page load)
  applyDirection(locale.value)

  // Watch for external changes (e.g., another tab)
  watch(locale, (newVal) => {
    applyDirection(newVal)
  })

  return {
    locale: currentLang,
    isRTL,
    toggleLocale,
    setLocale,
    t,
  }
}
