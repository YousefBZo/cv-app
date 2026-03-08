import { ref, reactive } from 'vue'
import { useCVStore } from '@/modules/cv/stores/cv'
import { useToastStore } from '@/shared/stores/toast'
import i18n from '@/i18n'
import {
  validateExperience,
  validateEducation,
  validateProject,
  validateCertification,
  validateVolunteer,
  hasErrors,
} from '@/modules/cv/composables/useValidation'

/**
 * Composable — encapsulates all state & logic for the generic
 * "edit item" modal (experience, education, project, cert, volunteer,
 * skill, language).
 *
 * The view only needs to wire `open`, `close`, and bind the returned
 * refs to the <EditItemModal> component via v-model / props.
 */
export function useEditModal() {
  const cvStore = useCVStore()

  const visible = ref(false)
  const title = ref('')
  const section = ref('')
  const itemId = ref(null)
  const loading = ref(false)
  const errors = ref({})
  const form = reactive({})

  /** Open the modal pre-filled with the item's data */
  function open(sectionName, item, modalTitle) {
    section.value = sectionName
    itemId.value = item.id
    title.value = modalTitle
    errors.value = {}

    // Reset & clone
    Object.keys(form).forEach((k) => delete form[k])
    Object.assign(form, JSON.parse(JSON.stringify(item)))

    // Normalise date fields to YYYY-MM-DD for <input type="date">
    for (const key of ['start_date', 'end_date', 'issue_date', 'expiration_date']) {
      if (form[key]) form[key] = form[key].substring(0, 10)
    }

    visible.value = true
  }

  function close() {
    visible.value = false
    errors.value = {}
  }

  /** Validate → persist → close */
  async function save() {
    errors.value = {}

    // Client-side validation per section
    const validators = {
      experience: validateExperience,
      education: validateEducation,
      project: validateProject,
      certification: validateCertification,
      volunteer: validateVolunteer,
    }

    const validate = validators[section.value]
    if (validate) {
      const v = validate(form)
      if (hasErrors(v)) {
        errors.value = v
        return
      }
    }

    loading.value = true
    try {
      let payload
      if (section.value === 'skill' || section.value === 'language') {
        payload = { level: form.level }
      } else {
        payload = { ...form }
        delete payload.id
        delete payload.profile_id
        delete payload.created_at
        delete payload.updated_at
        delete payload.pivot
        delete payload.photo
        delete payload.cover
      }
      await cvStore.updateItem(section.value, itemId.value, payload)
      close()
    } catch (err) {
      if (err.response?.status === 422) {
        errors.value = err.response.data.errors || {}
      } else {
        errors.value = { general: [err.response?.data?.message || i18n.global.t('validation.genericError')] }
      }
    } finally {
      loading.value = false
    }
  }

  /** Delete the current item after confirmation */
  async function remove() {
    if (!confirm(i18n.global.t('forms.deleteItemConfirm'))) return
    loading.value = true
    try {
      await cvStore.deleteItem(section.value, itemId.value)
      close()
    } catch {
      // Error handled by store
    } finally {
      loading.value = false
    }
  }

  return {
    visible,
    title,
    section,
    form,
    loading,
    errors,
    open,
    close,
    save,
    remove,
  }
}
