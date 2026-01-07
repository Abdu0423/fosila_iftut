<template>
  <Layout :role="getRole">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.edit_group || 'Таҳрири гурӯҳ' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.edit_group_subtitle || 'Тағйири маълумоти гурӯҳ' }}
          </p>
        </div>
        <v-btn
          color="secondary"
          variant="outlined"
          @click="navigateTo(`/${getRoutePrefix()}/groups`)"
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
              <v-icon start>mdi-account-multiple-edit</v-icon>
              {{ translations.education_department?.group_info || 'Маълумоти гурӯҳ' }}
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submitForm">
                <!-- Основная информация -->
                <h3 class="text-h6 mb-4">{{ translations.messages?.basic_info || 'Маълумоти асосӣ' }}</h3>
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.name"
                      :label="(translations.education_department?.group_name || 'Номи гурӯҳ') + ' *'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.name"
                      required
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.status"
                      :items="statusOptions"
                      :label="(translations.messages?.status || 'Вазъият') + ' *'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.status"
                      required
                    ></v-select>
                  </v-col>
                </v-row>

                <!-- Годы -->
                <h3 class="text-h6 mb-4 mt-6">{{ translations.education_department?.years || 'Солҳо' }}</h3>
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.enrollment_year"
                      :label="(translations.education_department?.enrollment_year || 'Соли дохилшавӣ') + ' *'"
                      type="number"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.enrollment_year"
                      required
                      min="2020"
                      max="2030"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.graduation_year"
                      :label="(translations.education_department?.graduation_year || 'Соли хориҷшавӣ') + ' *'"
                      type="number"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.graduation_year"
                      required
                      min="2020"
                      max="2030"
                    ></v-text-field>
                  </v-col>
                </v-row>

                <!-- Дополнительная информация -->
                <h3 class="text-h6 mb-4 mt-6">{{ translations.messages?.additional_info || 'Маълумоти иловагӣ' }}</h3>
                <v-row>
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="form.course"
                      :label="translations.education_department?.course || 'Курс'"
                      type="number"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.course"
                      min="1"
                      max="6"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-select
                      v-model="form.department_id"
                      :items="departments"
                      item-title="display_name"
                      item-value="id"
                      :label="translations.education_department?.department || 'Кафедра'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.department_id"
                      clearable
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-select
                      v-model="form.specialty_id"
                      :items="specialties"
                      item-title="display_name"
                      item-value="id"
                      :label="translations.education_department?.specialty || 'Специалитет'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.specialty_id"
                      clearable
                    ></v-select>
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
                    @click="navigateTo(`/${getRoutePrefix()}/groups`)"
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
import { computed, onMounted } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const page = usePage()
const translations = computed(() => page.props.translations || {})

// Props из Inertia
const props = defineProps({
  group: {
    type: Object,
    required: true
  },
  departments: {
    type: Array,
    default: () => []
  },
  specialties: {
    type: Array,
    default: () => []
  }
})

// Форма
const form = useForm({
  name: '',
  enrollment_year: null,
  graduation_year: null,
  status: 'active',
  department_id: null,
  course: null,
  specialty_id: null
})

const statusOptions = [
  { title: translations.value.messages?.active || 'Фаъол', value: 'active' },
  { title: translations.value.education_department?.graduated || 'Хориҷшуда', value: 'graduated' },
  { title: translations.value.education_department?.suspended || 'Қатъшуда', value: 'suspended' }
]

// Заполняем форму данными группы
onMounted(() => {
  form.name = props.group.name || ''
  form.enrollment_year = props.group.enrollment_year || new Date().getFullYear()
  form.graduation_year = props.group.graduation_year || new Date().getFullYear() + 4
  form.status = props.group.status || 'active'
  form.department_id = props.group.department_id || null
  form.course = props.group.course || 1
  form.specialty_id = props.group.specialty_id || null
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
  form.put(`/${getRoutePrefix()}/groups/${props.group.id}`, {
    onSuccess: () => {
      // Форма автоматически перенаправит на список групп
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

