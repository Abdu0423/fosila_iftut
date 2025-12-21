<template>
  <Layout role="education_department">
    <v-container fluid class="pa-6">
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.edit_specialty || 'Редактирование специальности' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.edit_specialty_subtitle || 'Изменение информации о специальности' }}
          </p>
        </div>
        <v-btn
          color="secondary"
          variant="outlined"
          @click="navigateTo('/education/specialties')"
          prepend-icon="mdi-arrow-left"
        >
          {{ translations.messages?.back || 'Назад' }}
        </v-btn>
      </div>

      <v-row justify="center">
        <v-col cols="12" md="10" lg="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-school-edit</v-icon>
              {{ translations.education_department?.specialty_info || 'Информация о специальности' }}
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submitForm">
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.name"
                      :label="(translations.education_department?.specialty_name || 'Название') + ' *'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.name"
                      required
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.short_name"
                      :label="translations.education_department?.short_name || 'Короткое имя'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.short_name"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.code"
                      :label="translations.education_department?.specialty_code || 'Код'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.code"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.department_id"
                      :label="translations.messages?.category || 'Кафедра'"
                      :items="departments || []"
                      item-title="name"
                      item-value="id"
                      variant="outlined"
                      density="comfortable"
                      clearable
                      :error-messages="form.errors.department_id"
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.duration_years"
                      :label="translations.education_department?.duration_years || 'Длительность (лет)'"
                      type="number"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.duration_years"
                      min="1"
                      max="10"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-textarea
                      v-model="form.description"
                      :label="translations.education_department?.description || 'Описание'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.description"
                      rows="3"
                    ></v-textarea>
                  </v-col>
                  <v-col cols="12">
                    <v-switch
                      v-model="form.is_active"
                      :label="translations.messages?.active || 'Активна'"
                      color="success"
                    ></v-switch>
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
                    @click="navigateTo('/education/specialties')"
                  >
                    {{ translations.messages?.cancel || 'Отмена' }}
                  </v-btn>
                  <v-btn
                    type="submit"
                    color="primary"
                    :loading="form.processing"
                  >
                    {{ translations.messages?.save || 'Сохранить' }}
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
import { useForm, usePage, router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const page = usePage()
const translations = computed(() => page.props.translations || {})

const props = defineProps({
  specialty: {
    type: Object,
    required: true
  },
  departments: {
    type: Array,
    default: () => []
  }
})

const specialtyId = ref(null)

const form = useForm({
  name: '',
  short_name: '',
  code: '',
  description: '',
  duration_years: null,
  is_active: true,
  department_id: null
})

onMounted(() => {
  specialtyId.value = props.specialty.id
  form.name = props.specialty.name || ''
  form.short_name = props.specialty.short_name || ''
  form.code = props.specialty.code || ''
  form.description = props.specialty.description || ''
  form.duration_years = props.specialty.duration_years || null
  form.is_active = props.specialty.is_active !== undefined ? props.specialty.is_active : true
  form.department_id = props.specialty.department_id || null
})

const navigateTo = (path) => {
  router.visit(path)
}

const submitForm = () => {
  const id = specialtyId.value || props.specialty?.id
  if (!id) {
    console.error('ID специальности не найден')
    return
  }

  form.transform((data) => ({
    ...data,
    _method: 'PUT'
  })).post(`/education/specialties/${id}`, {
    preserveState: false,
    preserveScroll: false,
    onSuccess: () => {
      router.visit('/education/specialties')
    },
    onError: (errors) => {
      console.error('Ошибки валидации:', errors)
    }
  })
}
</script>

