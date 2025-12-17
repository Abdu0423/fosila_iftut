<template>
  <AdminApp>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Редактировать пользователя</h1>
              <p class="text-body-1 text-medium-emphasis">Измените информацию о пользователе</p>
            </div>
            <div class="d-flex gap-3">
              <v-btn
                color="secondary"
                variant="outlined"
                @click="navigateTo(`/admin/users/${user.id}`)"
                prepend-icon="mdi-eye"
              >
                Просмотр
              </v-btn>
              <v-btn
                color="secondary"
                variant="outlined"
                @click="navigateTo('/admin/users')"
                prepend-icon="mdi-arrow-left"
              >
                Назад к списку
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Форма редактирования -->
      <v-row justify="center">
        <v-col cols="12" md="10" lg="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-account-edit</v-icon>
              Информация о пользователе
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submitForm">
                <!-- Основная информация -->
                <v-row>
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="form.name"
                      label="Имя"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.name"
                      required
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="form.last_name"
                      label="Фамилия"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.last_name"
                      required
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="form.middle_name"
                      label="Отчество"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.middle_name"
                    ></v-text-field>
                  </v-col>
                </v-row>

                <!-- Контактная информация -->
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.email"
                      label="Email"
                      type="email"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.email"
                      hint="Необязательно, но необходимо указать хотя бы email или телефон"
                      persistent-hint
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.phone"
                      label="Телефон"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.phone"
                      hint="Необязательно, но необходимо указать хотя бы email или телефон"
                      persistent-hint
                    ></v-text-field>
                  </v-col>
                </v-row>

                <!-- Адрес -->
                <v-row>
                  <v-col cols="12">
                    <v-text-field
                      v-model="form.address"
                      label="Адрес"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.address"
                    ></v-text-field>
                  </v-col>
                </v-row>

                <!-- Роль и группа -->
                <v-row>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.role_id"
                      :items="roles"
                      item-title="display_name"
                      item-value="id"
                      label="Роль"
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
                      label="Группа"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.group_id"
                      clearable
                      placeholder="Выберите группу"
                    ></v-select>
                  </v-col>
                </v-row>

                <!-- Телефоны родителей (только для студентов) -->
                <v-row v-if="isStudent">
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.dad_phone"
                      label="Телефон отца"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.dad_phone"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.mom_phone"
                      label="Телефон матери"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.mom_phone"
                    ></v-text-field>
                  </v-col>
                </v-row>

                <!-- Смена пароля -->
                <v-divider class="my-6"></v-divider>
                <h3 class="text-h6 mb-4">Смена пароля (необязательно)</h3>
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.password"
                      label="Новый пароль"
                      type="password"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.password"
                      hint="Минимум 8 символов"
                      persistent-hint
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.password_confirmation"
                      label="Подтверждение пароля"
                      type="password"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.password_confirmation"
                    ></v-text-field>
                  </v-col>
                </v-row>

                <!-- Ошибки -->
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

                <!-- Кнопки -->
                <div class="d-flex justify-end gap-3">
                  <v-btn
                    color="secondary"
                    variant="outlined"
                    @click="navigateTo('/admin/users')"
                  >
                    Отмена
                  </v-btn>
                  <v-btn
                    color="primary"
                    type="submit"
                    :loading="form.processing"
                    :disabled="form.processing"
                  >
                    Сохранить изменения
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
import { ref, onMounted, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AdminApp from '../AdminApp.vue'

// Props из Inertia
const props = defineProps({
  user: {
    type: Object,
    required: true
  },
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
  phone: '',
  address: '',
  dad_phone: '',
  mom_phone: '',
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

// Заполняем форму данными пользователя
onMounted(() => {
  form.name = props.user.name
  form.last_name = props.user.last_name
  form.middle_name = props.user.middle_name
  form.email = props.user.email
  form.phone = props.user.phone
  form.address = props.user.address
  form.dad_phone = props.user.dad_phone
  form.mom_phone = props.user.mom_phone
  form.role_id = props.user.role_id
  form.group_id = props.user.group_id
})

// Методы
const navigateTo = (route) => {
  router.visit(route)
}

const submitForm = () => {
  // Валидация: хотя бы email или phone должен быть заполнен
  if (!form.email && !form.phone) {
    form.setError('email', 'Необходимо указать хотя бы email или телефон')
    form.setError('phone', 'Необходимо указать хотя бы email или телефон')
    return
  }
  
  form.put(`/admin/users/${props.user.id}`, {
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
