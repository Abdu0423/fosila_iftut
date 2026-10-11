<template>
  <AdminApp>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Управление тестами</h1>
              <p class="text-body-1 text-medium-emphasis">Создание и управление тестами для уроков</p>
            </div>
            <v-btn
              color="primary"
              size="large"
              prepend-icon="mdi-plus"
              @click="navigateTo('/admin/tests/create')"
            >
              Создать тест
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
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="search"
                    label="Поиск теста"
                    prepend-inner-icon="mdi-magnify"
                    variant="outlined"
                    density="compact"
                    clearable
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="3">
                  <v-select
                    v-model="selectedLesson"
                    :items="lessons"
                    item-title="title"
                    item-value="id"
                    label="Урок"
                    variant="outlined"
                    density="compact"
                    clearable
                  ></v-select>
                </v-col>
                <v-col cols="12" md="2">
                  <v-select
                    v-model="selectedType"
                    :items="testTypes"
                    label="Тип теста"
                    variant="outlined"
                    density="compact"
                    clearable
                  ></v-select>
                </v-col>
                <v-col cols="12" md="2">
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

      <!-- Таблица тестов -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <ExcelSheet :headers="headers" :rows="filteredTests">
              <!-- Название и описание -->
              <template #cell-title="{ row }">
                <div>
                  <div class="font-weight-medium">{{ row.title }}</div>
                  <div class="text-caption text-medium-emphasis">{{ row.description }}</div>
                  <div class="text-caption text-medium-emphasis">{{ row.lesson_name }}</div>
                </div>
              </template>

              <!-- Тип теста -->
              <template #cell-type="{ row }">
                <v-chip
                  :color="getTypeColor(row.type)"
                  size="small"
                  variant="tonal"
                >
                  {{ getTypeText(row.type) }}
                </v-chip>
              </template>

              <!-- Количество вопросов -->
              <template #cell-questions_count="{ row }">
                <v-chip
                  color="info"
                  size="small"
                  variant="tonal"
                >
                  {{ row.questions_count }} вопросов
                </v-chip>
              </template>

              <!-- Время выполнения -->
              <template #cell-time_limit="{ row }">
                <div class="d-flex align-center">
                  <v-icon size="16" class="mr-1">mdi-clock-outline</v-icon>
                  {{ row.time_limit }} мин
                </div>
              </template>

              <!-- Проходной балл -->
              <template #cell-passing_score="{ row }">
                <div class="d-flex align-center">
                  <v-icon size="16" class="mr-1">mdi-percent</v-icon>
                  {{ row.passing_score }}%
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
                      @click="viewTest(row)"
                      prepend-icon="mdi-eye"
                    >
                      <v-list-row-title>Просмотр</v-list-row-title>
                    </v-list-row>
                    <v-list-row
                      @click="editTest(row)"
                      prepend-icon="mdi-pencil"
                    >
                      <v-list-row-title>Редактировать</v-list-row-title>
                    </v-list-row>
                    <v-list-row
                      @click="manageQuestions(row)"
                      prepend-icon="mdi-help-circle"
                    >
                      <v-list-row-title>Вопросы</v-list-row-title>
                    </v-list-row>
                    <v-list-row
                      @click="previewTest(row)"
                      prepend-icon="mdi-play"
                    >
                      <v-list-row-title>Предварительный просмотр</v-list-row-title>
                    </v-list-row>
                    <v-divider></v-divider>
                    <v-list-row
                      @click="viewResults(row)"
                      prepend-icon="mdi-chart-bar"
                    >
                      <v-list-row-title>Результаты</v-list-row-title>
                    </v-list-row>
                    <v-list-row
                      @click="duplicateTest(row)"
                      prepend-icon="mdi-content-copy"
                    >
                      <v-list-row-title>Дублировать</v-list-row-title>
                    </v-list-row>
                    <v-list-row
                      @click="deleteTest(row)"
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
              <v-icon size="48" color="primary" class="mb-4">mdi-help-circle</v-icon>
              <div class="text-h4 font-weight-bold">{{ tests.length }}</div>
              <div class="text-body-2 text-medium-emphasis">Всего тестов</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="success" class="mb-4">mdi-check-circle</v-icon>
              <div class="text-h4 font-weight-bold">{{ activeTests }}</div>
              <div class="text-body-2 text-medium-emphasis">Активных</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="info" class="mb-4">mdi-help</v-icon>
              <div class="text-h4 font-weight-bold">{{ totalQuestions }}</div>
              <div class="text-body-2 text-medium-emphasis">Вопросов</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="warning" class="mb-4">mdi-account-group</v-icon>
              <div class="text-h4 font-weight-bold">{{ totalAttempts }}</div>
              <div class="text-body-2 text-medium-emphasis">Попыток</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Быстрые действия -->
      <v-row class="mt-6">
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-lightning-bolt</v-icon>
              Быстрые действия
            </v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="3">
                  <v-btn
                    variant="outlined"
                    block
                    prepend-icon="mdi-import"
                    @click="importTests"
                  >
                    Импорт тестов
                  </v-btn>
                </v-col>
                <v-col cols="12" md="3">
                  <v-btn
                    variant="outlined"
                    block
                    prepend-icon="mdi-export"
                    @click="exportTests"
                  >
                    Экспорт тестов
                  </v-btn>
                </v-col>
                <v-col cols="12" md="3">
                  <v-btn
                    variant="outlined"
                    block
                    prepend-icon="mdi-chart-line"
                    @click="viewAnalytics"
                  >
                    Аналитика результатов
                  </v-btn>
                </v-col>
                <v-col cols="12" md="3">
                  <v-btn
                    variant="outlined"
                    block
                    prepend-icon="mdi-settings"
                    @click="testSettings"
                  >
                    Настройки тестирования
                  </v-btn>
                </v-col>
              </v-row>
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

// Props из Inertia
const props = defineProps({
  tests: {
    type: Array,
    default: () => []
  },
  lessons: {
    type: Array,
    default: () => []
  },
  testTypes: {
    type: Array,
    default: () => []
  },
  statuses: {
    type: Array,
    default: () => []
  }
})

// Состояние
const loading = ref(false)
const search = ref('')
const selectedLesson = ref(null)
const selectedType = ref(null)
const selectedStatus = ref(null)

// Данные из props
const tests = ref(props.tests)
const lessons = ref(props.lessons)
const testTypes = ref(props.testTypes)
const statuses = ref(props.statuses)

const headers = ref([
  { title: 'Название', key: 'title', sortable: true },
  { title: 'Тип', key: 'type', sortable: true },
  { title: 'Вопросов', key: 'questions_count', sortable: true },
  { title: 'Время', key: 'time_limit', sortable: true },
  { title: 'Проходной балл', key: 'passing_score', sortable: true },
  { title: 'Статус', key: 'status', sortable: true },
  { title: 'Дата создания', key: 'created_at', sortable: true },
  { title: 'Действия', key: 'actions', sortable: false }
])

// Вычисляемые свойства
const filteredTests = computed(() => {
  let filtered = tests.value

  if (selectedLesson.value) {
    filtered = filtered.filter(t => t.lesson_id === selectedLesson.value)
  }

  if (selectedType.value) {
    filtered = filtered.filter(t => t.type === selectedType.value)
  }

  if (selectedStatus.value) {
    filtered = filtered.filter(t => t.status === selectedStatus.value)
  }

  return filtered
})

const activeTests = computed(() => 
  tests.value.filter(t => t.status === 'Активен').length
)

const totalQuestions = computed(() => 
  tests.value.reduce((sum, t) => sum + (t.questions_count || 0), 0)
)

const totalAttempts = computed(() => 
  tests.value.reduce((sum, t) => sum + (t.attempts_count || 0), 0)
)

// Методы
const navigateTo = (route) => {
  router.visit(route)
}

const applyFilters = () => {
  console.log('Применение фильтров:', { 
    search: search.value, 
    lesson: selectedLesson.value, 
    type: selectedType.value, 
    status: selectedStatus.value 
  })
}

const getTypeColor = (type) => {
  const colors = {
    quiz: 'primary',
    exam: 'error',
    homework: 'success',
    practice: 'info'
  }
  return colors[type]
}

const getTypeText = (type) => {
  const typeObj = testTypes.value.find(t => t.value === type)
  return typeObj ? typeObj.text : type
}

const getStatusColor = (status) => {
  const colors = {
    'Активен': 'success',
    'Неактивен': 'warning',
    'Ожидает': 'info',
    'Завершен': 'grey'
  }
  return colors[status]
}

const getStatusText = (status) => {
  return status
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('ru-RU')
}

const viewTest = (test) => {
  router.visit(`/admin/tests/${test.id}`)
}

const editTest = (test) => {
  router.visit(`/admin/tests/${test.id}/edit`)
}

const manageQuestions = (test) => {
  router.visit(`/admin/tests/${test.id}/questions`)
}

const previewTest = (test) => {
  console.log('Предварительный просмотр теста:', test)
  // Здесь будет переход на предварительный просмотр
}

const viewResults = (test) => {
  console.log('Просмотр результатов теста:', test)
  // Здесь будет переход на результаты
}

const duplicateTest = (test) => {
  console.log('Дублирование теста:', test)
  // Здесь будет логика дублирования
}

const deleteTest = (test) => {
  if (confirm('Вы уверены, что хотите удалить этот тест?')) {
    router.delete(`/admin/tests/${test.id}`)
  }
}

const importTests = () => {
  console.log('Импорт тестов')
  // Здесь будет логика импорта
}

const exportTests = () => {
  console.log('Экспорт тестов')
  // Здесь будет логика экспорта
}

const viewAnalytics = () => {
  console.log('Аналитика результатов')
  // Здесь будет переход на аналитику
}

const testSettings = () => {
  console.log('Настройки тестирования')
  // Здесь будет переход на настройки
}

onMounted(() => {
  console.log('Страница управления тестами загружена')
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
