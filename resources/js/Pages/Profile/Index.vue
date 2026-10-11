<template>
  <Layout :role="profile.role">
    <v-container fluid>
      <v-row>
        <v-col cols="12">
          <h1 class="text-h4 mb-6">{{ t('navigation.profile') }}</h1>
        </v-col>
      </v-row>

      <v-row>
        <v-col cols="12" md="4">
          <v-card>
            <v-card-text class="text-center">
              <v-avatar size="96" color="primary" class="mb-4">
                <span class="text-h4 text-white">{{ initials }}</span>
              </v-avatar>
              <h2 class="text-h5 mb-2">{{ profile.full_name }}</h2>
              <v-chip v-if="roleLabel" color="primary" variant="tonal">
                {{ roleLabel }}
              </v-chip>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="8">
          <v-card class="mb-4">
            <v-card-title>{{ t('education_department.users.basic_info') }}</v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    :model-value="display(profile.last_name)"
                    :label="t('education_department.users.last_name')"
                    variant="outlined"
                    density="comfortable"
                    readonly
                  />
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    :model-value="display(profile.name)"
                    :label="t('education_department.users.name')"
                    variant="outlined"
                    density="comfortable"
                    readonly
                  />
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    :model-value="display(profile.middle_name)"
                    :label="t('education_department.users.middle_name')"
                    variant="outlined"
                    density="comfortable"
                    readonly
                  />
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    :model-value="roleLabel"
                    :label="t('education_department.users.role')"
                    variant="outlined"
                    density="comfortable"
                    readonly
                  />
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    :model-value="display(profile.email)"
                    label="Email"
                    variant="outlined"
                    density="comfortable"
                    readonly
                  />
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    :model-value="display(profile.phone)"
                    :label="t('auth.phone')"
                    variant="outlined"
                    density="comfortable"
                    readonly
                  />
                </v-col>
                <v-col v-if="profile.group" cols="12" md="6">
                  <v-text-field
                    :model-value="profile.group"
                    :label="t('education_department.users.group')"
                    variant="outlined"
                    density="comfortable"
                    readonly
                  />
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>

          <v-card>
            <v-card-title>
              <v-icon class="mr-2">mdi-lock</v-icon>
              {{ t('auth.change_password') }}
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submit">
                <v-text-field
                  v-model="form.current_password"
                  :label="t('auth.current_password')"
                  :type="showCurrent ? 'text' : 'password'"
                  :append-inner-icon="showCurrent ? 'mdi-eye-off' : 'mdi-eye'"
                  :error-messages="form.errors.current_password"
                  variant="outlined"
                  density="comfortable"
                  autocomplete="current-password"
                  class="mb-2"
                  @click:append-inner="showCurrent = !showCurrent"
                />
                <v-text-field
                  v-model="form.password"
                  :label="t('auth.new_password')"
                  :type="showPassword ? 'text' : 'password'"
                  :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                  :error-messages="form.errors.password"
                  :hint="t('auth.password_min', { min: 4 })"
                  persistent-hint
                  variant="outlined"
                  density="comfortable"
                  autocomplete="new-password"
                  class="mb-2"
                  @click:append-inner="showPassword = !showPassword"
                />
                <v-text-field
                  v-model="form.password_confirmation"
                  :label="t('auth.confirm_password')"
                  :type="showConfirmation ? 'text' : 'password'"
                  :append-inner-icon="showConfirmation ? 'mdi-eye-off' : 'mdi-eye'"
                  :error-messages="form.errors.password_confirmation"
                  variant="outlined"
                  density="comfortable"
                  autocomplete="new-password"
                  @click:append-inner="showConfirmation = !showConfirmation"
                />
                <div class="d-flex justify-end mt-4">
                  <v-btn
                    type="submit"
                    color="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                  >
                    {{ t('auth.change_password') }}
                  </v-btn>
                </div>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useForm } from '@/compat/inertia'
import { useI18n } from 'vue-i18n'
import Layout from '../Layout.vue'

const props = defineProps({
  profile: {
    type: Object,
    required: true,
  },
})

const { t } = useI18n()
const route = useRoute()
const showCurrent = ref(false)
const showPassword = ref(false)
const showConfirmation = ref(false)

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const passwordUrl = computed(() => `${route.path.replace(/\/$/, '')}/password`)

const roleLabel = computed(() => {
  const role = props.profile?.role
  return role ? t(`roles.${role}`) : ''
})

const initials = computed(() => {
  const source = props.profile?.full_name || props.profile?.name || ''
  return source
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part: string) => part.charAt(0).toUpperCase())
    .join('')
})

function display(value: string | null | undefined) {
  return value || '—'
}

function submit() {
  form.post(passwordUrl.value, {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
    },
  })
}
</script>
