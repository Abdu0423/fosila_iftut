<template>
  <AdminApp>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Аналитика расписания</h1>
              <p class="text-body-1 text-medium-emphasis">Статистика и аналитика по расписанию занятий</p>
            </div>
            <v-btn
              color="secondary"
              variant="outlined"
              @click="navigateTo('/admin/schedules')"
              prepend-icon="mdi-arrow-left"
            >
              Назад к списку
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Основная статистика -->
      <v-row>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="primary" class="mb-4">mdi-calendar-clock</v-icon>
              <div class="text-h4 font-weight-bold">{{ totalSchedules }}</div>
              <div class="text-body-2 text-medium-emphasis">Всего записей</div>
            </v-card-text>
          </v-card>
        </v-col>
        
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="success" class="mb-4">mdi-check-circle</v-icon>
              <div class="text-h4 font-weight-bold">{{ activeSchedules }}</div>
              <div class="text-body-2 text-medium-emphasis">Активных</div>
            </v-card-text>
          </v-card>
        </v-col>
        
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="warning" class="mb-4">mdi-pause-circle</v-icon>
              <div class="text-h4 font-weight-bold">{{ inactiveSchedules }}</div>
              <div class="text-body-2 text-medium-emphasis">Неактивных</div>
            </v-card-text>
          </v-card>
        </v-col>
        
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="info" class="mb-4">mdi-percent</v-icon>
              <div class="text-h4 font-weight-bold">{{ activePercentage }}%</div>
              <div class="text-body-2 text-medium-emphasis">Активность</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Графики и диаграммы -->
      <v-row class="mt-6">
        <!-- Статистика по семестрам -->
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-chart-pie</v-icon>
              Распределение по семестрам
            </v-card-title>
            <v-card-text>
              <div v-for="stat in semesterStats" :key="stat.semester" class="mb-3">
                <div class="d-flex justify-space-between align-center">
                  <span class="text-body-1">{{ stat.semester }} семестр</span>
                  <span class="text-h6 font-weight-bold">{{ stat.count }}</span>
                </div>
                <v-progress-linear
                  :model-value="(stat.count / totalSchedules) * 100"
                  color="primary"
                  height="8"
                  rounded
                ></v-progress-linear>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Топ преподавателей -->
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-teach</v-icon>
              Топ преподавателей по нагрузке
            </v-card-title>
            <v-card-text>
              <div v-for="(stat, index) in teacherStats" :key="stat.teacher_id" class="mb-3">
                <div class="d-flex justify-space-between align-center">
                  <div class="d-flex align-center">
                    <v-chip size="small" color="primary" class="mr-2">{{ index + 1 }}</v-chip>
                    <span class="text-body-1">{{ stat.teacher?.name }}</span>
                  </div>
                  <span class="text-h6 font-weight-bold">{{ stat.count }}</span>
                </div>
                <v-progress-linear
                  :model-value="(stat.count / Math.max(...teacherStats.map(s => s.count))) * 100"
                  color="success"
                  height="6"
                  rounded
                ></v-progress-linear>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Дополнительная статистика -->
      <v-row class="mt-6">
        <!-- Топ групп -->
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-account-group</v-icon>
              Топ групп по количеству занятий
            </v-card-title>
            <v-card-text>
              <div v-for="(stat, index) in groupStats" :key="stat.group_id" class="mb-3">
                <div class="d-flex justify-space-between align-center">
                  <div class="d-flex align-center">
                    <v-chip size="small" color="warning" class="mr-2">{{ index + 1 }}</v-chip>
                    <span class="text-body-1">{{ stat.group?.name }}</span>
                  </div>
                  <span class="text-h6 font-weight-bold">{{ stat.count }}</span>
                </div>
                <v-progress-linear
                  :model-value="(stat.count / Math.max(...groupStats.map(s => s.count))) * 100"
                  color="warning"
                  height="6"
                  rounded
                ></v-progress-linear>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Статистика по месяцам -->
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-calendar-month</v-icon>
              Распределение по месяцам
            </v-card-title>
            <v-card-text>
              <div v-for="stat in monthlyStats" :key="stat.month" class="mb-3">
                <div class="d-flex justify-space-between align-center">
                  <span class="text-body-1">{{ getMonthName(stat.month) }}</span>
                  <span class="text-h6 font-weight-bold">{{ stat.count }}</span>
                </div>
                <v-progress-linear
                  :model-value="(stat.count / Math.max(...monthlyStats.map(s => s.count))) * 100"
                  color="info"
                  height="6"
                  rounded
                ></v-progress-linear>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Дополнительные действия -->
      <v-row class="mt-6">
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-chart-line</v-icon>
              Дополнительные действия
            </v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="3">
                  <v-btn
                    variant="outlined"
                    block
                    prepend-icon="mdi-download"
                    @click="exportAnalytics"
                  >
                    Экспорт отчета
                  </v-btn>
                </v-col>
                
                <v-col cols="12" md="3">
                  <v-btn
                    variant="outlined"
                    block
                    prepend-icon="mdi-printer"
                    @click="printAnalytics"
                  >
                    Печать отчета
                  </v-btn>
                </v-col>
                
                <v-col cols="12" md="3">
                  <v-btn
                    variant="outlined"
                    block
                    prepend-icon="mdi-refresh"
                    @click="refreshAnalytics"
                  >
                    Обновить данные
                  </v-btn>
                </v-col>
                
                <v-col cols="12" md="3">
                  <v-btn
                    variant="outlined"
                    block
                    prepend-icon="mdi-share"
                    @click="shareAnalytics"
                  >
                    Поделиться
                  </v-btn>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Информационная панель -->
      <v-row class="mt-6">
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-information</v-icon>
              Информация об аналитике
            </v-card-title>
            <v-card-text>
              <div class="text-body-2 mb-4">
                <strong>Последнее обновление:</strong> {{ new Date().toLocaleString('ru-RU') }}
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Общее количество записей:</strong> {{ totalSchedules }}
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Активных записей:</strong> {{ activeSchedules }} ({{ activePercentage }}%)
              </div>
              
              <div class="text-body-2 mb-4">
                <strong>Неактивных записей:</strong> {{ inactiveSchedules }} ({{ inactivePercentage }}%)
              </div>
              
              <div class="text-body-2">
                <strong>Средняя нагрузка на преподавателя:</strong> {{ averageTeacherLoad }}
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AdminApp>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminApp from '../AdminApp.vue'

// Props из Inertia
const props = defineProps({
  totalSchedules: {
    type: Number,
    default: 0
  },
  activeSchedules: {
    type: Number,
    default: 0
  },
  inactiveSchedules: {
    type: Number,
    default: 0
  },
  semesterStats: {
    type: Array,
    default: () => []
  },
  teacherStats: {
    type: Array,
    default: () => []
  },
  groupStats: {
    type: Array,
    default: () => []
  },
  monthlyStats: {
    type: Array,
    default: () => []
  }
})

// Вычисляемые свойства
const activePercentage = computed(() => {
  if (props.totalSchedules === 0) return 0
  return Math.round((props.activeSchedules / props.totalSchedules) * 100)
})

const inactivePercentage = computed(() => {
  if (props.totalSchedules === 0) return 0
  return Math.round((props.inactiveSchedules / props.totalSchedules) * 100)
})

const averageTeacherLoad = computed(() => {
  if (props.teacherStats.length === 0) return 0
  const totalLoad = props.teacherStats.reduce((sum, stat) => sum + stat.count, 0)
  return Math.round(totalLoad / props.teacherStats.length)
})

// Методы
const navigateTo = (path) => {
  router.visit(path)
}

const getMonthName = (month) => {
  const months = [
    'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
    'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
  ]
  return months[month - 1]
}

const exportAnalytics = () => {
  alert('Функция экспорта отчета будет реализована')
}

const printAnalytics = () => {
  window.print()
}

const refreshAnalytics = () => {
  router.reload()
}

const shareAnalytics = () => {
  alert('Функция совместного использования будет реализована')
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}

@media print {
  .v-btn {
    display: none !important;
  }
}
</style>
