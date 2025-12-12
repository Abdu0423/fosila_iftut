<template>
  <Layout role="admin">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Редактировать силлабус</h1>
              <p class="text-body-1 text-medium-emphasis">Измените информацию о силлабусе</p>
            </div>
            <v-btn
              color="secondary"
              variant="outlined"
              @click="navigateTo(`/admin/syllabuses/${syllabus.id}`)"
              prepend-icon="mdi-arrow-left"
            >
              Назад к силлабусу
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Форма редактирования силлабуса -->
      <v-row justify="center">
        <v-col cols="12" md="8" lg="6">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-file-document-edit</v-icon>
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

                <!-- Урок и год -->
                <v-row>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.lesson_id"
                      :items="lessons"
                      item-title="display_name"
                      item-value="id"
                      label="Урок"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.lesson_id"
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

                <!-- Текущий файл -->
                <v-row v-if="syllabus.file_name">
                  <v-col cols="12">
                    <v-card variant="outlined" class="pa-4">
                      <v-card-title class="text-subtitle-1">
                        <v-icon start>mdi-file</v-icon>
                        Текущий файл
                      </v-card-title>
                      <v-card-text>
                        <div class="d-flex align-center justify-space-between">
                          <div>
                            <div class="text-body-1 font-weight-medium">{{ syllabus.file_name }}</div>
                            <div class="text-caption text-medium-emphasis">
                              {{ syllabus.file_size_formatted }} • {{ syllabus.file_type }}
                            </div>
                          </div>
                          <v-chip color="info" variant="tonal" size="small">
                            Текущий
                          </v-chip>
                        </div>
                      </v-card-text>
                    </v-card>
                  </v-col>
                </v-row>

                <!-- Загрузка нового файла -->
                <v-row>
                  <v-col cols="12">
                    <v-file-input
                      v-model="form.file"
                      label="Новый файл (необязательно)"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.file"
                      accept=".pdf,.doc,.docx,.ppt,.pptx,.txt"
                      :rules="fileRules"
                      prepend-icon="mdi-file-plus"
                      @update:model-value="handleFileChange"
                      hint="Оставьте пустым, чтобы сохранить текущий файл"
                      persistent-hint
                    ></v-file-input>
                  </v-col>
                </v-row>

                <!-- Предварительный просмотр нового файла -->
                <v-row v-if="previewData">
                  <v-col cols="12">
                    <v-card variant="outlined" class="pa-4">
                      <v-card-title class="text-subtitle-1">
                        <v-icon start>mdi-eye</v-icon>
                        Предварительный просмотр нового файла
                      </v-card-title>
                      <v-card-text>
                        <div class="d-flex align-center justify-space-between">
                          <div>
                            <div class="text-body-1 font-weight-medium">{{ previewData.fileName }}</div>
                            <div class="text-caption text-medium-emphasis">
                              {{ previewData.fileSize }} • {{ previewData.fileType }}
                            </div>
                          </div>
                          <v-chip color="success" variant="tonal" size="small">
                            Новый
                          </v-chip>
                        </div>
                      </v-card-text>
                    </v-card>
                  </v-col>
                </v-row>

                <!-- Кнопки действий -->
                <v-row class="mt-6">
                  <v-col cols="12" class="d-flex gap-3">
                    <v-btn
                      type="submit"
                      color="primary"
                      size="large"
                      :loading="form.processing"
                      prepend-icon="mdi-content-save"
                    >
                      Сохранить изменения
                    </v-btn>
                    <v-btn
                      color="secondary"
                      variant="outlined"
                      size="large"
                      @click="navigateTo(`/admin/syllabuses/${syllabus.id}`)"
                    >
                      Отмена
                    </v-btn>
                  </v-col>
                </v-row>
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
import Layout from '../../Layout.vue'

// Props из Inertia
const props = defineProps({
  syllabus: {
    type: Object,
    required: true
  },
  lessons: {
    type: Array,
    default: () => []
  },
  years: {
    type: Array,
    default: () => []
  }
})

// Форма
const form = useForm({
  name: props.syllabus.name,
  description: props.syllabus.description,
  subject_id: props.syllabus.subject_id,
  creation_year: props.syllabus.creation_year,
  file: null
})

// Состояние
const previewData = ref(null)

// Правила валидации файла
const fileRules = [
  (value) => {
    if (!value) return true // Файл необязателен при редактировании
    if (value.size > 10 * 1024 * 1024) return 'Размер файла не должен превышать 10MB'
    return true
  }
]

// Вычисляемые свойства
const selectedLessonName = computed(() => {
  const lesson = props.lessons.find(l => l.id == form.lesson_id)
  return lesson ? lesson.display_name : 'Не выбран'
})

// Методы
const navigateTo = (route) => {
  router.visit(route)
}

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
  console.log('Отправка формы редактирования...')
  console.log('Form data:', form.data())
  console.log('Syllabus ID:', props.syllabus.id)
  
  form.put(`/admin/syllabuses/${props.syllabus.id}`, {
    onSuccess: (page) => {
      console.log('Силлабус успешно обновлен', page)
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
