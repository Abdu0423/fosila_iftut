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
      </v-row>

      <!-- Таблица оценок -->
      <v-row v-if="selectedScheduleId && students.length > 0">
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
              </div>
            </v-card-title>
            <v-card-text>
              <v-table>
                <thead>
                  <tr>
                    <th class="text-left">{{ t('teacher.student', {}, { default: 'Студент' }) }}</th>
                    <th class="text-center">{{ t('teacher.rating_teacher_1', {}, { default: 'Рейт. учитель 1' }) }}<br><span class="text-caption text-grey">({{ t('teacher.teacher_label', {}, { default: 'учитель' }) }})</span></th>
                    <th class="text-center">{{ t('teacher.rating_teacher_2', {}, { default: 'Рейт. учитель 2' }) }}<br><span class="text-caption text-grey">({{ t('teacher.teacher_label', {}, { default: 'учитель' }) }})</span></th>
                    <th class="text-center">{{ t('teacher.rating_test_1', {}, { default: 'Рейт. тест 1' }) }}<br><span class="text-caption text-grey">({{ t('teacher.test_label', {}, { default: 'тест' }) }})</span></th>
                    <th class="text-center">{{ t('teacher.rating_test_2', {}, { default: 'Рейт. тест 2' }) }}<br><span class="text-caption text-grey">({{ t('teacher.test_label', {}, { default: 'тест' }) }})</span></th>
                    <th class="text-center">{{ t('teacher.exam', {}, { default: 'Экзамен' }) }}<br><span class="text-caption text-grey">({{ getExamType(student) }})</span></th>
                    <th class="text-center">{{ t('teacher.final_100', {}, { default: 'Итоговая' }) }}<br><span class="text-caption text-grey">({{ t('teacher.points_100', {}, { default: '100 баллов' }) }})</span></th>
                    <th class="text-center">{{ t('teacher.final_10', {}, { default: 'Итоговая' }) }}<br><span class="text-caption text-grey">({{ t('teacher.points_10', {}, { default: '10 баллов' }) }})</span></th>
                    <th class="text-center">{{ t('teacher.final_letter', {}, { default: 'Итоговая' }) }}<br><span class="text-caption text-grey">({{ t('teacher.letter', {}, { default: 'буква' }) }})</span></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="student in students" :key="student.id">
                    <td>
                      <div class="font-weight-medium">
                        {{ student.last_name }} {{ student.name }}
                      </div>
                      <div class="text-caption text-medium-emphasis">
                        {{ student.email }}
                      </div>
                    </td>
                    <td>
                      <v-text-field
                        v-model.number="student.rating_teacher_1"
                        type="number"
                        min="0"
                        max="100"
                        step="0.01"
                        variant="outlined"
                        density="compact"
                        hide-details
                        class="grade-input"
                        @blur="updateGrade(student)"
                      ></v-text-field>
                    </td>
                    <td>
                      <v-text-field
                        v-model.number="student.rating_teacher_2"
                        type="number"
                        min="0"
                        max="100"
                        step="0.01"
                        variant="outlined"
                        density="compact"
                        hide-details
                        class="grade-input"
                        @blur="updateGrade(student)"
                      ></v-text-field>
                    </td>
                    <td class="text-center">
                      <v-chip
                        :color="getGradeColor(student.rating_test_1)"
                        size="small"
                        variant="tonal"
                      >
                        {{ student.rating_test_1 !== null ? student.rating_test_1.toFixed(2) : '-' }}
                      </v-chip>
                    </td>
                    <td class="text-center">
                      <v-chip
                        :color="getGradeColor(student.rating_test_2)"
                        size="small"
                        variant="tonal"
                      >
                        {{ student.rating_test_2 !== null ? student.rating_test_2.toFixed(2) : '-' }}
                      </v-chip>
                    </td>
                    <td class="text-center">
                      <v-chip
                        :color="getGradeColor(student.final_exam_grade)"
                        size="small"
                        variant="tonal"
                      >
                        {{ student.final_exam_grade !== null ? student.final_exam_grade.toFixed(2) : '-' }}
                      </v-chip>
                    </td>
                    <td class="text-center">
                      <v-chip
                        :color="getGradeColor(student.final_grade_100)"
                        size="small"
                        variant="flat"
                        class="font-weight-bold"
                      >
                        {{ student.final_grade_100 !== null ? student.final_grade_100.toFixed(2) : '-' }}
                      </v-chip>
                    </td>
                    <td class="text-center">
                      <v-chip
                        :color="getGradeColor(student.final_grade_10 ? student.final_grade_10 * 10 : null)"
                        size="small"
                        variant="flat"
                        class="font-weight-bold"
                      >
                        {{ student.final_grade_10 !== null ? student.final_grade_10.toFixed(1) : '-' }}
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
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Пустое состояние -->
      <v-row v-else-if="selectedScheduleId && students.length === 0">
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
      <v-row v-else-if="!selectedScheduleId">
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
  </Layout>
</template>

<script setup>
import { ref, computed } from 'vue'
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

// Вычисляемые свойства
const schedules = computed(() => {
  return props.schedules.map(schedule => ({
    ...schedule,
    label: `${schedule.group_name} - ${schedule.subject_name}`
  }))
})

// Методы
const loadGrades = async () => {
  if (!selectedScheduleId.value) {
    students.value = []
    selectedSchedule.value = null
    return
  }

  loading.value = true
  try {
    const response = await axios.get(`/teacher/grades/schedule/${selectedScheduleId.value}`)
    if (response.data.success) {
      students.value = response.data.students
      selectedSchedule.value = response.data.schedule
    }
  } catch (error) {
    console.error('Ошибка при загрузке оценок:', error)
    alert(t('teacher.error_loading_grades', {}, { default: 'Ошибка при загрузке оценок' }) + ': ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const updateGrade = async (student) => {
  if (!student.grade_id) {
    return
  }

  try {
    const response = await axios.put(`/teacher/grades/${student.grade_id}`, {
      rating_teacher_1: student.rating_teacher_1,
      rating_teacher_2: student.rating_teacher_2,
    }, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    if (response.data.success) {
      // Обновляем данные студента
      const index = students.value.findIndex(s => s.id === student.id)
      if (index !== -1) {
        const updatedGrade = response.data.grade
        students.value[index] = {
          ...students.value[index],
          rating_teacher_1: updatedGrade.rating_teacher_1,
          rating_teacher_2: updatedGrade.rating_teacher_2,
          final_grade_100: updatedGrade.final_grade_100,
          final_grade_10: updatedGrade.final_grade_10,
          final_grade_letter: updatedGrade.final_grade_letter,
        }
      }
    }
  } catch (error) {
    console.error('Ошибка при обновлении оценки:', error)
    alert(t('teacher.error_updating_grade', {}, { default: 'Ошибка при обновлении оценки' }) + ': ' + (error.response?.data?.message || error.message))
  }
}

const getExamType = (student) => {
  if (student.final_exam_type === 'teacher') return t('teacher.teacher_label', {}, { default: 'учитель' })
  if (student.final_exam_type === 'test') return t('teacher.test_label', {}, { default: 'тест' })
  return '-'
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
</style>
