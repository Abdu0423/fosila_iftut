<template>
  <AdminLayout>
    <v-row>
      <v-col cols="12">
        <div class="d-flex justify-space-between align-center mb-4">
          <h1 class="text-h4">{{ student.name }}</h1>
          <div class="d-flex gap-2">
            <v-btn
              color="warning"
              :href="route('admin.students.edit', student.id)"
              prepend-icon="mdi-pencil"
            >
              Редактировать
            </v-btn>
            <v-btn
              variant="outlined"
              :href="route('admin.students.index')"
              prepend-icon="mdi-arrow-left"
            >
              Назад к списку
            </v-btn>
          </div>
        </div>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="8">
        <v-card>
          <v-card-title>Информация о студенте</v-card-title>
          <v-card-text>
            <v-row>
              <v-col cols="12" md="4" class="text-center">
                <v-avatar size="120" class="mb-3">
                  <v-img
                    :src="`https://ui-avatars.com/api/?name=${student.name}&size=120&background=random`"
                    :alt="student.name"
                  ></v-img>
                </v-avatar>
                <div class="text-h6">{{ student.name }}</div>
                <div class="text-body-2 text-medium-emphasis">{{ student.email }}</div>
                <v-chip
                  :color="student.is_active ? 'success' : 'error'"
                  size="small"
                  class="mt-2"
                >
                  {{ student.is_active ? 'Активен' : 'Неактивен' }}
                </v-chip>
              </v-col>
              
              <v-col cols="12" md="8">
                <v-list>
                  <v-list-item>
                    <v-list-item-title class="font-weight-bold">Email</v-list-item-title>
                    <v-list-item-subtitle>{{ student.email }}</v-list-item-subtitle>
                  </v-list-item>
                  
                  <v-list-item v-if="student.phone">
                    <v-list-item-title class="font-weight-bold">Телефон</v-list-item-title>
                    <v-list-item-subtitle>{{ student.phone }}</v-list-item-subtitle>
                  </v-list-item>
                  
                  <v-list-item>
                    <v-list-item-title class="font-weight-bold">Группа</v-list-item-title>
                    <v-list-item-subtitle>
                      {{ student.group?.name }}
                    </v-list-item-subtitle>
                  </v-list-item>
                  
                  <v-list-item>
                    <v-list-item-title class="font-weight-bold">Курс</v-list-item-title>
                    <v-list-item-subtitle>
                      <v-chip color="primary" size="small">
                        {{ student.course }} курс
                      </v-chip>
                    </v-list-item-subtitle>
                  </v-list-item>
                  
                  <v-list-item v-if="student.student_id">
                    <v-list-item-title class="font-weight-bold">Студенческий билет</v-list-item-title>
                    <v-list-item-subtitle>{{ student.student_id }}</v-list-item-subtitle>
                  </v-list-item>
                  
                  <v-list-item>
                    <v-list-item-title class="font-weight-bold">ID студента</v-list-item-title>
                    <v-list-item-subtitle>{{ student.id }}</v-list-item-subtitle>
                  </v-list-item>
                  
                  <v-list-item>
                    <v-list-item-title class="font-weight-bold">Дата регистрации</v-list-item-title>
                    <v-list-item-subtitle>{{ formatDate(student.created_at) }}</v-list-item-subtitle>
                  </v-list-item>
                  
                  <v-list-item>
                    <v-list-item-title class="font-weight-bold">Последнее обновление</v-list-item-title>
                    <v-list-item-subtitle>{{ formatDate(student.updated_at) }}</v-list-item-subtitle>
                  </v-list-item>
                </v-list>
              </v-col>
            </v-row>
            
            <v-divider class="my-4"></v-divider>
            
            <div v-if="student.address">
              <h3 class="text-h6 mb-2">Адрес</h3>
              <p class="text-body-1">{{ student.address }}</p>
            </div>
            <div v-else>
              <v-alert type="info">
                Адрес не указан
              </v-alert>
            </div>
          </v-card-text>
        </v-card>

        <!-- Расписание студента -->
        <v-card class="mt-4" v-if="student.schedules && student.schedules.length">
          <v-card-title>Расписание занятий</v-card-title>
          <v-card-text>
            <v-data-table
              :headers="scheduleHeaders"
              :items="student.schedules"
              :items-per-page="5"
            >
              <template v-slot:item.lesson="{ item }">
                <v-btn
                  variant="text"
                  color="primary"
                  :href="route('admin.lessons.show', item.raw.lesson.id)"
                >
                  {{ item.raw.lesson.title }}
                </v-btn>
              </template>
              
              <template v-slot:item.teacher="{ item }">
                {{ item.raw.teacher?.name }}
              </template>
              
              <template v-slot:item.day_of_week="{ item }">
                {{ getDayName(item.raw.day_of_week) }}
              </template>
              
              <template v-slot:item.time="{ item }">
                {{ item.raw.time }}
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card>
          <v-card-title>Действия</v-card-title>
          <v-card-text>
            <v-btn
              block
              color="warning"
              :href="route('admin.students.edit', student.id)"
              prepend-icon="mdi-pencil"
              class="mb-2"
            >
              Редактировать профиль
            </v-btn>
            
            <v-btn
              block
              color="info"
              :href="route('admin.schedules.create')"
              prepend-icon="mdi-calendar-plus"
              class="mb-2"
            >
              Добавить в расписание
            </v-btn>
            
            <v-btn
              block
              color="error"
              variant="outlined"
              prepend-icon="mdi-delete"
              @click="deleteStudent"
            >
              Удалить студента
            </v-btn>
          </v-card-text>
        </v-card>

        <v-card class="mt-4">
          <v-card-title>Статистика</v-card-title>
          <v-card-text>
            <v-list>
              <v-list-item>
                <v-list-item-title>Занятий в расписании</v-list-item-title>
                <v-list-item-subtitle>
                  {{ student.schedules?.length || 0 }}
                </v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item>
                <v-list-item-title>Уроков изучает</v-list-item-title>
                <v-list-item-subtitle>
                  {{ uniqueLessons }}
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Диалог подтверждения удаления -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title>Подтверждение удаления</v-card-title>
        <v-card-text>
          Вы действительно хотите удалить студента "{{ student.name }}"?
          Это действие нельзя отменить.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="deleteDialog = false">Отмена</v-btn>
          <v-btn color="error" @click="confirmDelete">Удалить</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const props = defineProps({
  student: Object,
})

const deleteDialog = ref(false)

const scheduleHeaders = [
  { title: 'Урок', key: 'lesson' },
  { title: 'Учитель', key: 'teacher' },
  { title: 'День недели', key: 'day_of_week' },
  { title: 'Время', key: 'time' },
]

const uniqueLessons = computed(() => {
  if (!props.student.schedules) return 0
  const lessonIds = new Set(props.student.schedules.map(s => s.lesson.id))
  return lessonIds.size
})

const deleteStudent = () => {
  deleteDialog.value = true
}

const confirmDelete = () => {
  router.delete(route('admin.students.destroy', props.student.id), {
    onSuccess: () => {
      deleteDialog.value = false
    }
  })
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getDayName = (dayNumber) => {
  const days = [
    'Понедельник',
    'Вторник',
    'Среда',
    'Четверг',
    'Пятница',
    'Суббота',
    'Воскресенье'
  ]
  return days[dayNumber - 1]
}
</script>
