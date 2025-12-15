<template>
  <v-app>
    <v-main class="bg-gradient">
      <v-container fluid class="fill-height">
        <v-row align="center" justify="center">
          <v-col cols="12" sm="8" md="6" lg="4">
            <!-- Карточка смены пароля -->
            <v-card class="elevation-12 rounded-lg" color="white">
              <!-- Заголовок -->
              <v-card-title class="text-center pa-6 bg-primary">
                <div class="d-flex flex-column align-center w-100">
                  <v-icon size="64" color="white" class="mb-3">mdi-lock-reset</v-icon>
                  <h2 class="text-h5 text-white font-weight-bold">{{ t('auth.change_password') }}</h2>
                  <p class="text-caption text-white mt-2 opacity-90">
                    {{ t('auth.password_required') }}
                  </p>
                </div>
              </v-card-title>

              <v-card-text class="pa-6">
                <!-- Уведомления -->
                <v-alert
                  v-if="page.props.flash?.warning"
                  type="warning"
                  variant="tonal"
                  class="mb-4"
                  closable
                >
                  {{ page.props.flash.warning }}
                </v-alert>

                <v-alert
                  v-if="page.props.flash?.success"
                  type="success"
                  variant="tonal"
                  class="mb-4"
                  closable
                >
                  {{ page.props.flash.success }}
                </v-alert>

                <!-- Информация о пользователе -->
                <div class="user-info mb-6 pa-4 bg-grey-lighten-4 rounded">
                  <div class="d-flex align-center">
                    <v-avatar color="primary" size="48" class="mr-3">
                      <v-icon color="white">mdi-account</v-icon>
                    </v-avatar>
                    <div>
                      <div class="text-subtitle-1 font-weight-medium">{{ user.name }}</div>
                      <div class="text-caption text-grey-darken-1">{{ user.email }}</div>
                    </div>
                  </div>
                </div>

                <!-- Форма смены пароля -->
                <v-form @submit.prevent="handleSubmit" ref="formRef">
                  <!-- Текущий пароль -->
                  <v-text-field
                    :model-value="formData.current_password"
                    @update:model-value="updateField('current_password', $event)"
                    :label="t('auth.current_password')"
                    :type="showCurrentPassword ? 'text' : 'password'"
                    :append-inner-icon="showCurrentPassword ? 'mdi-eye-off' : 'mdi-eye'"
                    @click:append-inner="toggleShowCurrentPassword"
                    variant="outlined"
                    density="comfortable"
                    :error-messages="getErrorMessage('current_password')"
                    prepend-inner-icon="mdi-lock"
                    class="mb-4"
                    required
                    autocomplete="current-password"
                  />

                  <!-- Новый пароль -->
                  <v-text-field
                    :model-value="formData.password"
                    @update:model-value="updateField('password', $event)"
                    :label="t('auth.new_password')"
                    :type="showPassword ? 'text' : 'password'"
                    :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                    @click:append-inner="toggleShowPassword"
                    variant="outlined"
                    density="comfortable"
                    :error-messages="getErrorMessage('password')"
                    prepend-inner-icon="mdi-lock-plus"
                    :hint="isPasswordSame ? t('auth.password_must_differ') : t('auth.password_min', { min: 4 })"
                    :persistent-hint="isPasswordSame"
                    :color="isPasswordSame ? 'error' : undefined"
                    class="mb-4"
                    required
                    autocomplete="new-password"
                  />
                  
                  <!-- Предупреждение о совпадении паролей -->
                  <v-alert
                    v-if="isPasswordSame"
                    type="warning"
                    variant="tonal"
                    density="compact"
                    class="mb-4"
                  >
                    <v-icon start>mdi-alert</v-icon>
                    {{ t('auth.password_must_differ') }}
                  </v-alert>

                  <!-- Подтверждение пароля -->
                  <v-text-field
                    :model-value="formData.password_confirmation"
                    @update:model-value="updateField('password_confirmation', $event)"
                    :label="t('auth.confirm_password')"
                    :type="showPasswordConfirmation ? 'text' : 'password'"
                    :append-inner-icon="showPasswordConfirmation ? 'mdi-eye-off' : 'mdi-eye'"
                    @click:append-inner="toggleShowPasswordConfirmation"
                    variant="outlined"
                    density="comfortable"
                    :error-messages="getErrorMessage('password_confirmation')"
                    prepend-inner-icon="mdi-lock-check"
                    :hint="isPasswordMismatch ? t('auth.password_confirmed') : t('auth.confirm_password')"
                    :persistent-hint="isPasswordMismatch"
                    :color="isPasswordMismatch ? 'error' : undefined"
                    class="mb-4"
                    required
                    autocomplete="new-password"
                  />
                  
                  <!-- Предупреждение о несовпадении паролей -->
                  <v-alert
                    v-if="isPasswordMismatch"
                    type="error"
                    variant="tonal"
                    density="compact"
                    class="mb-4"
                  >
                    <v-icon start>mdi-alert-circle</v-icon>
                    {{ t('auth.password_confirmed') }}
                  </v-alert>

                  <!-- Кнопки действий -->
                  <div class="d-flex gap-3">
                    <v-btn
                      type="submit"
                      color="primary"
                      size="large"
                      :loading="isLoading"
                      :disabled="!isFormValid || isLoading"
                      block
                      class="text-none"
                    >
                      <v-icon left>mdi-content-save</v-icon>
                      {{ t('auth.change_password') }}
                    </v-btn>
                  </div>

                  <v-divider class="my-4" />

                  <!-- Кнопка выхода -->
                  <v-btn
                    color="error"
                    variant="outlined"
                    size="large"
                    @click="handleLogout"
                    block
                    class="text-none"
                  >
                    <v-icon left>mdi-logout</v-icon>
                    {{ t('navigation.logout') }}
                  </v-btn>
                </v-form>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

const page = usePage()

// Состояние формы - используем reactive для реактивности
const formData = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
})

// Локальные ошибки валидации
const localErrors = reactive({
  password: null,
  password_confirmation: null
})

// Состояние UI
const showCurrentPassword = ref(false)
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const isLoading = ref(false)
const formRef = ref(null)

// Проверка, что новый пароль отличается от старого
const isPasswordSame = computed(() => {
  return formData.current_password && 
         formData.password && 
         formData.current_password === formData.password
})

// Проверка, что пароль и подтверждение совпадают
const isPasswordMismatch = computed(() => {
  return formData.password && 
         formData.password_confirmation && 
         formData.password !== formData.password_confirmation
})

// Проверка всех требований для активации кнопки
const isFormValid = computed(() => {
  // Все поля должны быть заполнены
  if (!formData.current_password || !formData.password || !formData.password_confirmation) {
    return false
  }
  
  // Новый пароль должен отличаться от старого
  if (isPasswordSame.value) {
    return false
  }
  
  // Пароль и подтверждение должны совпадать
  if (isPasswordMismatch.value) {
    return false
  }
  
  // Минимальная длина пароля - 4 символа
  if (formData.password.length < 4) {
    return false
  }
  
  // Нет локальных ошибок
  if (localErrors.password || localErrors.password_confirmation) {
    return false
  }
  
  return true
})

// Методы для обновления полей формы
const updateField = (field, value) => {
  formData[field] = value
  
  // Сбрасываем локальные ошибки при изменении полей
  if (field === 'password' || field === 'current_password') {
    localErrors.password = null
  }
  if (field === 'password_confirmation' || field === 'password') {
    localErrors.password_confirmation = null
  }
}

// Методы для переключения видимости паролей
const toggleShowCurrentPassword = () => {
  showCurrentPassword.value = !showCurrentPassword.value
}

const toggleShowPassword = () => {
  showPassword.value = !showPassword.value
}

const toggleShowPasswordConfirmation = () => {
  showPasswordConfirmation.value = !showPasswordConfirmation.value
}

// Метод для получения ошибок (приоритет: локальные ошибки, затем серверные)
const getErrorMessage = (field) => {
  if (field === 'password' && localErrors.password) {
    return localErrors.password
  }
  if (field === 'password_confirmation' && localErrors.password_confirmation) {
    return localErrors.password_confirmation
  }
  return page.props.errors?.[field] || null
}

// Метод для отправки формы
const handleSubmit = () => {
  if (isLoading.value) return
  
  // Валидация на фронтенде: проверяем, что новый пароль отличается от старого
  if (isPasswordSame.value) {
    localErrors.password = t('auth.password_must_differ')
    isLoading.value = false
    return
  }
  
  // Проверяем минимальную длину пароля
  if (formData.password.length < 4) {
    localErrors.password = t('auth.password_min', { min: 4 })
    isLoading.value = false
    return
  }
  
  // Проверяем совпадение пароля и подтверждения
  if (formData.password !== formData.password_confirmation) {
    localErrors.password_confirmation = t('auth.password_confirmed')
    isLoading.value = false
    return
  }
  
  // Сбрасываем локальные ошибки перед отправкой
  localErrors.password = null
  localErrors.password_confirmation = null
  isLoading.value = true
  
  // Создаём копию данных для отправки
  const submitData = {
    current_password: formData.current_password,
    password: formData.password,
    password_confirmation: formData.password_confirmation
  }
  
  // Используем прямой URL вместо route() для избежания проблем с инициализацией
  router.post('/change-password', submitData, {
    preserveState: true, // Сохраняем состояние для отображения ошибок
    preserveScroll: true,
    onFinish: () => {
      isLoading.value = false
    },
    onSuccess: (response) => {
      // Проверяем, есть ли ошибки валидации с сервера
      const hasErrors = response.props?.errors && Object.keys(response.props.errors).length > 0
      
      if (hasErrors) {
        console.log('⚠️ Есть ошибки валидации:', response.props.errors)
        // Если сервер вернул ошибку о совпадении паролей, показываем её
        if (response.props.errors.password) {
          localErrors.password = response.props.errors.password[0]
        }
        return // Не делаем редирект, показываем ошибки
      }
      
      // Если нет ошибок - пароль успешно изменен
      console.log('✅ Пароль успешно изменен!')
    },
    onError: (errors) => {
      console.error('❌ Ошибки при смене пароля:', errors)
      // При ошибке данные остаются в форме благодаря preserveState: true
      // Показываем серверные ошибки
      if (errors.password) {
        localErrors.password = Array.isArray(errors.password) ? errors.password[0] : errors.password
      }
      if (errors.password_confirmation) {
        localErrors.password_confirmation = Array.isArray(errors.password_confirmation) ? errors.password_confirmation[0] : errors.password_confirmation
      }
    }
  })
}

// Метод для выхода
const handleLogout = () => {
  // Используем прямой URL вместо route() для избежания проблем с инициализацией
  router.post('/logout')
}
</script>

<style scoped>
.bg-gradient {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 100vh;
}

.bg-primary {
  background: linear-gradient(135deg, #5e72e4 0%, #825ee4 100%) !important;
}

.v-card {
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
}
</style>

