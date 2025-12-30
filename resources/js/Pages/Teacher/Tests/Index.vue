<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('teacher.schedule_tests', {}, { default: 'Тесты расписаний' }) }}</h1>
              <p class="text-body-1 text-medium-emphasis">{{ t('teacher.each_schedule_has_test', {}, { default: 'Каждое расписание имеет свой тест' }) }}</p>
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

      <!-- Статистика -->
      <v-row>
        <v-col cols="12" md="4">
          <v-card variant="outlined">
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold text-primary">{{ stats.total_schedules }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ t('teacher.total_schedules', {}, { default: 'Всего расписаний' }) }}</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="4">
          <v-card variant="outlined">
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold text-success">{{ stats.with_tests }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ t('teacher.with_tests', {}, { default: 'С тестами' }) }}</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="4">
          <v-card variant="outlined">
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold text-warning">{{ stats.without_tests }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ t('teacher.without_tests', {}, { default: 'Без тестов' }) }}</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Фильтры -->
      <v-row>
        <v-col cols="12">
          <v-card variant="outlined">
            <v-card-text>
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="search"
                    :label="t('teacher.search_subject_group', {}, { default: 'Поиск по предмету или группе' })"
                    variant="outlined"
                    density="compact"
                    prepend-inner-icon="mdi-magnify"
                    clearable
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="3">
                  <v-select
                    v-model="selectedSemester"
                    :items="[
                      { title: t('teacher.all_semesters', {}, { default: 'Все семестры' }), value: '' },
                      { title: t('teacher.semester_1', {}, { default: 'Семестр 1' }), value: 1 },
                      { title: t('teacher.semester_2', {}, { default: 'Семестр 2' }), value: 2 }
                    ]"
                    item-title="title"
                    item-value="value"
                    :label="t('teacher.semester', {}, { default: 'Семестр' })"
                    variant="outlined"
                    density="compact"
                  ></v-select>
                </v-col>
                <v-col cols="12" md="3">
                  <v-select
                    v-model="selectedStatus"
                    :items="[
                      { title: t('teacher.all', {}, { default: 'Все' }), value: '' },
                      { title: t('teacher.with_test', {}, { default: 'С тестами' }), value: 'with_test' },
                      { title: t('teacher.without_test', {}, { default: 'Без тестов' }), value: 'without_test' },
                      { title: t('teacher.active_tests', {}, { default: 'Активные тесты' }), value: 'active' },
                      { title: t('teacher.inactive_tests', {}, { default: 'Неактивные тесты' }), value: 'inactive' }
                    ]"
                    item-title="title"
                    item-value="value"
                    :label="t('messages.status', {}, { default: 'Статус' })"
                    variant="outlined"
                    density="compact"
                  ></v-select>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Список расписаний -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-calendar-text</v-icon>
              {{ t('teacher.schedules_count', {}, { default: 'Расписания' }) }} ({{ filteredSchedules.length }})
            </v-card-title>
            <v-card-text>
              <div v-if="filteredSchedules.length === 0" class="text-center py-8">
                <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-calendar-text-outline</v-icon>
                <h3 class="text-h6 text-grey">{{ t('teacher.no_schedules_found', {}, { default: 'Нет расписаний' }) }}</h3>
                <p class="text-body-2 text-grey">{{ t('teacher.contact_admin_for_schedule', {}, { default: 'Обратитесь к администратору для создания расписания' }) }}</p>
              </div>
              
              <div v-else>
                <v-list>
                  <v-list-item
                    v-for="schedule in filteredSchedules"
                    :key="schedule.id"
                    class="mb-4"
                  >
                    <template v-slot:prepend>
                      <v-avatar :color="schedule.has_test ? 'success' : 'grey'" size="40">
                        <v-icon color="white">
                          {{ schedule.has_test ? 'mdi-clipboard-check' : 'mdi-clipboard-text-outline' }}
                        </v-icon>
                      </v-avatar>
                    </template>
                    
                    <v-list-item-title class="font-weight-medium mb-2">
                      {{ schedule.subject_name }}
                    </v-list-item-title>
                    
                    <v-list-item-subtitle>
                      <div class="d-flex align-center gap-4 mb-2">
                        <v-chip
                          color="primary"
                          size="small"
                          variant="tonal"
                        >
                          {{ schedule.group_name }}
                        </v-chip>
                        
                        <v-chip
                          color="secondary"
                          size="small"
                          variant="tonal"
                        >
                          {{ t('teacher.semester', {}, { default: 'Семестр' }) }} {{ schedule.semester }}
                        </v-chip>
                        
                        <v-chip
                          color="info"
                          size="small"
                          variant="tonal"
                        >
                          {{ schedule.study_year }}
                        </v-chip>
                        
                        <v-chip
                          v-if="schedule.has_test"
                          :color="schedule.test.is_active ? 'success' : 'warning'"
                          size="small"
                          variant="tonal"
                        >
                          {{ t('navigation.tests', {}, { default: 'Тест' }) }}: {{ schedule.test.is_active ? t('teacher.test_active', {}, { default: 'Активен' }) : t('teacher.test_inactive', {}, { default: 'Неактивен' }) }}
                        </v-chip>
                        
                        <v-chip
                          v-else
                          color="grey"
                          size="small"
                          variant="tonal"
                        >
                          {{ t('teacher.test_not_created', {}, { default: 'Тест не создан' }) }}
                        </v-chip>
                      </div>
                      
                      <div v-if="schedule.has_test" class="d-flex align-center gap-4 text-caption text-medium-emphasis mb-2">
                        <span>
                          <v-icon size="16" class="mr-1">mdi-clipboard-text</v-icon>
                          {{ schedule.test.title }}
                        </span>
                        <span v-if="schedule.test.questions_count > 0">
                          <v-icon size="16" class="mr-1">mdi-help-circle</v-icon>
                          {{ schedule.test.questions_count }} {{ t('teacher.questions', {}, { default: 'вопросов' }) }}
                        </span>
                        <span v-if="schedule.test.time_limit">
                          <v-icon size="16" class="mr-1">mdi-clock</v-icon>
                          {{ schedule.test.time_limit }} {{ t('teacher.minutes', {}, { default: 'мин.' }) }}
                        </span>
                        <span v-if="schedule.test.passing_score">
                          <v-icon size="16" class="mr-1">mdi-percent</v-icon>
                          {{ schedule.test.passing_score }}%
                        </span>
                      </div>
                    </v-list-item-subtitle>
                    
                    <template v-slot:append>
                      <v-btn
                        color="primary"
                        @click="openTest(schedule)"
                        variant="tonal"
                      >
                        <v-icon start>mdi-eye</v-icon>
                        {{ t('teacher.view', {}, { default: 'Просмотр' }) }}
                      </v-btn>
                    </template>
                  </v-list-item>
                </v-list>
              </div>
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

const page = usePage()
const { t } = useI18n()

// Props из Inertia
const props = defineProps({
  schedules: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({
      total_schedules: 0,
      with_tests: 0,
      without_tests: 0
    })
  }
})

// Состояние
const search = ref('')
const selectedSemester = ref('')
const selectedStatus = ref('')

// Вычисляемые свойства
const filteredSchedules = computed(() => {
  let filtered = props.schedules

  // Поиск по предмету или группе
  if (search.value) {
    filtered = filtered.filter(schedule =>
      schedule.subject_name.toLowerCase().includes(search.value.toLowerCase()) ||
      schedule.group_name.toLowerCase().includes(search.value.toLowerCase())
    )
  }

  // Фильтр по семестру
  if (selectedSemester.value) {
    filtered = filtered.filter(schedule => schedule.semester === selectedSemester.value)
  }

  // Фильтр по статусу
  if (selectedStatus.value === 'with_test') {
    filtered = filtered.filter(schedule => schedule.has_test)
  } else if (selectedStatus.value === 'without_test') {
    filtered = filtered.filter(schedule => !schedule.has_test)
  } else if (selectedStatus.value === 'active') {
    filtered = filtered.filter(schedule => schedule.has_test && schedule.test.is_active)
  } else if (selectedStatus.value === 'inactive') {
    filtered = filtered.filter(schedule => schedule.has_test && !schedule.test.is_active)
  }

  return filtered
})

// Методы
const openTest = (schedule) => {
  router.visit(`/teacher/schedules/${schedule.id}/test`)
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
