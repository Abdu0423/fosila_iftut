<template>
  <AdminApp>
    <div class="admin-reports">
      <v-row>
        <v-col cols="12">
          <h1 class="text-h4 mb-6">Отчеты и статистика</h1>
        </v-col>
      </v-row>
      
      <!-- Фильтры по датам -->
      <v-row>
        <v-col cols="12" md="3">
          <v-text-field
            v-model="dateFrom"
            label="Дата с"
            type="date"
            outlined
            dense
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="3">
          <v-text-field
            v-model="dateTo"
            label="Дата по"
            type="date"
            outlined
            dense
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="3">
          <v-select
            v-model="selectedReport"
            :items="reportTypes"
            label="Тип отчета"
            outlined
            dense
          ></v-select>
        </v-col>
        <v-col cols="12" md="3">
          <v-btn color="primary" block @click="generateReport">
            <v-icon left>mdi-chart-bar</v-icon>
            Сформировать отчет
          </v-btn>
        </v-col>
      </v-row>
      
      <!-- Статистика -->
      <v-row>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold primary--text">{{ stats.totalUsers }}</div>
              <div class="text-subtitle-1">Всего пользователей</div>
              <v-progress-linear
                :model-value="stats.userGrowth"
                color="primary"
                height="4"
                class="mt-2"
              ></v-progress-linear>
              <div class="text-caption text-grey mt-1">+{{ stats.userGrowth }}% за месяц</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold success--text">{{ stats.activeCourses }}</div>
              <div class="text-subtitle-1">Активных курсов</div>
              <v-progress-linear
                :model-value="stats.courseCompletion"
                color="success"
                height="4"
                class="mt-2"
              ></v-progress-linear>
              <div class="text-caption text-grey mt-1">{{ stats.courseCompletion }}% завершено</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold warning--text">{{ stats.totalAssignments }}</div>
              <div class="text-subtitle-1">Заданий</div>
              <v-progress-linear
                :model-value="stats.assignmentCompletion"
                color="warning"
                height="4"
                class="mt-2"
              ></v-progress-linear>
              <div class="text-caption text-grey mt-1">{{ stats.assignmentCompletion }}% выполнено</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <div class="text-h4 font-weight-bold info--text">{{ stats.averageGrade }}</div>
              <div class="text-subtitle-1">Средний балл</div>
              <v-progress-linear
                :model-value="stats.averageGrade * 20"
                color="info"
                height="4"
                class="mt-2"
              ></v-progress-linear>
              <div class="text-caption text-grey mt-1">из 5.0</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
      
      <!-- Графики -->
      <v-row class="mt-6">
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Активность пользователей</v-card-title>
            <v-card-text>
              <div style="height: 300px;" class="d-flex align-center justify-center">
                <div class="text-center">
                  <v-icon size="64" color="grey">mdi-chart-line</v-icon>
                  <h3 class="text-h6 mt-4">График активности</h3>
                  <p class="text-grey">Здесь будет отображаться график активности пользователей</p>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Популярность курсов</v-card-title>
            <v-card-text>
              <div style="height: 300px;" class="d-flex align-center justify-center">
                <div class="text-center">
                  <v-icon size="64" color="grey">mdi-chart-pie</v-icon>
                  <h3 class="text-h6 mt-4">Диаграмма курсов</h3>
                  <p class="text-grey">Здесь будет отображаться популярность курсов</p>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
      
      <!-- Таблица отчетов -->
      <v-row class="mt-6">
        <v-col cols="12">
          <v-card>
            <v-card-title>Детальная статистика</v-card-title>
            <v-card-text>
              <v-data-table
                :headers="tableHeaders"
                :items="reportData"
                :items-per-page="10"
                class="elevation-0"
              >
                <!-- Название -->
                <template v-slot:item.name="{ item }">
                  <div class="font-weight-medium">{{ item.name }}</div>
                </template>
                
                <!-- Значение -->
                <template v-slot:item.value="{ item }">
                  <div class="text-h6 font-weight-bold" :class="item.color + '--text'">
                    {{ item.value }}
                  </div>
                </template>
                
                <!-- Изменение -->
                <template v-slot:item.change="{ item }">
                  <v-chip :color="item.change >= 0 ? 'success' : 'error'" small>
                    <v-icon left size="small">
                      {{ item.change >= 0 ? 'mdi-trending-up' : 'mdi-trending-down' }}
                    </v-icon>
                    {{ Math.abs(item.change) }}%
                  </v-chip>
                </template>
                
                <!-- Статус -->
                <template v-slot:item.status="{ item }">
                  <v-chip :color="getStatusColor(item.status)" small>
                    {{ item.status }}
                  </v-chip>
                </template>
              </v-data-table>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
      
      <!-- Экспорт -->
      <v-row class="mt-6">
        <v-col cols="12">
          <v-card>
            <v-card-title>Экспорт отчетов</v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="4">
                  <v-btn color="primary" block @click="exportPDF">
                    <v-icon left>mdi-file-pdf-box</v-icon>
                    Экспорт в PDF
                  </v-btn>
                </v-col>
                <v-col cols="12" md="4">
                  <v-btn color="success" block @click="exportExcel">
                    <v-icon left>mdi-file-excel</v-icon>
                    Экспорт в Excel
                  </v-btn>
                </v-col>
                <v-col cols="12" md="4">
                  <v-btn color="info" block @click="exportCSV">
                    <v-icon left>mdi-file-csv</v-icon>
                    Экспорт в CSV
                  </v-btn>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </AdminApp>
</template>

<script>
import AdminApp from '../AdminApp.vue'

export default {
  name: 'AdminReportsIndex',
  components: {
    AdminApp
  },
  data() {
    return {
      dateFrom: '',
      dateTo: '',
      selectedReport: 'general',
      reportTypes: [
        { text: 'Общий отчет', value: 'general' },
        { text: 'Отчет по пользователям', value: 'users' },
        { text: 'Отчет по курсам', value: 'courses' },
        { text: 'Отчет по заданиям', value: 'assignments' }
      ],
      stats: {
        totalUsers: 156,
        userGrowth: 12,
        activeCourses: 24,
        courseCompletion: 78,
        totalAssignments: 89,
        assignmentCompletion: 65,
        averageGrade: 4.2
      },
      tableHeaders: [
        { text: 'Показатель', value: 'name', sortable: true },
        { text: 'Значение', value: 'value', sortable: true },
        { text: 'Изменение', value: 'change', sortable: true },
        { text: 'Статус', value: 'status', sortable: true }
      ],
      reportData: [
        {
          name: 'Новые пользователи',
          value: 23,
          change: 15,
          status: 'Хорошо',
          color: 'success'
        },
        {
          name: 'Активные курсы',
          value: 18,
          change: 8,
          status: 'Стабильно',
          color: 'primary'
        },
        {
          name: 'Завершенные задания',
          value: 156,
          change: -5,
          status: 'Снижение',
          color: 'warning'
        },
        {
          name: 'Средняя оценка',
          value: 4.2,
          change: 3,
          status: 'Улучшение',
          color: 'success'
        }
      ]
    }
  },
  methods: {
    generateReport() {
      // Здесь будет логика генерации отчета
      console.log('Генерация отчета:', {
        dateFrom: this.dateFrom,
        dateTo: this.dateTo,
        reportType: this.selectedReport
      })
    },
    
    getStatusColor(status) {
      const colors = {
        'Хорошо': 'success',
        'Стабильно': 'primary',
        'Снижение': 'warning',
        'Улучшение': 'success'
      }
      return colors[status]
    },
    
    exportPDF() {
      console.log('Экспорт в PDF')
    },
    
    exportExcel() {
      console.log('Экспорт в Excel')
    },
    
    exportCSV() {
      console.log('Экспорт в CSV')
    }
  },
  mounted() {
    // Устанавливаем даты по умолчанию (последний месяц)
    const today = new Date()
    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate())
    
    this.dateFrom = lastMonth.toISOString().split('T')[0]
    this.dateTo = today.toISOString().split('T')[0]
  }
}
</script>
