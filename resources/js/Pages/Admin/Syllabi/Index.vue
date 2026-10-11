<template>
  <AdminApp>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Управление силлабусами</h1>
              <p class="text-body-1 text-medium-emphasis">Создание и управление учебными планами курсов</p>
            </div>
            <v-btn
              color="primary"
              size="large"
              prepend-icon="mdi-plus"
              @click="navigateTo('/admin/syllabi/create')"
            >
              Создать силлабус
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
                    label="Поиск силлабуса"
                    prepend-inner-icon="mdi-magnify"
                    variant="outlined"
                    density="compact"
                    clearable
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="3">
                  <v-select
                    v-model="selectedCourse"
                    :items="courses"
                    item-title="name"
                    item-value="id"
                    label="Курс"
                    variant="outlined"
                    density="compact"
                    clearable
                  ></v-select>
                </v-col>
                <v-col cols="12" md="3">
                  <v-select
                    v-model="selectedStatus"
                    :items="statuses"
                    label="Статус"
                    variant="outlined"
                    density="compact"
                    clearable
                  ></v-select>
                </v-col>
                <v-col cols="12" md="2">
                  <v-btn
                    color="primary"
                    variant="outlined"
                    block
                    @click="applyFilters"
                  >
                    Применить
                  </v-btn>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Таблица силлабусов -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <ExcelSheet :headers="headers" :rows="filteredSyllabi">
              <!-- Название -->
              <template #cell-title="{ row }">
                <div>
                  <div class="font-weight-medium">{{ row.title }}</div>
                  <div class="text-caption text-medium-emphasis">{{ row.course?.name }}</div>
                </div>
              </template>

              <!-- Статус -->
              <template #cell-status="{ row }">
                <v-chip
                  :color="getStatusColor(row.status)"
                  size="small"
                  variant="tonal"
                >
                  {{ getStatusText(row.status) }}
                </v-chip>
              </template>

              <!-- Количество уроков -->
              <template #cell-lessons_count="{ row }">
                <v-chip
                  color="info"
                  size="small"
                  variant="tonal"
                >
                  {{ row.lessons_count }} уроков
                </v-chip>
              </template>

              <!-- Дата создания -->
              <template #cell-created_at="{ row }">
                {{ formatDate(row.created_at) }}
              </template>

              <!-- Действия -->
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
                      @click="viewSyllabus(row)"
                      prepend-icon="mdi-eye"
                    >
                      <v-list-row-title>Просмотр</v-list-row-title>
                    </v-list-row>
                    <v-list-row
                      @click="editSyllabus(row)"
                      prepend-icon="mdi-pencil"
                    >
                      <v-list-row-title>Редактировать</v-list-row-title>
                    </v-list-row>
                    <v-list-row
                      @click="manageLessons(row)"
                      prepend-icon="mdi-teach"
                    >
                      <v-list-row-title>Уроки</v-list-row-title>
                    </v-list-row>
                    <v-list-row
                      @click="exportSyllabus(row)"
                      prepend-icon="mdi-download"
                    >
                      <v-list-row-title>Выгрузить</v-list-row-title>
                    </v-list-row>
                    <v-divider></v-divider>
                    <v-list-row
                      @click="duplicateSyllabus(row)"
                      prepend-icon="mdi-content-copy"
                    >
                      <v-list-row-title>Дублировать</v-list-row-title>
                    </v-list-row>
                    <v-list-row
                      @click="deleteSyllabus(row)"
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
        </v-col>
      </v-row>

      <!-- Статистика -->
      <v-row class="mt-6">
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="primary" class="mb-4">mdi-file-document-multiple</v-icon>
              <div class="text-h4 font-weight-bold">{{ syllabi.length }}</div>
              <div class="text-body-2 text-medium-emphasis">Всего силлабусов</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="success" class="mb-4">mdi-check-circle</v-icon>
              <div class="text-h4 font-weight-bold">{{ activeSyllabi }}</div>
              <div class="text-body-2 text-medium-emphasis">Активных</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="warning" class="mb-4">mdi-clock</v-icon>
              <div class="text-h4 font-weight-bold">{{ draftSyllabi }}</div>
              <div class="text-body-2 text-medium-emphasis">Черновиков</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="info" class="mb-4">mdi-teach</v-icon>
              <div class="text-h4 font-weight-bold">{{ totalLessons }}</div>
              <div class="text-body-2 text-medium-emphasis">Всего уроков</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AdminApp>
</template>

<script setup lang="ts">
import ExcelSheet from '@/Components/ExcelSheet.vue'
import { ref, computed, onMounted } from 'vue'
import { router } from '@/compat/inertia'
import AdminApp from '../AdminApp.vue'

// Состояние
const loading = ref(false)
const search = ref('')
const selectedCourse = ref(null)
const selectedStatus = ref(null)

// Данные
const syllabi = ref([
  {
    id: 1,
    title: 'Основы программирования на Python',
    course: { id: 1, name: 'Программирование' },
    status: 'active',
    lessons_count: 24,
    created_at: '2024-01-15',
    description: 'Базовый курс по программированию на Python для начинающих'
  },
  {
    id: 2,
    title: 'Веб-разработка с Laravel',
    course: { id: 2, name: 'Веб-разработка' },
    status: 'active',
    lessons_count: 32,
    created_at: '2024-01-20',
    description: 'Создание веб-приложений с использованием Laravel'
  },
  {
    id: 3,
    title: 'Базы данных SQL',
    course: { id: 3, name: 'Базы данных' },
    status: 'draft',
    lessons_count: 18,
    created_at: '2024-02-01',
    description: 'Изучение SQL и управления базами данных'
  },
  {
    id: 4,
    title: 'JavaScript для фронтенда',
    course: { id: 4, name: 'Фронтенд разработка' },
    status: 'active',
    lessons_count: 28,
    created_at: '2024-02-10',
    description: 'Современная разработка на JavaScript'
  }
])

const courses = ref([
  { id: 1, name: 'Программирование' },
  { id: 2, name: 'Веб-разработка' },
  { id: 3, name: 'Базы данных' },
  { id: 4, name: 'Фронтенд разработка' }
])

const statuses = ref([
  { value: 'active', text: 'Активный' },
  { value: 'draft', text: 'Черновик' },
  { value: 'archived', text: 'Архивный' }
])

const headers = ref([
  { title: 'Название', key: 'title', sortable: true },
  { title: 'Статус', key: 'status', sortable: true },
  { title: 'Уроков', key: 'lessons_count', sortable: true },
  { title: 'Дата создания', key: 'created_at', sortable: true },
  { title: 'Действия', key: 'actions', sortable: false }
])

// Вычисляемые свойства
const filteredSyllabi = computed(() => {
  let filtered = syllabi.value

  if (selectedCourse.value) {
    filtered = filtered.filter(s => s.course.id === selectedCourse.value)
  }

  if (selectedStatus.value) {
    filtered = filtered.filter(s => s.status === selectedStatus.value)
  }

  return filtered
})

const activeSyllabi = computed(() => 
  syllabi.value.filter(s => s.status === 'active').length
)

const draftSyllabi = computed(() => 
  syllabi.value.filter(s => s.status === 'draft').length
)

const totalLessons = computed(() => 
  syllabi.value.reduce((sum, s) => sum + s.lessons_count, 0)
)

// Методы
const navigateTo = (route) => {
  router.visit(route)
}

const applyFilters = () => {
  // Применение фильтров
  console.log('Применение фильтров:', { search: search.value, course: selectedCourse.value, status: selectedStatus.value })
}

const getStatusColor = (status) => {
  const colors = {
    active: 'success',
    draft: 'warning',
    archived: 'grey'
  }
  return colors[status]
}

const getStatusText = (status) => {
  const texts = {
    active: 'Активный',
    draft: 'Черновик',
    archived: 'Архивный'
  }
  return texts[status] || status
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('ru-RU')
}

const viewSyllabus = (syllabus) => {
  console.log('Просмотр силлабуса:', syllabus)
  // Здесь будет переход на страницу просмотра
}

const editSyllabus = (syllabus) => {
  navigateTo(`/admin/syllabi/${syllabus.id}/edit`)
}

const manageLessons = (syllabus) => {
  navigateTo(`/admin/syllabi/${syllabus.id}/lessons`)
}

const exportSyllabus = (syllabus) => {
  console.log('Выгрузка силлабуса:', syllabus)
  // Здесь будет логика выгрузки в PDF/Word
}

const duplicateSyllabus = (syllabus) => {
  console.log('Дублирование силлабуса:', syllabus)
  // Здесь будет логика дублирования
}

const deleteSyllabus = (syllabus) => {
  console.log('Удаление силлабуса:', syllabus)
  // Здесь будет подтверждение удаления
}

onMounted(() => {
  console.log('Страница управления силлабусами загружена')
})
</script>

<style scoped>
.v-data-table {
  border-radius: 8px;
}

.v-card {
  border-radius: 12px;
}
</style>
