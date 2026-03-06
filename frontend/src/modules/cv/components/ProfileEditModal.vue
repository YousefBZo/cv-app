<script setup>
/**
 * ProfileEditModal — 3-tab modal for editing profile, account name,
 * and password.  All state comes from the useProfileModal composable
 * via props; this component is purely presentational.
 */
import EditModal from '@/shared/components/EditModal.vue'
import LoadingSpinner from '@/shared/components/LoadingSpinner.vue'
import { useAuthStore } from '@/modules/auth/stores/auth'

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
    title="Edit Profile & Account"
    :loading="profileLoading"
    show-delete
    @close="emit('close')"
    @delete="emit('deleteAccount')"
  >
    <!-- Tab Navigation -->
    <div class="flex gap-1 mb-6 bg-white/5 rounded-xl p-1">
      <button @click="emit('update:tab', 'profile')"
        :class="tab === 'profile' ? 'bg-blue-500/20 text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-300'"
        class="flex-1 py-2 px-3 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
        Profile
      </button>
      <button @click="emit('update:tab', 'account')"
        :class="tab === 'account' ? 'bg-blue-500/20 text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-300'"
        class="flex-1 py-2 px-3 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
        Account
      </button>
      <button @click="emit('update:tab', 'password')"
        :class="tab === 'password' ? 'bg-blue-500/20 text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-300'"
        class="flex-1 py-2 px-3 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
        Password
      </button>
    </div>

    <!-- ── PROFILE TAB ── -->
    <form v-if="tab === 'profile'" @submit.prevent="emit('saveProfile')" class="space-y-4">
      <div>
        <label class="label-dark">Profile Photo</label>
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
        <label class="label-dark">Headline</label>
        <input v-model="profileForm.headline" class="input-dark" placeholder="e.g. Full Stack Developer" />
        <p v-if="profileErrors.headline" class="text-red-400 text-xs mt-1">{{ profileErrors.headline[0] }}</p>
      </div>
      <div>
        <label class="label-dark">Summary</label>
        <textarea v-model="profileForm.summary" rows="3" class="input-dark resize-none" placeholder="Write about yourself..."></textarea>
        <p v-if="profileErrors.summary" class="text-red-400 text-xs mt-1">{{ profileErrors.summary[0] }}</p>
      </div>
      <div>
        <label class="label-dark">Location</label>
        <input v-model="profileForm.location" class="input-dark" placeholder="e.g. New York, USA" />
        <p v-if="profileErrors.location" class="text-red-400 text-xs mt-1">{{ profileErrors.location[0] }}</p>
      </div>
      <button type="submit" :disabled="profileLoading" class="btn-primary w-full flex items-center justify-center gap-2">
        <LoadingSpinner v-if="profileLoading" /> {{ profileLoading ? 'Saving...' : 'Update Profile' }}
      </button>
    </form>

    <!-- ── ACCOUNT TAB ── -->
    <form v-else-if="tab === 'account'" @submit.prevent="emit('saveUserName')" class="space-y-4">
      <div class="text-center mb-2">
        <p class="text-sm text-slate-400">Update your display name. Your email cannot be changed.</p>
      </div>
      <div>
        <label class="label-dark">Display Name</label>
        <input v-model="userNameForm.name" class="input-dark" placeholder="Your name" />
        <p v-if="userNameErrors.name" class="text-red-400 text-xs mt-1">{{ userNameErrors.name[0] }}</p>
      </div>
      <div>
        <label class="label-dark">Email</label>
        <input :value="authStore.user?.email" class="input-dark opacity-50 cursor-not-allowed" disabled />
        <p class="text-slate-600 text-xs mt-1">Email cannot be changed.</p>
      </div>
      <button type="submit" :disabled="userNameLoading" class="btn-primary w-full flex items-center justify-center gap-2">
        <LoadingSpinner v-if="userNameLoading" /> {{ userNameLoading ? 'Saving...' : 'Update Name' }}
      </button>
    </form>

    <!-- ── PASSWORD TAB ── -->
    <form v-else-if="tab === 'password'" @submit.prevent="emit('savePassword')" class="space-y-4">
      <div class="text-center mb-2">
        <p class="text-sm text-slate-400">Change your account password.</p>
      </div>
      <div>
        <label class="label-dark">Current Password</label>
        <input type="password" v-model="passwordForm.current_password" class="input-dark" placeholder="Enter current password" />
        <p v-if="passwordErrors.current_password" class="text-red-400 text-xs mt-1">{{ passwordErrors.current_password[0] }}</p>
      </div>
      <div>
        <label class="label-dark">New Password</label>
        <input type="password" v-model="passwordForm.password" class="input-dark" placeholder="Min 8 characters" />
        <p v-if="passwordErrors.password" class="text-red-400 text-xs mt-1">{{ passwordErrors.password[0] }}</p>
      </div>
      <div>
        <label class="label-dark">Confirm New Password</label>
        <input type="password" v-model="passwordForm.password_confirmation" class="input-dark" placeholder="Repeat new password" />
        <p v-if="passwordErrors.password_confirmation" class="text-red-400 text-xs mt-1">{{ passwordErrors.password_confirmation[0] }}</p>
      </div>
      <button type="submit" :disabled="passwordLoading" class="btn-primary w-full flex items-center justify-center gap-2">
        <LoadingSpinner v-if="passwordLoading" /> {{ passwordLoading ? 'Updating...' : 'Change Password' }}
      </button>
    </form>

    <div class="mt-4 pt-4 border-t border-white/5">
      <p class="text-xs text-slate-600 text-center">The Delete button below will permanently delete your entire account.</p>
    </div>
  </EditModal>
</template>
