<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Панель учителя</h1>
              <p class="text-body-1 text-medium-emphasis">Добро пожаловать в систему управления обучением</p>
            </div>
            <v-avatar size="64" color="primary">
              <v-icon size="32" color="white">mdi-account-tie</v-icon>
            </v-avatar>
          </div>
        </v-col>
      </v-row>

      <!-- Статистика -->
      <v-row class="mb-6">
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="primary" class="mb-4">mdi-calendar-clock</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats.schedules || 0 }}</div>
              <div class="text-body-2 text-medium-emphasis">Расписаний</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="success" class="mb-4">mdi-teach</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats.lessons || 0 }}</div>
              <div class="text-body-2 text-medium-emphasis">Уроков</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="info" class="mb-4">mdi-account-group</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats.students || 0 }}</div>
              <div class="text-body-2 text-medium-emphasis">Студентов</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="warning" class="mb-4">mdi-help-circle</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats.tests || 0 }}</div>
              <div class="text-body-2 text-medium-emphasis">Тестов</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Быстрые действия -->
      <v-row class="mb-6">
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-lightning-bolt</v-icon>
              Быстрые действия
            </v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="3">
                  <v-btn
                    color="primary"
                    variant="elevated"
                    block
                    @click="navigateTo('/teacher/schedule')"
                    prepend-icon="mdi-calendar-clock"
                  >
                    Мои расписания
                  </v-btn>
                </v-col>
                <v-col cols="12" md="3">
                  <v-btn
                    color="success"
                    variant="elevated"
                    block
                    @click="navigateTo('/teacher/lessons')"
                    prepend-icon="mdi-teach"
                  >
                    Мои уроки
                  </v-btn>
                </v-col>
                <v-col cols="12" md="3">
                  <v-btn
                    color="info"
                    variant="elevated"
                    block
                    @click="navigateTo('/teacher/students')"
                    prepend-icon="mdi-account-group"
                  >
                    Мои студенты
                  </v-btn>
                </v-col>
                <v-col cols="12" md="3">
                  <v-btn
                    color="warning"
                    variant="elevated"
                    block
                    @click="navigateTo('/teacher/tests')"
                    prepend-icon="mdi-help-circle"
                  >
                    Мои тесты
                  </v-btn>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Последние активности -->
      <v-row>
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-clock-outline</v-icon>
              Последние расписания
            </v-card-title>
            <v-card-text>
              <div v-if="recentSchedules.length === 0" class="text-center py-4">
                <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-calendar-clock-outline</v-icon>
                <p class="text-body-2 text-grey">Нет расписаний</p>
              </div>
              <v-list v-else>
                <v-list-item
                  v-for="schedule in recentSchedules"
                  :key="schedule.id"
                >
                  <template v-slot:prepend>
                    <v-avatar color="primary" size="32">
                      <v-icon color="white" size="16">mdi-calendar-clock</v-icon>
                    </v-avatar>
                  </template>
                  <v-list-item-title>{{ schedule.subject?.name }}</v-list-item-title>
                  <v-list-item-subtitle>{{ schedule.group?.name }}</v-list-item-subtitle>
                  <template v-slot:append>
                    <v-btn
                      icon="mdi-eye"
                      variant="text"
                      size="small"
                      @click="navigateTo(`/teacher/schedule/${schedule.id}`)"
                    ></v-btn>
                  </template>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
        
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-chat-outline</v-icon>
              Последние сообщения
            </v-card-title>
            <v-card-text>
              <div v-if="recentMessages.length === 0" class="text-center py-4">
                <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-chat-outline</v-icon>
                <p class="text-body-2 text-grey">Нет сообщений</p>
              </div>
              <v-list v-else>
                <v-list-item
                  v-for="message in recentMessages"
                  :key="message.id"
                >
                  <template v-slot:prepend>
                    <v-avatar color="secondary" size="32">
                      <v-icon color="white" size="16">mdi-chat</v-icon>
                    </v-avatar>
                  </template>
                  <v-list-item-title>{{ message.user?.name }}</v-list-item-title>
                  <v-list-item-subtitle>{{ message.message }}</v-list-item-subtitle>
                  <template v-slot:append>
                    <v-btn
                      icon="mdi-eye"
                      variant="text"
                      size="small"
                      @click="navigateTo('/teacher/chat')"
                    ></v-btn>
                  </template>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Layout from '../Layout.vue'

// Props
const props = defineProps({
  stats: {
    type: Object,
    default: () => ({})
  },
  recentSchedules: {
    type: Array,
    default: () => []
  },
  recentMessages: {
    type: Array,
    default: () => []
  }
})

// Методы
const navigateTo = (route) => {
  window.location.href = route
}

// Жизненный цикл
onMounted(() => {
  console.log('Панель учителя загружена')
})
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
