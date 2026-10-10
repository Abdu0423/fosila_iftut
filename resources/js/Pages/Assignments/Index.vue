<template>
  <Layout>
    <v-container fluid>
      <div class="assignments-page">
        <v-row>
        <v-col cols="12">
          <h1 class="text-h4 mb-6">Мои задания</h1>
        </v-col>
      </v-row>
      
      <v-row>
        <v-col cols="12">
          <v-tabs v-model="activeTab" background-color="transparent">
            <v-tab value="active">Активные</v-tab>
            <v-tab value="completed">Завершенные</v-tab>
            <v-tab value="overdue">Просроченные</v-tab>
          </v-tabs>
        </v-col>
      </v-row>
      
      <v-row>
        <v-col cols="12">
          <v-window v-model="activeTab">
            <v-window-item value="active">
              <v-card v-for="assignment in activeAssignments" :key="assignment.id" class="mb-4">
                <v-card-title>
                  {{ assignment.title }}
                  <v-spacer></v-spacer>
                  <v-chip :color="getStatusColor(assignment.status)">
                    {{ assignment.status }}
                  </v-chip>
                </v-card-title>
                <v-card-text>
                  <p>{{ assignment.description }}</p>
                  <v-list dense>
                    <v-list-item>
                      <v-list-item-icon>
                        <v-icon>mdi-book</v-icon>
                      </v-list-item-icon>
                      <v-list-item-content>
                        <v-list-item-title>{{ assignment.course }}</v-list-item-title>
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-icon>
                        <v-icon>mdi-calendar</v-icon>
                      </v-list-item-icon>
                      <v-list-item-content>
                        <v-list-item-title>Срок сдачи: {{ assignment.dueDate }}</v-list-item-title>
                      </v-list-item-content>
                    </v-list-item>
                  </v-list>
                </v-card-text>
                                 <v-card-actions>
                  <v-btn color="primary" @click="openAssignment(assignment.id)">
                    Открыть задание
                  </v-btn>
                  <v-btn color="secondary" outlined>
                    Сдать работу
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-window-item>
            
            <v-window-item value="completed">
              <v-card v-for="assignment in completedAssignments" :key="assignment.id" class="mb-4">
                <v-card-title>
                  {{ assignment.title }}
                  <v-spacer></v-spacer>
                  <v-chip color="success">Завершено</v-chip>
                </v-card-title>
                <v-card-text>
                  <p>{{ assignment.description }}</p>
                  <v-list dense>
                    <v-list-item>
                      <v-list-item-icon>
                        <v-icon>mdi-book</v-icon>
                      </v-list-item-icon>
                      <v-list-item-content>
                        <v-list-item-title>{{ assignment.course }}</v-list-item-title>
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-icon>
                        <v-icon>mdi-star</v-icon>
                      </v-list-item-icon>
                      <v-list-item-content>
                        <v-list-item-title>Оценка: {{ assignment.grade }}/100</v-list-item-title>
                      </v-list-item-content>
                    </v-list-item>
                  </v-list>
                </v-card-text>
                                 <v-card-actions>
                  <v-btn color="primary" @click="openAssignment(assignment.id)">
                    Просмотреть
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-window-item>
            
            <v-window-item value="overdue">
              <v-card v-for="assignment in overdueAssignments" :key="assignment.id" class="mb-4">
                <v-card-title>
                  {{ assignment.title }}
                  <v-spacer></v-spacer>
                  <v-chip color="error">Просрочено</v-chip>
                </v-card-title>
                <v-card-text>
                  <p>{{ assignment.description }}</p>
                  <v-list dense>
                    <v-list-item>
                      <v-list-item-icon>
                        <v-icon>mdi-book</v-icon>
                      </v-list-item-icon>
                      <v-list-item-content>
                        <v-list-item-title>{{ assignment.course }}</v-list-item-title>
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-icon>
                        <v-icon>mdi-calendar-alert</v-icon>
                      </v-list-item-icon>
                      <v-list-item-content>
                        <v-list-item-title class="error--text">Просрочено с: {{ assignment.dueDate }}</v-list-item-title>
                      </v-list-item-content>
                    </v-list-item>
                  </v-list>
                </v-card-text>
                                 <v-card-actions>
                  <v-btn color="primary" @click="openAssignment(assignment.id)">
                    Открыть задание
                  </v-btn>
                  <v-btn color="error" outlined>
                    Сдать работу
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-window-item>
          </v-window>
        </v-col>
      </v-row>
      </div>
    </v-container>
  </Layout>
</template>

<script lang="ts">
import { router } from '@/compat/inertia'
import Layout from '../Layout.vue'

export default {
  name: 'AssignmentsIndex',
  components: {
    Layout
  },
  data() {
    return {
      activeTab: 'active',
      activeAssignments: [
        {
          id: 1,
          title: 'Лабораторная работа №1',
          description: 'Создание простого калькулятора на Python',
          course: 'Введение в программирование',
          dueDate: '2024-01-20',
          status: 'В работе'
        },
        {
          id: 2,
          title: 'Проект веб-сайта',
          description: 'Создание персонального веб-сайта с использованием HTML/CSS',
          course: 'Веб-разработка',
          dueDate: '2024-01-25',
          status: 'Новое'
        }
      ],
      completedAssignments: [
        {
          id: 3,
          title: 'Домашнее задание №1',
          description: 'Основы синтаксиса Python',
          course: 'Введение в программирование',
          grade: 95
        }
      ],
      overdueAssignments: [
        {
          id: 4,
          title: 'Контрольная работа',
          description: 'Проверка знаний по основам программирования',
          course: 'Введение в программирование',
          dueDate: '2024-01-10'
        }
      ]
    }
  },
  methods: {
    getStatusColor(status) {
      switch (status) {
        case 'В работе':
          return 'warning'
        case 'Новое':
          return 'info'
        default:
          return 'primary'
      }
    },
    openAssignment(assignmentId) {
      router.visit(window.route('assignments.show', { assignment: assignmentId }))
    }
  }
}
</script>
