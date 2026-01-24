<template>
  <Layout role="teacher">
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
              {{ t('teacher.to_list', {}, { default: 'К списку' }) }}
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Уведомления -->
      <v-row v-if="page.props.flash?.success">
        <v-col cols="12">
          <v-alert type="success" variant="tonal" closable>
            {{ page.props.flash.success }}
          </v-alert>
        </v-col>
      </v-row>

      <!-- Форма добавления вопроса -->
      <v-row class="mb-4">
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-plus-circle</v-icon>
              {{ t('teacher.add_question', {}, { default: 'Добавить вопрос' }) }}
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="saveQuestion">
                <v-row>
                  <v-col cols="12">
                    <v-textarea
                      v-model="questionForm.question"
                      :label="t('teacher.question_text', {}, { default: 'Текст вопроса' }) + ' *'"
                      variant="outlined"
                      rows="3"
                      :rules="[v => !!v]"
                    ></v-textarea>
                  </v-col>

                  <!-- Ответы -->
                  <v-col cols="12">
                    <div class="d-flex justify-space-between align-center mb-3">
                      <h3 class="text-h6">{{ t('teacher.answers', {}, { default: 'Ответы' }) }}</h3>
                      <v-btn
                        size="small"
                        color="primary"
                        variant="tonal"
                        @click="addAnswer"
                        prepend-icon="mdi-plus"
                      >
                        {{ t('teacher.add_answer', {}, { default: 'Добавить ответ' }) }}
                      </v-btn>
                    </div>

                    <v-radio-group
                      v-model="selectedCorrectAnswer"
                      hide-details
                      class="mb-3"
                    >
                      <div class="d-flex flex-column" style="gap: 16px;">
                        <v-card
                          v-for="(answer, index) in questionForm.answers"
                          :key="index"
                          variant="outlined"
                          class="pa-3"
                          :class="{ 'border-success': selectedCorrectAnswer === index }"
                        >
                          <div class="d-flex align-center gap-2">
                            <v-radio
                              :value="index"
                              hide-details
                              density="compact"
                            ></v-radio>
                            <v-text-field
                              v-model="answer.answer"
                              :label="t('teacher.answer_n', { n: index + 1 }, { default: `Ответ ${index + 1}` }) + ' *'"
                              variant="outlined"
                              density="compact"
                              hide-details
                              class="flex-grow-1"
                              style="width: 100%;"
                              :rules="[v => !!v]"
                            ></v-text-field>
                            <v-btn
                              v-if="questionForm.answers.length > 4"
                              icon="mdi-delete"
                              size="small"
                              color="error"
                              variant="text"
                              @click="removeAnswer(index)"
                            ></v-btn>
                          </div>
                        </v-card>
                      </div>
                    </v-radio-group>
                  </v-col>

                  <v-col cols="12">
                    <div class="d-flex justify-end gap-2">
                      <v-btn
                        variant="text"
                        @click="resetQuestionForm"
                      >
                        {{ t('common.clear', {}, { default: 'Очистить' }) }}
                      </v-btn>
                      <v-btn
                        color="primary"
                        type="submit"
                        :disabled="!isQuestionFormValid"
                        :loading="questionForm.processing"
                        prepend-icon="mdi-content-save"
                      >
                        {{ t('teacher.save_question', {}, { default: 'Сохранить вопрос' }) }}
                      </v-btn>
                    </div>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Список вопросов -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-help-circle</v-icon>
              {{ t('teacher.test_questions', {}, { default: 'Вопросы теста' }) }} ({{ searchQuery ? `${filteredQuestions.length} ${t('common.of', {}, { default: 'из' })} ${test.questions.length}` : test.questions.length }})
            </v-card-title>
            <v-card-text>
              <div v-if="test.questions.length === 0" class="text-center py-8">
                <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-help-circle-outline</v-icon>
                <h3 class="text-h6 text-grey">{{ t('teacher.no_questions', {}, { default: 'Нет вопросов' }) }}</h3>
                <p class="text-body-2 text-grey">{{ t('teacher.add_first_question', {}, { default: 'Добавьте первый вопрос для теста' }) }}</p>
              </div>

              <div v-else>
                <!-- Поле поиска -->
                <div class="mb-4">
                  <v-text-field
                    v-model="searchQuery"
                    :label="t('teacher.search_questions', {}, { default: 'Поиск вопросов' })"
                    variant="outlined"
                    density="comfortable"
                    prepend-inner-icon="mdi-magnify"
                    clearable
                    hide-details
                  ></v-text-field>
                </div>

                <div v-if="filteredQuestions.length === 0" class="text-center py-8">
                  <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-magnify</v-icon>
                  <h3 class="text-h6 text-grey">{{ t('teacher.questions_not_found', {}, { default: 'Вопросы не найдены' }) }}</h3>
                  <p class="text-body-2 text-grey">{{ t('teacher.try_change_search', {}, { default: 'Попробуйте изменить поисковый запрос' }) }}</p>
                </div>

                <!-- Аккордеон с вопросами -->
                <v-expansion-panels v-else variant="accordion">
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
                        <span class="font-weight-medium text-body-1 question-title">{{ question.question }}</span>
                        <v-spacer></v-spacer>
                        <v-chip
                          color="info"
                          size="x-small"
                          variant="tonal"
                          class="mr-2"
                        >
                          {{ question.answers.length }} {{ t('teacher.answers_short', {}, { default: 'отв.' }) }}
                        </v-chip>
                      </div>
                    </v-expansion-panel-title>
                    
                    <v-expansion-panel-text>
                      <div class="pa-2">
                        <!-- Ответы -->
                        <div class="d-flex flex-column gap-2 mb-3">
                          <!-- Правильные ответы -->
                          <div
                            v-for="answer in question.answers.filter(a => a.is_correct)"
                            :key="`correct-${answer.id}`"
                            class="pa-3 answer-correct"
                          >
                            <div class="d-flex align-center">
                              <v-icon color="success" size="18" class="mr-2">mdi-check-circle</v-icon>
                              <span class="font-weight-medium text-success flex-grow-1">
                                {{ answer.answer }}
                              </span>
                              <v-chip size="x-small" color="success" variant="tonal">
                                {{ t('teacher.correct_answer', {}, { default: 'Правильный ответ' }) }}
                              </v-chip>
                            </div>
                          </div>
                          
                          <!-- Неправильные ответы -->
                          <div
                            v-for="answer in question.answers.filter(a => !a.is_correct)"
                            :key="`wrong-${answer.id}`"
                            class="pa-2 d-flex align-center"
                          >
                            <v-icon color="grey-lighten-1" size="18" class="mr-2">mdi-circle-outline</v-icon>
                            <span class="text-body-2">{{ answer.answer }}</span>
                          </div>
                        </div>

                        <!-- Объяснение -->
                        <v-alert
                          v-if="question.explanation"
                          type="info"
                          variant="tonal"
                          density="compact"
                          class="mb-3"
                        >
                          <strong>{{ t('teacher.explanation', {}, { default: 'Объяснение' }) }}:</strong> {{ question.explanation }}
                        </v-alert>

                        <!-- Действия -->
                        <v-divider class="mb-3"></v-divider>
                        <div class="d-flex gap-2">
                          <v-btn
                            size="small"
                            variant="tonal"
                            color="primary"
                            prepend-icon="mdi-pencil"
                            @click.stop="editQuestion(question)"
                          >
                            {{ t('common.edit', {}, { default: 'Редактировать' }) }}
                          </v-btn>
                          <v-btn
                            size="small"
                            variant="tonal"
                            color="error"
                            prepend-icon="mdi-delete"
                            @click.stop="deleteQuestion(question)"
                          >
                            {{ t('common.delete', {}, { default: 'Удалить' }) }}
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
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'

const page = usePage()
const { t } = useI18n()

const props = defineProps({
  test: {
    type: Object,
    required: true
  }
})

// Состояние
const editingQuestion = ref(null)
const selectedCorrectAnswer = ref(0)
const searchQuery = ref('')

// Форма вопроса
const questionForm = reactive({
  question: '',
  type: 'single_choice',
  explanation: '',
  answers: [
    { answer: '', is_correct: false },
    { answer: '', is_correct: false },
    { answer: '', is_correct: false },
    { answer: '', is_correct: false }
  ],
  processing: false
})

// Валидация формы
const isQuestionFormValid = computed(() => {
  const hasQuestion = !!questionForm.question
  const hasEnoughAnswers = questionForm.answers.length >= 4
  const allAnswersFilled = questionForm.answers.every(a => !!a.answer)
  const hasCorrectAnswer = selectedCorrectAnswer.value !== null
  
  return hasQuestion && hasEnoughAnswers && allAnswersFilled && hasCorrectAnswer
})

// Фильтрация вопросов
const filteredQuestions = computed(() => {
  let questions = props.test.questions
  
  // Применяем фильтр, если есть поисковый запрос
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    questions = questions.filter(question => {
      // Поиск по тексту вопроса
      if (question.question.toLowerCase().includes(query)) {
        return true
      }
      
      // Поиск по ответам
      if (question.answers.some(answer => answer.answer.toLowerCase().includes(query))) {
        return true
      }
      
      // Поиск по объяснению
      if (question.explanation && question.explanation.toLowerCase().includes(query)) {
        return true
      }
      
      return false
    })
  }
  
  // Сортируем по дате создания в убывающем порядке (новые сверху)
  // Используем ID как fallback, если created_at не доступен
  return [...questions].sort((a, b) => {
    const dateA = a.created_at ? new Date(a.created_at).getTime() : a.id || 0
    const dateB = b.created_at ? new Date(b.created_at).getTime() : b.id || 0
    return dateB - dateA
  })
})

// Инициализация правильного ответа
questionForm.answers[0].is_correct = true

// Отслеживаем изменение выбранного правильного ответа
watch(selectedCorrectAnswer, (newValue) => {
  // Сбрасываем все ответы и устанавливаем выбранный как правильный
  questionForm.answers.forEach((answer, index) => {
    answer.is_correct = (index === newValue)
  })
}, { immediate: true })

// Методы
const goBack = () => {
  router.visit('/teacher/tests')
}

const resetQuestionForm = () => {
  questionForm.question = ''
  questionForm.type = 'single_choice'
  questionForm.explanation = ''
  questionForm.answers = [
    { answer: '', is_correct: true },
    { answer: '', is_correct: false },
    { answer: '', is_correct: false },
    { answer: '', is_correct: false }
  ]
  selectedCorrectAnswer.value = 0
  editingQuestion.value = null
}

const addAnswer = () => {
  questionForm.answers.push({ answer: '', is_correct: false })
  // После добавления нового ответа правильный ответ остается выбранным
}

const removeAnswer = (index) => {
  if (questionForm.answers.length > 4) {
    questionForm.answers.splice(index, 1)
    // Если удалили выбранный правильный ответ, выбираем первый
    if (selectedCorrectAnswer.value === index) {
      selectedCorrectAnswer.value = 0
    } else if (selectedCorrectAnswer.value > index) {
      selectedCorrectAnswer.value = selectedCorrectAnswer.value - 1
    }
  }
}

const editQuestion = (question) => {
  editingQuestion.value = question
  questionForm.question = question.question
  questionForm.type = question.type
  questionForm.explanation = question.explanation
  questionForm.answers = question.answers.map(a => ({
    id: a.id,
    answer: a.answer,
    is_correct: a.is_correct
  }))
  
  // Если ответов меньше 4, добавляем до 4
  while (questionForm.answers.length < 4) {
    questionForm.answers.push({ answer: '', is_correct: false })
  }
  
  // Находим индекс правильного ответа
  const correctIndex = questionForm.answers.findIndex(a => a.is_correct)
  selectedCorrectAnswer.value = correctIndex >= 0 ? correctIndex : 0
}

const saveQuestion = () => {
  if (!isQuestionFormValid.value) {
    return
  }

  questionForm.processing = true
  
  // Устанавливаем правильный ответ на основе выбранного индекса
  questionForm.answers.forEach((answer, index) => {
    answer.is_correct = (index === selectedCorrectAnswer.value)
  })
  
  // Убираем пустые ответы
  const validAnswers = questionForm.answers.filter(a => !!a.answer)
  
  const formData = {
    question: questionForm.question,
    type: questionForm.type,
    explanation: questionForm.explanation,
    answers: validAnswers
  }

  const form = useForm(formData)

  if (editingQuestion.value) {
    // Обновление
    form.put(`/teacher/tests/${props.test.id}/questions/${editingQuestion.value.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        resetQuestionForm()
        router.reload()
      },
      onFinish: () => {
        questionForm.processing = false
      }
    })
  } else {
    // Создание
    form.post(`/teacher/tests/${props.test.id}/questions`, {
      preserveScroll: true,
      onSuccess: () => {
        resetQuestionForm()
        router.reload()
      },
      onFinish: () => {
        questionForm.processing = false
      }
    })
  }
}

const deleteQuestion = (question) => {
  const confirmMsg = t('teacher.confirm_delete_question', {}, { default: 'Вы уверены, что хотите удалить этот вопрос?' })
  if (!confirm(confirmMsg)) {
    return
  }

  const form = useForm({})
  form.delete(`/teacher/tests/${props.test.id}/questions/${question.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      router.reload()
    }
  })
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}

.border-success {
  border: 2px solid rgb(var(--v-theme-success)) !important;
}

.v-expansion-panel {
  border-radius: 8px !important;
  margin-bottom: 8px;
}

.v-expansion-panel-title {
  min-height: 48px;
}

.question-title {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  max-width: 70%;
}

.answer-correct {
  background-color: rgba(76, 175, 80, 0.1);
  border-radius: 6px;
}
</style>
