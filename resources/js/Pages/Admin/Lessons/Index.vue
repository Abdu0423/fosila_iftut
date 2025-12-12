<template>
  <Layout role="admin">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Управление уроками</h1>
              <p class="text-body-1 text-medium-emphasis">Создание и управление учебными материалами</p>
            </div>
            <v-btn
              color="primary"
              size="large"
              prepend-icon="mdi-plus"
              @click="router.visit('/admin/lessons/create')"
            >
              Создать урок
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Фильтры и поиск -->
      <v-row>
        <v-col cols="12">
          <v-card class="mb-6">
            <v-card-text>
              <v-row>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="search"
                    label="Поиск урока"
                    prepend-inner-icon="mdi-magnify"
                    variant="outlined"
                    density="compact"
                    clearable
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="4">
                  <v-select
                    v-model="selectedSubject"
                    :items="subjects"
                    item-title="name"
                    item-value="id"
                    label="Предмет"
                    variant="outlined"
                    density="compact"
                    clearable
                  ></v-select>
                </v-col>
                <v-col cols="12" md="4">
                  <v-btn
                    color="primary"
                    variant="outlined"
                    block
                    @click="applyFilters"
                  >
                    Применить фильтры
                  </v-btn>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Таблица уроков -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-data-table
              :headers="headers"
              :items="lessonsData"
              :loading="loading"
              :search="search"
              class="elevation-1"
            >
              <!-- Название урока -->
              <template v-slot:item.title="{ item }">
                <div class="d-flex align-center">
                  <v-icon color="primary" class="mr-2">mdi-book</v-icon>
                  <div>
                    <div class="font-weight-medium">{{ item.title }}</div>
                    <div class="text-caption text-medium-emphasis" v-if="item.description">
                      {{ truncateText(item.description, 50) }}
                    </div>
                  </div>
                </div>
              </template>

              <!-- Предмет -->
              <template v-slot:item.subject="{ item }">
                <v-chip
                  v-if="item.subject"
                  color="primary"
                  variant="tonal"
                  size="small"
                >
                  {{ item.subject.name }}
                </v-chip>
                <span v-else class="text-medium-emphasis">Не указан</span>
              </template>

              <!-- Файл -->
              <template v-slot:item.file="{ item }">
                <div v-if="item.file_name" class="d-flex align-center">
                  <v-icon color="success" class="mr-2">mdi-file-check</v-icon>
                  <div>
                    <div class="text-body-2">{{ item.file_name }}</div>
                    <div class="text-caption text-medium-emphasis">
                      {{ item.file_size_formatted }}
                    </div>
                  </div>
                </div>
                <div v-else class="d-flex align-center">
                  <v-icon color="warning" class="mr-2">mdi-file-remove</v-icon>
                  <span class="text-medium-emphasis">Файл не загружен</span>
                </div>
              </template>

              <!-- Дата создания -->
              <template v-slot:item.created_at="{ item }">
                <div class="text-body-2">
                  {{ formatDate(item.created_at) }}
                </div>
              </template>

              <!-- Действия -->
              <template v-slot:item.actions="{ item }">
                <div class="d-flex gap-2">
                  <v-btn
                    icon="mdi-eye"
                    variant="text"
                    size="small"
                    color="info"
                    @click="viewLesson(item)"
                  ></v-btn>
                  <v-btn
                    icon="mdi-pencil"
                    variant="text"
                    size="small"
                    color="primary"
                    @click="editLesson(item)"
                  ></v-btn>
                  <v-btn
                    icon="mdi-download"
                    variant="text"
                    size="small"
                    color="success"
                    v-if="item.download_url"
                    :href="item.download_url"
                    target="_blank"
                  ></v-btn>
                  <v-btn
                    icon="mdi-delete"
                    variant="text"
                    size="small"
                    color="error"
                    @click="deleteLesson(item)"
                  ></v-btn>
                </div>
              </template>

              <!-- Пустое состояние -->
              <template v-slot:no-data>
                <div class="text-center py-8">
                  <v-icon size="64" color="grey-lighten-2">mdi-book-outline</v-icon>
                  <div class="text-h6 mt-4 text-medium-emphasis">Уроки не найдены</div>
                  <div class="text-body-2 text-medium-emphasis mb-4">
                    Создайте первый урок для начала работы
                  </div>
                  <v-btn
                    color="primary"
                    @click="router.visit('/admin/lessons/create')"
                  >
                    Создать урок
                  </v-btn>
                </div>
              </template>
            </v-data-table>
          </v-card>
        </v-col>
      </v-row>

      <!-- Диалог удаления -->
      <v-dialog v-model="deleteDialog" max-width="400">
        <v-card>
          <v-card-title class="text-h6">
            <v-icon color="error" class="mr-2">mdi-delete</v-icon>
            Удаление урока
          </v-card-title>
          <v-card-text>
            Вы уверены, что хотите удалить урок "{{ selectedLesson?.title }}"? Это действие нельзя отменить.
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="secondary"
              @click="deleteDialog = false"
            >
              Отмена
            </v-btn>
            <v-btn
              color="error"
              @click="confirmDelete"
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

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

// Props из Inertia
const props = defineProps({
  lessons: {
    type: [Object, Array],
    default: () => ({ data: [] })
  },
  subjects: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

// Состояние
const search = ref(props.filters.search)
const selectedSubject = ref(props.filters.subject_id || null)
const loading = ref(false)
const deleteDialog = ref(false)
const selectedLesson = ref(null)
const deleting = ref(false)

// Computed для безопасного получения данных уроков
const lessonsData = computed(() => {
  if (Array.isArray(props.lessons)) {
    return props.lessons
  }
  if (props.lessons && props.lessons.data) {
    return props.lessons.data
  }
  return []
})

// Заголовки таблицы
const headers = [
  { title: 'Название урока', key: 'title', sortable: true },
  { title: 'Предмет', key: 'subject', sortable: false },
  { title: 'Файл', key: 'file', sortable: false },
  { title: 'Дата создания', key: 'created_at', sortable: true },
  { title: 'Действия', key: 'actions', sortable: false, align: 'center' }
]

// Методы
const truncateText = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

const formatDate = (date) => {
  if (!date) return 'Не указано'
  return new Date(date).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const applyFilters = () => {
  loading.value = true
  router.get('/admin/lessons', {
    search: search.value,
    subject_id: selectedSubject.value
  }, {
    preserveState: true,
    onFinish: () => {
      loading.value = false
    }
  })
}

const viewLesson = (lesson) => {
  router.visit(`/admin/lessons/${lesson.id}`)
}

const editLesson = (lesson) => {
  router.visit(`/admin/lessons/${lesson.id}/edit`)
}

const deleteLesson = (lesson) => {
  selectedLesson.value = lesson
  deleteDialog.value = true
}

const confirmDelete = () => {
  if (!selectedLesson.value) return
  
  deleting.value = true
  
  // Используем fetch для отправки DELETE запроса
  fetch(`/admin/lessons/${selectedLesson.value.id}`, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Удаляем урок из локального списка
      const lessonIndex = lessonsData.value.findIndex(lesson => lesson.id === selectedLesson.value.id)
      if (lessonIndex > -1) {
        lessonsData.value.splice(lessonIndex, 1)
      }
      
      // Закрываем диалог
      deleteDialog.value = false
      selectedLesson.value = null
      
      // Показываем уведомление об успехе
      console.log('Урок успешно удален:', data.message)
    } else {
      console.error('Ошибка при удалении урока:', data.message)
    }
  })
  .catch(error => {
    console.error('Ошибка при удалении урока:', error)
  })
  .finally(() => {
    deleting.value = false
  })
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>