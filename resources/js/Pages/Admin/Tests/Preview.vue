<template>
  <AdminApp>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Предварительный просмотр теста</h1>
              <p class="text-body-1 text-medium-emphasis">{{ test.title }} - {{ test.lesson_name }}</p>
            </div>
            <div class="d-flex gap-2">
              <v-btn
                color="secondary"
                variant="outlined"
                @click="navigateTo(`/admin/tests/${test.id}/questions`)"
                prepend-icon="mdi-arrow-left"
              >
                Назад к вопросам
              </v-btn>
              <v-btn
                color="primary"
                @click="startTest"
                prepend-icon="mdi-play"
              >
                Начать тест
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Информация о тесте -->
      <v-row>
        <v-col cols="12" md="4">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-information</v-icon>
              Информация о тесте
            </v-card-title>
            <v-card-text>
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
                <strong>Тип:</strong> {{ getTypeText(test.type) }}
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Время:</strong> {{ test.time_limit ? `${test.time_limit} минут` : 'Не ограничено' }}
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Проходной балл:</strong> {{ test.passing_score }}%
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Максимум попыток:</strong> {{ test.max_attempts }}
              </div>
              
              <div class="text-body-2">
                <strong>Вопросов:</strong> {{ questions.length }}
              </div>
            </v-card-text>
          </v-card>

          <!-- Прогресс -->
          <v-card class="mt-4">
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
                <div class="text-h6">{{ currentQuestionIndex + 1 }} из {{ questions.length }}</div>
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
        </v-col>

        <!-- Вопросы -->
        <v-col cols="12" md="8">
          <v-card v-if="currentQuestion">
            <v-card-title class="text-h6">
              <v-icon start>mdi-help-circle</v-icon>
              Вопрос {{ currentQuestionIndex + 1 }} из {{ questions.length }}
            </v-card-title>
            <v-card-text>
              <!-- Текст вопроса -->
              <div class="mb-6">
                <h3 class="text-h6 mb-4">{{ currentQuestion.question }}</h3>
                
                <!-- Тип вопроса -->
                <v-chip
                  :color="getQuestionTypeColor(currentQuestion.type)"
                  size="small"
                  variant="tonal"
                  class="mb-4"
                >
                  {{ currentQuestion.type_text }}
                </v-chip>
                
                
              </div>

              <!-- Ответы -->
              <div v-if="currentQuestion.type === 'single_choice'">
                <v-radio-group v-model="currentAnswer">
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
                  v-model="currentAnswers"
                  :value="answer.id"
                  :label="answer.answer"
                  class="mb-2"
                ></v-checkbox>
              </div>

              <div v-else-if="currentQuestion.type === 'true_false'">
                <v-radio-group v-model="currentAnswer">
                  <v-radio
                    value="true"
                    label="Верно"
                    class="mb-2"
                  ></v-radio>
                  <v-radio
                    value="false"
                    label="Неверно"
                    class="mb-2"
                  ></v-radio>
                </v-radio-group>
              </div>

                             <div v-else-if="currentQuestion.type === 'matching'">
                 <div class="mb-4">
                   <h4 class="text-h6 mb-3">Установите соответствие:</h4>
                   <div class="d-flex flex-wrap gap-4">
                     <div v-for="answer in currentQuestion.answers" :key="answer.id" class="mb-3">
                       <v-card variant="outlined" class="pa-3">
                         <div class="text-body-2 font-weight-medium mb-2">{{ answer.answer }}</div>
                         <v-select
                           v-model="matchingAnswers[answer.id]"
                           :items="currentQuestion.answers"
                           item-title="matching_value"
                           item-value="id"
                           label="Выберите соответствие"
                           variant="outlined"
                           density="compact"
                         ></v-select>
                       </v-card>
                     </div>
                   </div>
                 </div>
               </div>

              <!-- Объяснение (в режиме просмотра) -->
              <div v-if="showExplanation && currentQuestion.explanation" class="mt-6">
                <v-alert
                  type="info"
                  variant="tonal"
                  class="mb-0"
                >
                  <strong>Объяснение:</strong> {{ currentQuestion.explanation }}
                </v-alert>
              </div>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn
                v-if="currentQuestionIndex > 0"
                color="secondary"
                variant="outlined"
                @click="previousQuestion"
                prepend-icon="mdi-arrow-left"
              >
                Предыдущий
              </v-btn>
              <v-btn
                v-if="currentQuestionIndex < questions.length - 1"
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
                append-icon="mdi-check"
              >
                Завершить тест
              </v-btn>
            </v-card-actions>
          </v-card>

          <!-- Результаты -->
          <v-card v-if="showResults">
            <v-card-title class="text-h6">
              <v-icon start>mdi-chart-bar</v-icon>
              Результаты теста
            </v-card-title>
            <v-card-text>
              <div class="text-center mb-6">
                <v-progress-circular
                  :model-value="testScore"
                  :size="120"
                  :width="12"
                  :color="testScore >= test.passing_score ? 'success' : 'error'"
                >
                  <div class="text-center">
                    <div class="text-h4 font-weight-bold">{{ testScore }}%</div>
                    <div class="text-body-2">{{ testScore >= test.passing_score ? 'Сдано' : 'Не сдано' }}</div>
                  </div>
                </v-progress-circular>
              </div>

              <v-row>
                <v-col cols="12" md="4">
                  <div class="text-center">
                    <div class="text-h4 font-weight-bold text-primary">{{ correctAnswers }}</div>
                    <div class="text-body-2 text-medium-emphasis">Правильных ответов</div>
                  </div>
                </v-col>
                <v-col cols="12" md="4">
                  <div class="text-center">
                    <div class="text-h4 font-weight-bold text-warning">{{ totalQuestions }}</div>
                    <div class="text-body-2 text-medium-emphasis">Всего вопросов</div>
                  </div>
                </v-col>
                <v-col cols="12" md="4">
                  <div class="text-center">
                    <div class="text-h4 font-weight-bold text-info">{{ formatTime(totalTime) }}</div>
                    <div class="text-body-2 text-medium-emphasis">Время выполнения</div>
                  </div>
                </v-col>
              </v-row>

              <v-divider class="my-6"></v-divider>

              <!-- Детали по вопросам -->
              <h3 class="text-h6 mb-4">Детали по вопросам</h3>
              <div v-for="(result, index) in questionResults" :key="index" class="mb-4">
                <v-card variant="outlined">
                  <v-card-text>
                    <div class="d-flex align-center mb-2">
                      <v-icon
                        :color="result.correct ? 'success' : 'error'"
                        size="20"
                        class="mr-2"
                      >
                        {{ result.correct ? 'mdi-check-circle' : 'mdi-close-circle' }}
                      </v-icon>
                      <span class="font-weight-medium">Вопрос {{ index + 1 }}</span>
                      <v-spacer></v-spacer>
                      <v-chip
                        :color="result.correct ? 'success' : 'error'"
                        size="small"
                      >
                        {{ result.correct ? 'Правильно' : 'Неправильно' }}
                      </v-chip>
                    </div>
                    <p class="text-body-2">{{ result.question }}</p>
                    <div v-if="result.explanation" class="mt-2">
                      <v-alert
                        type="info"
                        variant="tonal"
                        density="compact"
                        class="mb-0"
                      >
                        <strong>Объяснение:</strong> {{ result.explanation }}
                      </v-alert>
                    </div>
                  </v-card-text>
                </v-card>
              </div>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn
                color="primary"
                @click="restartTest"
                prepend-icon="mdi-refresh"
              >
                Пройти заново
              </v-btn>
              <v-btn
                color="secondary"
                variant="outlined"
                @click="navigateTo(`/admin/tests/${test.id}/questions`)"
                prepend-icon="mdi-arrow-left"
              >
                Назад к вопросам
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AdminApp>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import AdminApp from '../AdminApp.vue'

// Props из Inertia
const props = defineProps({
  test: {
    type: Object,
    required: true
  },
  questions: {
    type: Array,
    default: () => []
  },
  testTypes: {
    type: Array,
    default: () => []
  }
})

// Данные
const test = ref(props.test)
const questions = ref(props.questions)
const testTypes = ref(props.testTypes)

// Состояние теста
const currentQuestionIndex = ref(0)
const currentAnswer = ref(null)
const currentAnswers = ref([])
const currentTextAnswer = ref('')
const matchingAnswers = ref({})
const showExplanation = ref(false)
const showResults = ref(false)
const startTime = ref(null)
const totalTime = ref(0)
const remainingTime = ref(0)
const timer = ref(null)

// Результаты
const questionResults = ref([])
const correctAnswers = ref(0)
const totalQuestions = ref(0)
const testScore = ref(0)

// Вычисляемые свойства
const currentQuestion = computed(() => {
  return questions.value[currentQuestionIndex.value] || null
})

const progress = computed(() => {
  return (currentQuestionIndex.value / questions.value.length) * 100
})

// Методы
const navigateTo = (route) => {
  window.location.href = route
}

const getTypeText = (type) => {
  const typeObj = testTypes.value.find(t => t.value === type)
  return typeObj ? typeObj.text : type
}

const getQuestionTypeColor = (type) => {
  const colors = {
    single_choice: 'primary',
    multiple_choice: 'success',
    matching: 'warning'
  }
  return colors[type]
}

const formatTime = (seconds) => {
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = seconds % 60
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}

const getTimeColor = () => {
  if (remainingTime.value <= 60) return 'error'
  if (remainingTime.value <= 300) return 'warning'
  return 'primary'
}

const startTest = () => {
  startTime.value = Date.now()
  if (test.value.time_limit) {
    remainingTime.value = test.value.time_limit * 60
    startTimer()
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
  if (currentQuestionIndex.value < questions.value.length - 1) {
    currentQuestionIndex.value++
    resetCurrentAnswer()
  }
}

const previousQuestion = () => {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--
    resetCurrentAnswer()
  }
}

const resetCurrentAnswer = () => {
  currentAnswer.value = null
  currentAnswers.value = []
  currentTextAnswer.value = ''
  showExplanation.value = false
}

const finishTest = () => {
  stopTimer()
  
  if (startTime.value) {
    totalTime.value = Math.floor((Date.now() - startTime.value) / 1000)
  }
  
  calculateResults()
  showResults.value = true
}

const calculateResults = () => {
  totalQuestions.value = questions.value.length
  correctAnswers.value = 0
  questionResults.value = []
  
  questions.value.forEach((question, index) => {
    let isCorrect = false
    
    if (question.type === 'single_choice') {
      const correctAnswer = question.answers.find(a => a.is_correct)
      isCorrect = currentAnswer.value === correctAnswer?.id
    } else if (question.type === 'multiple_choice') {
      const correctAnswerIds = question.answers.filter(a => a.is_correct).map(a => a.id)
      isCorrect = correctAnswerIds.length === currentAnswers.value.length &&
                  correctAnswerIds.every(id => currentAnswers.value.includes(id))
    } else if (question.type === 'matching') {
      // Проверяем соответствие для вопросов на соответствие
      const questionMatchingAnswers = matchingAnswers.value[question.id] || {}
      isCorrect = question.answers.every(answer => {
        const userMatch = questionMatchingAnswers[answer.id]
        return userMatch && userMatch === answer.id
      })
    }
    
    if (isCorrect) {
      correctAnswers.value++
    }
    
    questionResults.value.push({
      question: question.question,
      correct: isCorrect,
      explanation: question.explanation
    })
  })
  
  testScore.value = Math.round((correctAnswers.value / totalQuestions.value) * 100)
}

const restartTest = () => {
  currentQuestionIndex.value = 0
  resetCurrentAnswer()
  showResults.value = false
  startTime.value = null
  totalTime.value = 0
  remainingTime.value = 0
  questionResults.value = []
  correctAnswers.value = 0
  totalQuestions.value = 0
  testScore.value = 0
}

// Жизненный цикл
onMounted(() => {
  // Автоматически начинаем тест при загрузке
  startTest()
})

onUnmounted(() => {
  stopTimer()
})
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
