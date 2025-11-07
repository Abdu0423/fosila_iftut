<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Оценки студентов</h1>
              <p class="text-body-1 text-medium-emphasis">Выберите расписание для управления оценками</p>
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
              Выберите расписание
            </v-card-title>
            <v-card-text>
              <v-select
                v-model="selectedScheduleId"
                :items="schedules"
                item-title="label"
                item-value="id"
                label="Расписание (группа - предмет)"
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
                      Семестр {{ item.raw.semester }} • {{ item.raw.study_year }}
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
                Оценки студентов
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
                    <th class="text-left">Студент</th>
                    <th class="text-center">Оценка 1<br><span class="text-caption text-grey">(учитель)</span></th>
                    <th class="text-center">Оценка 2<br><span class="text-caption text-grey">(учитель)</span></th>
                    <th class="text-center">Оценка 3<br><span class="text-caption text-grey">(тест)</span></th>
                    <th class="text-center">Оценка 4<br><span class="text-caption text-grey">(тест)</span></th>
                    <th class="text-center">Экзамен<br><span class="text-caption text-grey">(тест)</span></th>
                    <th class="text-center">Средняя</th>
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
                        v-model.number="student.grade_1"
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
                        v-model.number="student.grade_2"
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
                        :color="getGradeColor(student.grade_3)"
                        size="small"
                        variant="tonal"
                      >
                        {{ student.grade_3 !== null ? student.grade_3 : '-' }}
                      </v-chip>
                    </td>
                    <td class="text-center">
                      <v-chip
                        :color="getGradeColor(student.grade_4)"
                        size="small"
                        variant="tonal"
                      >
                        {{ student.grade_4 !== null ? student.grade_4 : '-' }}
                      </v-chip>
                    </td>
                    <td class="text-center">
                      <v-chip
                        :color="getGradeColor(student.grade_5)"
                        size="small"
                        variant="tonal"
                      >
                        {{ student.grade_5 !== null ? student.grade_5 : '-' }}
                      </v-chip>
                    </td>
                    <td class="text-center">
                      <v-chip
                        :color="getGradeColor(getAverageGrade(student))"
                        size="small"
                        variant="flat"
                        class="font-weight-bold"
                      >
                        {{ getAverageGrade(student).toFixed(2) }}
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
              <h3 class="text-h6 mb-2">Студенты не найдены</h3>
              <p class="text-body-2 text-grey">В выбранной группе нет студентов</p>
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
              <h3 class="text-h6 mb-2">Выберите расписание</h3>
              <p class="text-body-2 text-grey">Выберите расписание из списка выше, чтобы увидеть студентов и управлять оценками</p>
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
import Layout from '../../Layout.vue'
import axios from 'axios'

const page = usePage()

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
    alert('Ошибка при загрузке оценок: ' + (error.response?.data?.message || error.message))
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
      grade_1: student.grade_1,
      grade_2: student.grade_2,
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
        students.value[index] = {
          ...students.value[index],
          grade_1: response.data.grade.grade_1,
          grade_2: response.data.grade.grade_2,
        }
      }
    }
  } catch (error) {
    console.error('Ошибка при обновлении оценки:', error)
    alert('Ошибка при обновлении оценки: ' + (error.response?.data?.message || error.message))
  }
}

const getAverageGrade = (student) => {
  const grades = [
    student.grade_1,
    student.grade_2,
    student.grade_3,
    student.grade_4,
    student.grade_5,
  ].filter(grade => grade !== null && grade !== undefined)

  if (grades.length === 0) {
    return 0
  }

  return grades.reduce((sum, grade) => sum + parseFloat(grade), 0) / grades.length
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
