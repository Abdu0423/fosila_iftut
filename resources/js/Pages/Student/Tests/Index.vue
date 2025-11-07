<template>
  <Layout role="student">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <h1 class="text-h4 font-weight-bold mb-2">Мои экзамены</h1>
          <p class="text-body-1 text-medium-emphasis">Периодические и итоговые экзамены</p>
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

      <v-row v-if="page.props.flash?.error">
        <v-col cols="12">
          <v-alert type="error" variant="tonal" closable>
            {{ page.props.flash.error }}
          </v-alert>
        </v-col>
      </v-row>

      <!-- Список экзаменов по расписаниям -->
      <v-row v-if="examsBySchedule.length === 0">
        <v-col cols="12">
          <v-card>
            <v-card-text class="text-center py-8">
              <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-school-outline</v-icon>
              <p class="text-h6 text-medium-emphasis">Нет доступных экзаменов</p>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <v-row v-for="(examGroup, index) in examsBySchedule" :key="index">
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h6 bg-primary text-white">
              <v-icon start>mdi-book-open-variant</v-icon>
              {{ examGroup.schedule.subject_name }} - {{ examGroup.schedule.group_name }}
            </v-card-title>
            <v-card-text>
              <!-- Периодические экзамены -->
              <div v-if="examGroup.periodic_exams.length > 0" class="mb-4">
                <h3 class="text-h6 mb-3">Периодические экзамены</h3>
                <v-row>
                  <v-col
                    v-for="exam in examGroup.periodic_exams"
                    :key="exam.id"
                    cols="12"
                    md="6"
                  >
                    <v-card variant="outlined" class="h-100">
                      <v-card-title class="text-subtitle-1">
                        {{ exam.title }}
                      </v-card-title>
                      <v-card-text>
                        <div class="mb-2">
                          <v-chip
                            :color="exam.is_passed ? 'success' : 'warning'"
                            size="small"
                            class="mr-2"
                          >
                            {{ exam.is_passed ? 'Пройден' : 'Не пройден' }}
                          </v-chip>
                          <v-chip
                            v-if="exam.best_score !== null"
                            color="primary"
                            size="small"
                          >
                            {{ exam.best_score }}%
                          </v-chip>
                        </div>
                        <div class="text-body-2 text-medium-emphasis">
                          <div>Попыток использовано: {{ exam.attempts_count }} / {{ exam.max_attempts }}</div>
                          <div v-if="exam.remaining_attempts > 0">
                            Осталось попыток: {{ exam.remaining_attempts }}
                          </div>
                          <div v-else class="text-error">
                            Все попытки использованы
                          </div>
                          <div v-if="exam.last_attempt_at">
                            Последняя попытка: {{ exam.last_attempt_at }}
                          </div>
                        </div>
                      </v-card-text>
                      <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                          color="primary"
                          :disabled="exam.remaining_attempts === 0"
                          @click="startExam(exam.id)"
                        >
                          <v-icon start>mdi-play</v-icon>
                          Начать экзамен
                        </v-btn>
                      </v-card-actions>
                    </v-card>
                  </v-col>
                </v-row>
              </div>

              <!-- Итоговый экзамен -->
              <div v-if="examGroup.final_exam">
                <h3 class="text-h6 mb-3">Итоговый экзамен</h3>
                <v-card variant="outlined">
                  <v-card-title class="text-subtitle-1">
                    {{ examGroup.final_exam.title }}
                  </v-card-title>
                  <v-card-text>
                    <div class="mb-2">
                      <v-chip
                        :color="examGroup.final_exam.is_passed ? 'success' : 'warning'"
                        size="small"
                        class="mr-2"
                      >
                        {{ examGroup.final_exam.is_passed ? 'Пройден' : 'Не пройден' }}
                      </v-chip>
                      <v-chip
                        v-if="examGroup.final_exam.best_score !== null"
                        color="primary"
                        size="small"
                      >
                        {{ examGroup.final_exam.best_score }}%
                      </v-chip>
                    </div>
                    <div class="text-body-2 text-medium-emphasis">
                      <div>Попыток использовано: {{ examGroup.final_exam.attempts_count }} / {{ examGroup.final_exam.max_attempts }}</div>
                      <div v-if="examGroup.final_exam.remaining_attempts > 0">
                        Осталось попыток: {{ examGroup.final_exam.remaining_attempts }}
                      </div>
                      <div v-else class="text-error">
                        Все попытки использованы
                      </div>
                      <div v-if="examGroup.final_exam.last_attempt_at">
                        Последняя попытка: {{ examGroup.final_exam.last_attempt_at }}
                      </div>
                    </div>
                  </v-card-text>
                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                      color="primary"
                      :disabled="examGroup.final_exam.remaining_attempts === 0"
                      @click="startExam(examGroup.final_exam.id)"
                    >
                      <v-icon start>mdi-play</v-icon>
                      Начать экзамен
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { usePage, router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const page = usePage()

defineProps({
  examsBySchedule: {
    type: Array,
    default: () => []
  }
})

const startExam = (testId) => {
  router.get(`/student/tests/${testId}`)
}
</script>

