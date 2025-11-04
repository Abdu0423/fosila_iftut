<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Мое расписание</h1>
              <p class="text-body-1 text-medium-emphasis">Просмотр расписаний, силлабусов, уроков и тестов</p>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Уведомления -->
      <v-row v-if="page.props.flash?.success">
        <v-col cols="12">
          <v-alert
            type="success"
            variant="tonal"
            closable
          >
            {{ page.props.flash.success }}
          </v-alert>
        </v-col>
      </v-row>

      <!-- Список расписаний -->
      <v-row v-if="schedules.length > 0">
        <v-col
          v-for="schedule in schedules"
          :key="schedule.id"
          cols="12"
        >
          <v-card class="mb-4">
            <v-card-title class="d-flex justify-space-between align-center">
              <div>
                <h3 class="text-h6">{{ schedule.subject?.name || 'Без предмета' }}</h3>
                <p class="text-body-2 text-medium-emphasis mb-0">
                  Группа: {{ schedule.group?.name || 'Не указана' }}
                </p>
            </div>
              <v-chip
                :color="schedule.is_active ? 'success' : 'grey'"
                size="small"
              >
                {{ schedule.is_active ? 'Активно' : 'Неактивно' }}
              </v-chip>
            </v-card-title>

            <v-card-text>
              <!-- Основная информация -->
              <v-row class="mb-4">
                <v-col cols="12" md="4">
                  <div class="d-flex align-center mb-2">
                    <v-icon class="mr-2" color="primary">mdi-account-group</v-icon>
                    <div>
                      <div class="text-caption text-medium-emphasis">Группа</div>
                      <div class="text-body-1 font-weight-medium">
                        {{ schedule.group?.name || 'Не указана' }}
                      </div>
                    </div>
                  </div>
                </v-col>
                <v-col cols="12" md="4">
                  <div class="d-flex align-center mb-2">
                    <v-icon class="mr-2" color="primary">mdi-book-open-variant</v-icon>
                    <div>
                      <div class="text-caption text-medium-emphasis">Предмет</div>
                      <div class="text-body-1 font-weight-medium">
                        {{ schedule.subject?.name || 'Не указан' }}
                      </div>
                    </div>
                  </div>
                </v-col>
                <v-col cols="12" md="4">
                  <div class="d-flex align-center mb-2">
                    <v-icon class="mr-2" color="primary">mdi-calendar-clock</v-icon>
                    <div>
                      <div class="text-caption text-medium-emphasis">Период урока</div>
                      <div class="text-body-1 font-weight-medium">
                        {{ schedule.scheduled_at_formatted }}
                      </div>
                    </div>
          </div>
        </v-col>
      </v-row>

      <!-- Статистика -->
              <v-row class="mb-4">
                <v-col cols="6" md="3">
                  <v-card variant="outlined" class="text-center pa-3">
                    <v-icon size="32" color="purple" class="mb-2">mdi-file-document-multiple</v-icon>
                    <div class="text-h6 font-weight-bold">{{ schedule.syllabuses?.length || 0 }}</div>
                    <div class="text-caption text-medium-emphasis">Силлабусов</div>
          </v-card>
        </v-col>
                <v-col cols="6" md="3">
                  <v-card variant="outlined" class="text-center pa-3">
                    <v-icon size="32" color="info" class="mb-2">mdi-teach</v-icon>
                    <div class="text-h6 font-weight-bold">{{ schedule.lessons?.length || 0 }}</div>
                    <div class="text-caption text-medium-emphasis">Уроков</div>
          </v-card>
        </v-col>
                <v-col cols="6" md="3">
                  <v-card variant="outlined" class="text-center pa-3">
                    <v-icon size="32" color="warning" class="mb-2">mdi-help-circle</v-icon>
                    <div class="text-h6 font-weight-bold">{{ schedule.tests_count || 0 }}</div>
                    <div class="text-caption text-medium-emphasis">Тестов</div>
          </v-card>
        </v-col>
                <v-col cols="6" md="3">
                  <v-card variant="outlined" class="text-center pa-3">
                    <v-icon size="32" color="success" class="mb-2">mdi-calendar-check</v-icon>
                    <div class="text-h6 font-weight-bold">{{ schedule.semester || '-' }}</div>
                    <div class="text-caption text-medium-emphasis">Семестр</div>
          </v-card>
        </v-col>
      </v-row>

              <!-- Силлабусы -->
              <v-expansion-panels class="mb-4">
                <v-expansion-panel>
                  <v-expansion-panel-title>
                    <v-icon start>mdi-file-document-multiple</v-icon>
                    Силлабусы ({{ schedule.syllabuses?.length || 0 }})
                  </v-expansion-panel-title>
                  <v-expansion-panel-text>
                    <!-- Добавление силлабусов -->
                    <div class="mb-4">
                      <v-tabs v-model="syllabusTabs[schedule.id]" density="compact" class="mb-4">
                        <v-tab value="select">Выбрать из списка</v-tab>
                        <v-tab value="upload">Загрузить из файла</v-tab>
                      </v-tabs>

                      <v-window v-model="syllabusTabs[schedule.id]" @update:model-value="() => initUploadForm(schedule.id)">
                        <!-- Вкладка выбора из списка -->
                        <v-window-item value="select">
                          <v-select
                            v-model="selectedSyllabuses[schedule.id]"
                            :items="getAvailableSyllabusesForSchedule(schedule)"
                            item-title="name"
                            item-value="id"
                            :label="getSyllabusSelectLabel(schedule)"
                            variant="outlined"
                            density="compact"
                            clearable
                            :hint="getAvailableSyllabusesForSchedule(schedule).length === 0 ? 'Нет доступных силлабусов для этого предмета' : ''"
                            persistent-hint
                          >
                            <template v-slot:item="{ props, item }">
                              <v-list-item v-bind="props">
                                <v-list-item-title>{{ item.raw.name }}</v-list-item-title>
                                <v-list-item-subtitle v-if="item.raw.subject">
                                  {{ item.raw.subject.name }}
                                </v-list-item-subtitle>
                              </v-list-item>
                            </template>
                            <template v-slot:no-data>
                              <v-list-item>
                                <v-list-item-title>
                                  {{ schedule.subject ? `Нет доступных силлабусов для предмета "${schedule.subject.name}"` : 'Нет доступных силлабусов' }}
                                </v-list-item-title>
                              </v-list-item>
                            </template>
                          </v-select>
                          <v-btn
                            color="primary"
                            @click="addSyllabus(schedule)"
                            :disabled="!selectedSyllabuses[schedule.id] || getAvailableSyllabusesForSchedule(schedule).length === 0"
                            class="mt-2"
                            prepend-icon="mdi-plus"
                          >
                            Добавить силлабус
                          </v-btn>
                        </v-window-item>

                        <!-- Вкладка загрузки из файла -->
                        <v-window-item value="upload">
                          <v-text-field
                            v-model="uploadSyllabusForms[schedule.id].name"
                            label="Название силлабуса *"
                            variant="outlined"
                            density="compact"
                            class="mb-2"
                            :error-messages="uploadSyllabusForms[schedule.id]?.errors?.name"
                          ></v-text-field>
                          <v-textarea
                            v-model="uploadSyllabusForms[schedule.id].description"
                            label="Описание"
                            variant="outlined"
                            density="compact"
                            rows="2"
                            class="mb-2"
                            :error-messages="uploadSyllabusForms[schedule.id]?.errors?.description"
                          ></v-textarea>
                          <v-file-input
                            v-model="uploadSyllabusForms[schedule.id].file"
                            label="Файл силлабуса *"
                            variant="outlined"
                            density="compact"
                            accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.bmp,.webp,.md,.html,.css,.js,.json,.xml,.csv,.log"
                            prepend-icon="mdi-file-upload"
                            class="mb-2"
                            :error-messages="uploadSyllabusForms[schedule.id]?.errors?.file"
                            show-size
                          ></v-file-input>
                          <v-btn
                            color="primary"
                            @click="uploadSyllabus(schedule)"
                            :disabled="!uploadSyllabusForms[schedule.id] || !uploadSyllabusForms[schedule.id].name || !hasFile(uploadSyllabusForms[schedule.id].file) || uploadSyllabusForms[schedule.id].processing"
                            :loading="uploadSyllabusForms[schedule.id]?.processing"
                            prepend-icon="mdi-upload"
                          >
                            Загрузить и добавить
                          </v-btn>
                        </v-window-item>
                      </v-window>
                    </div>

                    <!-- Список силлабусов -->
                    <div v-if="schedule.syllabuses && schedule.syllabuses.length > 0">
              <v-list>
                <v-list-item
                          v-for="syllabus in schedule.syllabuses"
                          :key="syllabus.id"
                          class="mb-2"
                >
                  <template v-slot:prepend>
                            <v-avatar color="purple" size="40">
                              <v-icon color="white">mdi-file-document</v-icon>
                    </v-avatar>
                  </template>
                          <v-list-item-title>{{ syllabus.name }}</v-list-item-title>
                          <v-list-item-subtitle>
                            <div v-if="syllabus.description" class="mb-1">
                              {{ syllabus.description }}
                    </div>
                            <div class="d-flex align-center gap-2">
                              <span v-if="syllabus.file_name">
                                <v-icon size="16">mdi-file</v-icon>
                                {{ syllabus.file_name }}
                              </span>
                              <span v-if="syllabus.file_size_formatted" class="ml-2">
                                ({{ syllabus.file_size_formatted }})
                              </span>
                    </div>
                            <div v-if="syllabus.creator" class="mt-1">
                              <v-icon size="14">mdi-account</v-icon>
                              Создал: {{ syllabus.creator.name }}
                    </div>
                  </v-list-item-subtitle>
                  <template v-slot:append>
                          <v-btn
                              icon="mdi-delete"
                            variant="text"
                              color="error"
                              size="small"
                              @click="removeSyllabus(schedule, syllabus)"
                          ></v-btn>
                        </template>
                          </v-list-item>
                        </v-list>
                    </div>
                    <div v-else class="text-center py-4">
                      <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-file-document-outline</v-icon>
                      <p class="text-body-2 text-grey">Нет добавленных силлабусов</p>
                    </div>
                  </v-expansion-panel-text>
                </v-expansion-panel>
              </v-expansion-panels>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Пустое состояние -->
      <v-row v-else>
        <v-col cols="12">
          <v-card>
            <v-card-text class="text-center py-8">
              <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-calendar-outline</v-icon>
              <h3 class="text-h6 mb-2">У вас пока нет расписаний</h3>
              <p class="text-body-2 text-grey">Расписания будут отображаться здесь после их создания</p>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Snackbar для уведомлений -->
      <v-snackbar
        v-model="snackbar.show"
        :color="snackbar.color"
        :timeout="snackbar.timeout"
        location="top right"
        variant="elevated"
      >
        <div class="d-flex align-center">
          <v-icon class="mr-3" :color="snackbar.iconColor">
            {{ snackbar.icon }}
          </v-icon>
          <div>
            <div class="font-weight-medium">{{ snackbar.title }}</div>
            <div class="text-caption">{{ snackbar.message }}</div>
          </div>
        </div>
        
        <template v-slot:actions>
          <v-btn
            :color="snackbar.iconColor"
            variant="text"
            @click="snackbar.show = false"
            size="small"
          >
            Закрыть
          </v-btn>
        </template>
      </v-snackbar>

      <!-- Диалог подтверждения удаления силлабуса -->
      <v-dialog v-model="deleteSyllabusDialog.show" max-width="500px">
        <v-card>
          <v-card-title class="text-h6">
            <v-icon color="error" class="mr-2">mdi-alert-circle</v-icon>
            Подтверждение удаления
          </v-card-title>
          <v-card-text>
            <p>Вы уверены, что хотите удалить силлабус <strong>"{{ deleteSyllabusDialog.syllabusName }}"</strong> из расписания?</p>
            <v-alert
              type="warning"
              variant="outlined"
              class="mt-3"
            >
              Это действие нельзя отменить!
            </v-alert>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="grey"
              variant="text"
              @click="deleteSyllabusDialog.show = false"
            >
              Отмена
            </v-btn>
            <v-btn
              color="error"
              @click="confirmDeleteSyllabus"
              :loading="deleteSyllabusDialog.processing"
            >
              Удалить
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'
import axios from 'axios'

const page = usePage()

// Состояние для snackbar уведомлений
const snackbar = reactive({
  show: false,
  title: '',
  message: '',
  color: 'info',
  icon: 'mdi-information',
  iconColor: 'white',
  timeout: 4000
})

// Состояние для диалога удаления силлабуса
const deleteSyllabusDialog = reactive({
  show: false,
  scheduleId: null,
  syllabusId: null,
  syllabusName: '',
  processing: false
})

// Функции для показа уведомлений
const showNotification = (title, message, type = 'info') => {
  const configs = {
    success: {
      color: 'success',
      icon: 'mdi-check-circle',
      iconColor: 'white',
      timeout: 3000
    },
    error: {
      color: 'error',
      icon: 'mdi-alert-circle',
      iconColor: 'white',
      timeout: 5000
    },
    warning: {
      color: 'warning',
      icon: 'mdi-alert',
      iconColor: 'white',
      timeout: 4000
    },
    info: {
      color: 'info',
      icon: 'mdi-information',
      iconColor: 'white',
      timeout: 3000
    }
  }

  const config = configs[type] || configs.info
  snackbar.title = title
  snackbar.message = message
  snackbar.color = config.color
  snackbar.icon = config.icon
  snackbar.iconColor = config.iconColor
  snackbar.timeout = config.timeout
  snackbar.show = true
}

const showSuccess = (message, title = 'Успешно') => {
  showNotification(title, message, 'success')
}

const showError = (message, title = 'Ошибка') => {
  showNotification(title, message, 'error')
}

const showWarning = (message, title = 'Предупреждение') => {
  showNotification(title, message, 'warning')
}

const showInfo = (message, title = 'Информация') => {
  showNotification(title, message, 'info')
}

// Props
const props = defineProps({
  schedules: {
    type: Array,
    default: () => []
  },
  availableSyllabuses: {
    type: Array,
    default: () => []
  }
})

// Состояние для выбранных силлабусов (по ID расписания)
const selectedSyllabuses = ref({})
const syllabusTabs = ref({})

// Состояние для форм загрузки файлов (по ID расписания)
const uploadSyllabusForms = ref({})

// Функция проверки наличия файла
const hasFile = (file) => {
  if (!file) return false
  if (Array.isArray(file)) return file.length > 0 && file[0] instanceof File
  if (file instanceof FileList) return file.length > 0
  if (file instanceof File) return true
  return false
}

// Функция для инициализации формы загрузки для расписания
const initUploadForm = (scheduleId) => {
  if (!uploadSyllabusForms.value[scheduleId]) {
    uploadSyllabusForms.value[scheduleId] = reactive({
      name: '',
      description: '',
      file: null,
      processing: false,
      errors: {}
    })
  }
  if (!syllabusTabs.value[scheduleId]) {
    syllabusTabs.value[scheduleId] = 'select'
  }
}

// Инициализация форм загрузки для каждого расписания
props.schedules.forEach(schedule => {
  initUploadForm(schedule.id)
})

// Правила валидации файла удалены - проверка выполняется в методе uploadSyllabus

// Метод получения доступных силлабусов для расписания (исключая уже добавленные и фильтруя по предмету)
const getAvailableSyllabusesForSchedule = (schedule) => {
  const existingIds = schedule.syllabuses?.map(s => s.id) || []
  const scheduleSubjectId = schedule.subject?.id
  
  return props.availableSyllabuses.filter(s => {
    // Исключаем уже добавленные силлабусы
    if (existingIds.includes(s.id)) {
      return false
    }
    
    // Если у расписания есть предмет, показываем только силлабусы этого предмета
    if (scheduleSubjectId && s.subject) {
      return s.subject.id === scheduleSubjectId
    }
    
    // Если у расписания нет предмета, показываем все доступные
    // Если у силлабуса нет предмета, тоже показываем (на случай, если предмет не указан)
    return true
  })
}

// Метод получения label для выбора силлабуса
const getSyllabusSelectLabel = (schedule) => {
  if (schedule.subject && schedule.subject.name) {
    return `Выберите силлабус для предмета "${schedule.subject.name}"`
  }
  return 'Выберите силлабус для добавления'
}

// Метод добавления силлабуса к расписанию
const addSyllabus = (schedule) => {
  const syllabusId = selectedSyllabuses.value[schedule.id]
  if (!syllabusId) {
    showWarning('Пожалуйста, выберите силлабус из списка')
    return
  }

  // Получаем все существующие ID силлабусов + новый
  const existingIds = schedule.syllabuses?.map(s => s.id) || []
  const allIds = [...existingIds, syllabusId]

  const form = useForm({
    syllabus_ids: allIds
  })

  form.post(`/teacher/schedule/${schedule.id}/syllabuses`, {
    preserveScroll: true,
    onSuccess: () => {
      selectedSyllabuses.value[schedule.id] = null
      router.reload({ only: ['schedules'] })
      showSuccess('Силлабус успешно добавлен к расписанию')
    },
    onError: (errors) => {
      console.error('Ошибка при добавлении силлабуса:', errors)
      showError('Ошибка при добавлении силлабуса: ' + (errors.message || 'Неизвестная ошибка'))
    }
  })
}

// Метод удаления силлабуса из расписания (открывает диалог подтверждения)
const removeSyllabus = (schedule, syllabus) => {
  deleteSyllabusDialog.scheduleId = schedule.id
  deleteSyllabusDialog.syllabusId = syllabus.id
  deleteSyllabusDialog.syllabusName = syllabus.name
  deleteSyllabusDialog.show = true
}

// Подтверждение удаления силлабуса
const confirmDeleteSyllabus = () => {
  if (!deleteSyllabusDialog.scheduleId || !deleteSyllabusDialog.syllabusId) {
    return
  }

  deleteSyllabusDialog.processing = true

  // Используем axios для удаления, чтобы обработать JSON ответ
  axios.delete(
    `/teacher/schedule/${deleteSyllabusDialog.scheduleId}/syllabuses/${deleteSyllabusDialog.syllabusId}`,
    {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    }
  )
    .then((response) => {
      deleteSyllabusDialog.show = false
      deleteSyllabusDialog.processing = false
      
      // Проверяем ответ от сервера
      if (response.data && response.data.success) {
        const message = response.data.message || `Силлабус "${deleteSyllabusDialog.syllabusName}" успешно удален из расписания`
        showSuccess(message)
      } else {
        showSuccess(`Силлабус "${deleteSyllabusDialog.syllabusName}" успешно удален из расписания`)
      }
      
      // Очищаем данные диалога
      deleteSyllabusDialog.scheduleId = null
      deleteSyllabusDialog.syllabusId = null
      deleteSyllabusDialog.syllabusName = ''
      
      // Перезагружаем страницу для обновления списка
      router.reload({ only: ['schedules'] })
    })
    .catch((error) => {
      deleteSyllabusDialog.processing = false
      console.error('Ошибка при удалении силлабуса:', error)
      
      // Обрабатываем разные типы ошибок
      let errorMessage = 'Не удалось удалить силлабус из расписания'
      
      if (error.response && error.response.data) {
        if (error.response.data.message) {
          errorMessage = error.response.data.message
        } else if (error.response.data.error) {
          errorMessage = error.response.data.error
        } else if (typeof error.response.data === 'string') {
          errorMessage = error.response.data
        }
      } else if (error.message) {
        errorMessage = error.message
      }
      
      showError(errorMessage)
    })
}

// Метод загрузки силлабуса из файла
const uploadSyllabus = async (schedule) => {
  // Инициализация формы, если её нет
  initUploadForm(schedule.id)
  
  const form = uploadSyllabusForms.value[schedule.id]
  
  if (!form.name || !form.name.trim()) {
    showWarning('Пожалуйста, введите название силлабуса')
    return
  }
  
  // Проверка файла - v-file-input может возвращать массив или один файл
  let fileToUpload = null
  if (form.file) {
    if (Array.isArray(form.file)) {
      fileToUpload = form.file.length > 0 ? form.file[0] : null
    } else if (form.file instanceof FileList) {
      fileToUpload = form.file.length > 0 ? form.file[0] : null
    } else if (form.file instanceof File) {
      fileToUpload = form.file
    }
  }
  
  if (!fileToUpload) {
    showWarning('Пожалуйста, выберите файл для загрузки')
    return
  }

  form.processing = true
  form.errors = {}

  try {
    const formData = new FormData()
    formData.append('name', form.name.trim())
    formData.append('description', form.description || '')
    formData.append('file', fileToUpload)

    const response = await axios.post(
      `/teacher/schedule/${schedule.id}/syllabuses/upload`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        },
        onUploadProgress: (progressEvent) => {
          // Можно добавить прогресс-бар если нужно
        }
      }
    )

    if (response.data && response.data.success) {
      // Сброс формы
      form.name = ''
      form.description = ''
      form.file = null
      form.errors = {}
      
      // Перезагрузка страницы для обновления списка
      router.reload({ only: ['schedules'] })
      showSuccess('Силлабус успешно загружен и добавлен к расписанию')
    } else {
      showError('Ошибка: ' + (response.data?.message || 'Не удалось загрузить силлабус'))
    }
  } catch (error) {
    console.error('Ошибка при загрузке силлабуса:', error)
    if (error.response && error.response.data) {
      if (error.response.data.errors) {
        form.errors = error.response.data.errors
        // Показываем первую ошибку валидации
        const firstError = Object.values(error.response.data.errors)[0]
        if (Array.isArray(firstError) && firstError.length > 0) {
          showError(firstError[0])
        }
      } else if (error.response.data.message) {
        showError('Ошибка при загрузке силлабуса: ' + error.response.data.message)
      } else {
        showError('Ошибка при загрузке силлабуса: ' + error.message)
      }
    } else {
      showError('Ошибка при загрузке силлабуса: ' + (error.message || 'Неизвестная ошибка'))
    }
  } finally {
    form.processing = false
  }
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
