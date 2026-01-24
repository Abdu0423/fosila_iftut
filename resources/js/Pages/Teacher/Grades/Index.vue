<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('teacher.student_grades', {}, { default: 'Оценки студентов' }) }}</h1>
              <p class="text-body-1 text-medium-emphasis">{{ t('teacher.select_schedule_for_grades', {}, { default: 'Выберите расписание для управления оценками' }) }}</p>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Уведомления -->
      <v-row v-if="page.props.flash?.success">
        <v-col cols="12">
          <v-alert
            type="success"
            variant="tonal"
            closable
          >
            {{ page.props.flash.success }}
          </v-alert>
        </v-col>
      </v-row>

      <!-- Выбор расписания -->
      <v-row>
        <v-col cols="12" md="6">
          <v-card variant="outlined">
            <v-card-title>
              <v-icon start>mdi-calendar-clock</v-icon>
              {{ t('teacher.select_schedule', {}, { default: 'Выберите расписание' }) }}
            </v-card-title>
            <v-card-text>
              <v-select
                v-model="selectedScheduleId"
                :items="schedules"
                item-title="label"
                item-value="id"
                :label="t('teacher.schedule_group_subject', {}, { default: 'Расписание (группа - предмет)' })"
                variant="outlined"
                density="compact"
                prepend-inner-icon="mdi-school"
                @update:model-value="loadGrades"
                clearable
                :loading="loading"
                :disabled="loading"
              >
                <template v-slot:item="{ props, item }">
                  <v-list-item v-bind="props">
                    <v-list-item-title>{{ item.raw.label }}</v-list-item-title>
                    <v-list-item-subtitle>
                      {{ t('teacher.semester', {}, { default: 'Семестр' }) }} {{ item.raw.semester }} • {{ item.raw.study_year }}
                    </v-list-item-subtitle>
                  </v-list-item>
                </template>
              </v-select>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Статус рейтингов -->
        <v-col cols="12" md="6" v-if="selectedScheduleId && students.length > 0">
          <v-card variant="outlined">
            <v-card-title>
              <v-icon start>mdi-chart-timeline-variant</v-icon>
              {{ t('teacher.rating_status', {}, { default: 'Статус рейтингов' }) }}
            </v-card-title>
            <v-card-text>
              <div class="d-flex gap-4">
                <v-chip
                  :color="rating1Closed ? 'success' : 'warning'"
                  variant="flat"
                  size="large"
                >
                  <v-icon start>{{ rating1Closed ? 'mdi-lock' : 'mdi-lock-open' }}</v-icon>
                  {{ t('teacher.rating_1', {}, { default: 'Рейтинг 1' }) }}: 
                  {{ rating1Closed ? t('teacher.closed', {}, { default: 'Закрыт' }) : t('teacher.open', {}, { default: 'Открыт' }) }}
                </v-chip>
                <v-chip
                  :color="rating2Closed ? 'success' : (rating1Closed ? 'warning' : 'grey')"
                  variant="flat"
                  size="large"
                >
                  <v-icon start>{{ rating2Closed ? 'mdi-lock' : 'mdi-lock-open' }}</v-icon>
                  {{ t('teacher.rating_2', {}, { default: 'Рейтинг 2' }) }}: 
                  {{ rating2Closed ? t('teacher.closed', {}, { default: 'Закрыт' }) : t('teacher.open', {}, { default: 'Открыт' }) }}
                </v-chip>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Индикатор загрузки -->
      <v-row v-if="loading">
        <v-col cols="12">
          <v-card>
            <v-card-text class="text-center py-8">
              <v-progress-circular indeterminate color="primary" size="48" class="mb-4"></v-progress-circular>
              <h3 class="text-h6">{{ t('messages.loading', {}, { default: 'Загрузка...' }) }}</h3>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Таблица оценок -->
      <v-row v-if="!loading && selectedScheduleId && students.length > 0">
        <v-col cols="12">
          <v-card>
            <v-card-title>
              <div class="d-flex align-center">
                <v-icon start>mdi-table</v-icon>
                {{ t('teacher.grades_students', {}, { default: 'Оценки студентов' }) }}
                <v-chip class="ml-3" color="primary" size="small">
                  {{ selectedSchedule?.group_name }}
                </v-chip>
                <v-chip class="ml-2" color="secondary" size="small">
                  {{ selectedSchedule?.subject_name }}
                </v-chip>
                <v-spacer></v-spacer>
                <!-- Текущий этап -->
                <v-chip 
                  v-if="!rating1Closed" 
                  color="warning" 
                  variant="elevated"
                  prepend-icon="mdi-pencil"
                >
                  {{ t('teacher.entering_rating_1', {}, { default: 'Ввод Рейтинга 1' }) }}
                </v-chip>
                <v-chip 
                  v-else-if="!rating2Closed" 
                  color="info" 
                  variant="elevated"
                  prepend-icon="mdi-pencil"
                >
                  {{ t('teacher.entering_rating_2', {}, { default: 'Ввод Рейтинга 2' }) }}
                </v-chip>
                <v-chip 
                  v-else 
                  color="success" 
                  variant="elevated"
                  prepend-icon="mdi-check"
                >
                  {{ t('teacher.all_ratings_closed', {}, { default: 'Все рейтинги закрыты' }) }}
                </v-chip>
              </div>
            </v-card-title>
            <v-card-text>
              <v-table>
                <thead>
                  <tr>
                    <th class="text-left" style="width: 50px;">#</th>
                    <th class="text-left">{{ t('teacher.student', {}, { default: 'Студент' }) }}</th>
                    <th class="text-center" :class="{ 'bg-warning-lighten-4': !rating1Closed }">
                      {{ t('teacher.rating_teacher_1', {}, { default: 'Рейтинг 1' }) }}
                      <br><span class="text-caption text-grey">({{ t('teacher.teacher_label', {}, { default: 'учитель' }) }})</span>
                      <v-icon v-if="rating1Closed" size="small" color="success" class="ml-1">mdi-lock</v-icon>
                    </th>
                    <th class="text-center" :class="{ 'bg-info-lighten-4': rating1Closed && !rating2Closed }">
                      {{ t('teacher.rating_teacher_2', {}, { default: 'Рейтинг 2' }) }}
                      <br><span class="text-caption text-grey">({{ t('teacher.teacher_label', {}, { default: 'учитель' }) }})</span>
                      <v-icon v-if="rating2Closed" size="small" color="success" class="ml-1">mdi-lock</v-icon>
                    </th>
                    <th class="text-center">{{ t('teacher.rating_test_1', {}, { default: 'Рейт. тест 1' }) }}<br><span class="text-caption text-grey">({{ t('teacher.test_label', {}, { default: 'тест' }) }})</span></th>
                    <th class="text-center">{{ t('teacher.rating_test_2', {}, { default: 'Рейт. тест 2' }) }}<br><span class="text-caption text-grey">({{ t('teacher.test_label', {}, { default: 'тест' }) }})</span></th>
                    <th class="text-center">{{ t('teacher.exam', {}, { default: 'Экзамен' }) }}<br><span class="text-caption text-grey">({{ t('teacher.test_label', {}, { default: 'тест' }) }})</span></th>
                    <th class="text-center">{{ t('teacher.final_100', {}, { default: 'Итоговая' }) }}<br><span class="text-caption text-grey">({{ t('teacher.points_100', {}, { default: '100 баллов' }) }})</span></th>
                    <th class="text-center">{{ t('teacher.final_letter', {}, { default: 'Итоговая' }) }}<br><span class="text-caption text-grey">({{ t('teacher.letter', {}, { default: 'буква' }) }})</span></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(student, index) in students" :key="student.id">
                    <td class="text-grey">{{ index + 1 }}</td>
                    <td>
                      <div class="font-weight-medium">
                        {{ student.last_name }} {{ student.name }}
                      </div>
                    </td>
                    <!-- Рейтинг 1 -->
                    <td :class="{ 'bg-warning-lighten-5': !rating1Closed }">
                      <v-text-field
                        v-if="!rating1Closed"
                        v-model.number="student.rating_teacher_1"
                        type="number"
                        min="0"
                        max="100"
                        step="0.1"
                        variant="outlined"
                        density="compact"
                        hide-details
                        class="grade-input"
                        placeholder="0-100"
                      ></v-text-field>
                      <v-chip
                        v-else
                        :color="getGradeColor(student.rating_teacher_1)"
                        size="small"
                        variant="flat"
                      >
                        {{ student.rating_teacher_1 !== null ? student.rating_teacher_1.toFixed(1) : '-' }}
                      </v-chip>
                    </td>
                    <!-- Рейтинг 2 -->
                    <td :class="{ 'bg-info-lighten-5': rating1Closed && !rating2Closed }">
                      <v-text-field
                        v-if="rating1Closed && !rating2Closed"
                        v-model.number="student.rating_teacher_2"
                        type="number"
                        min="0"
                        max="100"
                        step="0.1"
                        variant="outlined"
                        density="compact"
                        hide-details
                        class="grade-input"
                        placeholder="0-100"
                      ></v-text-field>
                      <v-chip
                        v-else-if="rating2Closed"
                        :color="getGradeColor(student.rating_teacher_2)"
                        size="small"
                        variant="flat"
                      >
                        {{ student.rating_teacher_2 !== null ? student.rating_teacher_2.toFixed(1) : '-' }}
                      </v-chip>
                      <span v-else class="text-grey">-</span>
                    </td>
                    <td class="text-center">
                      <v-chip
                        :color="getGradeColor(student.rating_test_1)"
                        size="small"
                        variant="tonal"
                      >
                        {{ student.rating_test_1 !== null ? student.rating_test_1.toFixed(1) : '-' }}
                      </v-chip>
                    </td>
                    <td class="text-center">
                      <v-chip
                        :color="getGradeColor(student.rating_test_2)"
                        size="small"
                        variant="tonal"
                      >
                        {{ student.rating_test_2 !== null ? student.rating_test_2.toFixed(1) : '-' }}
                      </v-chip>
                    </td>
                    <td class="text-center">
                      <v-chip
                        :color="getGradeColor(student.final_exam_grade)"
                        size="small"
                        variant="tonal"
                      >
                        {{ student.final_exam_grade !== null ? student.final_exam_grade.toFixed(1) : '-' }}
                      </v-chip>
                    </td>
                    <td class="text-center">
                      <v-chip
                        :color="getGradeColor(student.final_grade_100)"
                        size="small"
                        variant="flat"
                        class="font-weight-bold"
                      >
                        {{ student.final_grade_100 !== null ? student.final_grade_100.toFixed(1) : '-' }}
                      </v-chip>
                    </td>
                    <td class="text-center">
                      <v-chip
                        :color="getGradeColor(student.final_grade_100)"
                        size="small"
                        variant="flat"
                        class="font-weight-bold"
                      >
                        {{ student.final_grade_letter || '-' }}
                      </v-chip>
                    </td>
                  </tr>
                </tbody>
              </v-table>

              <!-- Блок сохранения -->
              <v-divider class="my-4"></v-divider>
              
              <div v-if="!rating1Closed || !rating2Closed">
                <!-- Предупреждение -->
                <v-alert
                  type="warning"
                  variant="tonal"
                  class="mb-4"
                  icon="mdi-alert"
                >
                  <strong>{{ t('teacher.attention', {}, { default: 'Внимание!' }) }}</strong>
                  <span v-if="!rating1Closed">
                    {{ t('teacher.rating1_close_warning', {}, { default: 'После сохранения Рейтинг 1 будет закрыт и вы не сможете изменить оценки.' }) }}
                  </span>
                  <span v-else>
                    {{ t('teacher.rating2_close_warning', {}, { default: 'После сохранения Рейтинг 2 будет закрыт и вы не сможете изменить оценки.' }) }}
                  </span>
                </v-alert>

                <!-- Чекбокс подтверждения -->
                <v-checkbox
                  v-model="confirmSave"
                  color="primary"
                  class="mb-4"
                >
                  <template v-slot:label>
                    <span class="text-body-1">
                      {{ t('teacher.confirm_grades_correct', {}, { default: 'Я подтверждаю, что все оценки введены правильно и хочу сохранить их' }) }}
                    </span>
                  </template>
                </v-checkbox>

                <!-- Статистика -->
                <v-alert
                  v-if="!allGradesFilled"
                  type="info"
                  variant="tonal"
                  class="mb-4"
                  icon="mdi-information"
                >
                  {{ t('teacher.grades_not_filled', {}, { default: 'Не все оценки заполнены.' }) }}
                  {{ t('teacher.filled_count', {}, { default: 'Заполнено' }) }}: {{ filledGradesCount }} {{ t('teacher.of', {}, { default: 'из' }) }} {{ students.length }}
                </v-alert>

                <!-- Кнопка сохранения -->
                <div class="d-flex justify-end">
                  <v-btn
                    color="primary"
                    size="large"
                    :disabled="!confirmSave || !allGradesFilled"
                    :loading="saving"
                    @click="saveAndCloseRating"
                    prepend-icon="mdi-content-save-check"
                  >
                    <span v-if="!rating1Closed">
                      {{ t('teacher.save_and_close_rating_1', {}, { default: 'Сохранить и закрыть Рейтинг 1' }) }}
                    </span>
                    <span v-else>
                      {{ t('teacher.save_and_close_rating_2', {}, { default: 'Сохранить и закрыть Рейтинг 2' }) }}
                    </span>
                  </v-btn>
                </div>
              </div>

              <!-- Все рейтинги закрыты -->
              <v-alert
                v-else
                type="success"
                variant="tonal"
                icon="mdi-check-circle"
              >
                {{ t('teacher.all_ratings_completed', {}, { default: 'Все рейтинги успешно закрыты. Оценки больше нельзя редактировать.' }) }}
              </v-alert>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Пустое состояние -->
      <v-row v-else-if="!loading && selectedScheduleId && students.length === 0">
        <v-col cols="12">
          <v-card>
            <v-card-text class="text-center py-8">
              <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-account-group-outline</v-icon>
              <h3 class="text-h6 mb-2">{{ t('teacher.students_not_found', {}, { default: 'Студенты не найдены' }) }}</h3>
              <p class="text-body-2 text-grey">{{ t('teacher.no_students_in_group', {}, { default: 'В выбранной группе нет студентов' }) }}</p>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Инструкция -->
      <v-row v-else-if="!loading && !selectedScheduleId">
        <v-col cols="12">
          <v-card variant="outlined">
            <v-card-text class="text-center py-8">
              <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-calendar-clock-outline</v-icon>
              <h3 class="text-h6 mb-2">{{ t('teacher.select_schedule', {}, { default: 'Выберите расписание' }) }}</h3>
              <p class="text-body-2 text-grey">{{ t('teacher.select_schedule_instruction', {}, { default: 'Выберите расписание из списка выше, чтобы увидеть студентов и управлять оценками' }) }}</p>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
    
    <!-- Snackbar для уведомлений -->
    <v-snackbar
      v-model="snackbar"
      :color="snackbarColor"
      :timeout="3000"
      location="top right"
    >
      {{ snackbarText }}
      <template v-slot:actions>
        <v-btn
          variant="text"
          @click="snackbar = false"
        >
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </template>
    </v-snackbar>

    <!-- Диалог подтверждения -->
    <v-dialog v-model="confirmDialog" max-width="500">
      <v-card>
        <v-card-title class="text-h6">
          <v-icon start color="warning">mdi-alert</v-icon>
          {{ t('teacher.confirm_action', {}, { default: 'Подтвердите действие' }) }}
        </v-card-title>
        <v-card-text>
          <p class="text-body-1">
            {{ t('teacher.confirm_close_rating', {}, { default: 'Вы уверены, что хотите закрыть рейтинг? После этого вы не сможете изменить оценки.' }) }}
          </p>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="grey"
            variant="text"
            @click="confirmDialog = false"
          >
            {{ t('messages.cancel', {}, { default: 'Отмена' }) }}
          </v-btn>
          <v-btn
            color="primary"
            variant="flat"
            :loading="saving"
            @click="confirmAndSave"
          >
            {{ t('teacher.confirm_and_save', {}, { default: 'Подтвердить и сохранить' }) }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </Layout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'
import axios from 'axios'

const page = usePage()
const { t } = useI18n()

// Props
const props = defineProps({
  schedules: {
    type: Array,
    default: () => []
  }
})

// Состояние
const selectedScheduleId = ref(null)
const students = ref([])
const selectedSchedule = ref(null)
const loading = ref(false)
const saving = ref(false)
const snackbar = ref(false)
const snackbarText = ref('')
const snackbarColor = ref('success')
const confirmSave = ref(false)
const confirmDialog = ref(false)
const rating1Closed = ref(false)
const rating2Closed = ref(false)

// Вычисляемые свойства
const schedules = computed(() => {
  return props.schedules.map(schedule => ({
    ...schedule,
    label: `${schedule.group_name} - ${schedule.subject_name}`
  }))
})

// Подсчёт заполненных оценок
const filledGradesCount = computed(() => {
  if (!rating1Closed.value) {
    return students.value.filter(s => s.rating_teacher_1 !== null && s.rating_teacher_1 !== '' && s.rating_teacher_1 !== undefined).length
  } else {
    return students.value.filter(s => s.rating_teacher_2 !== null && s.rating_teacher_2 !== '' && s.rating_teacher_2 !== undefined).length
  }
})

// Все оценки заполнены
const allGradesFilled = computed(() => {
  return filledGradesCount.value === students.value.length
})

// Сбросить чекбокс при переключении расписания
watch(selectedScheduleId, () => {
  confirmSave.value = false
})

// Методы
const loadGrades = async () => {
  if (!selectedScheduleId.value) {
    students.value = []
    selectedSchedule.value = null
    rating1Closed.value = false
    rating2Closed.value = false
    return
  }

  loading.value = true
  students.value = []
  selectedSchedule.value = null
  confirmSave.value = false
  
  try {
    const response = await axios.get(`/teacher/grades/schedule/${selectedScheduleId.value}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.data.success) {
      students.value = response.data.students || []
      selectedSchedule.value = response.data.schedule
      rating1Closed.value = response.data.rating1_closed || false
      rating2Closed.value = response.data.rating2_closed || false
    }
  } catch (error) {
    console.error('Ошибка при загрузке оценок:', error)
    showSnackbar(t('teacher.error_loading_grades', {}, { default: 'Ошибка при загрузке оценок' }), 'error')
  } finally {
    loading.value = false
  }
}

const saveAndCloseRating = () => {
  confirmDialog.value = true
}

const confirmAndSave = async () => {
  saving.value = true
  
  try {
    const ratingNumber = !rating1Closed.value ? 1 : 2
    const grades = students.value.map(student => ({
      grade_id: student.grade_id,
      rating_teacher_1: student.rating_teacher_1,
      rating_teacher_2: student.rating_teacher_2,
    }))

    const response = await axios.post(`/teacher/grades/save-rating`, {
      schedule_id: selectedScheduleId.value,
      rating_number: ratingNumber,
      grades: grades
    }, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })

    if (response.data.success) {
      if (ratingNumber === 1) {
        rating1Closed.value = true
      } else {
        rating2Closed.value = true
      }
      confirmSave.value = false
      confirmDialog.value = false
      showSnackbar(t('teacher.rating_saved_and_closed', {}, { default: 'Рейтинг сохранён и закрыт' }), 'success')
      
      // Перезагружаем данные
      await loadGrades()
    }
  } catch (error) {
    console.error('Ошибка при сохранении:', error)
    showSnackbar(t('teacher.error_saving_grades', {}, { default: 'Ошибка при сохранении оценок' }), 'error')
  } finally {
    saving.value = false
    confirmDialog.value = false
  }
}

const showSnackbar = (text, color = 'success') => {
  snackbarText.value = text
  snackbarColor.value = color
  snackbar.value = true
}

const getGradeColor = (grade) => {
  if (grade === null || grade === undefined) {
    return 'grey'
  }
  const numGrade = parseFloat(grade)
  if (numGrade >= 90) return 'success'
  if (numGrade >= 75) return 'info'
  if (numGrade >= 60) return 'warning'
  return 'error'
}
</script>

<style scoped>
.grade-input {
  max-width: 100px;
  margin: 0 auto;
}

.v-table th {
  font-weight: 600;
  background-color: rgb(var(--v-theme-surface));
}

.bg-warning-lighten-4 {
  background-color: rgb(var(--v-theme-warning), 0.15) !important;
}

.bg-warning-lighten-5 {
  background-color: rgb(var(--v-theme-warning), 0.08) !important;
}

.bg-info-lighten-4 {
  background-color: rgb(var(--v-theme-info), 0.15) !important;
}

.bg-info-lighten-5 {
  background-color: rgb(var(--v-theme-info), 0.08) !important;
}
</style>
