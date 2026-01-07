<template>
  <Layout role="education_department">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.edit_student || 'Таҳрири донишҷӯ' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.edit_student_subtitle || 'Тағйири маълумоти донишҷӯ' }}
          </p>
        </div>
        <v-btn
          color="secondary"
          variant="outlined"
          @click="navigateTo(`/${getRoutePrefix()}/users`)"
          prepend-icon="mdi-arrow-left"
        >
          {{ translations.messages?.back || 'Бозгашт' }}
        </v-btn>
      </div>

      <!-- Форма редактирования -->
      <v-row justify="center">
        <v-col cols="12" md="10" lg="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-account-edit</v-icon>
              {{ translations.education_department?.student_info || 'Маълумоти донишҷӯ' }}
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submitForm">
                <!-- Основная информация -->
                <h3 class="text-h6 mb-4">{{ translations.messages?.basic_info || 'Маълумоти асосӣ' }}</h3>
                <v-row>
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="form.name"
                      :label="(translations.messages?.name || 'Ном') + ' *'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.name"
                      required
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="form.last_name"
                      :label="(translations.messages?.last_name || 'Насаб') + ' *'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.last_name"
                      required
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="form.middle_name"
                      :label="translations.messages?.middle_name || 'Номи падар'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.middle_name"
                    ></v-text-field>
                  </v-col>
                </v-row>

                <!-- Контактная информация -->
                <h3 class="text-h6 mb-4 mt-6">{{ translations.messages?.contact_info || 'Маълумоти тамос' }}</h3>
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.email"
                      :label="translations.messages?.email || 'Email'"
                      type="email"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.email"
                      :hint="translations.messages?.email_hint || 'Ихтиёрӣ, аммо бояд ягон email ё телефон бошад'"
                      persistent-hint
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.phone"
                      :label="translations.messages?.phone || 'Телефон'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.phone"
                      :hint="translations.messages?.phone_hint || 'Ихтиёрӣ, аммо бояд ягон email ё телефон бошад'"
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
                      :label="translations.messages?.address || 'Суроға'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.address"
                    ></v-text-field>
                  </v-col>
                </v-row>

                <!-- Группа -->
                <h3 class="text-h6 mb-4 mt-6">{{ translations.messages?.system_info || 'Маълумоти система' }}</h3>
                <v-row>
                  <v-col cols="12">
                    <v-autocomplete
                      v-model="form.group_id"
                      :items="groups"
                      item-title="display_name"
                      item-value="id"
                      :label="translations.messages?.group || 'Гурӯҳ'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.group_id"
                      clearable
                      :placeholder="translations.messages?.select_group || 'Гурӯҳро интихоб кунед'"
                    ></v-autocomplete>
                  </v-col>
                </v-row>

                <!-- Телефоны родителей -->
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.dad_phone"
                      :label="translations.messages?.dad_phone || 'Телефони падар'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.dad_phone"
                      v-mask="'+992#########'"
                      placeholder="+992XXXXXXXXX"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.mom_phone"
                      :label="translations.messages?.mom_phone || 'Телефони модар'"
                      variant="outlined"
                      density="comfortable"
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
                    <strong>{{ field }}:</strong> {{ Array.isArray(error) ? error[0] : error }}
                  </div>
                </v-alert>

                <!-- Кнопки -->
                <div class="d-flex justify-end gap-3">
                  <v-btn
                    color="secondary"
                    variant="outlined"
                    @click="navigateTo(`/${getRoutePrefix()}/users`)"
                  >
                    {{ translations.messages?.cancel || 'Бекор кардан' }}
                  </v-btn>
                  <v-btn
                    color="primary"
                    type="submit"
                    :loading="form.processing"
                    :disabled="form.processing"
                  >
                    {{ translations.messages?.save || 'Сабт кардан' }}
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

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const page = usePage()
const translations = computed(() => page.props.translations || {})

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
  role_id: '',
  group_id: ''
})

// Проверяем, является ли пользователь студентом
const isStudent = computed(() => {
  // Проверяем через role_id в форме или через props.user
  const roleId = form.role_id || props.user.role_id
  if (!roleId) return false
  const role = props.roles.find(r => r.id === roleId)
  return role?.name === 'student'
})

// Заполняем форму данными пользователя
onMounted(() => {
  userId.value = props.user.id
  form.name = props.user.name || ''
  form.last_name = props.user.last_name || ''
  form.middle_name = props.user.middle_name || ''
  form.email = props.user.email || ''
  form.phone = props.user.phone || '+992'
  form.address = props.user.address || ''
  form.dad_phone = props.user.dad_phone || '+992'
  form.mom_phone = props.user.mom_phone || '+992'
  form.role_id = props.user.role_id || ''
  form.group_id = props.user.group_id || ''
})

// Определяем префикс маршрута на основе текущего URL
const getRoutePrefix = () => {
  const path = window.location.pathname
  if (path.startsWith('/registration')) {
    return 'registration'
  }
  return 'education'
}

// Определяем роль для Layout
const getRole = computed(() => {
  return getRoutePrefix() === 'registration' ? 'registration_center' : 'education_department'
})

// Методы
const navigateTo = (path) => {
  router.visit(path)
}

const submitForm = () => {
  // Валидация: хотя бы email или phone должен быть заполнен
  if (!form.email && !form.phone) {
    form.setError('email', 'Необходимо указать хотя бы email или телефон')
    form.setError('phone', 'Необходимо указать хотя бы email или телефон')
    return
  }
  
  // Проверяем наличие ID пользователя
  const id = userId.value || props.user?.id
  if (!id) {
    console.error('ID пользователя не найден')
    return
  }
  
  const routePrefix = getRoutePrefix()
  
  // Используем form.transform().post() с _method: 'PUT' для правильной отправки
  form.transform((data) => ({
    ...data,
    _method: 'PUT'
  })).post(`/${routePrefix}/users/${id}`, {
    preserveState: false,
    preserveScroll: false,
    onSuccess: () => {
      router.visit(`/${routePrefix}/users`)
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

