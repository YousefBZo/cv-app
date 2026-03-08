import { createI18n } from 'vue-i18n'
import en from './locales/en.json'
import ar from './locales/ar.json'

/**
 * i18n — Internationalization Setup
 *
 * WHY vue-i18n?
 *   - Official Vue 3 i18n solution with Composition API support
 *   - Built-in pluralization (Arabic has 6 plural forms!)
 *   - Vue Devtools integration for debugging translations
 *   - Tree-shakeable — unused features are stripped at build time
 *
 * DESIGN DECISIONS:
 *   1. Single JSON file per language (not split per module) — simpler for
 *      a project with ~200 strings. Split when you exceed ~500 keys.
 *   2. Flat-ish keys with dot notation (e.g., 'nav.home') — easy to search.
 *   3. localStorage persistence — user's choice survives page refreshes.
 *   4. fallbackLocale: 'en' — if an Arabic key is missing, English shows
 *      instead of a broken key like "nav.home".
 *
 * HOW LOCALE IS STORED:
 *   localStorage.getItem('locale') → 'en' | 'ar'
 *   Default: 'en' (English)
 */

const savedLocale = localStorage.getItem('locale') || 'en'

const i18n = createI18n({
  legacy: false,               // Use Composition API mode ($t still works in templates)
  locale: savedLocale,         // Active locale
  fallbackLocale: 'en',        // If a key is missing in Arabic, show English
  messages: { en, ar },        // Eagerly load both — only 2 languages, very small
})

export default i18n
