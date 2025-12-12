<template>
  <AdminApp>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ test.title }}</h1>
              <p class="text-body-1 text-medium-emphasis">{{ test.description }}</p>
            </div>
            <div class="d-flex gap-2">
              <v-btn
                color="secondary"
                variant="outlined"
                @click="navigateTo('/admin/tests')"
                prepend-icon="mdi-arrow-left"
              >
                Назад к списку
              </v-btn>
              <v-btn
                color="primary"
                @click="navigateTo(`/admin/tests/${test.id}/edit`)"
                prepend-icon="mdi-pencil"
              >
                Редактировать
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Основная информация -->
      <v-row>
        <v-col cols="12" md="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-information</v-icon>
              Основная информация
            </v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="6">
                  <div class="text-body-2 mb-4">
                    <strong>Название:</strong> {{ test.title }}
                  </div>
                  
                  <div class="text-body-2 mb-4">
                    <strong>Описание:</strong> {{ test.description }}
                  </div>
                  
                  <div class="text-body-2 mb-4">
                    <strong>Урок:</strong> {{ test.lesson_name }}
                  </div>
                  
                  <div class="text-body-2 mb-4">
                    <strong>Тип теста:</strong> 
                    <v-chip
                      :color="getTypeColor(test.type)"
                      size="small"
                      variant="tonal"
                    >
                      {{ getTypeText(test.type) }}
                    </v-chip>
                  </div>
                </v-col>
                
                <v-col cols="12" md="6">
                  <div class="text-body-2 mb-4">
                    <strong>Статус:</strong> 
                    <v-chip
                      :color="getStatusColor(test.status)"
                      size="small"
                      variant="tonal"
                    >
                      {{ test.status }}
                    </v-chip>
                  </div>
                  
                  <div class="text-body-2 mb-4">
                    <strong>Время выполнения:</strong> {{ test.time_limit ? `${test.time_limit} минут` : 'Не ограничено' }}
                  </div>
                  
                  <div class="text-body-2 mb-4">
                    <strong>Проходной балл:</strong> {{ test.passing_score }}%
                  </div>
                  
                  <div class="text-body-2 mb-4">
                    <strong>Максимум попыток:</strong> {{ test.max_attempts }}
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>

          <!-- Даты и настройки -->
          <v-card class="mt-4">
            <v-card-title class="text-h6">
              <v-icon start>mdi-calendar</v-icon>
              Даты и настройки
            </v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="6">
                  <div class="text-body-2 mb-4">
                    <strong>Дата начала:</strong> {{ test.start_date }}
                  </div>
                  
                  <div class="text-body-2 mb-4">
                    <strong>Дата окончания:</strong> {{ test.end_date }}
                  </div>
                </v-col>
                
                <v-col cols="12" md="6">
                  <div class="text-body-2 mb-4">
                    <strong>Активный:</strong> 
                    <v-chip
                      :color="test.is_active ? 'success' : 'error'"
                      size="small"
                      variant="tonal"
                    >
                      {{ test.is_active ? 'Да' : 'Нет' }}
                    </v-chip>
                  </div>
                  
                  <div class="text-body-2 mb-4">
                    <strong>Публичный:</strong> 
                    <v-chip
                      :color="test.is_public ? 'success' : 'warning'"
                      size="small"
                      variant="tonal"
                    >
                      {{ test.is_public ? 'Да' : 'Нет' }}
                    </v-chip>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>

          <!-- Статистика -->
          <v-card class="mt-4">
            <v-card-title class="text-h6">
              <v-icon start>mdi-chart-bar</v-icon>
              Статистика
            </v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="3">
                  <div class="text-center">
                    <div class="text-h4 font-weight-bold text-primary">{{ test.questions_count || 0 }}</div>
                    <div class="text-body-2 text-medium-emphasis">Вопросов</div>
                  </div>
                </v-col>
                <v-col cols="12" md="3">
                  <div class="text-center">
                    <div class="text-h4 font-weight-bold text-success">{{ test.attempts_count || 0 }}</div>
                    <div class="text-body-2 text-medium-emphasis">Попыток</div>
                  </div>
                </v-col>
                <v-col cols="12" md="3">
                  <div class="text-center">
                    <div class="text-h4 font-weight-bold text-info">{{ test.avg_score || 0 }}%</div>
                    <div class="text-body-2 text-medium-emphasis">Средний балл</div>
                  </div>
                </v-col>
                <v-col cols="12" md="3">
                  <div class="text-center">
                    <div class="text-h4 font-weight-bold text-warning">{{ test.pass_rate || 0 }}%</div>
                    <div class="text-body-2 text-medium-emphasis">Процент сдачи</div>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Боковая панель -->
        <v-col cols="12" md="4">
          <!-- Информация о создателе -->
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-account</v-icon>
              Создатель
            </v-card-title>
            <v-card-text>
              <div class="text-body-2 mb-4">
                <strong>Имя:</strong> {{ test.creator_name }}
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Создан:</strong> {{ formatDate(test.created_at) }}
              </div>
              
              <div class="text-body-2">
                <strong>Обновлен:</strong> {{ formatDate(test.updated_at) }}
              </div>
            </v-card-text>
          </v-card>

          <!-- Быстрые действия -->
          <v-card class="mt-4">
            <v-card-title class="text-h6">
              <v-icon start>mdi-lightning-bolt</v-icon>
              Быстрые действия
            </v-card-title>
            <v-card-text>
              <v-btn
                color="primary"
                variant="outlined"
                block
                class="mb-2"
                @click="navigateTo(`/admin/tests/${test.id}/questions`)"
                prepend-icon="mdi-help-circle"
              >
                Управление вопросами
              </v-btn>
              
              <v-btn
                color="info"
                variant="outlined"
                block
                class="mb-2"
                @click="previewTest"
                prepend-icon="mdi-play"
              >
                Предварительный просмотр
              </v-btn>
              
              <v-btn
                color="warning"
                variant="outlined"
                block
                class="mb-2"
                @click="duplicateTest"
                prepend-icon="mdi-content-copy"
              >
                Дублировать тест
              </v-btn>
              
              <v-btn
                color="success"
                variant="outlined"
                block
                class="mb-2"
                @click="viewResults"
                prepend-icon="mdi-chart-bar"
              >
                Результаты
              </v-btn>
              
              <v-btn
                color="error"
                variant="outlined"
                block
                @click="deleteTest"
                prepend-icon="mdi-delete"
              >
                Удалить тест
              </v-btn>
            </v-card-text>
          </v-card>

          <!-- Последние результаты -->
          <v-card class="mt-4">
            <v-card-title class="text-h6">
              <v-icon start>mdi-clock</v-icon>
              Последние результаты
            </v-card-title>
            <v-card-text>
              <div v-if="recentResults.length === 0" class="text-center text-medium-emphasis">
                Нет результатов
              </div>
              <div v-else>
                <div v-for="result in recentResults" :key="result.id" class="mb-3">
                  <div class="d-flex justify-space-between align-center">
                    <div>
                      <div class="text-body-2 font-weight-medium">{{ result.student_name }}</div>
                      <div class="text-caption text-medium-emphasis">{{ formatDate(result.completed_at) }}</div>
                    </div>
                    <v-chip
                      :color="result.score >= test.passing_score ? 'success' : 'error'"
                      size="small"
                    >
                      {{ result.score }}%
                    </v-chip>
                  </div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AdminApp>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminApp from '../AdminApp.vue'

// Props из Inertia
const props = defineProps({
  test: {
    type: Object,
    required: true
  },
  testTypes: {
    type: Array,
    default: () => []
  }
})

// Данные
const test = ref(props.test)
const testTypes = ref(props.testTypes)

// Моковые данные для последних результатов
const recentResults = ref([
  {
    id: 1,
    student_name: 'Иванов Иван',
    score: 85,
    completed_at: '2024-01-15 14:30:00'
  },
  {
    id: 2,
    student_name: 'Петров Петр',
    score: 72,
    completed_at: '2024-01-15 13:45:00'
  },
  {
    id: 3,
    student_name: 'Сидоров Сидор',
    score: 95,
    completed_at: '2024-01-15 12:20:00'
  }
])

// Форма для удаления
const form = useForm({})

// Методы
const navigateTo = (route) => {
  window.location.href = route
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('ru-RU')
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

const previewTest = () => {
  navigateTo(`/admin/tests/${test.value.id}/preview`)
}

const duplicateTest = () => {
  console.log('Дублирование теста:', test.value)
  // Здесь будет логика дублирования
}

const viewResults = () => {
  console.log('Просмотр результатов теста:', test.value)
  // Здесь будет переход на результаты
}

const deleteTest = () => {
  if (confirm('Вы уверены, что хотите удалить этот тест?')) {
    form.delete(`/admin/tests/${test.value.id}`)
  }
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>

