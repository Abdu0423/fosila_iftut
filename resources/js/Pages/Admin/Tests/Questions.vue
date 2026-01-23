<template>
  <AdminApp>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Управление вопросами</h1>
              <p class="text-body-1 text-medium-emphasis">{{ test.title }} - {{ test.lesson_name }}</p>
            </div>
            <div class="d-flex gap-2">
              <v-btn
                color="secondary"
                variant="outlined"
                @click="navigateTo(`/admin/tests/${test.id}/edit`)"
                prepend-icon="mdi-arrow-left"
              >
                Назад к тесту
              </v-btn>
              <v-btn
                color="primary"
                @click="showAddQuestionDialog = true"
                prepend-icon="mdi-plus"
              >
                Добавить вопрос
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Список вопросов -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-help-circle</v-icon>
              Вопросы теста ({{ questions.length }})
            </v-card-title>
            <v-card-text>
              <div v-if="questions.length === 0" class="text-center py-8">
                <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-help-circle-outline</v-icon>
                <h3 class="text-h6 text-grey">Нет вопросов</h3>
                <p class="text-body-2 text-grey">Добавьте первый вопрос для вашего теста</p>
                <v-btn
                  color="primary"
                  @click="showAddQuestionDialog = true"
                  class="mt-4"
                  prepend-icon="mdi-plus"
                >
                  Добавить вопрос
                </v-btn>
              </div>
              
              <div v-else>
                <!-- Поиск вопросов -->
                <v-text-field
                  v-model="searchQuery"
                  prepend-inner-icon="mdi-magnify"
                  label="Поиск вопросов"
                  variant="outlined"
                  density="compact"
                  clearable
                  class="mb-4"
                  hide-details
                ></v-text-field>

                <!-- Аккордеон с вопросами -->
                <v-expansion-panels variant="accordion">
                  <v-expansion-panel
                    v-for="(question, index) in filteredQuestions"
                    :key="question.id"
                    class="mb-2"
                  >
                    <v-expansion-panel-title>
                      <div class="d-flex align-center flex-grow-1">
                        <v-avatar color="primary" size="28" class="mr-3">
                          <span class="text-white text-caption font-weight-bold">{{ index + 1 }}</span>
                        </v-avatar>
                        <span class="font-weight-medium text-body-1">{{ question.question }}</span>
                        <v-spacer></v-spacer>
                        <v-chip
                          :color="getQuestionTypeColor(question.type)"
                          size="x-small"
                          variant="tonal"
                          class="mr-2"
                        >
                          {{ question.answers.length }} отв.
                        </v-chip>
                      </div>
                    </v-expansion-panel-title>
                    
                    <v-expansion-panel-text>
                      <!-- Ответы -->
                      <div class="pa-2">
                        <div
                          v-for="answer in question.answers"
                          :key="answer.id"
                          class="d-flex align-center py-1"
                          :class="{ 'answer-correct': answer.is_correct }"
                        >
                          <v-icon
                            :color="answer.is_correct ? 'success' : 'grey-lighten-1'"
                            size="18"
                            class="mr-2"
                          >
                            {{ answer.is_correct ? 'mdi-check-circle' : 'mdi-circle-outline' }}
                          </v-icon>
                          <span 
                            class="text-body-2"
                            :class="{ 'text-success font-weight-medium': answer.is_correct }"
                          >
                            {{ answer.answer }}
                          </span>
                          <v-chip
                            v-if="answer.is_correct"
                            color="success"
                            size="x-small"
                            variant="tonal"
                            class="ml-2"
                          >
                            Правильный ответ
                          </v-chip>
                        </div>
                        
                        <!-- Объяснение -->
                        <v-alert
                          v-if="question.explanation"
                          type="info"
                          variant="tonal"
                          density="compact"
                          class="mt-3"
                        >
                          <strong>Объяснение:</strong> {{ question.explanation }}
                        </v-alert>
                        
                        <!-- Действия -->
                        <v-divider class="my-3"></v-divider>
                        <div class="d-flex gap-2">
                          <v-btn
                            size="small"
                            variant="tonal"
                            color="primary"
                            prepend-icon="mdi-pencil"
                            @click.stop="editQuestion(question)"
                          >
                            Редактировать
                          </v-btn>
                          <v-btn
                            size="small"
                            variant="tonal"
                            prepend-icon="mdi-content-copy"
                            @click.stop="duplicateQuestion(question)"
                          >
                            Дублировать
                          </v-btn>
                          <v-btn
                            size="small"
                            variant="tonal"
                            color="error"
                            prepend-icon="mdi-delete"
                            @click.stop="deleteQuestion(question)"
                          >
                            Удалить
                          </v-btn>
                        </div>
                      </div>
                    </v-expansion-panel-text>
                  </v-expansion-panel>
                </v-expansion-panels>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Диалог добавления/редактирования вопроса -->
      <v-dialog
        v-model="showAddQuestionDialog"
        max-width="800px"
        persistent
      >
        <v-card>
          <v-card-title class="text-h6">
            <v-icon start>{{ editingQuestion ? 'mdi-pencil' : 'mdi-plus' }}</v-icon>
            {{ editingQuestion ? 'Редактирование вопроса' : 'Добавление вопроса' }}
          </v-card-title>
          <v-card-text>
            <v-form @submit.prevent="saveQuestion">
              <v-row>
                <!-- Текст вопроса -->
                <v-col cols="12">
                  <v-textarea
                    v-model="questionForm.question"
                    label="Текст вопроса *"
                    variant="outlined"
                    :error-messages="questionForm.errors.question"
                    rows="3"
                    required
                  ></v-textarea>
                </v-col>

                                 <!-- Тип вопроса -->
                 <v-col cols="12" md="6">
                   <v-select
                     v-model="questionForm.type"
                     :items="questionTypes"
                     item-title="text"
                     item-value="value"
                     label="Тип вопроса *"
                     variant="outlined"
                     :error-messages="questionForm.errors.type"
                     required
                     @update:model-value="onTypeChange"
                   ></v-select>
                 </v-col>

                

                <!-- Порядок -->
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model.number="questionForm.order"
                    label="Порядок"
                    type="number"
                    min="1"
                    variant="outlined"
                    :error-messages="questionForm.errors.order"
                  ></v-text-field>
                </v-col>

                <!-- Объяснение -->
                <v-col cols="12">
                  <v-textarea
                    v-model="questionForm.explanation"
                    label="Объяснение (необязательно)"
                    variant="outlined"
                    :error-messages="questionForm.errors.explanation"
                    rows="2"
                  ></v-textarea>
                </v-col>

                <!-- Ответы -->
                <v-col cols="12">
                  <v-divider class="my-4"></v-divider>
                  <h3 class="text-h6 mb-4">Ответы</h3>
                  
                                     <div v-for="(answer, index) in questionForm.answers" :key="index" class="mb-4">
                     <v-row>
                       <v-col cols="1">
                         <v-radio
                           v-if="questionForm.type === 'single_choice'"
                           v-model="correctAnswerId"
                           :value="index"
                           :label="''"
                           hide-details
                           @change="updateCorrectAnswer"
                         ></v-radio>
                         <v-checkbox
                           v-else
                           v-model="answer.is_correct"
                           :label="''"
                           hide-details
                         ></v-checkbox>
                       </v-col>
                      <v-col cols="8">
                        <v-text-field
                          v-model="answer.answer"
                          :label="`Ответ ${index + 1}`"
                          variant="outlined"
                          density="compact"
                          :error-messages="getAnswerError(index)"
                        ></v-text-field>
                      </v-col>
                                             <v-col cols="2">
                         <v-text-field
                           v-model.number="answer.order"
                           label="Порядок"
                           type="number"
                           min="1"
                           variant="outlined"
                           density="compact"
                         ></v-text-field>
                       </v-col>
                       <v-col v-if="questionForm.type === 'matching'" cols="2">
                         <v-text-field
                           v-model="answer.matching_key"
                           label="Ключ"
                           variant="outlined"
                           density="compact"
                         ></v-text-field>
                       </v-col>
                       <v-col v-if="questionForm.type === 'matching'" cols="2">
                         <v-text-field
                           v-model="answer.matching_value"
                           label="Значение"
                           variant="outlined"
                           density="compact"
                         ></v-text-field>
                       </v-col>
                      <v-col cols="1">
                        <v-btn
                          icon="mdi-delete"
                          variant="text"
                          color="error"
                          size="small"
                          @click="removeAnswer(index)"
                                                     :disabled="questionForm.answers.length <= 3"
                        ></v-btn>
                      </v-col>
                    </v-row>
                  </div>
                  
                  <v-btn
                    color="primary"
                    variant="outlined"
                    @click="addAnswer"
                    prepend-icon="mdi-plus"
                  >
                    Добавить ответ
                  </v-btn>
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="secondary"
              variant="outlined"
              @click="closeQuestionDialog"
            >
              Отмена
            </v-btn>
            <v-btn
              color="primary"
              @click="saveQuestion"
              :loading="questionForm.processing"
            >
              {{ editingQuestion ? 'Сохранить' : 'Добавить' }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </AdminApp>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
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
  questionTypes: {
    type: Array,
    default: () => []
  }
})

// Данные
const test = ref(props.test)
const questions = ref(props.questions)
const questionTypes = ref(props.questionTypes)

// Состояние
const showAddQuestionDialog = ref(false)
const editingQuestion = ref(null)
const correctAnswerId = ref(null)
const searchQuery = ref('')

// Фильтрация вопросов по поиску
const filteredQuestions = computed(() => {
  if (!searchQuery.value) return questions.value
  const query = searchQuery.value.toLowerCase()
  return questions.value.filter(q => 
    q.question.toLowerCase().includes(query) ||
    q.answers.some(a => a.answer.toLowerCase().includes(query))
  )
})

// Форма вопроса
const questionForm = useForm({
  question: '',
  type: 'single_choice',
  order: 1,
  explanation: '',
  answers: [
    { answer: '', is_correct: false, order: 1 },
    { answer: '', is_correct: false, order: 2 },
    { answer: '', is_correct: false, order: 3 },
    { answer: '', is_correct: false, order: 4 }
  ]
})

// Методы
const navigateTo = (path) => {
  router.visit(path)
}

const getQuestionTypeColor = (type) => {
  const colors = {
    single_choice: 'primary',
    multiple_choice: 'success',
    matching: 'warning'
  }
  return colors[type]
}

const updateCorrectAnswer = () => {
  // Сбрасываем все ответы
  questionForm.answers.forEach(answer => {
    answer.is_correct = false
  })
  // Устанавливаем правильный ответ
  if (correctAnswerId.value !== null) {
    questionForm.answers[correctAnswerId.value].is_correct = true
  }
}

const onTypeChange = () => {
  // Сбрасываем правильный ответ при смене типа вопроса
  correctAnswerId.value = null
  questionForm.answers.forEach(answer => {
    answer.is_correct = false
  })
}

const addAnswer = () => {
  questionForm.answers.push({
    answer: '',
    is_correct: false,
    order: questionForm.answers.length + 1,
    matching_key: '',
    matching_value: ''
  })
}

const removeAnswer = (index) => {
  if (questionForm.answers.length > 3) {
    questionForm.answers.splice(index, 1)
    // Обновляем порядок
    questionForm.answers.forEach((answer, i) => {
      answer.order = i + 1
    })
  }
}

const getAnswerError = (index) => {
  if (questionForm.errors[`answers.${index}.answer`]) {
    return questionForm.errors[`answers.${index}.answer`]
  }
  return null
}

const editQuestion = (question) => {
  editingQuestion.value = question
  questionForm.question = question.question
  questionForm.type = question.type
  questionForm.order = question.order
  questionForm.explanation = question.explanation
  questionForm.answers = question.answers.map(answer => ({
    answer: answer.answer,
    is_correct: answer.is_correct,
    order: answer.order,
    matching_key: answer.matching_key,
    matching_value: answer.matching_value
  }))
  
  // Устанавливаем правильный ответ для single_choice
  if (question.type === 'single_choice') {
    const correctIndex = question.answers.findIndex(answer => answer.is_correct)
    correctAnswerId.value = correctIndex >= 0 ? correctIndex : null
  }
  
  showAddQuestionDialog.value = true
}

const duplicateQuestion = (question) => {
  editingQuestion.value = null
  questionForm.question = question.question + ' (копия)'
  questionForm.type = question.type
  questionForm.order = questions.value.length + 1
  questionForm.explanation = question.explanation
  questionForm.answers = question.answers.map(answer => ({
    answer: answer.answer,
    is_correct: answer.is_correct,
    order: answer.order,
    matching_key: answer.matching_key,
    matching_value: answer.matching_value
  }))
  
  // Устанавливаем правильный ответ для single_choice
  if (question.type === 'single_choice') {
    const correctIndex = question.answers.findIndex(answer => answer.is_correct)
    correctAnswerId.value = correctIndex >= 0 ? correctIndex : null
  }
  
  showAddQuestionDialog.value = true
}

const deleteQuestion = (question) => {
  if (confirm('Вы уверены, что хотите удалить этот вопрос?')) {
    const form = useForm({})
    form.delete(`/admin/tests/${test.value.id}/questions/${question.id}`, {
      onSuccess: () => {
        console.log('Вопрос успешно удален')
        // Обновляем список вопросов
        const index = questions.value.findIndex(q => q.id === question.id)
        if (index > -1) {
          questions.value.splice(index, 1)
        }
      },
      onError: (errors) => {
        console.error('Ошибка при удалении:', errors)
      }
    })
  }
}

const saveQuestion = () => {
  // Проверяем, что есть хотя бы один правильный ответ
  const hasCorrectAnswer = questionForm.answers.some(answer => answer.is_correct)
  if (!hasCorrectAnswer) {
    alert('Должен быть хотя бы один правильный ответ!')
    return
  }

  // Проверяем, что все ответы заполнены
  const hasEmptyAnswers = questionForm.answers.some(answer => !answer.answer.trim())
  if (hasEmptyAnswers) {
    alert('Все ответы должны быть заполнены!')
    return
  }

  if (editingQuestion.value) {
    // Обновление существующего вопроса
    questionForm.put(`/admin/tests/${test.value.id}/questions/${editingQuestion.value.id}`, {
      onSuccess: () => {
        closeQuestionDialog()
        console.log('Вопрос успешно обновлен')
      },
      onError: (errors) => {
        console.error('Ошибки валидации:', errors)
      }
    })
  } else {
    // Создание нового вопроса
    questionForm.post(`/admin/tests/${test.value.id}/questions`, {
      onSuccess: () => {
        closeQuestionDialog()
        console.log('Вопрос успешно создан')
      },
      onError: (errors) => {
        console.error('Ошибки валидации:', errors)
      }
    })
  }
}

const closeQuestionDialog = () => {
  showAddQuestionDialog.value = false
  editingQuestion.value = null
  correctAnswerId.value = null
  questionForm.reset()
  questionForm.type = 'single_choice'
  questionForm.order = 1
  questionForm.answers = [
    { answer: '', is_correct: false, order: 1 },
    { answer: '', is_correct: false, order: 2 },
    { answer: '', is_correct: false, order: 3 }
  ]
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}

.v-expansion-panel {
  border-radius: 8px !important;
  margin-bottom: 8px;
}

.v-expansion-panel-title {
  min-height: 48px;
}

.answer-correct {
  background-color: rgba(76, 175, 80, 0.08);
  border-radius: 4px;
  padding: 4px 8px;
  margin: 2px 0;
}
</style>
