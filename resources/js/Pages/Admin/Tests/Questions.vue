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
                <v-list>
                  <v-list-item
                    v-for="(question, index) in questions"
                    :key="question.id"
                    class="mb-4"
                  >
                    <template v-slot:prepend>
                      <v-avatar color="primary" size="32">
                        <span class="text-white font-weight-bold">{{ index + 1 }}</span>
                      </v-avatar>
                    </template>
                    
                    <v-list-item-title class="font-weight-medium mb-2">
                      {{ question.question }}
                    </v-list-item-title>
                    
                    <v-list-item-subtitle>
                      <div class="d-flex align-center gap-4 mb-2">
                        <v-chip
                          :color="getQuestionTypeColor(question.type)"
                          size="small"
                          variant="tonal"
                        >
                          {{ question.type_text }}
                        </v-chip>
                        
                        <v-chip
                          color="secondary"
                          size="small"
                          variant="tonal"
                        >
                          {{ question.answers.length }} ответов
                        </v-chip>
                      </div>
                      
                      <!-- Ответы -->
                      <div class="mt-3">
                        <div
                          v-for="(answer, answerIndex) in question.answers"
                          :key="answer.id"
                          class="d-flex align-center mb-1"
                        >
                          <v-icon
                            :color="answer.is_correct ? 'success' : 'grey'"
                            size="16"
                            class="mr-2"
                          >
                            {{ answer.is_correct ? 'mdi-check-circle' : 'mdi-circle-outline' }}
                          </v-icon>
                          <span class="text-body-2">{{ answer.answer }}</span>
                        </div>
                      </div>
                      
                      <!-- Объяснение -->
                      <div v-if="question.explanation" class="mt-3">
                        <v-alert
                          type="info"
                          variant="tonal"
                          density="compact"
                          class="mb-0"
                        >
                          <strong>Объяснение:</strong> {{ question.explanation }}
                        </v-alert>
                      </div>
                    </v-list-item-subtitle>
                    
                    <template v-slot:append>
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
                            @click="editQuestion(question)"
                            prepend-icon="mdi-pencil"
                          >
                            <v-list-item-title>Редактировать</v-list-item-title>
                          </v-list-item>
                          <v-list-item
                            @click="duplicateQuestion(question)"
                            prepend-icon="mdi-content-copy"
                          >
                            <v-list-item-title>Дублировать</v-list-item-title>
                          </v-list-item>
                          <v-divider></v-divider>
                          <v-list-item
                            @click="deleteQuestion(question)"
                            prepend-icon="mdi-delete"
                            color="error"
                          >
                            <v-list-item-title>Удалить</v-list-item-title>
                          </v-list-item>
                        </v-list>
                      </v-menu>
                    </template>
                  </v-list-item>
                </v-list>
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

.v-list-item {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  margin-bottom: 8px;
}
</style>
