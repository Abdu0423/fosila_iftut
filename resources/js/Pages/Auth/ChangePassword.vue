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
                  <h2 class="text-h5 text-white font-weight-bold">Смена пароля</h2>
                  <p class="text-caption text-white mt-2 opacity-90">
                    Для продолжения работы необходимо сменить пароль
                  </p>
                </div>
              </v-card-title>

              <v-card-text class="pa-6">
                <!-- Уведомления -->
                <v-alert
                  v-if="$page.props.flash?.warning"
                  type="warning"
                  variant="tonal"
                  class="mb-4"
                  closable
                >
                  {{ $page.props.flash.warning }}
                </v-alert>

                <v-alert
                  v-if="$page.props.flash?.success"
                  type="success"
                  variant="tonal"
                  class="mb-4"
                  closable
                >
                  {{ $page.props.flash.success }}
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
                <v-form @submit.prevent="submitForm" ref="form">
                  <!-- Текущий пароль -->
                  <v-text-field
                    v-model="form.current_password"
                    label="Текущий пароль"
                    :type="showCurrentPassword ? 'text' : 'password'"
                    :append-inner-icon="showCurrentPassword ? 'mdi-eye-off' : 'mdi-eye'"
                    @click:append-inner="showCurrentPassword = !showCurrentPassword"
                    variant="outlined"
                    density="comfortable"
                    :error-messages="getError('current_password')"
                    prepend-inner-icon="mdi-lock"
                    class="mb-4"
                    required
                  />

                  <!-- Новый пароль -->
                  <v-text-field
                    v-model="form.password"
                    label="Новый пароль"
                    :type="showPassword ? 'text' : 'password'"
                    :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                    @click:append-inner="showPassword = !showPassword"
                    variant="outlined"
                    density="comfortable"
                    :error-messages="getError('password')"
                    prepend-inner-icon="mdi-lock-plus"
                    hint="Минимум 4 символа"
                    class="mb-4"
                    required
                  />

                  <!-- Подтверждение пароля -->
                  <v-text-field
                    v-model="form.password_confirmation"
                    label="Подтвердите новый пароль"
                    :type="showPasswordConfirmation ? 'text' : 'password'"
                    :append-inner-icon="showPasswordConfirmation ? 'mdi-eye-off' : 'mdi-eye'"
                    @click:append-inner="showPasswordConfirmation = !showPasswordConfirmation"
                    variant="outlined"
                    density="comfortable"
                    :error-messages="getError('password_confirmation')"
                    prepend-inner-icon="mdi-lock-check"
                    class="mb-4"
                    required
                  />

                  <!-- Кнопки действий -->
                  <div class="d-flex gap-3">
                    <v-btn
                      type="submit"
                      color="primary"
                      size="large"
                      :loading="loading"
                      block
                      class="text-none"
                    >
                      <v-icon left>mdi-content-save</v-icon>
                      Сменить пароль
                    </v-btn>
                  </div>

                  <v-divider class="my-4" />

                  <!-- Кнопка выхода -->
                  <v-btn
                    color="error"
                    variant="outlined"
                    size="large"
                    @click="logout"
                    block
                    class="text-none"
                  >
                    <v-icon left>mdi-logout</v-icon>
                    Выйти из системы
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
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

const page = usePage()

const form = ref({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const showCurrentPassword = ref(false)
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const loading = ref(false)

// Получаем ошибки из Inertia
const getError = (field) => {
  return page.props.errors?.[field] || null
}

// Отправка формы
const submitForm = () => {
  loading.value = true
  
  // Отправляем только данные формы без реактивности Vue
  router.post(route('change-password.update'), {
    current_password: form.value.current_password,
    password: form.value.password,
    password_confirmation: form.value.password_confirmation
  }, {
    onFinish: () => {
      loading.value = false
    },
    onError: (errors) => {
      console.error('Ошибки:', errors)
    }
  })
}

// Выход из системы
const logout = () => {
  router.post(route('logout'))
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

.requirement {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
  opacity: 0.6;
  transition: opacity 0.3s ease;
}

.requirement.met {
  opacity: 1;
}

.v-card {
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
}
</style>

