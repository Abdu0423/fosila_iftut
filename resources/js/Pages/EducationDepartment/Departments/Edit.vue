<template>
  <Layout role="education_department">
    <v-container fluid class="pa-6">
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.edit_department || 'Редактирование кафедры' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.edit_department_subtitle || 'Изменение информации о кафедре' }}
          </p>
        </div>
        <v-btn
          color="secondary"
          variant="outlined"
          @click="navigateTo(`/${getRoutePrefix()}/departments`)"
          prepend-icon="mdi-arrow-left"
        >
          {{ translations.messages?.back || 'Назад' }}
        </v-btn>
      </div>

      <v-row justify="center">
        <v-col cols="12" md="10" lg="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-office-building-edit</v-icon>
              {{ translations.education_department?.department_info || 'Информация о кафедре' }}
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submitForm">
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.name"
                      :label="(translations.education_department?.department_name || 'Название') + ' *'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.name"
                      required
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.code"
                      :label="translations.education_department?.department_code || 'Код'"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.code"
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
                    @click="navigateTo(`/${getRoutePrefix()}/departments`)"
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
  department: {
    type: Object,
    required: true
  }
})

const departmentId = ref(null)

const form = useForm({
  name: '',
  code: '',
  description: '',
  is_active: true
})

onMounted(() => {
  departmentId.value = props.department.id
  form.name = props.department.name || ''
  form.code = props.department.code || ''
  form.description = props.department.description || ''
  form.is_active = props.department.is_active !== undefined ? props.department.is_active : true
})

// Определяем префикс маршрута на основе текущего URL
const getRoutePrefix = () => {
  const path = window.location.pathname
  if (path.startsWith('/registration')) {
    return 'registration'
  }
  return 'education'
}

const navigateTo = (path) => {
  router.visit(path)
}

const submitForm = () => {
  const id = departmentId.value || props.department?.id
  if (!id) {
    console.error('ID кафедры не найден')
    return
  }

  const routePrefix = getRoutePrefix()

  form.transform((data) => ({
    ...data,
    _method: 'PUT'
  })).post(`/${routePrefix}/departments/${id}`, {
    preserveState: false,
    preserveScroll: false,
    onSuccess: () => {
      router.visit(`/${routePrefix}/departments`)
    },
    onError: (errors) => {
      console.error('Ошибки валидации:', errors)
    }
  })
}
</script>

