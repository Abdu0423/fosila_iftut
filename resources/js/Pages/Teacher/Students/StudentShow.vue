<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ student.full_name }}</h1>
              <p class="text-body-1 text-medium-emphasis">Профиль студента</p>
            </div>
            <div class="d-flex gap-2">
              <v-btn
                color="secondary"
                variant="outlined"
                @click="navigateTo('/teacher/students')"
                prepend-icon="mdi-arrow-left"
              >
                Назад к студентам
              </v-btn>
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

      <v-row>
        <!-- Информация о студенте -->
        <v-col cols="12" md="4">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-account</v-icon>
              Информация о студенте
            </v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="primary">mdi-account</v-icon>
                  </template>
                  <v-list-item-title>ФИО</v-list-item-title>
                  <v-list-item-subtitle>{{ student.full_name }}</v-list-item-subtitle>
                </v-list-item>
                
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="secondary">mdi-email</v-icon>
                  </template>
                  <v-list-item-title>Email</v-list-item-title>
                  <v-list-item-subtitle>{{ student.email }}</v-list-item-subtitle>
                </v-list-item>
                
                <v-list-item v-if="student.phone">
                  <template v-slot:prepend>
                    <v-icon color="info">mdi-phone</v-icon>
                  </template>
                  <v-list-item-title>Телефон</v-list-item-title>
                  <v-list-item-subtitle>{{ student.phone }}</v-list-item-subtitle>
                </v-list-item>
                
                <v-list-item v-if="student.address">
                  <template v-slot:prepend>
                    <v-icon color="warning">mdi-map-marker</v-icon>
                  </template>
                  <v-list-item-title>Адрес</v-list-item-title>
                  <v-list-item-subtitle>{{ student.address }}</v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
        
        <!-- Группы студента -->
        <v-col cols="12" md="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-account-group</v-icon>
              Группы студента ({{ groups.length }})
            </v-card-title>
            <v-card-text>
              <div v-if="groups.length === 0" class="text-center py-4">
                <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-account-group-outline</v-icon>
                <p class="text-body-2 text-grey">Студент не состоит в группах</p>
              </div>
              
              <div v-else>
                <v-expansion-panels variant="accordion">
                  <v-expansion-panel
                    v-for="group in groups"
                    :key="group.id"
                    class="mb-4"
                  >
                    <v-expansion-panel-title>
                      <div class="d-flex align-center justify-space-between w-100">
                        <div class="d-flex align-center">
                          <v-avatar color="primary" size="40" class="mr-3">
                            <v-icon color="white">mdi-account-group</v-icon>
                          </v-avatar>
                          <div>
                            <div class="font-weight-medium">{{ group.name }}</div>
                            <div class="text-caption text-medium-emphasis">{{ group.full_name }}</div>
                          </div>
                        </div>
                        <div class="d-flex align-center gap-4">
                          <v-chip
                            color="info"
                            size="small"
                            variant="tonal"
                          >
                            {{ group.schedules.length }} уроков
                          </v-chip>
                          <v-btn
                            color="primary"
                            variant="text"
                            size="small"
                            @click.stop="viewGroup(group)"
                            prepend-icon="mdi-eye"
                          >
                            Просмотр группы
                          </v-btn>
                        </div>
                      </div>
                    </v-expansion-panel-title>
                    
                    <v-expansion-panel-text>
                      <div v-if="group.schedules.length === 0" class="text-center py-4">
                        <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-calendar-outline</v-icon>
                        <p class="text-body-2 text-grey">Нет запланированных уроков</p>
                      </div>
                      
                      <div v-else>
                        <v-list>
                          <v-list-item
                            v-for="schedule in group.schedules"
                            :key="schedule.id"
                            class="mb-2"
                          >
                            <template v-slot:prepend>
                              <v-avatar size="32" color="secondary">
                                <v-icon color="white" size="16">mdi-teach</v-icon>
                              </v-avatar>
                            </template>
                            
                            <v-list-item-title class="text-body-2 font-weight-medium">
                              {{ schedule.lesson_title }}
                            </v-list-item-title>
                            
                            <v-list-item-subtitle class="text-caption">
                              <div class="d-flex align-center gap-2 mb-1">
                                <v-chip
                                  color="primary"
                                  size="x-small"
                                  variant="tonal"
                                >
                                  {{ schedule.subject_name }}
                                </v-chip>
                                <v-chip
                                  color="info"
                                  size="x-small"
                                  variant="tonal"
                                >
                                  {{ getDayOfWeekText(schedule.day_of_week) }}
                                </v-chip>
                                <v-chip
                                  :color="schedule.is_active ? 'success' : 'warning'"
                                  size="x-small"
                                  variant="tonal"
                                >
                                  {{ schedule.is_active ? 'Активен' : 'Неактивен' }}
                                </v-chip>
                              </div>
                              
                              <div class="d-flex align-center gap-4 text-caption text-medium-emphasis">
                                <span v-if="schedule.start_time && schedule.end_time">
                                  <v-icon size="14" class="mr-1">mdi-clock</v-icon>
                                  {{ schedule.start_time }} - {{ schedule.end_time }}
                                </span>
                                <span v-if="schedule.room">
                                  <v-icon size="14" class="mr-1">mdi-map-marker</v-icon>
                                  {{ schedule.room }}
                                </span>
                                <span v-if="schedule.semester">
                                  <v-icon size="14" class="mr-1">mdi-school</v-icon>
                                  Семестр {{ schedule.semester }}
                                </span>
                              </div>
                            </v-list-item-subtitle>
                            
                            <template v-slot:append>
                              <v-btn
                                icon="mdi-eye"
                                variant="text"
                                size="small"
                                @click="viewLesson(schedule)"
                                color="primary"
                              ></v-btn>
                            </template>
                          </v-list-item>
                        </v-list>
                      </div>
                    </v-expansion-panel-text>
                  </v-expansion-panel>
                </v-expansion-panels>
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

// Props из Inertia
const props = defineProps({
  student: {
    type: Object,
    required: true
  },
  groups: {
    type: Array,
    default: () => []
  }
})

// Методы
const navigateTo = (path) => {
  router.visit(path)
}

const getDayOfWeekText = (day) => {
  const days = {
    'monday': 'Понедельник',
    'tuesday': 'Вторник',
    'wednesday': 'Среда',
    'thursday': 'Четверг',
    'friday': 'Пятница',
    'saturday': 'Суббота',
    'sunday': 'Воскресенье'
  }
  return days[day] || day
}

const viewGroup = (group) => {
  navigateTo(`/teacher/students/group/${group.id}`)
}

const viewLesson = (schedule) => {
  navigateTo(`/teacher/lessons/${schedule.id}`)
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}

.v-expansion-panel {
  border-radius: 12px;
  border: 1px solid #e0e0e0;
}

.v-list-item {
  border-radius: 8px;
}
</style>

