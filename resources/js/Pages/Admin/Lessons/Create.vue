<template>
  <Layout role="admin">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Создать урок</h1>
              <p class="text-body-1 text-medium-emphasis">Добавьте новый учебный материал</p>
            </div>
            <v-btn
              color="secondary"
              variant="outlined"
              @click="router.visit('/admin/lessons')"
              prepend-icon="mdi-arrow-left"
            >
              Назад к списку
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Форма создания урока -->
      <v-row justify="center">
        <v-col cols="12" md="8" lg="6">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-book-plus</v-icon>
              Информация об уроке
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submitForm">
                <!-- Основная информация -->
                <v-row>
                  <v-col cols="12">
                    <v-text-field
                      v-model="form.title"
                      label="Название урока"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.title"
                      required
                    ></v-text-field>
                  </v-col>
                </v-row>

                <v-row>
                  <v-col cols="12">
                    <v-textarea
                      v-model="form.description"
                      label="Описание урока"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.description"
                      rows="3"
                      auto-grow
                    ></v-textarea>
                  </v-col>
                </v-row>

                <!-- Расписание -->
                <v-row>
                  <v-col cols="12">
                    <v-select
                      v-model="form.schedule_id"
                      :items="schedules"
                      item-title="display_name"
                      item-value="id"
                      label="Расписание (Предмет)"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.schedule_id"
                      required
                    >
                      <template v-slot:item="{ props, item }">
                        <v-list-item v-bind="props">
                          <v-list-item-title>{{ item.raw.display_name }}</v-list-item-title>
                          <v-list-item-subtitle>
                            Группа: {{ item.raw.group?.name }} | 
                            Учитель: {{ item.raw.teacher?.name }}
                          </v-list-item-subtitle>
                        </v-list-item>
                      </template>
                    </v-select>
                  </v-col>
                </v-row>

                <!-- Загрузка файла -->
                <v-row>
                  <v-col cols="12">
                    <v-file-input
                      v-model="form.file"
                      label="Файл урока"
                      variant="outlined"
                      density="compact"
                      :error-messages="form.errors.file"
                      accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.bmp,.webp"
                      :rules="fileRules"
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
                              <strong>Название:</strong> {{ form.title }}
                            </div>
                            <div class="text-body-2 mt-2">
                              <strong>Описание:</strong> {{ form.description }}
                            </div>
                            <div class="text-body-2 mt-2">
                              <strong>Расписание:</strong> {{ selectedScheduleInfo?.subjectName }}
                            </div>
                            <div class="text-body-2 mt-2">
                              <strong>Группа:</strong> {{ selectedScheduleInfo?.groupName }}
                            </div>
                            <div class="text-body-2 mt-2">
                              <strong>Учитель:</strong> {{ selectedScheduleInfo?.teacherName }}
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
                    @click="router.visit('/admin/lessons')"
                  >
                    Отмена
                  </v-btn>
                  <v-btn
                    color="primary"
                    type="submit"
                    :loading="form.processing"
                    :disabled="form.processing"
                  >
                    Создать урок
                  </v-btn>
                </div>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Боковая панель со статистикой -->
      <v-col cols="12" md="4" class="d-md-none d-lg-block">
        <v-card class="mt-6">
          <v-card-title class="text-h6">
            <v-icon start>mdi-chart-line</v-icon>
            Статистика
          </v-card-title>
          <v-card-text>
            <div class="mb-4">
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2">Всего уроков</span>
                <span class="text-h6 font-weight-bold text-primary">{{ totalLessons || 0 }}</span>
              </div>
              <v-progress-linear
                :model-value="100"
                color="primary"
                height="8"
                rounded
              ></v-progress-linear>
            </div>
            
            <div class="mb-4">
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2">Расписаний</span>
                <span class="text-h6 font-weight-bold text-info">{{ schedules?.length || 0 }}</span>
              </div>
              <v-progress-linear
                :model-value="100"
                color="info"
                height="8"
                rounded
              ></v-progress-linear>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

// Props из Inertia
const props = defineProps({
  schedules: {
    type: Array,
    default: () => []
  },
  totalLessons: {
    type: Number,
    default: 0
  }
})




// Форма
const form = useForm({
  title: '',
  description: '',
  schedule_id: '',
  file: null
})

// Состояние
const previewData = ref(null)

// Правила валидации файла
const fileRules = [
  (value) => {
    if (!value) return 'Файл не обязателен, но рекомендуется'
    if (value.size > 10 * 1024 * 1024) return 'Размер файла не должен превышать 10MB'
    return true
  }
]

// Вычисляемые свойства
const selectedScheduleInfo = computed(() => {
  const schedule = props.schedules.find(s => s.id == form.schedule_id)
  if (!schedule) return null
  
  return {
    subjectName: schedule.subject?.name,
    groupName: schedule.group?.name,
    teacherName: schedule.teacher?.name
  }
})

// Методы
const handleFileChange = (file) => {
  if (file) {
    previewData.value = {
      fileName: file.name,
      fileSize: formatFileSize(file.size),
      fileType: file.type
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
  console.log('Отправка формы урока...')
  console.log('Form data:', form.data())
  
  form.post('/admin/lessons', {
    onSuccess: (page) => {
      console.log('Урок успешно создан', page)
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