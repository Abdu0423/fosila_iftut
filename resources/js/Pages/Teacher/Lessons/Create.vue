<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('teacher.create_lesson', {}, { default: 'Создать урок' }) }}</h1>
              <p class="text-body-1 text-medium-emphasis">{{ t('teacher.add_new_lesson_to_schedule', {}, { default: 'Добавьте новый урок к расписанию' }) }}</p>
            </div>
            <v-btn
              color="secondary"
              variant="outlined"
              @click="goBack"
              prepend-icon="mdi-arrow-left"
            >
              {{ t('teacher.back_to_list', {}, { default: 'Назад к списку' }) }}
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Уведомления -->
      <v-row v-if="page.props.flash?.error">
        <v-col cols="12">
          <v-alert
            type="error"
            variant="tonal"
            closable
          >
            {{ page.props.flash.error }}
          </v-alert>
        </v-col>
      </v-row>

      <v-row v-if="form.value?.errors && Object.keys(form.value.errors || {}).length > 0">
        <v-col cols="12">
          <v-alert
            type="error"
            variant="tonal"
            closable
          >
            <div v-for="(errors, field) in (form.value?.errors || {})" :key="field">
              <div v-for="error in errors" :key="error" class="mb-1">
                {{ error }}
              </div>
            </div>
          </v-alert>
        </v-col>
      </v-row>

      <!-- Форма создания урока -->
      <v-row>
        <v-col cols="12" md="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-plus</v-icon>
              {{ t('teacher.lesson_info', {}, { default: 'Информация об уроке' }) }}
            </v-card-title>
            <v-card-text>
              <v-form>
                <v-row>
                  <v-col cols="12">
                    <v-text-field
                      v-model="formTitle"
                      :label="t('teacher.lesson_title', {}, { default: 'Название урока' })"
                      variant="outlined"
                      density="compact"
                      required
                      :error-messages="form.value?.errors?.title || []"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-textarea
                      v-model="formDescription"
                      :label="t('teacher.lesson_description', {}, { default: 'Описание урока' })"
                      variant="outlined"
                      density="compact"
                      rows="4"
                      :error-messages="form.value?.errors?.description || []"
                    ></v-textarea>
                  </v-col>
                  <v-col cols="12" v-if="selectedSchedule || selectedScheduleId">
                    <v-card variant="outlined" class="pa-3">
                      <div class="d-flex align-center">
                        <v-icon class="mr-2" color="primary">mdi-calendar-clock</v-icon>
                        <div v-if="selectedSchedule">
                          <div class="text-body-1 font-weight-medium">
                            {{ selectedSchedule.subject?.name }}
                          </div>
                          <div class="text-caption text-medium-emphasis">
                            {{ t('teacher.group', {}, { default: 'Группа' }) }}: {{ selectedSchedule.group?.name }}
                          </div>
                        </div>
                        <div v-else>
                          <div class="text-body-1 font-weight-medium">
                            {{ t('teacher.schedule_selected', {}, { default: 'Расписание выбрано' }) }}
                          </div>
                          <div class="text-caption text-medium-emphasis">
                            ID: {{ selectedScheduleId }}
                          </div>
                        </div>
                      </div>
                    </v-card>
                  </v-col>
                  <v-col cols="12">
                    <v-file-input
                      v-model="formFile"
                      :label="t('teacher.lesson_file', {}, { default: 'Файл урока (PDF, DOC, DOCX, PPT, PPTX, TXT)' })"
                      variant="outlined"
                      density="compact"
                      accept=".pdf,.doc,.docx,.ppt,.pptx,.txt"
                      prepend-icon="mdi-file-document"
                      :error-messages="form.value?.errors?.file || []"
                    ></v-file-input>
                    <v-alert
                      type="info"
                      variant="tonal"
                      class="mt-2"
                    >
                      {{ t('teacher.max_file_size', {}, { default: 'Максимальный размер файла: 10MB' }) }}
                    </v-alert>
                  </v-col>
                </v-row>
                <div class="d-flex justify-end gap-2 mt-4">
                  <v-btn
                    color="secondary"
                    @click="goBack"
                  >
                    {{ t('common.cancel', {}, { default: 'Отмена' }) }}
                  </v-btn>
                  <v-btn
                    color="primary"
                    @click="createLesson"
                    :loading="form.value?.processing"
                    :disabled="form.value?.processing"
                  >
                    {{ t('teacher.create_lesson', {}, { default: 'Создать урок' }) }}
                  </v-btn>
                </div>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
        
        <v-col cols="12" md="4">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-information</v-icon>
              {{ t('common.info', {}, { default: 'Информация' }) }}
            </v-card-title>
            <v-card-text>
              <div class="text-body-2 text-medium-emphasis mb-4">
                <p>{{ t('teacher.create_lesson_hint1', {}, { default: 'Создайте новый урок и добавьте его к одному из ваших расписаний.' }) }}</p>
                <p>{{ t('teacher.create_lesson_hint2', {}, { default: 'Вы можете прикрепить файл с материалами урока.' }) }}</p>
              </div>
              
              <div v-if="selectedSchedule" class="mt-4">
                <h3 class="text-h6 mb-2">{{ t('teacher.selected_schedule', {}, { default: 'Выбранное расписание' }) }}:</h3>
                <v-card variant="outlined">
                  <v-card-text>
                    <div class="text-subtitle-1 font-weight-medium">
                      {{ selectedSchedule.subject?.name }}
                    </div>
                    <div class="text-body-2 text-medium-emphasis">
                      {{ t('teacher.group', {}, { default: 'Группа' }) }}: {{ selectedSchedule.group?.name }}
                    </div>
                    <div class="text-body-2 text-medium-emphasis">
                      {{ t('teacher.period', {}, { default: 'Период' }) }}: {{ selectedSchedule.start_date }} - {{ selectedSchedule.end_date }}
                    </div>
                  </v-card-text>
                </v-card>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'
import axios from 'axios'

const page = usePage()
const { t } = useI18n()

// Props
const props = defineProps({
  schedules: {
    type: Array,
    default: () => []
  },
  selectedScheduleId: {
    type: [Number, String],
    default: null
  }
})

// Форма
const form = ref({
  title: '',
  description: '',
  schedule_id: null,
  file: null,
  errors: {},
  processing: false
})

// Автоматически устанавливаем schedule_id при загрузке страницы
onMounted(() => {
  if (props.selectedScheduleId) {
    form.value.schedule_id = Number(props.selectedScheduleId)
  }
})

// Вычисляемые свойства
const selectedSchedule = computed(() => {
  const scheduleId = form.value?.schedule_id || props.selectedScheduleId
  if (!scheduleId) return null
  
  // Преобразуем оба значения к числу для сравнения
  const numericScheduleId = Number(scheduleId)
  return props.schedules.find(schedule => Number(schedule.id) === numericScheduleId)
})

// Computed свойства для формы
const formTitle = computed({
  get: () => form.value?.title,
  set: (value) => {
    if (form.value) form.value.title = value
  }
})

const formDescription = computed({
  get: () => form.value?.description,
  set: (value) => {
    if (form.value) form.value.description = value
  }
})


const formFile = computed({
  get: () => form.value?.file || null,
  set: (value) => {
    if (form.value) form.value.file = value
  }
})

// Методы
const goBack = () => {
  router.visit('/teacher/lessons')
}

const createLesson = async () => {
  // Проверяем обязательные поля
  if (!form.value?.title || !form.value?.schedule_id) {
    alert(t('teacher.fill_required_fields', {}, { default: 'Пожалуйста, заполните все обязательные поля' }))
    return
  }

  console.log('Отправка формы:', form.value)
  
  form.value.processing = true
  form.value.errors = {}

  try {
    const formData = new FormData()
    formData.append('title', form.value?.title)
    formData.append('description', form.value?.description)
    formData.append('schedule_id', form.value?.schedule_id)
    if (form.value?.file) {
      console.log('Добавляем файл в FormData:', form.value.file)
      console.log('Тип файла:', form.value.file.type)
      console.log('Имя файла:', form.value.file.name)
      formData.append('file', form.value.file)
    }

    const response = await axios.post('/teacher/lessons', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    console.log('Урок создан успешно', response.data)
    
    // Перенаправляем на страницу уроков
    router.visit('/teacher/lessons')
    
  } catch (error) {
    console.error('Ошибки при создании урока:', error)
    
    if (error.response?.status === 422) {
      // Ошибки валидации
      if (form.value) {
        form.value.errors = error.response.data.errors || {}
      }
    } else {
      // Другие ошибки
      alert(t('teacher.error_creating_lesson', {}, { default: 'Ошибка при создании урока' }) + ': ' + (error.response?.data?.message || error.message))
    }
  } finally {
    if (form.value) {
      form.value.processing = false
    }
  }
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
