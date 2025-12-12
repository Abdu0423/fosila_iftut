<template>
  <AdminApp>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Управление оценками</h1>
              <p class="text-body-1 text-medium-emphasis">Просмотр и управление оценками студентов</p>
            </div>
            <div class="d-flex gap-3">
              <v-btn
                variant="outlined"
                prepend-icon="mdi-export"
                @click="exportGrades"
              >
                Экспорт оценок
              </v-btn>
              <v-btn
                color="primary"
                prepend-icon="mdi-plus"
                @click="bulkGradeEntry"
              >
                Массовое оценивание
              </v-btn>
            </div>
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
                    label="Поиск студента"
                    prepend-inner-icon="mdi-magnify"
                    variant="outlined"
                    density="compact"
                    clearable
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="2">
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
                <v-col cols="12" md="2">
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
                    v-model="selectedGrade"
                    :items="gradeRanges"
                    label="Диапазон оценок"
                    variant="outlined"
                    density="compact"
                    clearable
                  ></v-select>
                </v-col>
                <v-col cols="12" md="2">
                  <v-select
                    v-model="selectedPeriod"
                    :items="periods"
                    label="Период"
                    variant="outlined"
                    density="compact"
                    clearable
                  ></v-select>
                </v-col>
                <v-col cols="12" md="1">
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

      <!-- Статистика -->
      <v-row class="mb-6">
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="primary" class="mb-4">mdi-account-group</v-icon>
              <div class="text-h4 font-weight-bold">{{ totalStudents }}</div>
              <div class="text-body-2 text-medium-emphasis">Всего студентов</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="success" class="mb-4">mdi-star</v-icon>
              <div class="text-h4 font-weight-bold">{{ averageGrade }}</div>
              <div class="text-body-2 text-medium-emphasis">Средний балл</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="warning" class="mb-4">mdi-alert-circle</v-icon>
              <div class="text-h4 font-weight-bold">{{ failingStudents }}</div>
              <div class="text-body-2 text-medium-emphasis">Неуспевающих</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="info" class="mb-4">mdi-chart-line</v-icon>
              <div class="text-h4 font-weight-bold">{{ totalGrades }}</div>
              <div class="text-body-2 text-medium-emphasis">Всего оценок</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Таблица оценок -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-data-table
              :headers="headers"
              :items="filteredGrades"
              :search="search"
              :loading="loading"
              class="elevation-1"
            >
              <!-- Студент -->
              <template v-slot:item.student="{ item }">
                <div class="d-flex align-center">
                  <v-avatar size="32" class="mr-3">
                    <v-img :src="item.student.avatar" :alt="item.student.name">
                      <template v-slot:placeholder>
                        <v-icon>mdi-account</v-icon>
                      </template>
                    </v-img>
                  </v-avatar>
                  <div>
                    <div class="font-weight-medium">{{ item.student.name }}</div>
                    <div class="text-caption text-medium-emphasis">{{ item.student.email }}</div>
                  </div>
                </div>
              </template>

              <!-- Курс и урок -->
              <template v-slot:item.course="{ item }">
                <div>
                  <div class="font-weight-medium">{{ item.course.name }}</div>
                  <div class="text-caption text-medium-emphasis">{{ item.lesson.title }}</div>
                </div>
              </template>

              <!-- Оценка -->
              <template v-slot:item.grade="{ item }">
                <v-chip
                  :color="getGradeColor(item.grade)"
                  size="small"
                  variant="tonal"
                >
                  {{ item.grade }}
                </v-chip>
              </template>

              <!-- Максимальный балл -->
              <template v-slot:item.max_grade="{ item }">
                <div class="text-body-2">{{ item.max_grade }}</div>
              </template>

              <!-- Процент -->
              <template v-slot:item.percentage="{ item }">
                <div class="d-flex align-center">
                  <v-progress-linear
                    :model-value="item.percentage"
                    :color="getGradeColor(item.grade)"
                    height="8"
                    rounded
                    class="mr-2"
                  ></v-progress-linear>
                  <span class="text-body-2">{{ item.percentage }}%</span>
                </div>
              </template>

              <!-- Статус -->
              <template v-slot:item.status="{ item }">
                <v-chip
                  :color="getStatusColor(item.status)"
                  size="small"
                  variant="tonal"
                >
                  {{ getStatusText(item.status) }}
                </v-chip>
              </template>

              <!-- Дата -->
              <template v-slot:item.graded_at="{ item }">
                {{ formatDate(item.graded_at) }}
              </template>

              <!-- Действия -->
              <template v-slot:item.actions="{ item }">
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
                    <v-list-item
                      @click="viewDetails(item)"
                      prepend-icon="mdi-eye"
                    >
                      <v-list-item-title>Детали</v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      @click="editGrade(item)"
                      prepend-icon="mdi-pencil"
                    >
                      <v-list-item-title>Изменить оценку</v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      @click="viewStudent(item)"
                      prepend-icon="mdi-account"
                    >
                      <v-list-item-title>Профиль студента</v-list-item-title>
                    </v-list-item>
                    <v-divider></v-divider>
                    <v-list-item
                      @click="addComment(item)"
                      prepend-icon="mdi-comment"
                    >
                      <v-list-item-title>Добавить комментарий</v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      @click="deleteGrade(item)"
                      prepend-icon="mdi-delete"
                      color="error"
                    >
                      <v-list-item-title>Удалить оценку</v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </template>
            </v-data-table>
          </v-card>
        </v-col>
      </v-row>

      <!-- Графики и аналитика -->
      <v-row class="mt-6">
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-chart-bar</v-icon>
              Распределение оценок
            </v-card-title>
            <v-card-text>
              <div class="d-flex justify-space-between align-center mb-4">
                <div class="text-h6">Отлично (5)</div>
                <div class="text-h6 font-weight-bold text-success">{{ excellentCount }}</div>
              </div>
              <div class="d-flex justify-space-between align-center mb-4">
                <div class="text-h6">Хорошо (4)</div>
                <div class="text-h6 font-weight-bold text-info">{{ goodCount }}</div>
              </div>
              <div class="d-flex justify-space-between align-center mb-4">
                <div class="text-h6">Удовлетворительно (3)</div>
                <div class="text-h6 font-weight-bold text-warning">{{ satisfactoryCount }}</div>
              </div>
              <div class="d-flex justify-space-between align-center">
                <div class="text-h6">Неудовлетворительно (2)</div>
                <div class="text-h6 font-weight-bold text-error">{{ unsatisfactoryCount }}</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-trending-up</v-icon>
              Динамика успеваемости
            </v-card-title>
            <v-card-text>
              <div class="d-flex justify-space-between align-center mb-4">
                <div class="text-body-1">Средний балл по курсам:</div>
              </div>
              <div v-for="course in courseAverages" :key="course.id" class="mb-3">
                <div class="d-flex justify-space-between align-center">
                  <span class="text-body-2">{{ course.name }}</span>
                  <v-chip
                    :color="getGradeColor(course.average)"
                    size="small"
                    variant="tonal"
                  >
                    {{ course.average.toFixed(1) }}
                  </v-chip>
                </div>
                <v-progress-linear
                  :model-value="(course.average / 5) * 100"
                  :color="getGradeColor(course.average)"
                  height="4"
                  rounded
                  class="mt-1"
                ></v-progress-linear>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AdminApp>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminApp from '../AdminApp.vue'

// Состояние
const loading = ref(false)
const search = ref('')
const selectedCourse = ref(null)
const selectedLesson = ref(null)
const selectedGrade = ref(null)
const selectedPeriod = ref(null)

// Данные
const grades = ref([
  {
    id: 1,
    student: {
      id: 1,
      name: 'Иванов Иван',
      email: 'ivanov@example.com',
      avatar: 'https://ui-avatars.com/api/?name=Иван+Иванов'
    },
    course: { id: 1, name: 'Программирование' },
    lesson: { id: 1, title: 'Введение в Python' },
    grade: 5,
    max_grade: 5,
    percentage: 100,
    status: 'completed',
    graded_at: '2024-01-15'
  },
  {
    id: 2,
    student: {
      id: 2,
      name: 'Петрова Анна',
      email: 'petrova@example.com',
      avatar: 'https://ui-avatars.com/api/?name=Анна+Петрова'
    },
    course: { id: 1, name: 'Программирование' },
    lesson: { id: 1, title: 'Введение в Python' },
    grade: 4,
    max_grade: 5,
    percentage: 80,
    status: 'completed',
    graded_at: '2024-01-15'
  },
  {
    id: 3,
    student: {
      id: 3,
      name: 'Сидоров Петр',
      email: 'sidorov@example.com',
      avatar: 'https://ui-avatars.com/api/?name=Петр+Сидоров'
    },
    course: { id: 1, name: 'Программирование' },
    lesson: { id: 2, title: 'Переменные и типы данных' },
    grade: 3,
    max_grade: 5,
    percentage: 60,
    status: 'completed',
    graded_at: '2024-01-16'
  },
  {
    id: 4,
    student: {
      id: 4,
      name: 'Козлова Мария',
      email: 'kozlova@example.com',
      avatar: 'https://ui-avatars.com/api/?name=Мария+Козлова'
    },
    course: { id: 2, name: 'Веб-разработка' },
    lesson: { id: 5, title: 'Основы HTML' },
    grade: 5,
    max_grade: 5,
    percentage: 100,
    status: 'completed',
    graded_at: '2024-01-17'
  }
])

const courses = ref([
  { id: 1, name: 'Программирование' },
  { id: 2, name: 'Веб-разработка' },
  { id: 3, name: 'Базы данных' }
])

const lessons = ref([
  { id: 1, title: 'Введение в Python' },
  { id: 2, title: 'Переменные и типы данных' },
  { id: 3, title: 'Управляющие конструкции' },
  { id: 4, title: 'Функции и модули' },
  { id: 5, title: 'Основы HTML' }
])

const gradeRanges = ref([
  { value: '5', text: 'Отлично (5)' },
  { value: '4', text: 'Хорошо (4)' },
  { value: '3', text: 'Удовлетворительно (3)' },
  { value: '2', text: 'Неудовлетворительно (2)' }
])

const periods = ref([
  { value: 'current', text: 'Текущий семестр' },
  { value: 'previous', text: 'Предыдущий семестр' },
  { value: 'year', text: 'Текущий год' }
])

const headers = ref([
  { title: 'Студент', key: 'student', sortable: true },
  { title: 'Курс/Урок', key: 'course', sortable: true },
  { title: 'Оценка', key: 'grade', sortable: true },
  { title: 'Макс. балл', key: 'max_grade', sortable: true },
  { title: 'Процент', key: 'percentage', sortable: true },
  { title: 'Статус', key: 'status', sortable: true },
  { title: 'Дата', key: 'graded_at', sortable: true },
  { title: 'Действия', key: 'actions', sortable: false }
])

// Вычисляемые свойства
const filteredGrades = computed(() => {
  let filtered = grades.value

  if (selectedCourse.value) {
    filtered = filtered.filter(g => g.course.id === selectedCourse.value)
  }

  if (selectedLesson.value) {
    filtered = filtered.filter(g => g.lesson.id === selectedLesson.value)
  }

  if (selectedGrade.value) {
    filtered = filtered.filter(g => g.grade === parseInt(selectedGrade.value))
  }

  return filtered
})

const totalStudents = computed(() => {
  const uniqueStudents = new Set(grades.value.map(g => g.student.id))
  return uniqueStudents.size
})

const averageGrade = computed(() => {
  if (grades.value.length === 0) return 0
  const sum = grades.value.reduce((acc, g) => acc + g.grade, 0)
  return Math.round((sum / grades.value.length) * 10) / 10
})

const failingStudents = computed(() => {
  const failingGrades = grades.value.filter(g => g.grade < 3)
  const uniqueFailingStudents = new Set(failingGrades.map(g => g.student.id))
  return uniqueFailingStudents.size
})

const totalGrades = computed(() => grades.value.length)

const excellentCount = computed(() => 
  grades.value.filter(g => g.grade === 5).length
)

const goodCount = computed(() => 
  grades.value.filter(g => g.grade === 4).length
)

const satisfactoryCount = computed(() => 
  grades.value.filter(g => g.grade === 3).length
)

const unsatisfactoryCount = computed(() => 
  grades.value.filter(g => g.grade < 3).length
)

const courseAverages = computed(() => {
  const courseStats = {}
  
  grades.value.forEach(grade => {
    const courseId = grade.course.id
    if (!courseStats[courseId]) {
      courseStats[courseId] = {
        id: courseId,
        name: grade.course.name,
        grades: []
      }
    }
    courseStats[courseId].grades.push(grade.grade)
  })
  
  return Object.values(courseStats).map(course => ({
    id: course.id,
    name: course.name,
    average: course.grades.reduce((sum, grade) => sum + grade, 0) / course.grades.length
  }))
})

// Методы
const navigateTo = (route) => {
  router.visit(route)
}

const applyFilters = () => {
  console.log('Применение фильтров:', { 
    search: search.value, 
    course: selectedCourse.value, 
    lesson: selectedLesson.value,
    grade: selectedGrade.value,
    period: selectedPeriod.value
  })
}

const getGradeColor = (grade) => {
  if (grade >= 4.5) return 'success'
  if (grade >= 3.5) return 'info'
  if (grade >= 2.5) return 'warning'
  return 'error'
}

const getStatusColor = (status) => {
  const colors = {
    completed: 'success',
    pending: 'warning',
    overdue: 'error'
  }
  return colors[status]
}

const getStatusText = (status) => {
  const texts = {
    completed: 'Завершено',
    pending: 'В ожидании',
    overdue: 'Просрочено'
  }
  return texts[status] || status
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('ru-RU')
}

const viewDetails = (grade) => {
  console.log('Просмотр деталей оценки:', grade)
  // Здесь будет переход на детали
}

const editGrade = (grade) => {
  console.log('Редактирование оценки:', grade)
  // Здесь будет переход на редактирование
}

const viewStudent = (grade) => {
  console.log('Просмотр профиля студента:', grade.student)
  // Здесь будет переход на профиль студента
}

const addComment = (grade) => {
  console.log('Добавление комментария к оценке:', grade)
  // Здесь будет диалог добавления комментария
}

const deleteGrade = (grade) => {
  console.log('Удаление оценки:', grade)
  // Здесь будет подтверждение удаления
}

const exportGrades = () => {
  console.log('Экспорт оценок')
  // Здесь будет логика экспорта
}

const bulkGradeEntry = () => {
  console.log('Массовое оценивание')
  // Здесь будет переход на массовое оценивание
}

onMounted(() => {
  console.log('Страница управления оценками загружена')
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
