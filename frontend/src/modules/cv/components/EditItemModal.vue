<script setup>
import EditModal from '@/shared/components/EditModal.vue'
import LoadingSpinner from '@/shared/components/LoadingSpinner.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  visible: { type: Boolean, default: false },
  title: { type: String, default: '' },
  section: { type: String, default: '' },
  form: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
  errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['close', 'save', 'delete'])
</script>

<template>
  <EditModal
    :visible="visible"
    :title="title"
    :loading="loading"
    show-delete
    @close="emit('close')"
    @delete="emit('delete')"
  >
    <p v-if="errors.general" class="text-red-400 text-sm mb-4 bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-2">
      {{ errors.general[0] }}
    </p>

    <!-- EXPERIENCE FORM -->
    <form v-if="section === 'experience'" @submit.prevent="emit('save')" class="space-y-4">
      <div>
        <label class="label-dark">{{ t('forms.company') }}</label>
        <input v-model="form.company" class="input-dark" />
        <p v-if="errors.company" class="text-red-400 text-xs mt-1">{{ errors.company[0] }}</p>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.position') }}</label>
        <input v-model="form.position" class="input-dark" />
        <p v-if="errors.position" class="text-red-400 text-xs mt-1">{{ errors.position[0] }}</p>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label-dark">{{ t('forms.startDate') }}</label>
          <input type="date" v-model="form.start_date" class="input-dark" />
          <p v-if="errors.start_date" class="text-red-400 text-xs mt-1">{{ errors.start_date[0] }}</p>
        </div>
        <div>
          <label class="label-dark">{{ t('forms.endDate') }}</label>
          <input type="date" v-model="form.end_date" class="input-dark" />
          <p v-if="errors.end_date" class="text-red-400 text-xs mt-1">{{ errors.end_date[0] }}</p>
        </div>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.description') }}</label>
        <textarea v-model="form.description" rows="3" class="input-dark resize-none"></textarea>
        <p v-if="errors.description" class="text-red-400 text-xs mt-1">{{ errors.description[0] }}</p>
      </div>
      <button type="submit" :disabled="loading" class="btn-primary w-full flex items-center justify-center gap-2">
        <LoadingSpinner v-if="loading" /> {{ loading ? t('forms.saving') : t('forms.saveChanges') }}
      </button>
    </form>

    <!-- EDUCATION FORM -->
    <form v-else-if="section === 'education'" @submit.prevent="emit('save')" class="space-y-4">
      <div>
        <label class="label-dark">{{ t('forms.institution') }}</label>
        <input v-model="form.institution" class="input-dark" />
        <p v-if="errors.institution" class="text-red-400 text-xs mt-1">{{ errors.institution[0] }}</p>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.degree') }}</label>
        <input v-model="form.degree" class="input-dark" />
        <p v-if="errors.degree" class="text-red-400 text-xs mt-1">{{ errors.degree[0] }}</p>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.fieldOfStudy') }}</label>
        <input v-model="form.field_of_study" class="input-dark" />
        <p v-if="errors.field_of_study" class="text-red-400 text-xs mt-1">{{ errors.field_of_study[0] }}</p>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label-dark">{{ t('forms.startDate') }}</label>
          <input type="date" v-model="form.start_date" class="input-dark" />
          <p v-if="errors.start_date" class="text-red-400 text-xs mt-1">{{ errors.start_date[0] }}</p>
        </div>
        <div>
          <label class="label-dark">{{ t('forms.endDate') }}</label>
          <input type="date" v-model="form.end_date" class="input-dark" />
          <p v-if="errors.end_date" class="text-red-400 text-xs mt-1">{{ errors.end_date[0] }}</p>
        </div>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.description') }}</label>
        <textarea v-model="form.description" rows="3" class="input-dark resize-none"></textarea>
        <p v-if="errors.description" class="text-red-400 text-xs mt-1">{{ errors.description[0] }}</p>
      </div>
      <button type="submit" :disabled="loading" class="btn-primary w-full flex items-center justify-center gap-2">
        <LoadingSpinner v-if="loading" /> {{ loading ? t('forms.saving') : t('forms.saveChanges') }}
      </button>
    </form>

    <!-- PROJECT FORM -->
    <form v-else-if="section === 'project'" @submit.prevent="emit('save')" class="space-y-4">
      <div>
        <label class="label-dark">{{ t('forms.title') }}</label>
        <input v-model="form.title" class="input-dark" />
        <p v-if="errors.title" class="text-red-400 text-xs mt-1">{{ errors.title[0] }}</p>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.description') }}</label>
        <textarea v-model="form.description" rows="3" class="input-dark resize-none"></textarea>
        <p v-if="errors.description" class="text-red-400 text-xs mt-1">{{ errors.description[0] }}</p>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.liveLink') }}</label>
        <input v-model="form.link" class="input-dark" :placeholder="t('forms.liveLinkPlaceholder')" />
        <p v-if="errors.link" class="text-red-400 text-xs mt-1">{{ errors.link[0] }}</p>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.githubUrl') }}</label>
        <input v-model="form.github_url" class="input-dark" :placeholder="t('forms.githubUrlPlaceholder')" />
        <p v-if="errors.github_url" class="text-red-400 text-xs mt-1">{{ errors.github_url[0] }}</p>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label-dark">{{ t('forms.startDate') }}</label>
          <input type="date" v-model="form.start_date" class="input-dark" />
          <p v-if="errors.start_date" class="text-red-400 text-xs mt-1">{{ errors.start_date[0] }}</p>
        </div>
        <div>
          <label class="label-dark">{{ t('forms.endDate') }}</label>
          <input type="date" v-model="form.end_date" class="input-dark" />
          <p v-if="errors.end_date" class="text-red-400 text-xs mt-1">{{ errors.end_date[0] }}</p>
        </div>
      </div>
      <button type="submit" :disabled="loading" class="btn-primary w-full flex items-center justify-center gap-2">
        <LoadingSpinner v-if="loading" /> {{ loading ? t('forms.saving') : t('forms.saveChanges') }}
      </button>
    </form>

    <!-- CERTIFICATION FORM -->
    <form v-else-if="section === 'certification'" @submit.prevent="emit('save')" class="space-y-4">
      <div>
        <label class="label-dark">{{ t('forms.certName') }}</label>
        <input v-model="form.name" class="input-dark" />
        <p v-if="errors.name" class="text-red-400 text-xs mt-1">{{ errors.name[0] }}</p>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.organization') }}</label>
        <input v-model="form.organization" class="input-dark" />
        <p v-if="errors.organization" class="text-red-400 text-xs mt-1">{{ errors.organization[0] }}</p>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label-dark">{{ t('forms.issueDate') }}</label>
          <input type="date" v-model="form.issue_date" class="input-dark" />
          <p v-if="errors.issue_date" class="text-red-400 text-xs mt-1">{{ errors.issue_date[0] }}</p>
        </div>
        <div>
          <label class="label-dark">{{ t('forms.expirationDate') }}</label>
          <input type="date" v-model="form.expiration_date" class="input-dark" />
          <p v-if="errors.expiration_date" class="text-red-400 text-xs mt-1">{{ errors.expiration_date[0] }}</p>
        </div>
      </div>
      <button type="submit" :disabled="loading" class="btn-primary w-full flex items-center justify-center gap-2">
        <LoadingSpinner v-if="loading" /> {{ loading ? t('forms.saving') : t('forms.saveChanges') }}
      </button>
    </form>

    <!-- VOLUNTEER FORM -->
    <form v-else-if="section === 'volunteer'" @submit.prevent="emit('save')" class="space-y-4">
      <div>
        <label class="label-dark">{{ t('forms.organization') }}</label>
        <input v-model="form.organization" class="input-dark" />
        <p v-if="errors.organization" class="text-red-400 text-xs mt-1">{{ errors.organization[0] }}</p>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.role') }}</label>
        <input v-model="form.role" class="input-dark" />
        <p v-if="errors.role" class="text-red-400 text-xs mt-1">{{ errors.role[0] }}</p>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label-dark">{{ t('forms.startDate') }}</label>
          <input type="date" v-model="form.start_date" class="input-dark" />
          <p v-if="errors.start_date" class="text-red-400 text-xs mt-1">{{ errors.start_date[0] }}</p>
        </div>
        <div>
          <label class="label-dark">{{ t('forms.endDate') }}</label>
          <input type="date" v-model="form.end_date" class="input-dark" />
          <p v-if="errors.end_date" class="text-red-400 text-xs mt-1">{{ errors.end_date[0] }}</p>
        </div>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.description') }}</label>
        <textarea v-model="form.description" rows="3" class="input-dark resize-none"></textarea>
        <p v-if="errors.description" class="text-red-400 text-xs mt-1">{{ errors.description[0] }}</p>
      </div>
      <button type="submit" :disabled="loading" class="btn-primary w-full flex items-center justify-center gap-2">
        <LoadingSpinner v-if="loading" /> {{ loading ? t('forms.saving') : t('forms.saveChanges') }}
      </button>
    </form>

    <!-- SKILL (edit level + delete) -->
    <div v-else-if="section === 'skill'" class="space-y-5">
      <div class="text-center">
        <span class="text-white font-bold text-lg">{{ form.name }}</span>
      </div>
      <form @submit.prevent="emit('save')" class="space-y-4">
        <div>
          <label class="label-dark">{{ t('forms.proficiencyLevel') }}</label>
          <select v-model="form.level" class="input-dark">
            <option value="beginner">{{ t('levels.beginner') }}</option>
            <option value="intermediate">{{ t('levels.intermediate') }}</option>
            <option value="advanced">{{ t('levels.advanced') }}</option>
            <option value="expert">{{ t('levels.expert') }}</option>
          </select>
          <p v-if="errors.level" class="text-red-400 text-xs mt-1">{{ errors.level[0] }}</p>
        </div>
        <button type="submit" :disabled="loading" class="btn-primary w-full flex items-center justify-center gap-2">
          <LoadingSpinner v-if="loading" /> {{ loading ? t('forms.saving') : t('forms.updateLevel') }}
        </button>
      </form>
    </div>

    <!-- LANGUAGE (edit level + delete) -->
    <div v-else-if="section === 'language'" class="space-y-5">
      <div class="text-center">
        <span class="text-white font-bold text-lg">{{ form.name }}</span>
      </div>
      <form @submit.prevent="emit('save')" class="space-y-4">
        <div>
          <label class="label-dark">{{ t('forms.proficiencyLevel') }}</label>
          <select v-model="form.level" class="input-dark">
            <option value="beginner">{{ t('levels.beginner') }}</option>
            <option value="elementary">{{ t('levels.elementary') }}</option>
            <option value="intermediate">{{ t('levels.intermediate') }}</option>
            <option value="upper_intermediate">{{ t('levels.upper_intermediate') }}</option>
            <option value="advanced">{{ t('levels.advanced') }}</option>
            <option value="native">{{ t('levels.native') }}</option>
          </select>
          <p v-if="errors.level" class="text-red-400 text-xs mt-1">{{ errors.level[0] }}</p>
        </div>
        <button type="submit" :disabled="loading" class="btn-primary w-full flex items-center justify-center gap-2">
          <LoadingSpinner v-if="loading" /> {{ loading ? t('forms.saving') : t('forms.updateLevel') }}
        </button>
      </form>
    </div>
  </EditModal>
</template>
