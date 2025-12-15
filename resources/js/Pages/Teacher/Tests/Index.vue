<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Тесты расписаний</h1>
              <p class="text-body-1 text-medium-emphasis">Каждое расписание имеет свой тест</p>
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
              <div class="text-body-2 text-medium-emphasis">Всего расписаний</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="4">
          <v-card variant="outlined">
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold text-success">{{ stats.with_tests }}</div>
              <div class="text-body-2 text-medium-emphasis">С тестами</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="4">
          <v-card variant="outlined">
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold text-warning">{{ stats.without_tests }}</div>
              <div class="text-body-2 text-medium-emphasis">Без тестов</div>
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
                    label="Поиск по предмету или группе"
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
                      { title: 'Все семестры', value: '' },
                      { title: 'Семестр 1', value: 1 },
                      { title: 'Семестр 2', value: 2 }
                    ]"
                    item-title="title"
                    item-value="value"
                    label="Семестр"
                    variant="outlined"
                    density="compact"
                  ></v-select>
                </v-col>
                <v-col cols="12" md="3">
                  <v-select
                    v-model="selectedStatus"
                    :items="[
                      { title: 'Все', value: '' },
                      { title: 'С тестами', value: 'with_test' },
                      { title: 'Без тестов', value: 'without_test' },
                      { title: 'Активные тесты', value: 'active' },
                      { title: 'Неактивные тесты', value: 'inactive' }
                    ]"
                    item-title="title"
                    item-value="value"
                    label="Статус"
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
              Расписания ({{ filteredSchedules.length }})
            </v-card-title>
            <v-card-text>
              <div v-if="filteredSchedules.length === 0" class="text-center py-8">
                <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-calendar-text-outline</v-icon>
                <h3 class="text-h6 text-grey">Нет расписаний</h3>
                <p class="text-body-2 text-grey">Обратитесь к администратору для создания расписания</p>
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
                          Семестр {{ schedule.semester }}
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
                          Тест: {{ schedule.test.is_active ? 'Активен' : 'Неактивен' }}
                        </v-chip>
                        
                        <v-chip
                          v-else
                          color="grey"
                          size="small"
                          variant="tonal"
                        >
                          Тест не создан
                        </v-chip>
                      </div>
                      
                      <div v-if="schedule.has_test" class="d-flex align-center gap-4 text-caption text-medium-emphasis mb-2">
                        <span>
                          <v-icon size="16" class="mr-1">mdi-clipboard-text</v-icon>
                          {{ schedule.test.title }}
                        </span>
                        <span v-if="schedule.test.questions_count > 0">
                          <v-icon size="16" class="mr-1">mdi-help-circle</v-icon>
                          {{ schedule.test.questions_count }} вопросов
                        </span>
                        <span v-if="schedule.test.time_limit">
                          <v-icon size="16" class="mr-1">mdi-clock</v-icon>
                          {{ schedule.test.time_limit }} мин.
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
                        Просмотр
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
import Layout from '../../Layout.vue'

const page = usePage()

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
