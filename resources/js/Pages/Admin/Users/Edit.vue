<template>
  <AdminApp>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('admin.users.edit_user') }}</h1>
              <p class="text-body-1 text-medium-emphasis">{{ t('admin.users.edit_user_subtitle') }}</p>
            </div>
            <div class="d-flex gap-3">
              <v-btn
                color="secondary"
                variant="outlined"
                @click="navigateTo(`/admin/users/${user.id}`)"
                prepend-icon="mdi-eye"
              >
                {{ t('admin.users.view') }}
              </v-btn>
              <v-btn
                color="secondary"
                variant="outlined"
                @click="navigateTo('/admin/users')"
                prepend-icon="mdi-arrow-left"
              >
                {{ t('admin.users.back_to_list') }}
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
              {{ t('admin.users.user_info') }}
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submitForm">
                <!-- Основная информация -->
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
                      v-mask="'+992#########'"
                      placeholder="+992XXXXXXXXX"
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
                      :label="t('admin.users.role')"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.role_id"
                      required
                    ></v-select>
                  </v-col>
                  <v-col v-if="isStudent" cols="12" md="6">
                    <v-autocomplete
                      v-model="form.group_id"
                      :items="normalizedGroups"
                      item-title="display_name"
                      item-value="id"
                      :label="t('admin.users.group')"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.group_id"
                      clearable
                      :placeholder="t('admin.users.select_group')"
                    ></v-autocomplete>
                  </v-col>
                  <v-col v-if="isDepartment" cols="12" md="6">
                    <v-autocomplete
                      v-model="form.department_id"
                      :items="departments"
                      item-title="name"
                      item-value="id"
                      label="Кафедра *"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.department_id"
                      clearable
                      placeholder="Выберите кафедру"
                      required
                    ></v-autocomplete>
                  </v-col>
                  <v-col v-if="isFaculty" cols="12" md="6">
                    <v-autocomplete
                      v-model="form.faculty_id"
                      :items="faculties"
                      item-title="name"
                      item-value="id"
                      label="Факультет *"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.faculty_id"
                      clearable
                      placeholder="Выберите факультет"
                      required
                    ></v-autocomplete>
                  </v-col>
                </v-row>

                <!-- Телефоны родителей (только для студентов) -->
                <v-row v-if="isStudent">
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.dad_phone"
                      :label="t('admin.users.dad_phone')"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.dad_phone"
                      v-mask="'+992#########'"
                      placeholder="+992XXXXXXXXX"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.mom_phone"
                      :label="t('admin.users.mom_phone')"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.mom_phone"
                      v-mask="'+992#########'"
                      placeholder="+992XXXXXXXXX"
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
                    {{ t('admin.users.cancel') }}
                  </v-btn>
                  <v-btn
                    color="primary"
                    type="submit"
                    :loading="form.processing"
                    :disabled="form.processing"
                  >
                    {{ t('admin.users.save_changes') }}
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

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useForm, router } from '@/compat/inertia'
import { useI18n } from 'vue-i18n'
import AdminApp from '../AdminApp.vue'

const { t } = useI18n()

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
  },
  departments: {
    type: Array,
    default: () => []
  },
  faculties: {
    type: Array,
    default: () => []
  }
})

// Сохраняем ID пользователя
const userId = ref(null)

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
  role_id: props.user?.role_id ? Number(props.user.role_id) : null,
  group_id: props.user?.group_id ? Number(props.user.group_id) : null,
  department_id: props.user?.department_id ? Number(props.user.department_id) : null,
  faculty_id: props.user?.faculty_id ? Number(props.user.faculty_id) : null
})

const normalizedGroups = computed(() => {
  const list = (props.groups || []).map((g: any) => ({
    ...g,
    id: Number(g.id),
    display_name: g.display_name || g.name || '',
  }))
  const currentGroupId = props.user?.group_id ? Number(props.user.group_id) : null
  if (currentGroupId && !list.some((g) => g.id === currentGroupId)) {
    list.unshift({
      id: currentGroupId,
      name: props.user?.group_name || `Группа #${currentGroupId}`,
      display_name: props.user?.group_name || `Группа #${currentGroupId}`,
    })
  }
  return list
})

const selectedRole = computed(() => {
  const roleId = form.role_id || props.user.role_id
  if (!roleId) return null
  return props.roles.find(r => Number(r.id) === Number(roleId)) || null
})

// Проверяем, является ли пользователь студентом, кафедрой или факультетом
const isStudent = computed(() => selectedRole.value?.name === 'student')
const isDepartment = computed(() => selectedRole.value?.name === 'department')
const isFaculty = computed(() => selectedRole.value?.name === 'faculty')

watch(() => form.role_id, () => {
  if (!isStudent.value) {
    form.group_id = null
  }
  if (!isDepartment.value) {
    form.department_id = null
    form.clearErrors('department_id')
  }
  if (!isFaculty.value) {
    form.faculty_id = null
    form.clearErrors('faculty_id')
  }
})

// Заполняем форму данными пользователя
onMounted(() => {
  userId.value = props.user.id
  form.name = props.user.name
  form.last_name = props.user.last_name
  form.middle_name = props.user.middle_name
  form.email = props.user.email
  form.phone = props.user.phone || '+992'
  form.address = props.user.address
  form.dad_phone = props.user.dad_phone || '+992'
  form.mom_phone = props.user.mom_phone || '+992'
  form.role_id = props.user.role_id
  // Преобразуем group_id в число, если он есть
  form.group_id = props.user.group_id ? Number(props.user.group_id) : null
  form.department_id = props.user.department_id ? Number(props.user.department_id) : null
  form.faculty_id = props.user.faculty_id ? Number(props.user.faculty_id) : null
})

// Методы
const navigateTo = (route) => {
  router.visit(route)
}

const submitForm = () => {
  form.clearErrors()

  // Валидация: хотя бы email или phone должен быть заполнен
  if (!form.email && !form.phone) {
    const errorMsg = t('admin.users.email_or_phone_required')
    form.errors.email = errorMsg
    form.errors.phone = errorMsg
    form.hasErrors = true
    return
  }

  if (isDepartment.value && !form.department_id) {
    form.errors.department_id = 'Необходимо указать кафедру'
    form.hasErrors = true
    return
  }

  if (isFaculty.value && !form.faculty_id) {
    form.errors.faculty_id = 'Необходимо указать факультет'
    form.hasErrors = true
    return
  }
  
  // Проверяем наличие ID пользователя
  const id = userId.value || props.user?.id
  if (!id) {
    console.error('ID пользователя не найден')
    return
  }
  
  // Используем form.transform().post() с _method: 'PUT' для правильной отправки
  form.transform((data) => ({
    ...data,
    _method: 'PUT'
  })).post(`/admin/users/${id}`, {
    preserveState: false,
    preserveScroll: false,
    onSuccess: () => {
      router.visit('/admin/users')
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
