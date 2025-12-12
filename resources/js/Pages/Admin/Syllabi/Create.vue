<template>
  <AdminApp>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex align-center mb-6">
            <v-btn
              icon="mdi-arrow-left"
              variant="text"
              @click="goBack"
              class="mr-4"
            ></v-btn>
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Создание силлабуса</h1>
              <p class="text-body-1 text-medium-emphasis">Создайте новый учебный план для курса</p>
            </div>
          </div>
        </v-col>
      </v-row>

      <v-row>
        <v-col cols="12" lg="8">
          <!-- Основная форма -->
          <v-card class="mb-6">
            <v-card-title class="text-h6">
              <v-icon start>mdi-file-document-edit</v-icon>
              Основная информация
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="saveSyllabus">
                <v-row>
                  <v-col cols="12" md="8">
                    <v-text-field
                      v-model="form.title"
                      label="Название силлабуса"
                      variant="outlined"
                      :error-messages="form.errors.title"
                      required
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-select
                      v-model="form.course_id"
                      :items="courses"
                      item-title="name"
                      item-value="id"
                      label="Курс"
                      variant="outlined"
                      :error-messages="form.errors.course_id"
                      required
                    ></v-select>
                  </v-col>
                  <v-col cols="12">
                    <v-textarea
                      v-model="form.description"
                      label="Описание"
                      variant="outlined"
                      rows="4"
                      :error-messages="form.errors.description"
                      placeholder="Краткое описание содержания силлабуса..."
                    ></v-textarea>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.duration_weeks"
                      label="Продолжительность (недель)"
                      type="number"
                      variant="outlined"
                      :error-messages="form.errors.duration_weeks"
                      min="1"
                      max="52"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.lessons_per_week"
                      label="Уроков в неделю"
                      type="number"
                      variant="outlined"
                      :error-messages="form.errors.lessons_per_week"
                      min="1"
                      max="7"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.difficulty"
                      :items="difficultyLevels"
                      label="Уровень сложности"
                      variant="outlined"
                      :error-messages="form.errors.difficulty"
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.status"
                      :items="statuses"
                      label="Статус"
                      variant="outlined"
                      :error-messages="form.errors.status"
                    ></v-select>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>
          </v-card>

          <!-- Цели обучения -->
          <v-card class="mb-6">
            <v-card-title class="text-h6">
              <v-icon start>mdi-target</v-icon>
              Цели обучения
            </v-card-title>
            <v-card-text>
              <div v-for="(goal, index) in form.learning_goals" :key="index" class="mb-4">
                <v-row>
                  <v-col cols="11">
                    <v-text-field
                      v-model="form.learning_goals[index]"
                      :label="`Цель ${index + 1}`"
                      variant="outlined"
                      density="compact"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="1">
                    <v-btn
                      icon="mdi-delete"
                      variant="text"
                      color="error"
                      @click="removeGoal(index)"
                      :disabled="form.learning_goals.length <= 1"
                    ></v-btn>
                  </v-col>
                </v-row>
              </div>
              <v-btn
                variant="outlined"
                prepend-icon="mdi-plus"
                @click="addGoal"
              >
                Добавить цель
              </v-btn>
            </v-card-text>
          </v-card>

          <!-- Результаты обучения -->
          <v-card class="mb-6">
            <v-card-title class="text-h6">
              <v-icon start>mdi-check-circle</v-icon>
              Результаты обучения
            </v-card-title>
            <v-card-text>
              <div v-for="(outcome, index) in form.learning_outcomes" :key="index" class="mb-4">
                <v-row>
                  <v-col cols="11">
                    <v-text-field
                      v-model="form.learning_outcomes[index]"
                      :label="`Результат ${index + 1}`"
                      variant="outlined"
                      density="compact"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="1">
                    <v-btn
                      icon="mdi-delete"
                      variant="text"
                      color="error"
                      @click="removeOutcome(index)"
                      :disabled="form.learning_outcomes.length <= 1"
                    ></v-btn>
                  </v-col>
                </v-row>
              </div>
              <v-btn
                variant="outlined"
                prepend-icon="mdi-plus"
                @click="addOutcome"
              >
                Добавить результат
              </v-btn>
            </v-card-text>
          </v-card>

          <!-- Требования -->
          <v-card class="mb-6">
            <v-card-title class="text-h6">
              <v-icon start>mdi-format-list-checks</v-icon>
              Предварительные требования
            </v-card-title>
            <v-card-text>
              <div v-for="(requirement, index) in form.prerequisites" :key="index" class="mb-4">
                <v-row>
                  <v-col cols="11">
                    <v-text-field
                      v-model="form.prerequisites[index]"
                      :label="`Требование ${index + 1}`"
                      variant="outlined"
                      density="compact"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="1">
                    <v-btn
                      icon="mdi-delete"
                      variant="text"
                      color="error"
                      @click="removePrerequisite(index)"
                    ></v-btn>
                  </v-col>
                </v-row>
              </div>
              <v-btn
                variant="outlined"
                prepend-icon="mdi-plus"
                @click="addPrerequisite"
              >
                Добавить требование
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" lg="4">
          <!-- Предварительный просмотр -->
          <v-card class="mb-6 sticky-top">
            <v-card-title class="text-h6">
              <v-icon start>mdi-eye</v-icon>
              Предварительный просмотр
            </v-card-title>
            <v-card-text>
              <div v-if="form.title" class="mb-4">
                <h3 class="text-h6 font-weight-bold">{{ form.title }}</h3>
                <p class="text-body-2 text-medium-emphasis">{{ form.description }}</p>
              </div>
              
              <v-divider class="my-4"></v-divider>
              
              <div class="d-flex justify-space-between mb-2">
                <span class="text-body-2">Курс:</span>
                <span class="text-body-2 font-weight-medium">{{ getCourseName(form.course_id) }}</span>
              </div>
              
              <div class="d-flex justify-space-between mb-2">
                <span class="text-body-2">Продолжительность:</span>
                <span class="text-body-2 font-weight-medium">{{ form.duration_weeks }} недель</span>
              </div>
              
              <div class="d-flex justify-space-between mb-2">
                <span class="text-body-2">Уроков в неделю:</span>
                <span class="text-body-2 font-weight-medium">{{ form.lessons_per_week }}</span>
              </div>
              
              <div class="d-flex justify-space-between mb-2">
                <span class="text-body-2">Всего уроков:</span>
                <span class="text-body-2 font-weight-medium">{{ totalLessons }}</span>
              </div>
              
              <div class="d-flex justify-space-between mb-2">
                <span class="text-body-2">Сложность:</span>
                <v-chip
                  :color="getDifficultyColor(form.difficulty)"
                  size="small"
                  variant="tonal"
                >
                  {{ getDifficultyText(form.difficulty) }}
                </v-chip>
              </div>
              
              <div class="d-flex justify-space-between mb-2">
                <span class="text-body-2">Статус:</span>
                <v-chip
                  :color="getStatusColor(form.status)"
                  size="small"
                  variant="tonal"
                >
                  {{ getStatusText(form.status) }}
                </v-chip>
              </div>
            </v-card-text>
          </v-card>

          <!-- Действия -->
          <v-card>
            <v-card-text>
              <v-btn
                type="submit"
                color="primary"
                size="large"
                block
                :loading="form.processing"
                :disabled="form.processing"
                @click="saveSyllabus"
                class="mb-4"
              >
                <v-icon start>mdi-content-save</v-icon>
                {{ form.processing ? 'Сохранение...' : 'Сохранить силлабус' }}
              </v-btn>
              
              <v-btn
                variant="outlined"
                size="large"
                block
                @click="saveAsDraft"
                :loading="form.processing"
                :disabled="form.processing"
                class="mb-4"
              >
                <v-icon start>mdi-content-save-outline</v-icon>
                Сохранить как черновик
              </v-btn>
              
              <v-btn
                variant="text"
                size="large"
                block
                @click="goBack"
                :disabled="form.processing"
              >
                <v-icon start>mdi-close</v-icon>
                Отмена
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AdminApp>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AdminApp from '../AdminApp.vue'

// Форма
const form = useForm({
  title: '',
  course_id: null,
  description: '',
  duration_weeks: 12,
  lessons_per_week: 2,
  difficulty: 'beginner',
  status: 'draft',
  learning_goals: [''],
  learning_outcomes: [''],
  prerequisites: []
})

// Данные
const courses = ref([
  { id: 1, name: 'Программирование' },
  { id: 2, name: 'Веб-разработка' },
  { id: 3, name: 'Базы данных' },
  { id: 4, name: 'Фронтенд разработка' },
  { id: 5, name: 'Мобильная разработка' }
])

const difficultyLevels = ref([
  { value: 'beginner', text: 'Начинающий' },
  { value: 'intermediate', text: 'Средний' },
  { value: 'advanced', text: 'Продвинутый' }
])

const statuses = ref([
  { value: 'draft', text: 'Черновик' },
  { value: 'active', text: 'Активный' },
  { value: 'archived', text: 'Архивный' }
])

// Вычисляемые свойства
const totalLessons = computed(() => {
  return (form.duration_weeks || 0) * (form.lessons_per_week || 0)
})

// Методы
const goBack = () => {
  router.visit('/admin/syllabi')
}

const getCourseName = (courseId) => {
  const course = courses.value.find(c => c.id === courseId)
  return course ? course.name : 'Не выбран'
}

const getDifficultyColor = (difficulty) => {
  const colors = {
    beginner: 'success',
    intermediate: 'warning',
    advanced: 'error'
  }
  return colors[difficulty]
}

const getDifficultyText = (difficulty) => {
  const level = difficultyLevels.value.find(d => d.value === difficulty)
  return level ? level.text : difficulty
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
  const statusObj = statuses.value.find(s => s.value === status)
  return statusObj ? statusObj.text : status
}

const addGoal = () => {
  form.learning_goals.push('')
}

const removeGoal = (index) => {
  form.learning_goals.splice(index, 1)
}

const addOutcome = () => {
  form.learning_outcomes.push('')
}

const removeOutcome = (index) => {
  form.learning_outcomes.splice(index, 1)
}

const addPrerequisite = () => {
  form.prerequisites.push('')
}

const removePrerequisite = (index) => {
  form.prerequisites.splice(index, 1)
}

const saveSyllabus = () => {
  form.post('/admin/syllabi', {
    onSuccess: () => {
      console.log('Силлабус успешно создан')
    },
    onError: (errors) => {
      console.log('Ошибки при создании силлабуса:', errors)
    }
  })
}

const saveAsDraft = () => {
  form.status = 'draft'
  saveSyllabus()
}
</script>

<style scoped>
.sticky-top {
  position: sticky;
  top: 20px;
}
</style>
