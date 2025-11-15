<template>
  <Layout>
    <v-container fluid>
      <div class="grades-page">
        <v-row>
        <v-col cols="12">
          <h1 class="text-h4 mb-6">Мои оценки</h1>
        </v-col>
      </v-row>
      
      <!-- Общая статистика -->
      <v-row>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold primary--text">{{ averageGrade }}</div>
              <div class="text-subtitle-1">Средний балл</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold success--text">{{ completedAssignments }}</div>
              <div class="text-subtitle-1">Завершенных заданий</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold warning--text">{{ pendingAssignments }}</div>
              <div class="text-subtitle-1">Ожидающих оценку</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold info--text">{{ totalCredits }}</div>
              <div class="text-subtitle-1">Заработано кредитов</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
      
      <!-- Фильтры -->
      <v-row class="mt-6">
        <v-col cols="12" md="6">
          <v-select
            v-model="selectedCourse"
            :items="courses"
            label="Курс"
            outlined
            dense
            clearable
          ></v-select>
        </v-col>
        <v-col cols="12" md="6">
          <v-select
            v-model="selectedSemester"
            :items="semesters"
            label="Семестр"
            outlined
            dense
            clearable
          ></v-select>
        </v-col>
      </v-row>
      
      <!-- Оценки по курсам -->
      <v-row>
        <v-col cols="12">
          <v-card v-for="course in filteredCourses" :key="course.id" class="mb-4">
            <v-card-title class="d-flex align-center">
              <v-icon class="mr-3" color="primary">mdi-book-open-variant</v-icon>
              {{ course.name }}
              <v-spacer></v-spacer>
              <v-chip :color="getGradeColor(course.averageGrade)" class="font-weight-bold">
                {{ course.averageGrade }}
              </v-chip>
            </v-card-title>
            
            <v-card-text>
              <v-data-table
                :headers="gradeHeaders"
                :items="course.assignments"
                :items-per-page="10"
                class="elevation-0"
                hide-default-footer
              >
                <template v-slot:item.grade="{ item }">
                  <v-chip :color="getGradeColor(item.grade)" small>
                    {{ item.grade }}
                  </v-chip>
                </template>
                
                <template v-slot:item.status="{ item }">
                  <v-chip :color="getStatusColor(item.status)" small>
                    {{ item.status }}
                  </v-chip>
                </template>
                
                <template v-slot:item.actions="{ item }">
                  <v-btn
                    icon
                    small
                    @click="viewAssignment(item)"
                  >
                    <v-icon>mdi-eye</v-icon>
                  </v-btn>
                </template>
              </v-data-table>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
      
      <!-- График прогресса -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title>Прогресс по семестрам</v-card-title>
            <v-card-text>
              <div style="height: 300px;">
                <!-- Здесь будет график -->
                <div class="text-center pa-8">
                  <v-icon size="64" color="grey">mdi-chart-line</v-icon>
                  <h3 class="text-h6 mt-4">График прогресса</h3>
                  <p class="text-grey">Здесь будет отображаться график вашего прогресса</p>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
      </div>
    </v-container>
    
    <!-- Диалог просмотра задания -->
    <v-dialog v-model="assignmentDialog" max-width="600">
      <v-card>
        <v-card-title>
          {{ selectedAssignment?.title }}
          <v-spacer></v-spacer>
          <v-btn icon @click="assignmentDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <div v-if="selectedAssignment">
            <v-list dense>
              <v-list-item>
                <v-list-item-title>Оценка</v-list-item-title>
                <v-list-item-subtitle>
                  <v-chip :color="getGradeColor(selectedAssignment.grade)">
                    {{ selectedAssignment.grade }}
                  </v-chip>
                </v-list-item-subtitle>
              </v-list-item>
              <v-list-item>
                <v-list-item-title>Статус</v-list-item-title>
                <v-list-item-subtitle>
                  <v-chip :color="getStatusColor(selectedAssignment.status)">
                    {{ selectedAssignment.status }}
                  </v-chip>
                </v-list-item-subtitle>
              </v-list-item>
              <v-list-item>
                <v-list-item-title>Дата сдачи</v-list-item-title>
                <v-list-item-subtitle>{{ selectedAssignment.submittedDate }}</v-list-item-subtitle>
              </v-list-item>
              <v-list-item>
                <v-list-item-title>Дата оценки</v-list-item-title>
                <v-list-item-subtitle>{{ selectedAssignment.gradedDate }}</v-list-item-subtitle>
              </v-list-item>
            </v-list>
            
            <v-divider class="my-4"></v-divider>
            
            <h3 class="text-h6 mb-3">Комментарий преподавателя</h3>
            <p>{{ selectedAssignment.comment || 'Комментарий отсутствует' }}</p>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>
  </Layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '../Layout.vue'

const props = defineProps({
  grades: Object,
  stats: Object,
  gradesBySemester: Object,
  semesters: Array,
  studyYears: Array,
  filters: Object
})

const selectedSemester = ref(props.filters?.semester || null)
const selectedStudyYear = ref(props.filters?.study_year || null)
const assignmentDialog = ref(false)
const selectedGrade = ref(null)

const gradeHeaders = [
  { title: 'Урок', key: 'schedule.lesson.name' },
  { title: 'Преподаватель', key: 'schedule.teacher.name' },
  { title: 'Оценка', key: 'grade' },
  { title: 'Дата', key: 'created_at' }
]

const averageGrade = computed(() => {
  return props.stats?.average_grade?.toFixed(1) || '0.0'
})

const completedAssignments = computed(() => {
  return props.stats?.total_grades || 0
})

const highestGrade = computed(() => {
  return props.stats?.highest_grade || 0
})

const lowestGrade = computed(() => {
  return props.stats?.lowest_grade || 0
})

const applyFilters = () => {
  router.get(route('student.grades.index'), {
    semester: selectedSemester.value,
    study_year: selectedStudyYear.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const getGradeColor = (grade) => {
  if (grade >= 85) return 'success'
  if (grade >= 70) return 'info'
  if (grade >= 50) return 'warning'
  return 'error'
}

const viewGrade = (grade) => {
  selectedGrade.value = grade
  assignmentDialog.value = true
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU')
}

const clearFilters = () => {
  selectedSemester.value = null
  selectedStudyYear.value = null
  applyFilters()
}
</script>
