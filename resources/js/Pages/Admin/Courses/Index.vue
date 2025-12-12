<template>
  <AdminApp>
    <div class="admin-courses">
      <v-row>
        <v-col cols="12">
          <div class="d-flex align-center justify-space-between mb-6">
            <h1 class="text-h4">Управление курсами</h1>
            <v-btn color="primary" @click="navigateTo('admin.courses.create')">
              <v-icon left>mdi-book-plus</v-icon>
              Создать курс
            </v-btn>
          </div>
        </v-col>
      </v-row>
      
      <!-- Фильтры и поиск -->
      <v-row>
        <v-col cols="12" md="4">
          <v-text-field
            v-model="searchQuery"
            label="Поиск курсов..."
            prepend-inner-icon="mdi-magnify"
            outlined
            dense
            clearable
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="3">
          <v-select
            v-model="selectedTeacher"
            :items="teachers"
            label="Преподаватель"
            outlined
            dense
            clearable
          ></v-select>
        </v-col>
        <v-col cols="12" md="3">
          <v-select
            v-model="selectedStatus"
            :items="statuses"
            label="Статус"
            outlined
            dense
            clearable
          ></v-select>
        </v-col>
        <v-col cols="12" md="2">
          <v-btn color="secondary" block @click="clearFilters">
            Сбросить
          </v-btn>
        </v-col>
      </v-row>
      
      <!-- Таблица курсов -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-data-table
              :headers="headers"
              :items="filteredCourses"
              :items-per-page="10"
              :search="searchQuery"
              class="elevation-1"
            >
              <!-- Название курса -->
              <template v-slot:item.name="{ item }">
                <div>
                  <div class="font-weight-medium">{{ item.name }}</div>
                  <div class="text-caption text-grey">{{ item.description }}</div>
                </div>
              </template>
              
              <!-- Преподаватель -->
              <template v-slot:item.teacher="{ item }">
                <div class="d-flex align-center">
                  <v-avatar size="24" class="mr-2">
                    <v-icon>mdi-account</v-icon>
                  </v-avatar>
                  {{ item.teacher }}
                </div>
              </template>
              
              <!-- Кредиты -->
              <template v-slot:item.credits="{ item }">
                <v-chip color="primary" small>
                  {{ item.credits }} кредитов
                </v-chip>
              </template>
              
              <!-- Статус -->
              <template v-slot:item.status="{ item }">
                <v-chip :color="getStatusColor(item.status)" small>
                  {{ getStatusName(item.status) }}
                </v-chip>
              </template>
              
              <!-- Студенты -->
              <template v-slot:item.students_count="{ item }">
                <v-chip color="info" small>
                  {{ item.students_count }} студентов
                </v-chip>
              </template>
              
              <!-- Дата создания -->
              <template v-slot:item.created_at="{ item }">
                {{ formatDate(item.created_at) }}
              </template>
              
              <!-- Действия -->
              <template v-slot:item.actions="{ item }">
                <v-menu offset-y>
                  <template v-slot:activator="{ props }">
                    <v-btn icon small v-bind="props">
                      <v-icon>mdi-dots-vertical</v-icon>
                    </v-btn>
                  </template>
                  
                  <v-list dense>
                    <v-list-item @click="viewCourse(item)">
                      <v-list-item-icon>
                        <v-icon>mdi-eye</v-icon>
                      </v-list-item-icon>
                      <v-list-item-title>Просмотреть</v-list-item-title>
                    </v-list-item>
                    
                    <v-list-item @click="editCourse(item)">
                      <v-list-item-icon>
                        <v-icon>mdi-pencil</v-icon>
                      </v-list-item-icon>
                      <v-list-item-title>Редактировать</v-list-item-title>
                    </v-list-item>
                    
                    <v-list-item @click="manageStudents(item)">
                      <v-list-item-icon>
                        <v-icon>mdi-account-group</v-icon>
                      </v-list-item-icon>
                      <v-list-item-title>Управление студентами</v-list-item-title>
                    </v-list-item>
                    
                    <v-divider></v-divider>
                    
                    <v-list-item @click="deleteCourse(item)" class="error--text">
                      <v-list-item-icon>
                        <v-icon color="error">mdi-delete</v-icon>
                      </v-list-item-icon>
                      <v-list-item-title>Удалить</v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </template>
            </v-data-table>
          </v-card>
        </v-col>
      </v-row>
      
      <!-- Диалог удаления -->
      <v-dialog v-model="deleteDialog" max-width="400">
        <v-card>
          <v-card-title class="error--text">
            <v-icon color="error" class="mr-2">mdi-alert</v-icon>
            Удаление курса
          </v-card-title>
          <v-card-text>
            <p>Вы уверены, что хотите удалить курс <strong>{{ courseToDelete?.name }}</strong>?</p>
            <p class="text-caption text-grey">Это действие нельзя отменить. Все данные курса будут удалены.</p>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn text @click="deleteDialog = false">Отмена</v-btn>
            <v-btn color="error" @click="confirmDelete">Удалить</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
  </AdminApp>
</template>

<script>
import { router } from '@inertiajs/vue3'
import AdminApp from '../AdminApp.vue'

export default {
  name: 'AdminCoursesIndex',
  components: {
    AdminApp
  },
  data() {
    return {
      searchQuery: '',
      selectedTeacher: null,
      selectedStatus: null,
      deleteDialog: false,
      courseToDelete: null,
      teachers: [
        { text: 'Иванов И.И.', value: 'Иванов И.И.' },
        { text: 'Петров П.П.', value: 'Петров П.П.' },
        { text: 'Сидорова А.А.', value: 'Сидорова А.А.' }
      ],
      statuses: [
        { text: 'Активный', value: 'active' },
        { text: 'Неактивный', value: 'inactive' },
        { text: 'В разработке', value: 'draft' }
      ],
      headers: [
        { text: 'Название курса', value: 'name', sortable: true },
        { text: 'Преподаватель', value: 'teacher', sortable: true },
        { text: 'Кредиты', value: 'credits', sortable: true },
        { text: 'Статус', value: 'status', sortable: true },
        { text: 'Студенты', value: 'students_count', sortable: true },
        { text: 'Дата создания', value: 'created_at', sortable: true },
        { text: 'Действия', value: 'actions', sortable: false, width: '100px' }
      ],
      courses: [
        {
          id: 1,
          name: 'Введение в программирование',
          description: 'Базовые концепции программирования на примере Python',
          teacher: 'Иванов И.И.',
          credits: 3,
          status: 'active',
          students_count: 25,
          created_at: '2024-01-15'
        },
        {
          id: 2,
          name: 'Веб-разработка',
          description: 'Создание современных веб-приложений',
          teacher: 'Петров П.П.',
          credits: 4,
          status: 'active',
          students_count: 18,
          created_at: '2024-01-16'
        },
        {
          id: 3,
          name: 'Базы данных',
          description: 'Проектирование и работа с базами данных',
          teacher: 'Сидорова А.А.',
          credits: 3,
          status: 'draft',
          students_count: 0,
          created_at: '2024-01-17'
        }
      ]
    }
  },
  computed: {
    filteredCourses() {
      let filtered = this.courses
      
      if (this.selectedTeacher) {
        filtered = filtered.filter(course => course.teacher === this.selectedTeacher)
      }
      
      if (this.selectedStatus) {
        filtered = filtered.filter(course => course.status === this.selectedStatus)
      }
      
      return filtered
    }
  },
  methods: {
    navigateTo(routeName) {
      router.visit(route(routeName))
    },
    
    getStatusColor(status) {
      const colors = {
        active: 'success',
        inactive: 'grey',
        draft: 'warning'
      }
      return colors[status]
    },
    
    getStatusName(status) {
      const names = {
        active: 'Активный',
        inactive: 'Неактивный',
        draft: 'В разработке'
      }
      return names[status]
    },
    
    formatDate(date) {
      return new Date(date).toLocaleDateString('ru-RU')
    },
    
    clearFilters() {
      this.searchQuery = ''
      this.selectedTeacher = null
      this.selectedStatus = null
    },
    
    viewCourse(course) {
      // Здесь будет логика просмотра курса
      console.log('Просмотр курса:', course)
    },
    
    editCourse(course) {
      router.visit(route('admin.courses.edit', { course: course.id }))
    },
    
    manageStudents(course) {
      // Здесь будет логика управления студентами курса
      console.log('Управление студентами курса:', course)
    },
    
    deleteCourse(course) {
      this.courseToDelete = course
      this.deleteDialog = true
    },
    
    confirmDelete() {
      // Здесь будет логика удаления курса
      console.log('Удаление курса:', this.courseToDelete)
      this.deleteDialog = false
      this.courseToDelete = null
    }
  }
}
</script>
