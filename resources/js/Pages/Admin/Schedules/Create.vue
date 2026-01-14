<template>
  <Layout role="admin">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Создание расписания</h1>
              <p class="text-body-1 text-medium-emphasis">Добавление новой записи в расписание</p>
            </div>
            <v-btn
              color="secondary"
              variant="outlined"
              @click="goBack"
              prepend-icon="mdi-arrow-left"
            >
              Назад к списку
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Форма создания -->
      <v-row>
        <v-col cols="12" lg="8">
          <v-card>
            <v-card-title class="text-h5 pa-6">
              Информация о расписании
            </v-card-title>
            <v-card-text class="pa-6">
              <v-form @submit.prevent="submit">
                <v-row>
                  <!-- Предмет -->
                  <v-col cols="12" md="6">
                    <v-autocomplete
                      v-model="form.subject_id"
                      :items="subjects || []"
                      item-title="name"
                      item-value="id"
                      label="Предмет *"
                      variant="outlined"
                      :error-messages="errors.subject_id"
                      prepend-inner-icon="mdi-magnify"
                      clearable
                      auto-select-first
                      required
                    />
                  </v-col>

                  <!-- Преподаватель -->
                  <v-col cols="12" md="6">
                    <v-autocomplete
                      v-model="form.teacher_id"
                      :items="teacherItems"
                      item-title="displayName"
                      item-value="id"
                      label="Преподаватель *"
                      variant="outlined"
                      :error-messages="errors.teacher_id"
                      prepend-inner-icon="mdi-magnify"
                      clearable
                      auto-select-first
                      required
                    >
                      <template v-slot:item="{ props, item }">
                        <v-list-item v-bind="props">
                          <v-list-item-title>{{ item.raw.name }} {{ item.raw.last_name }}{{ item.raw.middle_name ? ' ' + item.raw.middle_name : '' }}</v-list-item-title>
                          <v-list-item-subtitle v-if="item.raw.email">{{ item.raw.email }}</v-list-item-subtitle>
                        </v-list-item>
                      </template>
                    </v-autocomplete>
                  </v-col>

                  <!-- Группа -->
                  <v-col cols="12" md="6">
                    <v-autocomplete
                      v-model="form.group_id"
                      :items="groups || []"
                      item-title="name"
                      item-value="id"
                      label="Группа *"
                      variant="outlined"
                      :error-messages="errors.group_id"
                      prepend-inner-icon="mdi-magnify"
                      clearable
                      auto-select-first
                      required
                    />
                  </v-col>

                  <!-- Семестр -->
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.semester"
                      :items="semesterItems"
                      label="Семестр *"
                      variant="outlined"
                      :error-messages="errors.semester"
                      required
                    />
                  </v-col>

                  <!-- Кредиты -->
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model.number="form.credits"
                      label="Количество кредитов *"
                      type="number"
                      min="1"
                      max="10"
                      variant="outlined"
                      :error-messages="errors.credits"
                      required
                    />
                  </v-col>

                  <!-- Год обучения -->
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model.number="form.study_year"
                      label="Год обучения *"
                      type="number"
                      min="2020"
                      max="2030"
                      variant="outlined"
                      :error-messages="errors.study_year"
                      required
                    />
                  </v-col>

                  <!-- Порядок -->
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model.number="form.order"
                      label="Порядковый номер *"
                      type="number"
                      min="1"
                      variant="outlined"
                      :error-messages="errors.order"
                      hint="Порядок проведения занятия в расписании"
                      required
                    />
                  </v-col>

                  <!-- Дата и время -->
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.scheduled_at"
                      label="Дата и время"
                      type="datetime-local"
                      variant="outlined"
                      :error-messages="errors.scheduled_at"
                      hint="Оставьте пустым, если дата не определена"
                    />
                  </v-col>

                  <!-- Статус активности -->
                  <v-col cols="12">
                    <v-switch
                      v-model="form.is_active"
                      label="Активное расписание"
                      color="primary"
                      :error-messages="errors.is_active"
                      hide-details="auto"
                    />
                  </v-col>

                  <!-- Силлабусы -->
                  <v-col cols="12">
                    <v-select
                      v-model="form.syllabus_ids"
                      :items="syllabuses || []"
                      item-title="name"
                      item-value="id"
                      label="Силлабусы"
                      variant="outlined"
                      multiple
                      chips
                      :error-messages="errors.syllabus_ids"
                      hint="Выберите силлабусы для данного расписания"
                      persistent-hint
                    >
                      <template v-slot:chip="{ props, item }">
                        <v-chip
                          v-bind="props"
                          :text="item.raw.name"
                          size="small"
                          closable
                        />
                      </template>
                      <template v-slot:item="{ props, item }">
                        <v-list-item v-bind="props">
                          <v-list-item-title>{{ item.raw.name }}</v-list-item-title>
                          <v-list-item-subtitle v-if="item.raw.lesson">
                            Урок: {{ item.raw.lesson.name }} | Год: {{ item.raw.creation_year }}
                          </v-list-item-subtitle>
                        </v-list-item>
                      </template>
                    </v-select>
                  </v-col>

                  <!-- Уроки -->
                  <v-col cols="12">
                    <v-select
                      v-model="form.lesson_ids"
                      :items="lessons || []"
                      item-title="title"
                      item-value="id"
                      label="Уроки"
                      variant="outlined"
                      multiple
                      chips
                      :error-messages="errors.lesson_ids"
                      hint="Выберите уроки для данного расписания"
                      persistent-hint
                    >
                      <template v-slot:chip="{ props, item }">
                        <v-chip
                          v-bind="props"
                          :text="item.raw.title"
                          size="small"
                          closable
                        />
                      </template>
                      <template v-slot:item="{ props, item }">
                        <v-list-item v-bind="props">
                          <v-list-item-title>{{ item.raw.title }}</v-list-item-title>
                          <v-list-item-subtitle v-if="item.raw.subject">
                            Предмет: {{ item.raw.subject.name }} | Длительность: {{ item.raw.duration || 90 }} мин
                          </v-list-item-subtitle>
                        </v-list-item>
                      </template>
                    </v-select>
                  </v-col>
                </v-row>

                <div class="d-flex gap-3 mt-6">
                  <v-btn
                    @click="goBack"
                    variant="outlined"
                  >
                    Отмена
                  </v-btn>
                  <v-btn
                    type="submit"
                    color="primary"
                    :loading="processing"
                    :disabled="processing"
                  >
                    Создать расписание
                  </v-btn>
                </div>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Боковая панель с информацией -->
        <v-col cols="12" lg="4">
          <v-card>
            <v-card-title class="text-h6 pa-4">
              <v-icon icon="mdi-information" class="mr-2"></v-icon>
              Справка
            </v-card-title>
            <v-card-text class="pa-4">
              <div class="text-body-2 mb-3">
                <strong>Поля, отмеченные * - обязательные</strong>
              </div>
              
              <v-divider class="my-3" />
              
              <div class="text-body-2">
                <p class="mb-2"><strong>Предмет:</strong> Выберите предмет из списка</p>
                <p class="mb-2"><strong>Преподаватель:</strong> Назначенный преподаватель</p>
                <p class="mb-2"><strong>Группа:</strong> Группа студентов</p>
                <p class="mb-2"><strong>Семестр:</strong> 1 или 2 семестр</p>
                <p class="mb-2"><strong>Кредиты:</strong> От 1 до 10 кредитов</p>
                <p class="mb-2"><strong>Год обучения:</strong> Академический год</p>
                <p class="mb-2"><strong>Порядок:</strong> Номер занятия в расписании</p>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

// Props
const props = defineProps({
  subjects: {
    type: Array,
    default: () => []
  },
  groups: {
    type: Array,
    default: () => []
  },
  teachers: {
    type: Array,
    default: () => []
  },
  syllabuses: {
    type: Array,
    default: () => []
  },
  lessons: {
    type: Array,
    default: () => []
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

// Debug информация при необходимости
// console.log('Schedule Create.vue - Props loaded:', {
//   subjects: props.subjects?.length || 0,
//   groups: props.groups?.length || 0,
//   teachers: props.teachers?.length || 0,
//   errors: Object.keys(props.errors || {}).length
// })

// Функция для получения текущей даты и времени в формате YYYY-MM-DDTHH:mm
const getCurrentDateTime = () => {
  const now = new Date()
  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const day = String(now.getDate()).padStart(2, '0')
  const hours = String(now.getHours()).padStart(2, '0')
  const minutes = String(now.getMinutes()).padStart(2, '0')
  return `${year}-${month}-${day}T${hours}:${minutes}`
}

// Form data
const form = useForm({
  subject_id: null,
  teacher_id: null,
  group_id: null,
  semester: null,
  credits: 3,
  study_year: new Date().getFullYear(),
  order: 1,
  scheduled_at: getCurrentDateTime(), // Устанавливаем текущую дату и время по умолчанию
  is_active: true,
  syllabus_ids: [],
  lesson_ids: []
})

const processing = ref(false)

// Варианты семестров
const semesterItems = [
  { title: '1 семестр', value: 1 },
  { title: '2 семестр', value: 2 }
]

// Преобразуем преподавателей для автокомплита
const teacherItems = computed(() => {
  return (props.teachers || []).map(teacher => ({
    ...teacher,
    displayName: `${teacher.name || ''} ${teacher.last_name || ''}${teacher.middle_name ? ' ' + teacher.middle_name : ''}`.trim()
  }))
})

// Methods
const submit = () => {
  processing.value = true
  
  form.post('/admin/schedules', {
    onSuccess: (page) => {
      processing.value = false
      // Перенаправляем на список расписаний
      router.visit('/admin/schedules')
    },
    onError: (errors) => {
      processing.value = false
    },
    onFinish: () => {
      processing.value = false
    }
  })
}

const goBack = () => {
  router.visit('/admin/schedules')
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>