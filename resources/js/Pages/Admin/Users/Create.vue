<template>
  <AdminApp>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('admin.users.create_user') }}</h1>
              <p class="text-body-1 text-medium-emphasis">{{ t('admin.users.create_user_subtitle') }}</p>
            </div>
            <v-btn
              color="secondary"
              variant="outlined"
              @click="navigateTo('/admin/users')"
              prepend-icon="mdi-arrow-left"
            >
              {{ t('admin.users.back_to_list') }}
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Форма создания пользователя -->
      <v-row justify="center">
        <v-col cols="12" md="10" lg="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-account-plus</v-icon>
              {{ t('admin.users.user_info') }}
            </v-card-title>
            <v-card-text>
                             <v-form @submit.prevent="submitForm">
                 <!-- Основная информация -->
                 <h3 class="text-h6 mb-4">{{ t('admin.users.basic_info') }}</h3>
                 <v-row>
                   <v-col cols="12" md="4">
                     <v-text-field
                       v-model="form.name"
                       :label="t('admin.users.name')"
                       variant="outlined"
                       density="compact"
                       :error-messages="form.errors.name"
                       required
                     ></v-text-field>
                   </v-col>
                   <v-col cols="12" md="4">
                     <v-text-field
                       v-model="form.last_name"
                       :label="t('admin.users.last_name')"
                       variant="outlined"
                       density="compact"
                       :error-messages="form.errors.last_name"
                       required
                     ></v-text-field>
                   </v-col>
                   <v-col cols="12" md="4">
                     <v-text-field
                       v-model="form.middle_name"
                       :label="t('admin.users.middle_name')"
                       variant="outlined"
                       density="compact"
                       :error-messages="form.errors.middle_name"
                     ></v-text-field>
                   </v-col>
                 </v-row>

                 <!-- Контактная информация -->
                 <h3 class="text-h6 mb-4 mt-6">{{ t('admin.users.contact_info') }}</h3>
                 <v-row>
                  <v-col cols="12" md="6">
                    <PhoneInput
                      v-model="form.phone"
                      :label="t('admin.users.phone')"
                      :error-messages="form.errors.phone"
                      density="compact"
                    />
                  </v-col>
                 </v-row>

                 <!-- Адрес -->
                 <v-row>
                   <v-col cols="12">
                     <v-text-field
                       v-model="form.address"
                       :label="t('admin.users.address')"
                       variant="outlined"
                       density="compact"
                       :error-messages="form.errors.address"
                     ></v-text-field>
                   </v-col>
                 </v-row>

                 <!-- Роль и группа -->
                 <h3 class="text-h6 mb-4 mt-6">{{ t('admin.users.system_info') }}</h3>
                 <v-row>
                   <v-col cols="12" md="6">
                     <v-select
                       v-model="form.role_id"
                       :items="roles"
                       item-title="display_name"
                       item-value="id"
                       :label="t('admin.users.role')"
                       variant="outlined"
                       density="compact"
                       :error-messages="form.errors.role_id"
                       required
                     ></v-select>
                   </v-col>
                   <v-col v-if="isStudent" cols="12" md="6">
                     <v-select
                       v-model="form.group_id"
                       :items="groups"
                       item-title="display_name"
                       item-value="id"
                       :label="t('admin.users.group')"
                       variant="outlined"
                       density="compact"
                       :error-messages="form.errors.group_id"
                       clearable
                       :placeholder="t('admin.users.select_group')"
                     ></v-select>
                   </v-col>
                 </v-row>

                 <!-- Телефоны родителей (только для студентов) -->
                 <v-row v-if="isStudent">
                  <v-col cols="12" md="6">
                    <PhoneInput
                      v-model="form.dad_phone"
                      :label="t('admin.users.dad_phone')"
                      :error-messages="form.errors.dad_phone"
                      density="compact"
                    />
                  </v-col>
                  <v-col cols="12" md="6">
                    <PhoneInput
                      v-model="form.mom_phone"
                      :label="t('admin.users.mom_phone')"
                      :error-messages="form.errors.mom_phone"
                      density="compact"
                    />
                  </v-col>
                 </v-row>

                 <!-- Пароль -->
                 <h3 class="text-h6 mb-4 mt-6">{{ t('admin.users.password_section') }}</h3>
                 <v-row>
                   <v-col cols="12" md="6">
                     <v-text-field
                       v-model="form.password"
                       :label="t('admin.users.password')"
                       type="password"
                       variant="outlined"
                       density="compact"
                       :error-messages="form.errors.password"
                       :hint="t('admin.users.password_hint')"
                       persistent-hint
                     ></v-text-field>
                   </v-col>
                   <v-col cols="12" md="6">
                     <v-text-field
                       v-model="form.password_confirmation"
                       :label="t('admin.users.password_confirmation')"
                       type="password"
                       variant="outlined"
                       density="compact"
                       :error-messages="form.errors.password_confirmation"
                       :hint="t('admin.users.password_confirmation_hint')"
                       persistent-hint
                     ></v-text-field>
                   </v-col>
                 </v-row>

                <v-alert
                  v-if="Object.keys(form.errors).length > 0"
                  type="error"
                  variant="tonal"
                  class="mb-4"
                >
                  <div v-for="(error, field) in form.errors" :key="field">
                    <strong>{{ field }}:</strong> {{ error }}
                  </div>
                </v-alert>

                <div class="d-flex justify-end gap-3">
                  <v-btn
                    color="secondary"
                    variant="outlined"
                    @click="navigateTo('/admin/users')"
                  >
                    {{ t('admin.users.cancel') }}
                  </v-btn>
                  <v-btn
                    color="primary"
                    type="submit"
                    :loading="form.processing"
                    :disabled="form.processing"
                  >
                    {{ t('admin.users.create_user_button') }}
                  </v-btn>
                </div>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AdminApp>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminApp from '../AdminApp.vue'
import PhoneInput from '../../../Components/PhoneInput.vue'
import { routes } from '../../../utils/routes'

const { t } = useI18n()

// Props из Inertia
const props = defineProps({
  roles: {
    type: Array,
    default: () => []
  },
  groups: {
    type: Array,
    default: () => []
  }
})

// Форма
const form = useForm({
  name: '',
  last_name: '',
  middle_name: '',
  email: '',
  phone: '+992',
  address: '',
  dad_phone: '+992',
  mom_phone: '+992',
  role_id: '',
  group_id: '',
  password: '',
  password_confirmation: ''
})

// Проверяем, является ли выбранная роль студентом
const isStudent = computed(() => {
  if (!form.role_id) return false
  const role = props.roles.find(r => r.id === form.role_id)
  return role?.name === 'student'
})

// Методы
const navigateTo = (path) => {
  // Гарантируем абсолютный путь
  const absolutePath = path.startsWith('/') ? path : '/' + path
  router.visit(absolutePath)
}

const submitForm = () => {
  // Валидация: хотя бы email или phone должен быть заполнен
  if (!form.email && !form.phone) {
    const errorMsg = t('admin.users.email_or_phone_required')
    form.setError('email', errorMsg)
    form.setError('phone', errorMsg)
    return
  }
  
  // ЯВНЫЙ абсолютный путь
  form.post(routes.admin.users.store(), {
    onSuccess: () => {
      // Форма автоматически перенаправит на список пользователей
    },
    onError: (errors) => {
      console.error('Ошибки валидации:', errors)
    }
  })
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
