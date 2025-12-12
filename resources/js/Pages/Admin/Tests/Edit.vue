<template>
  <AdminApp>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Редактирование теста</h1>
              <p class="text-body-1 text-medium-emphasis">Изменение параметров теста</p>
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
                @click="navigateTo(`/admin/tests/${test.id}/questions`)"
                prepend-icon="mdi-help-circle"
              >
                Управление вопросами
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Форма редактирования -->
      <v-row>
        <v-col cols="12" md="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-pencil</v-icon>
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
                        Сохранить изменения
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

        <!-- Информация о тесте -->
        <v-col cols="12" md="4">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-information</v-icon>
              Информация о тесте
            </v-card-title>
            <v-card-text>
              <div class="text-body-2 mb-4">
                <strong>ID:</strong> {{ test.id }}
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Создан:</strong> {{ formatDate(test.created_at) }}
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Обновлен:</strong> {{ formatDate(test.updated_at) }}
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Создатель:</strong> {{ test.creator_name }}
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Урок:</strong> {{ test.lesson_name }}
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Тип:</strong> {{ getTypeText(test.type) }}
              </div>
              
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
              
              <div class="text-body-2">
                <strong>Вопросов:</strong> {{ test.questions_count || 0 }}
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
const test = ref(props.test)
const lessons = ref(props.lessons)
const testTypes = ref(props.testTypes)

// Форма
const form = useForm({
  title: test.value.title,
  description: test.value.description,
  lesson_id: test.value.lesson_id,
  type: test.value.type,
  time_limit: test.value.time_limit,
  passing_score: test.value.passing_score,
  max_attempts: test.value.max_attempts,
  is_active: test.value.is_active,
  is_public: test.value.is_public,
  start_date: test.value.start_date,
  end_date: test.value.end_date,
})

// Методы
const navigateTo = (route) => {
  window.location.href = route
}

const submitForm = () => {
  form.put(`/admin/tests/${test.value.id}`, {
    onSuccess: () => {
      console.log('Тест успешно обновлен')
    },
    onError: (errors) => {
      console.error('Ошибки валидации:', errors)
    }
  })
}

const resetForm = () => {
  form.reset()
  form.title = test.value.title
  form.description = test.value.description
  form.lesson_id = test.value.lesson_id
  form.type = test.value.type
  form.time_limit = test.value.time_limit
  form.passing_score = test.value.passing_score
  form.max_attempts = test.value.max_attempts
  form.is_active = test.value.is_active
  form.is_public = test.value.is_public
  form.start_date = test.value.start_date
  form.end_date = test.value.end_date
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('ru-RU')
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

