<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('navigation.my_syllabuses') }}</h1>
              <p class="text-body-1 text-medium-emphasis">
                {{ departmentMode
                  ? 'Активные расписания кафедры: силлабус, методичка и тест'
                  : t('teacher.syllabuses.subtitle', {}, { default: 'Управление учебными программами по моим предметам' }) }}
              </p>
            </div>
            <v-btn
              v-if="!departmentMode"
              color="primary"
              @click="navigateTo('/teacher/syllabuses/create')"
              prepend-icon="mdi-plus"
            >
              Создать силлабус
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <v-alert v-if="notice.message" :type="notice.type" variant="tonal" closable class="mb-4" @click:close="notice.message = ''">
        {{ notice.message }}
      </v-alert>

      <template v-if="departmentMode">
        <v-alert v-if="missingDepartment" type="warning" variant="tonal" class="mb-4">
          Кафедра для вашей учётной записи не назначена. Расписания появятся после выбора пользователя на странице кафедр.
        </v-alert>
        <v-alert v-else-if="!departmentSchedules.length" type="info" variant="tonal">
          У кафедры{{ departmentName ? ` «${departmentName}»` : '' }} нет активных расписаний.
        </v-alert>

        <v-row v-else>
          <v-col v-for="schedule in departmentSchedules" :key="schedule.id" cols="12">
            <v-card class="mb-2">
              <v-card-title class="d-flex flex-wrap align-center ga-2">
                <span>{{ schedule.subject_name }}</span>
                <v-chip size="small" variant="tonal">{{ schedule.group_name }}</v-chip>
                <v-chip size="small" variant="tonal">{{ schedule.semester }} семестр</v-chip>
                <v-chip size="small" variant="tonal">{{ schedule.study_year }}</v-chip>
              </v-card-title>
              <v-card-text>
                <div class="text-subtitle-2 mb-2">Силлабус</div>
                <div class="d-flex flex-wrap ga-2 mb-3">
                  <v-select
                    v-model="selectedSyllabus[schedule.id]"
                    :items="availableFor(schedule)"
                    item-title="name"
                    item-value="id"
                    label="Выбрать силлабус"
                    density="compact"
                    variant="outlined"
                    hide-details
                    clearable
                    style="max-width: 420px"
                  />
                  <v-btn color="primary" :disabled="!selectedSyllabus[schedule.id]" @click="addSyllabus(schedule)">
                    Добавить
                  </v-btn>
                </div>
                <div class="d-flex flex-wrap ga-2 mb-3">
                  <v-text-field
                    v-model="formFor(schedule.id).name"
                    label="Название нового силлабуса"
                    density="compact"
                    variant="outlined"
                    hide-details
                    style="max-width: 280px"
                  />
                  <v-file-input
                    v-model="formFor(schedule.id).file"
                    label="Файл силлабуса"
                    density="compact"
                    variant="outlined"
                    hide-details
                    prepend-icon=""
                    prepend-inner-icon="mdi-file-upload"
                    style="max-width: 320px"
                  />
                  <v-btn color="primary" variant="tonal" :loading="formFor(schedule.id).processing" @click="uploadSyllabus(schedule)">
                    Загрузить
                  </v-btn>
                </div>
                <v-list v-if="schedule.syllabuses.length" density="compact" class="mb-4">
                  <v-list-item v-for="syllabus in schedule.syllabuses" :key="syllabus.id">
                    <v-list-item-title>{{ syllabus.name }}</v-list-item-title>
                    <v-list-item-subtitle v-if="syllabus.file_name">{{ syllabus.file_name }}</v-list-item-subtitle>
                    <template #append>
                      <v-btn icon="mdi-delete" size="small" variant="text" color="error" @click="removeSyllabus(schedule, syllabus)" />
                    </template>
                  </v-list-item>
                </v-list>
                <p v-else class="text-body-2 text-medium-emphasis mb-4">Силлабус ещё не добавлен</p>

                <div class="text-subtitle-2 mb-2">Методичка</div>
                <div class="d-flex flex-wrap align-center ga-2 mb-4">
                  <span v-if="schedule.methodical" class="text-body-2">{{ schedule.methodical.file_name }}</span>
                  <v-btn
                    v-if="schedule.methodical"
                    size="small"
                    variant="text"
                    color="primary"
                    @click="downloadFile(schedule.methodical.download_url)"
                  >
                    Скачать
                  </v-btn>
                  <v-btn
                    v-if="schedule.methodical"
                    size="small"
                    variant="text"
                    color="error"
                    @click="deleteMethodical(schedule)"
                  >
                    Удалить
                  </v-btn>
                  <v-file-input
                    v-model="methodicalFiles[schedule.id]"
                    label="Файл методички"
                    density="compact"
                    variant="outlined"
                    hide-details
                    prepend-icon=""
                    prepend-inner-icon="mdi-file-upload"
                    style="max-width: 320px"
                  />
                  <v-btn color="primary" variant="tonal" @click="uploadMethodical(schedule)">Загрузить методичку</v-btn>
                </div>

                <v-btn color="primary" @click="navigateTo(schedule.test_url)">
                  {{ schedule.has_test ? 'Открыть тест' : 'Добавить тест' }}
                </v-btn>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </template>

      <!-- Статистика -->
      <v-row v-if="!departmentMode" class="mb-6">
        <v-col cols="12" sm="6" md="3">
          <v-card>
            <v-card-text>
              <div class="text-h6">{{ stats.total }}</div>
              <div class="text-body-2 text-medium-emphasis">Всего силлабусов</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card>
            <v-card-text>
              <div class="text-h6">{{ stats.this_year }}</div>
              <div class="text-body-2 text-medium-emphasis">За этот год</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card>
            <v-card-text>
              <div class="text-h6">{{ stats.last_year }}</div>
              <div class="text-body-2 text-medium-emphasis">За прошлый год</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card>
            <v-card-text>
              <div class="text-h6">{{ stats.with_files }}</div>
              <div class="text-body-2 text-medium-emphasis">С файлами</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Таблица силлабусов -->
      <v-card v-if="!departmentMode">
        <v-card-title>
          <v-text-field
            v-model="search"
            prepend-inner-icon="mdi-magnify"
            label="Поиск"
            single-line
            hide-details
            variant="outlined"
            density="compact"
            class="max-width-300"
          ></v-text-field>
        </v-card-title>

        <ExcelSheet :headers="headers" :rows="syllabuses">
          <template #cell-name="{ row }">
            <div class="font-weight-medium">{{ row.name }}</div>
            <div class="text-caption text-medium-emphasis">{{ row.description }}</div>
          </template>

          <template #cell-subject_name="{ row }">
            <v-chip
              :color="row.subject_name !== 'Не указан' ? 'primary' : 'grey'"
              variant="tonal"
              size="small"
            >
              {{ row.subject_name }}
            </v-chip>
          </template>

          <template #cell-file_name="{ row }">
            <div v-if="row.file_name">
              <v-icon size="small" class="mr-1">mdi-file</v-icon>
              {{ row.file_name }}
              <div class="text-caption text-medium-emphasis">
                {{ row.file_size_formatted }}
              </div>
            </div>
            <span v-else class="text-medium-emphasis">Файл не загружен</span>
          </template>

          <template #cell-creation_year="{ row }">
            <v-chip
              color="secondary"
              variant="tonal"
              size="small"
            >
              {{ row.creation_year }}
            </v-chip>
          </template>

          <template #cell-created_at="{ row }">
            {{ row.created_at }}
          </template>

          <template #cell-actions="{ row }">
            <v-menu>
              <template v-slot:activator="{ props }">
                <v-btn
                  icon="mdi-dots-vertical"
                  variant="text"
                  size="small"
                  v-bind="props"
                ></v-btn>
              </template>
              <v-list>
                <v-list-row
                  @click="navigateTo(`/teacher/syllabuses/${row.id}`)"
                  prepend-icon="mdi-eye"
                >
                  <v-list-row-title>Просмотр</v-list-row-title>
                </v-list-row>
                <v-list-row
                  @click="navigateTo(`/teacher/syllabuses/${row.id}/edit`)"
                  prepend-icon="mdi-pencil"
                >
                  <v-list-row-title>Редактировать</v-list-row-title>
                </v-list-row>
                <v-list-row
                  v-if="row.download_url"
                  @click="downloadFile(row.download_url)"
                  prepend-icon="mdi-download"
                >
                  <v-list-row-title>Скачать</v-list-row-title>
                </v-list-row>
                <v-list-row
                  v-if="row.preview_url"
                  @click="previewFile(row.preview_url)"
                  prepend-icon="mdi-eye"
                >
                  <v-list-row-title>Предварительный просмотр</v-list-row-title>
                </v-list-row>
                <v-divider></v-divider>
                <v-list-row
                  @click="confirmDelete(row)"
                  prepend-icon="mdi-delete"
                  color="error"
                >
                  <v-list-row-title>Удалить</v-list-row-title>
                </v-list-row>
              </v-list>
            </v-menu>
          </template>
        </ExcelSheet>
      </v-card>

      <!-- Диалог подтверждения удаления -->
      <v-dialog v-model="deleteDialog" max-width="400">
        <v-card>
          <v-card-title>Подтверждение удаления</v-card-title>
          <v-card-text>
            Вы уверены, что хотите удалить силлабус "{{ syllabusToDelete?.name }}"?
            Это действие нельзя отменить.
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="secondary"
              variant="outlined"
              @click="deleteDialog = false"
            >
              Отмена
            </v-btn>
            <v-btn
              color="error"
              @click="deleteSyllabus"
              :loading="deleting"
            >
              Удалить
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </Layout>
</template>

<script setup lang="ts">
import ExcelSheet from '@/Components/ExcelSheet.vue'
import { ref, computed } from 'vue'
import { router } from '@/compat/inertia'
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'

const { t } = useI18n()

// Props из Inertia
const props = defineProps({
  syllabuses: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({})
  },
  schedules: {
    type: Array,
    default: () => []
  },
  department_mode: {
    type: Boolean,
    default: false
  },
  missing_department: {
    type: Boolean,
    default: false
  },
  department_name: {
    type: String,
    default: ''
  },
  available_syllabuses: {
    type: Array,
    default: () => []
  }
})

const departmentMode = computed(() => props.department_mode)
const missingDepartment = computed(() => props.missing_department)
const departmentName = computed(() => props.department_name)
const departmentSchedules = computed(() => props.schedules || [])
const selectedSyllabus = ref({})
const methodicalFiles = ref({})
const uploadForms = ref({})
const notice = ref({ type: 'success', message: '' })

const ensureUploadForm = (scheduleId) => {
  if (!uploadForms.value[scheduleId]) {
    uploadForms.value[scheduleId] = { name: '', file: null, processing: false }
  }
  return uploadForms.value[scheduleId]
}

const formFor = (scheduleId) => ensureUploadForm(scheduleId)

const takenFile = (file) => {
  if (!file) return null
  if (Array.isArray(file)) return file[0] || null
  if (file instanceof FileList) return file[0] || null
  return file instanceof File ? file : null
}

const availableFor = (schedule) => {
  const used = new Set((schedule.syllabuses || []).map((item) => item.id))
  return (props.available_syllabuses || []).filter((item) => {
    if (used.has(item.id)) return false
    if (!schedule.subject_id || !item.subject_id) return true
    return item.subject_id === schedule.subject_id
  })
}

const showNotice = (type, message) => {
  notice.value = { type, message }
}

const addSyllabus = async (schedule) => {
  const syllabusId = selectedSyllabus.value[schedule.id]
  if (!syllabusId) return
  const syllabusIds = [...(schedule.syllabuses || []).map((item) => item.id), syllabusId]
  try {
    await window.axios.post(`/teacher/schedule/${schedule.id}/syllabuses`, { syllabus_ids: syllabusIds })
    selectedSyllabus.value[schedule.id] = null
    router.reload()
  } catch (error) {
    showNotice('error', error.response?.data?.message || 'Не удалось добавить силлабус')
  }
}

const uploadSyllabus = async (schedule) => {
  const form = ensureUploadForm(schedule.id)
  const file = takenFile(form.file)
  if (!form.name || !file) {
    showNotice('warning', 'Укажите название и файл силлабуса')
    return
  }
  form.processing = true
  const body = new FormData()
  body.append('name', form.name)
  body.append('file', file)
  try {
    await window.axios.post(`/teacher/schedule/${schedule.id}/syllabuses/upload`, body)
    form.name = ''
    form.file = null
    router.reload()
  } catch (error) {
    showNotice('error', error.response?.data?.message || 'Не удалось загрузить силлабус')
  } finally {
    form.processing = false
  }
}

const removeSyllabus = async (schedule, syllabus) => {
  try {
    await window.axios.delete(`/teacher/schedule/${schedule.id}/syllabuses/${syllabus.id}`)
    router.reload()
  } catch (error) {
    showNotice('error', error.response?.data?.message || 'Не удалось убрать силлабус')
  }
}

const uploadMethodical = async (schedule) => {
  const file = takenFile(methodicalFiles.value[schedule.id])
  if (!file) {
    showNotice('warning', 'Выберите файл методички')
    return
  }
  const body = new FormData()
  body.append('file', file)
  try {
    await window.axios.post(`/teacher/schedule/${schedule.id}/methodical`, body)
    methodicalFiles.value[schedule.id] = null
    router.reload()
  } catch (error) {
    showNotice('error', error.response?.data?.message || 'Не удалось загрузить методичку')
  }
}

const deleteMethodical = async (schedule) => {
  try {
    await window.axios.delete(`/teacher/schedule/${schedule.id}/methodical`)
    router.reload()
  } catch (error) {
    showNotice('error', error.response?.data?.message || 'Не удалось удалить методичку')
  }
}

// Состояние
const search = ref('')
const loading = ref(false)
const deleteDialog = ref(false)
const deleting = ref(false)
const syllabusToDelete = ref(null)

// Заголовки таблицы
const headers = [
  { title: 'Название', key: 'name', sortable: true },
  { title: 'Предмет', key: 'subject_name', sortable: true },
  { title: 'Файл', key: 'file_name', sortable: false },
  { title: 'Год создания', key: 'creation_year', sortable: true },
  { title: 'Дата создания', key: 'created_at', sortable: true },
  { title: 'Действия', key: 'actions', sortable: false, width: '100px' }
]

// Методы
const navigateTo = (route) => {
  router.visit(route)
}

const downloadFile = (url) => {
  window.open(url, '_blank')
}

const previewFile = (url) => {
  window.open(url, '_blank')
}

const confirmDelete = (syllabus) => {
  syllabusToDelete.value = syllabus
  deleteDialog.value = true
}

const deleteSyllabus = async () => {
  if (!syllabusToDelete.value) return

  deleting.value = true
  try {
    await router.delete(`/teacher/syllabuses/${syllabusToDelete.value.id}`)
    deleteDialog.value = false
    syllabusToDelete.value = null
  } catch (error) {
    console.error('Ошибка при удалении силлабуса:', error)
  } finally {
    deleting.value = false
  }
}
</script>

<style scoped>
.max-width-300 {
  max-width: 300px;
}
</style>
