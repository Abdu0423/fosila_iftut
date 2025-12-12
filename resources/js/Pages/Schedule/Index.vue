<template>
  <Layout>
    <v-container fluid>
      <div class="schedule-page">
        <v-row>
          <v-col cols="12">
            <div class="d-flex align-center justify-space-between mb-6">
              <div>
                <h1 class="text-h4 mb-2">Мое расписание</h1>
                <div v-if="group" class="text-subtitle-1 text-medium-emphasis">
                  Группа: {{ group.full_name }}
                  <span v-if="group.specialty" class="ml-2">• {{ group.specialty }}</span>
                </div>
                <div v-else-if="message" class="text-subtitle-1 text-warning">
                  {{ message }}
                </div>
              </div>
              <v-btn 
                color="primary" 
                @click="currentWeek = new Date()"
                prepend-icon="mdi-calendar-today"
              >
                Текущая неделя
              </v-btn>
            </div>
          </v-col>
        </v-row>
        
        <v-row v-if="group">
          <v-col cols="12">
            <v-card>
              <v-card-title>
                <v-icon class="mr-2">mdi-calendar-week</v-icon>
                Расписание на неделю
                <v-spacer></v-spacer>
                <v-chip color="primary" variant="outlined">
                  {{ schedules.length }} занятий
                </v-chip>
              </v-card-title>
              <v-card-text>
                <v-calendar
                  v-model="selectedDate"
                  :events="formattedEvents"
                  event-color="primary"
                  type="week"
                  :weekdays="[1, 2, 3, 4, 5, 6, 0]"
                  :first-day-of-week="1"
                  @click:event="showEventDetails"
                ></v-calendar>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
        
        <v-row class="mt-6" v-if="group">
          <v-col cols="12" md="8">
            <v-card>
              <v-card-title>
                <v-icon class="mr-2">mdi-clock-outline</v-icon>
                Ближайшие занятия
              </v-card-title>
              <v-card-text>
                <v-list v-if="upcomingLessons.length > 0">
                  <v-list-item 
                    v-for="lesson in upcomingLessons" 
                    :key="lesson.id"
                    class="mb-2"
                  >
                    <template v-slot:prepend>
                      <v-avatar color="primary" variant="tonal">
                        <v-icon>mdi-book-open-variant</v-icon>
                      </v-avatar>
                    </template>
                    <v-list-item-title class="font-weight-medium">
                      {{ lesson.course }}
                    </v-list-item-title>
                    <v-list-item-subtitle>
                      <div class="d-flex align-center gap-4 text-caption">
                        <span>
                          <v-icon size="16" class="mr-1">mdi-clock</v-icon>
                          {{ formatDateTime(lesson.scheduled_at) }}
                        </span>
                        <span v-if="lesson.room">
                          <v-icon size="16" class="mr-1">mdi-map-marker</v-icon>
                          {{ lesson.room }}
                        </span>
                        <span>
                          <v-icon size="16" class="mr-1">mdi-account</v-icon>
                          {{ lesson.teacher }}
                        </span>
                      </div>
                    </v-list-item-subtitle>
                    <template v-slot:append>
                      <v-chip 
                        :color="getLessonTypeColor(lesson.type)" 
                        variant="tonal"
                        size="small"
                      >
                        {{ lesson.type }}
                      </v-chip>
                    </template>
                  </v-list-item>
                </v-list>
                <v-alert v-else type="info" variant="tonal">
                  Ближайших занятий не найдено
                </v-alert>
              </v-card-text>
            </v-card>
          </v-col>
          
          <v-col cols="12" md="4">
            <v-card>
              <v-card-title>
                <v-icon class="mr-2">mdi-information</v-icon>
                Информация о группе
              </v-card-title>
              <v-card-text>
                <v-list density="compact">
                  <v-list-item>
                    <v-list-item-title>Название группы</v-list-item-title>
                    <v-list-item-subtitle>{{ group.name }}</v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item>
                    <v-list-item-title>Курс</v-list-item-title>
                    <v-list-item-subtitle>{{ group.course }}</v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item v-if="group.specialty">
                    <v-list-item-title>Специальность</v-list-item-title>
                    <v-list-item-subtitle>{{ group.specialty }}</v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item>
                    <v-list-item-title>Всего занятий</v-list-item-title>
                    <v-list-item-subtitle>{{ schedules.length }}</v-list-item-subtitle>
                  </v-list-item>
                </v-list>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
        
        <!-- Диалог с деталями события -->
        <v-dialog v-model="eventDialog" max-width="500">
          <v-card v-if="selectedEvent">
            <v-card-title>
              <v-icon class="mr-2">mdi-calendar-clock</v-icon>
              {{ selectedEvent.name }}
            </v-card-title>
            <v-card-text>
              <v-list density="compact">
                <v-list-item v-if="selectedEvent.details.teacher">
                  <v-list-item-title>Преподаватель</v-list-item-title>
                  <v-list-item-subtitle>{{ selectedEvent.details.teacher }}</v-list-item-subtitle>
                </v-list-item>
                <v-list-item v-if="selectedEvent.details.room">
                  <v-list-item-title>Аудитория</v-list-item-title>
                  <v-list-item-subtitle>{{ selectedEvent.details.room }}</v-list-item-subtitle>
                </v-list-item>
                <v-list-item v-if="selectedEvent.details.semester">
                  <v-list-item-title>Семестр</v-list-item-title>
                  <v-list-item-subtitle>{{ selectedEvent.details.semester }}</v-list-item-subtitle>
                </v-list-item>
                <v-list-item v-if="selectedEvent.details.credits">
                  <v-list-item-title>Кредиты</v-list-item-title>
                  <v-list-item-subtitle>{{ selectedEvent.details.credits }}</v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn @click="eventDialog = false">Закрыть</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </div>
    </v-container>
  </Layout>
</template>

<script>
import Layout from '../Layout.vue'

export default {
  name: 'ScheduleIndex',
  components: {
    Layout
  },
  props: {
    group: {
      type: Object,
      default: null
    },
    schedules: {
      type: Array,
      default: () => []
    },
    upcomingLessons: {
      type: Array,
      default: () => []
    },
    events: {
      type: Array,
      default: () => []
    },
    message: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      selectedDate: new Date(),
      currentWeek: new Date(),
      eventDialog: false,
      selectedEvent: null
    }
  },
  computed: {
    formattedEvents() {
      return this.events.map(event => ({
        ...event,
        start: event.start,
        end: event.end,
        color: event.color
      }))
    }
  },
  methods: {
    showEventDetails(event) {
      this.selectedEvent = event
      this.eventDialog = true
    },
    formatDateTime(dateTime) {
      if (!dateTime) return 'Время не указано'
      
      const date = new Date(dateTime)
      const now = new Date()
      const diffTime = date - now
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
      
      const timeStr = date.toLocaleTimeString('ru-RU', { 
        hour: '2-digit', 
        minute: '2-digit' 
      })
      
      if (diffDays === 0) {
        return `Сегодня, ${timeStr}`
      } else if (diffDays === 1) {
        return `Завтра, ${timeStr}`
      } else if (diffDays > 1 && diffDays <= 7) {
        const dayName = date.toLocaleDateString('ru-RU', { weekday: 'long' })
        return `${dayName}, ${timeStr}`
      } else {
        return date.toLocaleDateString('ru-RU', { 
          day: '2-digit', 
          month: '2-digit', 
          hour: '2-digit', 
          minute: '2-digit' 
        })
      }
    },
    getLessonTypeColor(type) {
      const colors = {
        'Лекция': 'primary',
        'Практика': 'secondary',
        'Лабораторная': 'success',
        'Семинар': 'warning',
        'Консультация': 'info'
      }
      return colors[type]
    }
  }
}
</script>
