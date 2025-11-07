<template>
  <Layout role="student">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ test.title }}</h1>
              <p class="text-body-1 text-medium-emphasis">
                {{ test.schedule.subject_name }} - {{ test.schedule.group_name }}
              </p>
            </div>
            <v-btn
              color="secondary"
              variant="outlined"
              @click="goBack"
              prepend-icon="mdi-arrow-left"
            >
              Назад
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Информация перед началом -->
      <v-row v-if="!testStarted && !showResults">
        <v-col cols="12" md="4">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-information</v-icon>
              Информация об экзамене
            </v-card-title>
            <v-card-text>
              <div class="text-body-2 mb-3">
                <strong>Описание:</strong> {{ test.description || 'Не указано' }}
              </div>
              <div class="text-body-2 mb-3">
                <strong>Время:</strong> {{ test.time_limit ? `${test.time_limit} минут` : 'Не ограничено' }}
              </div>
              <div class="text-body-2 mb-3">
                <strong>Проходной балл:</strong> {{ test.passing_score }}%
              </div>
              <div class="text-body-2 mb-3">
                <strong>Вопросов:</strong> {{ test.questions.length }}
              </div>
              <div class="text-body-2 mb-3">
                <strong>Попыток использовано:</strong> {{ test.attempts_count }} / {{ test.max_attempts }}
              </div>
              <div class="text-body-2">
                <strong>Осталось попыток:</strong> {{ test.remaining_attempts }}
              </div>
            </v-card-text>
          </v-card>

          <!-- Предыдущие попытки -->
          <v-card v-if="previousAttempts.length > 0" class="mt-4">
            <v-card-title class="text-h6">
              <v-icon start>mdi-history</v-icon>
              Предыдущие попытки
            </v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item
                  v-for="attempt in previousAttempts"
                  :key="attempt.id"
                >
                  <v-list-item-title>
                    {{ attempt.completed_at }} - {{ attempt.score }}%
                  </v-list-item-title>
                  <v-list-item-subtitle>
                    Правильных: {{ attempt.correct_answers }} / {{ attempt.total_questions }}
                    <v-chip
                      :color="attempt.is_passed ? 'success' : 'error'"
                      size="small"
                      class="ml-2"
                    >
                      {{ attempt.is_passed ? 'Пройден' : 'Не пройден' }}
                    </v-chip>
                  </v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="8">
          <v-card>
            <v-card-text class="text-center py-8">
              <v-icon size="64" color="primary" class="mb-4">mdi-file-document-edit</v-icon>
              <p class="text-h6 mb-4">Готовы начать экзамен?</p>
              <v-btn
                color="primary"
                size="large"
                @click="startTest"
                prepend-icon="mdi-play"
                :disabled="test.remaining_attempts === 0"
              >
                Начать экзамен
              </v-btn>
              <p v-if="test.remaining_attempts === 0" class="text-error mt-4">
                Все попытки использованы
              </p>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Экзамен в процессе -->
      <v-row v-if="testStarted && !showResults">
        <v-col cols="12" md="4">
          <!-- Прогресс -->
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-progress-clock</v-icon>
              Прогресс
            </v-card-title>
            <v-card-text>
              <div class="text-center">
                <v-progress-circular
                  :model-value="progress"
                  :size="80"
                  :width="8"
                  color="primary"
                >
                  {{ Math.round(progress) }}%
                </v-progress-circular>
              </div>
              <div class="text-center mt-4">
                <div class="text-h6">{{ currentQuestionIndex + 1 }} из {{ test.questions.length }}</div>
                <div class="text-body-2 text-medium-emphasis">вопросов пройдено</div>
              </div>
            </v-card-text>
          </v-card>

          <!-- Таймер -->
          <v-card v-if="test.time_limit" class="mt-4">
            <v-card-title class="text-h6">
              <v-icon start>mdi-clock</v-icon>
              Оставшееся время
            </v-card-title>
            <v-card-text>
              <div class="text-center">
                <div class="text-h4 font-weight-bold" :class="getTimeColor()">
                  {{ formatTime(remainingTime) }}
                </div>
              </div>
            </v-card-text>
          </v-card>

          <!-- Навигация по вопросам -->
          <v-card class="mt-4">
            <v-card-title class="text-h6">
              <v-icon start>mdi-menu</v-icon>
              Навигация
            </v-card-title>
            <v-card-text>
              <div class="d-flex flex-wrap gap-2">
                <v-btn
                  v-for="(q, index) in test.questions"
                  :key="q.id"
                  :color="isQuestionAnswered(index) ? 'success' : 'grey'"
                  :variant="currentQuestionIndex === index ? 'flat' : 'outlined'"
                  size="small"
                  @click="goToQuestion(index)"
                >
                  {{ index + 1 }}
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Вопросы -->
        <v-col cols="12" md="8">
          <v-card v-if="currentQuestion">
            <v-card-title class="text-h6">
              <v-icon start>mdi-help-circle</v-icon>
              Вопрос {{ currentQuestionIndex + 1 }} из {{ test.questions.length }}
            </v-card-title>
            <v-card-text>
              <!-- Текст вопроса -->
              <div class="mb-6">
                <h3 class="text-h6 mb-4">{{ currentQuestion.question }}</h3>
              </div>

              <!-- Ответы -->
              <div v-if="currentQuestion.type === 'single_choice'">
                <v-radio-group v-model="answers[currentQuestion.id]">
                  <v-radio
                    v-for="answer in currentQuestion.answers"
                    :key="answer.id"
                    :value="answer.id"
                    :label="answer.answer"
                    class="mb-2"
                  ></v-radio>
                </v-radio-group>
              </div>

              <div v-else-if="currentQuestion.type === 'multiple_choice'">
                <v-checkbox
                  v-for="answer in currentQuestion.answers"
                  :key="answer.id"
                  v-model="answers[currentQuestion.id]"
                  :value="answer.id"
                  :label="answer.answer"
                  class="mb-2"
                ></v-checkbox>
              </div>

              <!-- Навигация -->
              <div class="d-flex justify-space-between mt-6">
                <v-btn
                  :disabled="currentQuestionIndex === 0"
                  @click="previousQuestion"
                  prepend-icon="mdi-arrow-left"
                >
                  Предыдущий
                </v-btn>
                <v-btn
                  v-if="currentQuestionIndex < test.questions.length - 1"
                  color="primary"
                  @click="nextQuestion"
                  append-icon="mdi-arrow-right"
                >
                  Следующий
                </v-btn>
                <v-btn
                  v-else
                  color="success"
                  @click="finishTest"
                  prepend-icon="mdi-check"
                >
                  Завершить экзамен
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Результаты -->
      <v-row v-if="showResults">
        <v-col cols="12" md="8" offset-md="2">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-check-circle</v-icon>
              Результаты экзамена
            </v-card-title>
            <v-card-text>
              <div class="text-center mb-6">
                <v-icon
                  :color="attemptResult.is_passed ? 'success' : 'error'"
                  size="64"
                  class="mb-4"
                >
                  {{ attemptResult.is_passed ? 'mdi-check-circle' : 'mdi-close-circle' }}
                </v-icon>
                <h2 class="text-h4 mb-2">
                  {{ attemptResult.is_passed ? 'Экзамен пройден!' : 'Экзамен не пройден' }}
                </h2>
                <div class="text-h3 font-weight-bold mb-2" :class="attemptResult.is_passed ? 'text-success' : 'text-error'">
                  {{ attemptResult.score }}%
                </div>
                <div class="text-body-1 text-medium-emphasis mb-4">
                  Правильных ответов: {{ attemptResult.correct_answers }} из {{ attemptResult.total_questions }}
                </div>
                <v-chip
                  :color="attemptResult.is_passed ? 'success' : 'error'"
                  size="large"
                  variant="tonal"
                >
                  {{ attemptResult.is_passed ? 'Пройден' : 'Не пройден' }}
                </v-chip>
              </div>

              <v-divider class="my-4"></v-divider>

              <!-- Информация о попытке -->
              <div class="mb-4">
                <h3 class="text-h6 mb-3">Информация о попытке</h3>
                <v-row>
                  <v-col cols="12" md="6">
                    <div class="text-body-2">
                      <strong>Проходной балл:</strong> {{ test.passing_score }}%
                    </div>
                  </v-col>
                  <v-col cols="12" md="6">
                    <div class="text-body-2">
                      <strong>Ваш результат:</strong> {{ attemptResult.score }}%
                    </div>
                  </v-col>
                  <v-col cols="12" md="6">
                    <div class="text-body-2">
                      <strong>Осталось попыток:</strong> {{ test.remaining_attempts - 1 }}
                    </div>
                  </v-col>
                  <v-col cols="12" md="6">
                    <div class="text-body-2">
                      <strong>Тип экзамена:</strong>
                      <v-chip
                        :color="test.exam_type === 'final' ? 'error' : 'primary'"
                        size="small"
                        class="ml-2"
                      >
                        {{ test.exam_type === 'final' ? 'Итоговый' : 'Периодический' }}
                      </v-chip>
                    </div>
                  </v-col>
                </v-row>
              </div>

              <v-divider class="my-4"></v-divider>

              <div class="text-center">
                <v-btn
                  color="primary"
                  size="large"
                  @click="goBack"
                  prepend-icon="mdi-arrow-left"
                >
                  Вернуться к списку экзаменов
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'
import axios from 'axios'

const props = defineProps({
  test: {
    type: Object,
    required: true
  },
  previousAttempts: {
    type: Array,
    default: () => []
  }
})

// Состояние
const testStarted = ref(false)
const showResults = ref(false)
const currentQuestionIndex = ref(0)
const answers = ref({})
const remainingTime = ref(0)
const timer = ref(null)
const currentAttemptId = ref(null)
const attemptResult = ref({
  score: 0,
  correct_answers: 0,
  total_questions: 0,
  is_passed: false
})

// Вычисляемые свойства
const currentQuestion = computed(() => {
  return props.test.questions[currentQuestionIndex.value] || null
})

const progress = computed(() => {
  return (currentQuestionIndex.value / props.test.questions.length) * 100
})

// Методы
const goBack = () => {
  router.get('/student/tests')
}

const formatTime = (seconds) => {
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = seconds % 60
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}

const getTimeColor = () => {
  if (remainingTime.value <= 60) return 'text-error'
  if (remainingTime.value <= 300) return 'text-warning'
  return 'text-primary'
}

const isQuestionAnswered = (index) => {
  const questionId = props.test.questions[index].id
  const answer = answers.value[questionId]
  if (answer === undefined || answer === null) return false
  if (Array.isArray(answer)) {
    return answer.length > 0
  }
  return true
}

const startTest = async () => {
  try {
    const response = await axios.post(`/student/tests/${props.test.id}/start`)
    if (response.data.success) {
      currentAttemptId.value = response.data.attempt.id
      testStarted.value = true
      
      // Инициализируем ответы для всех вопросов
      props.test.questions.forEach(question => {
        if (question.type === 'multiple_choice') {
          answers.value[question.id] = []
        } else {
          answers.value[question.id] = null
        }
      })
      
      if (props.test.time_limit) {
        remainingTime.value = props.test.time_limit * 60
        startTimer()
      }
    }
  } catch (error) {
    console.error('Ошибка при начале экзамена:', error)
    alert('Не удалось начать экзамен. Попробуйте еще раз.')
  }
}

const startTimer = () => {
  timer.value = setInterval(() => {
    if (remainingTime.value > 0) {
      remainingTime.value--
    } else {
      finishTest()
    }
  }, 1000)
}

const stopTimer = () => {
  if (timer.value) {
    clearInterval(timer.value)
    timer.value = null
  }
}

const nextQuestion = () => {
  if (currentQuestionIndex.value < props.test.questions.length - 1) {
    currentQuestionIndex.value++
  }
}

const previousQuestion = () => {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--
  }
}

const goToQuestion = (index) => {
  currentQuestionIndex.value = index
}

const finishTest = async () => {
  stopTimer()
  
  try {
    // Инициализируем массив ответов для всех вопросов
    const answersData = {}
    props.test.questions.forEach(question => {
      if (question.type === 'multiple_choice') {
        answersData[question.id] = answers.value[question.id] || []
      } else {
        answersData[question.id] = answers.value[question.id] || null
      }
    })

    const response = await axios.post(`/student/tests/${props.test.id}/submit`, {
      attempt_id: currentAttemptId.value,
      answers: answersData
    })

    if (response.data.success) {
      attemptResult.value = response.data.attempt
      showResults.value = true
    }
  } catch (error) {
    console.error('Ошибка при завершении экзамена:', error)
    alert('Не удалось завершить экзамен. Попробуйте еще раз.')
  }
}

onUnmounted(() => {
  stopTimer()
})
</script>

