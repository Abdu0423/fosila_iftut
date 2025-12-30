<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Создать силлабус</h1>
              <p class="text-body-1 text-medium-emphasis">Добавьте новую учебную программу</p>
            </div>
            <v-btn
              color="secondary"
              variant="outlined"
              @click="router.visit('/teacher/syllabuses')"
              prepend-icon="mdi-arrow-left"
            >
              Назад к списку
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Уведомление об отсутствии расписаний -->
      <v-row v-if="!hasSchedules" justify="center">
        <v-col cols="12" md="8" lg="6">
          <v-alert
            type="warning"
            variant="tonal"
            prominent
            closable
            class="mb-4"
          >
            <v-alert-title class="text-h6 mb-2">
              <v-icon start>mdi-alert</v-icon>
              {{ t('teacher.syllabuses.no_schedules_title', {}, { default: 'Нет расписаний' }) }}
            </v-alert-title>
            <div class="text-body-1">
              {{ warningMessage || t('teacher.syllabuses.no_schedules_message', {}, { default: 'У вас пока нет расписаний. Обратитесь к администратору для создания расписания.' }) }}
            </div>
          </v-alert>
        </v-col>
      </v-row>

      <!-- Форма создания силлабуса -->
      <v-row justify="center">
        <v-col cols="12" md="8" lg="6">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-file-document-plus</v-icon>
              Информация о силлабусе
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submitForm">
                <!-- Основная информация -->
                <v-row>
                  <v-col cols="12">
                    <v-text-field
                      v-model="form.name"
                      label="Название силлабуса"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.name"
                      required
                    ></v-text-field>
                  </v-col>
                </v-row>

                <v-row>
                  <v-col cols="12">
                    <v-textarea
                      v-model="form.description"
                      label="Описание"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.description"
                      rows="3"
                      auto-grow
                    ></v-textarea>
                  </v-col>
                </v-row>

                <!-- Предмет и год -->
                <v-row>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.subject_id"
                      :items="subjects"
                      item-title="display_name"
                      item-value="id"
                      label="Предмет"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.subject_id"
                      :disabled="!hasSchedules || subjects.length === 0"
                      :hint="!hasSchedules ? t('teacher.syllabuses.no_schedules_message', {}, { default: 'У вас пока нет расписаний. Обратитесь к администратору для создания расписания.' }) : (subjects.length === 0 ? t('teacher.syllabuses.no_subjects_in_schedules', {}, { default: 'Нет предметов в расписаниях' }) : '')"
                      persistent-hint
                      required
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.creation_year"
                      :items="years"
                      label="Год создания"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.creation_year"
                      required
                    ></v-select>
                  </v-col>
                </v-row>

                <!-- Загрузка файла -->
                <v-row>
                  <v-col cols="12">
                    <v-file-input
                      v-model="form.file"
                      label="Файл силлабуса"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.file"
                      accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.bmp,.webp"
                      :rules="fileRules"
                      required
                      prepend-icon="mdi-file-upload"
                      @change="handleFileChange"
                    >
                      <template v-slot:selection="{ fileNames }">
                        <template v-for="fileName in fileNames" :key="fileName">
                          <v-chip
                            size="small"
                            label
                            color="primary"
                            class="me-2"
                          >
                            {{ fileName }}
                          </v-chip>
                        </template>
                      </template>
                    </v-file-input>
                  </v-col>
                </v-row>

                <!-- Предварительный просмотр -->
                <v-row v-if="previewData">
                  <v-col cols="12">
                    <v-card variant="outlined" class="pa-4">
                      <v-card-title class="text-h6">
                        <v-icon start>mdi-eye</v-icon>
                        Предварительный просмотр
                      </v-card-title>
                      <v-card-text>
                        <v-row>
                          <v-col cols="12" md="6">
                            <div class="text-body-2">
                              <strong>Название:</strong> {{ form.name }}
                            </div>
                            <div class="text-body-2 mt-2">
                              <strong>Описание:</strong> {{ form.description }}
                            </div>
                            <div class="text-body-2 mt-2">
                              <strong>Предмет:</strong> {{ selectedSubjectName }}
                            </div>
                            <div class="text-body-2 mt-2">
                              <strong>Год создания:</strong> {{ form.creation_year }}
                            </div>
                          </v-col>
                          <v-col cols="12" md="6">
                            <div class="text-body-2">
                              <strong>Файл:</strong> {{ previewData.fileName }}
                            </div>
                            <div class="text-body-2 mt-2">
                              <strong>Размер:</strong> {{ previewData.fileSize }}
                            </div>
                            <div class="text-body-2 mt-2">
                              <strong>Тип:</strong> {{ previewData.fileType }}
                            </div>
                            <div class="text-body-2 mt-2">
                              <strong>Дата создания:</strong> {{ previewData.creationDate }}
                            </div>
                          </v-col>
                        </v-row>
                      </v-card-text>
                    </v-card>
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
                    @click="router.visit('/teacher/syllabuses')"
                  >
                    Отмена
                  </v-btn>
                  <v-btn
                    color="primary"
                    type="submit"
                    :loading="form.processing"
                    :disabled="form.processing || !previewData || !hasSchedules || subjects.length === 0"
                  >
                    Создать силлабус
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
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'

const { t } = useI18n()

// Props из Inertia
const props = defineProps({
  subjects: {
    type: Array,
    default: () => []
  },
  years: {
    type: Array,
    default: () => []
  },
  has_schedules: {
    type: Boolean,
    default: true
  },
  warning_message: {
    type: String,
    default: null
  }
})

// Вычисляемые свойства
const hasSchedules = computed(() => props.has_schedules)
const warningMessage = computed(() => props.warning_message)

// Форма
const form = useForm({
  name: '',
  description: '',
  subject_id: '',
  creation_year: new Date().getFullYear(),
  file: null
})

// Состояние
const previewData = ref(null)

// Правила валидации файла
const fileRules = [
  (value) => {
    if (!value) return 'Файл обязателен'
    if (value.size > 10 * 1024 * 1024) return 'Размер файла не должен превышать 10MB'
    return true
  }
]

// Вычисляемые свойства
const selectedSubjectName = computed(() => {
  const subject = props.subjects.find(s => s.id == form.subject_id)
  return subject ? subject.display_name : 'Не выбран'
})

// Методы
const handleFileChange = (file) => {
  if (file) {
    previewData.value = {
      fileName: file.name,
      fileSize: formatFileSize(file.size),
      fileType: file.type,
      creationDate: new Date().toLocaleDateString('ru-RU')
    }
  } else {
    previewData.value = null
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const submitForm = () => {
  console.log('Отправка формы...')
  console.log('Form data:', form.data())
  console.log('Preview data:', previewData.value)
  console.log('Route:', '/teacher/syllabuses')
  
  if (!previewData.value) {
    alert('Пожалуйста, загрузите файл для предварительного просмотра')
    return
  }

  form.post('/teacher/syllabuses', {
    onSuccess: (page) => {
      console.log('Силлабус успешно создан', page)
      console.log('Перенаправление...')
    },
    onError: (errors) => {
      console.error('Ошибки валидации:', errors)
      console.error('Полные ошибки:', form.errors)
    },
    onFinish: () => {
      console.log('Форма завершена')
    }
  })
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
