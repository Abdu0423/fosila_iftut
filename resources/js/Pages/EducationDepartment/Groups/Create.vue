<template>
  <Layout role="education_department">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.create_group || 'Создать группу' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.create_group_subtitle || 'Добавление новой группы' }}
          </p>
        </div>
        <v-btn
          color="secondary"
          variant="outlined"
          @click="navigateTo('/education/groups')"
          prepend-icon="mdi-arrow-left"
        >
          {{ translations.messages?.back || 'Назад' }}
        </v-btn>
      </div>

      <!-- Форма создания группы -->
      <v-row justify="center">
        <v-col cols="12" md="10" lg="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-account-multiple-plus</v-icon>
              {{ translations.education_department?.group_info || 'Информация о группе' }}
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submitForm">
                <!-- Основная информация -->
                <h3 class="text-h6 mb-4">{{ translations.messages?.basic_info || 'Основная информация' }}</h3>
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.name"
                      :label="(translations.education_department?.group_name || 'Название группы') + ' *'"
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
                      :label="(translations.messages?.status || 'Статус') + ' *'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.status"
                      required
                    ></v-select>
                  </v-col>
                </v-row>

                <!-- Годы -->
                <h3 class="text-h6 mb-4 mt-6">{{ translations.education_department?.years || 'Годы' }}</h3>
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.enrollment_year"
                      :label="(translations.education_department?.enrollment_year || 'Год поступления') + ' *'"
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
                      :label="(translations.education_department?.graduation_year || 'Год выпуска') + ' *'"
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
                <h3 class="text-h6 mb-4 mt-6">{{ translations.messages?.additional_info || 'Дополнительная информация' }}</h3>
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
                      :label="translations.education_department?.specialty || 'Специальность'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.specialty_id"
                      clearable
                    ></v-select>
                  </v-col>
                </v-row>

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

                <div class="d-flex justify-end gap-3">
                  <v-btn
                    color="secondary"
                    variant="outlined"
                    @click="navigateTo('/education/groups')"
                  >
                    {{ translations.messages?.cancel || 'Отмена' }}
                  </v-btn>
                  <v-btn
                    color="primary"
                    type="submit"
                    :loading="form.processing"
                    :disabled="form.processing"
                  >
                    {{ translations.messages?.create || 'Создать' }}
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
import { computed } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const page = usePage()
const translations = computed(() => page.props.translations || {})

// Props из Inertia
const props = defineProps({
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
  enrollment_year: new Date().getFullYear(),
  graduation_year: new Date().getFullYear() + 4,
  status: 'active',
  department_id: null,
  course: 1,
  specialty_id: null
})

const statusOptions = computed(() => [
  { title: translations.value.messages?.active || 'Активна', value: 'active' },
  { title: translations.value.education_department?.graduated || 'Выпущена', value: 'graduated' },
  { title: translations.value.education_department?.suspended || 'Приостановлена', value: 'suspended' }
])

// Методы
const navigateTo = (path) => {
  router.visit(path)
}

const submitForm = () => {
  form.post('/education/groups', {
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
