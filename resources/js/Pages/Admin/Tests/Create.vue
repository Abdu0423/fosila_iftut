<template>
  <AdminApp>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Создание теста</h1>
              <p class="text-body-1 text-medium-emphasis">Создание нового теста для урока</p>
            </div>
            <v-btn
              color="secondary"
              variant="outlined"
              @click="navigateTo('/admin/tests')"
              prepend-icon="mdi-arrow-left"
            >
              Назад к списку
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Форма создания -->
      <v-row>
        <v-col cols="12" md="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-plus-circle</v-icon>
              Информация о тесте
            </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submitForm">
                <v-row>
                  <!-- Название -->
                  <v-col cols="12">
                    <v-text-field
                      v-model="form.title"
                      label="Название теста *"
                      variant="outlined"
                      :error-messages="form.errors.title"
                      required
                    ></v-text-field>
                  </v-col>

                  <!-- Описание -->
                  <v-col cols="12">
                    <v-textarea
                      v-model="form.description"
                      label="Описание теста"
                      variant="outlined"
                      :error-messages="form.errors.description"
                      rows="3"
                    ></v-textarea>
                  </v-col>

                  <!-- Урок -->
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.lesson_id"
                      :items="lessons"
                      item-title="name"
                      item-value="id"
                      label="Урок *"
                      variant="outlined"
                      :error-messages="form.errors.lesson_id"
                      required
                    ></v-select>
                  </v-col>

                  <!-- Тип теста -->
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.type"
                      :items="testTypes"
                      item-title="text"
                      item-value="value"
                      label="Тип теста *"
                      variant="outlined"
                      :error-messages="form.errors.type"
                      required
                    ></v-select>
                  </v-col>

                  <!-- Время выполнения -->
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model.number="form.time_limit"
                      label="Время выполнения (минуты)"
                      type="number"
                      min="1"
                      variant="outlined"
                      :error-messages="form.errors.time_limit"
                    ></v-text-field>
                  </v-col>

                  <!-- Проходной балл -->
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model.number="form.passing_score"
                      label="Проходной балл (%) *"
                      type="number"
                      min="1"
                      max="100"
                      variant="outlined"
                      :error-messages="form.errors.passing_score"
                      required
                    ></v-text-field>
                  </v-col>

                  <!-- Максимальное количество попыток -->
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model.number="form.max_attempts"
                      label="Максимум попыток *"
                      type="number"
                      min="1"
                      variant="outlined"
                      :error-messages="form.errors.max_attempts"
                      required
                    ></v-text-field>
                  </v-col>

                  <!-- Дата начала -->
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.start_date"
                      label="Дата начала"
                      type="datetime-local"
                      variant="outlined"
                      :error-messages="form.errors.start_date"
                    ></v-text-field>
                  </v-col>

                  <!-- Дата окончания -->
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.end_date"
                      label="Дата окончания"
                      type="datetime-local"
                      variant="outlined"
                      :error-messages="form.errors.end_date"
                    ></v-text-field>
                  </v-col>

                  <!-- Настройки -->
                  <v-col cols="12">
                    <v-divider class="my-4"></v-divider>
                    <h3 class="text-h6 mb-4">Настройки</h3>
                    
                    <v-row>
                      <v-col cols="12" md="6">
                        <v-switch
                          v-model="form.is_active"
                          label="Активный тест"
                          color="primary"
                          :error-messages="form.errors.is_active"
                        ></v-switch>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-switch
                          v-model="form.is_public"
                          label="Публичный тест"
                          color="primary"
                          :error-messages="form.errors.is_public"
                        ></v-switch>
                      </v-col>
                    </v-row>
                  </v-col>
                </v-row>

                <!-- Кнопки действий -->
                <v-row class="mt-6">
                  <v-col cols="12">
                    <div class="d-flex gap-4">
                      <v-btn
                        type="submit"
                        color="primary"
                        size="large"
                        :loading="form.processing"
                        prepend-icon="mdi-content-save"
                      >
                        Создать тест
                      </v-btn>
                      <v-btn
                        color="secondary"
                        variant="outlined"
                        size="large"
                        @click="resetForm"
                        prepend-icon="mdi-refresh"
                      >
                        Сбросить
                      </v-btn>
                    </div>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Справка -->
        <v-col cols="12" md="4">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-help-circle</v-icon>
              Справка
            </v-card-title>
            <v-card-text>
              <div class="text-body-2 mb-4">
                <strong>Название:</strong> Краткое и понятное название теста.
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Описание:</strong> Подробное описание теста и его целей.
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Урок:</strong> Выберите урок, к которому относится тест.
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Тип теста:</strong>
                <ul class="mt-2">
                  <li><strong>Тест</strong> - стандартный тест с вопросами</li>
                  <li><strong>Экзамен</strong> - итоговая проверка знаний</li>
                  <li><strong>Домашнее задание</strong> - практическая работа</li>
                  <li><strong>Практическая работа</strong> - выполнение заданий</li>
                </ul>
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Время выполнения:</strong> Ограничение времени на прохождение теста (необязательно).
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Проходной балл:</strong> Минимальный процент правильных ответов для успешного прохождения.
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Максимум попыток:</strong> Количество попыток прохождения теста.
              </div>
              
              <div class="text-body-2">
                <strong>Даты:</strong> Период доступности теста (необязательно).
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
import { useForm, router } from '@inertiajs/vue3'
import AdminApp from '../AdminApp.vue'

// Props из Inertia
const props = defineProps({
  lessons: {
    type: Array,
    default: () => []
  },
  testTypes: {
    type: Array,
    default: () => []
  }
})

// Данные
const lessons = ref(props.lessons)
const testTypes = ref(props.testTypes)

// Форма
const form = useForm({
  title: '',
  description: '',
  lesson_id: '',
  type: 'quiz',
  time_limit: null,
  passing_score: 60,
  max_attempts: 1,
  is_active: true,
  is_public: false,
  start_date: null,
  end_date: null,
})

// Методы
const navigateTo = (path) => {
  router.visit(path)
}

const submitForm = () => {
  form.post('/admin/tests', {
    onSuccess: () => {
      console.log('Тест успешно создан')
    },
    onError: (errors) => {
      console.error('Ошибки валидации:', errors)
    }
  })
}

const resetForm = () => {
  form.reset()
  form.type = 'quiz'
  form.passing_score = 60
  form.max_attempts = 1
  form.is_active = true
  form.is_public = false
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>

