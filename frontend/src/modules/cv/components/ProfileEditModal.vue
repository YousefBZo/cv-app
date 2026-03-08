<script setup>
/**
 * ProfileEditModal — 3-tab modal for editing profile, account name,
 * and password.  All state comes from the useProfileModal composable
 * via props; this component is purely presentational.
 */
import EditModal from '@/shared/components/EditModal.vue'
import LoadingSpinner from '@/shared/components/LoadingSpinner.vue'
import { useAuthStore } from '@/modules/auth/stores/auth'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const authStore = useAuthStore()

const props = defineProps({
  visible: { type: Boolean, default: false },
  tab: { type: String, default: 'profile' },
  // Profile
  profileForm: { type: Object, default: () => ({}) },
  profilePhotoPreview: { type: String, default: null },
  profileLoading: { type: Boolean, default: false },
  profileErrors: { type: Object, default: () => ({}) },
  // Account
  userNameForm: { type: Object, default: () => ({}) },
  userNameLoading: { type: Boolean, default: false },
  userNameErrors: { type: Object, default: () => ({}) },
  // Password
  passwordForm: { type: Object, default: () => ({}) },
  passwordLoading: { type: Boolean, default: false },
  passwordErrors: { type: Object, default: () => ({}) },
})

const emit = defineEmits([
  'close',
  'update:tab',
  'saveProfile',
  'saveUserName',
  'savePassword',
  'deleteAccount',
  'photoChange',
])
</script>

<template>
  <EditModal
    :visible="visible"
    :title="t('forms.editProfile')"
    :loading="profileLoading"
    show-delete
    @close="emit('close')"
    @delete="emit('deleteAccount')"
  >
    <!-- Tab Navigation -->
    <div class="flex gap-1 mb-6 bg-white/5 rounded-xl p-1">
      <button @click="emit('update:tab', 'profile')"
        :class="tab === 'profile' ? 'bg-blue-500/20 text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-300'"
        class="flex-1 py-2 px-2 sm:px-3 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
        {{ t('forms.profileTab') }}
      </button>
      <button @click="emit('update:tab', 'account')"
        :class="tab === 'account' ? 'bg-blue-500/20 text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-300'"
        class="flex-1 py-2 px-2 sm:px-3 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
        {{ t('forms.accountTab') }}
      </button>
      <button @click="emit('update:tab', 'password')"
        :class="tab === 'password' ? 'bg-blue-500/20 text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-300'"
        class="flex-1 py-2 px-2 sm:px-3 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
        {{ t('forms.passwordTab') }}
      </button>
    </div>

    <!-- ── PROFILE TAB ── -->
    <form v-if="tab === 'profile'" @submit.prevent="emit('saveProfile')" class="space-y-4">
      <div>
        <label class="label-dark">{{ t('forms.profilePhoto') }}</label>
        <div class="flex items-center gap-4">
          <div class="w-16 h-16 rounded-xl bg-white/5 border border-white/10 overflow-hidden flex items-center justify-center shrink-0">
            <img v-if="profilePhotoPreview" :src="profilePhotoPreview" alt="Preview" class="w-full h-full object-cover" />
            <span v-else class="text-2xl">📷</span>
          </div>
          <input type="file" accept="image/*" @change="emit('photoChange', $event)" class="input-dark flex-1" />
        </div>
        <p v-if="profileErrors.photo" class="text-red-400 text-xs mt-1">{{ profileErrors.photo[0] }}</p>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.headline') }}</label>
        <input v-model="profileForm.headline" class="input-dark" :placeholder="t('forms.headlinePlaceholder')" />
        <p v-if="profileErrors.headline" class="text-red-400 text-xs mt-1">{{ profileErrors.headline[0] }}</p>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.summary') }}</label>
        <textarea v-model="profileForm.summary" rows="3" class="input-dark resize-none" :placeholder="t('forms.summaryPlaceholder')"></textarea>
        <p v-if="profileErrors.summary" class="text-red-400 text-xs mt-1">{{ profileErrors.summary[0] }}</p>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.location') }}</label>
        <input v-model="profileForm.location" class="input-dark" :placeholder="t('forms.locationPlaceholder')" />
        <p v-if="profileErrors.location" class="text-red-400 text-xs mt-1">{{ profileErrors.location[0] }}</p>
      </div>
      <button type="submit" :disabled="profileLoading" class="btn-primary w-full flex items-center justify-center gap-2">
        <LoadingSpinner v-if="profileLoading" /> {{ profileLoading ? t('forms.saving') : t('forms.updateProfile') }}
      </button>
    </form>

    <!-- ── ACCOUNT TAB ── -->
    <form v-else-if="tab === 'account'" @submit.prevent="emit('saveUserName')" class="space-y-4">
      <div class="text-center mb-2">
        <p class="text-sm text-slate-400">{{ t('forms.updateNameDesc') }}</p>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.displayName') }}</label>
        <input v-model="userNameForm.name" class="input-dark" :placeholder="t('forms.displayNamePlaceholder')" />
        <p v-if="userNameErrors.name" class="text-red-400 text-xs mt-1">{{ userNameErrors.name[0] }}</p>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.email') }}</label>
        <input :value="authStore.user?.email" class="input-dark opacity-50 cursor-not-allowed" disabled />
        <p class="text-slate-600 text-xs mt-1">{{ t('forms.emailCannotChange') }}</p>
      </div>
      <button type="submit" :disabled="userNameLoading" class="btn-primary w-full flex items-center justify-center gap-2">
        <LoadingSpinner v-if="userNameLoading" /> {{ userNameLoading ? t('forms.saving') : t('forms.updateName') }}
      </button>
    </form>

    <!-- ── PASSWORD TAB ── -->
    <form v-else-if="tab === 'password'" @submit.prevent="emit('savePassword')" class="space-y-4">
      <div class="text-center mb-2">
        <p class="text-sm text-slate-400">{{ t('forms.changePasswordDesc') }}</p>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.currentPassword') }}</label>
        <input type="password" v-model="passwordForm.current_password" class="input-dark" :placeholder="t('forms.currentPasswordPlaceholder')" />
        <p v-if="passwordErrors.current_password" class="text-red-400 text-xs mt-1">{{ passwordErrors.current_password[0] }}</p>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.newPassword') }}</label>
        <input type="password" v-model="passwordForm.password" class="input-dark" :placeholder="t('forms.newPasswordPlaceholder')" />
        <p v-if="passwordErrors.password" class="text-red-400 text-xs mt-1">{{ passwordErrors.password[0] }}</p>
      </div>
      <div>
        <label class="label-dark">{{ t('forms.confirmNewPassword') }}</label>
        <input type="password" v-model="passwordForm.password_confirmation" class="input-dark" :placeholder="t('forms.confirmNewPasswordPlaceholder')" />
        <p v-if="passwordErrors.password_confirmation" class="text-red-400 text-xs mt-1">{{ passwordErrors.password_confirmation[0] }}</p>
      </div>
      <button type="submit" :disabled="passwordLoading" class="btn-primary w-full flex items-center justify-center gap-2">
        <LoadingSpinner v-if="passwordLoading" /> {{ passwordLoading ? t('forms.updating') : t('forms.changePassword') }}
      </button>
    </form>

    <div class="mt-4 pt-4 border-t border-white/5">
      <p class="text-xs text-slate-600 text-center">{{ t('forms.deleteAccountFooter') }}</p>
    </div>
  </EditModal>
</template>
