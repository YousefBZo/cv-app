/**
 * Pure helper functions for mapping skill / language level strings
 * to display widths, labels and gradient colors.
 *
 * Extracted from CVView so every section component can import
 * only what it needs without duplicating logic.
 */

export function getLangLevel(level) {
  if (!level) return { width: '50%', label: 'Conversational', color: 'from-slate-500 to-slate-400' }
  const l = String(level).toLowerCase()
  if (l.includes('native') || l.includes('fluent')) return { width: '100%', label: level, color: 'from-emerald-500 to-teal-400' }
  if (l.includes('advanced') || l.includes('proficient')) return { width: '80%', label: level, color: 'from-blue-500 to-cyan-400' }
  if (l.includes('intermediate') || l.includes('conversational')) return { width: '55%', label: level, color: 'from-amber-500 to-yellow-400' }
  if (l.includes('beginner') || l.includes('basic') || l.includes('elementary')) return { width: '30%', label: level, color: 'from-rose-500 to-pink-400' }
  return { width: '50%', label: level, color: 'from-slate-500 to-slate-400' }
}

export function getSkillLevel(level) {
  if (!level) return '70%'
  const l = String(level).toLowerCase()
  if (l.includes('expert') || l.includes('senior')) return '95%'
  if (l.includes('advanced') || l.includes('intermediate')) return '75%'
  if (l.includes('beginner') || l.includes('junior')) return '35%'
  return '65%'
}
