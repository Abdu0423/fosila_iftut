<template>
  <v-app>
    <!-- Боковое меню -->
    <v-navigation-drawer
      v-model="drawer"
      app
      :dark="isDarkTheme"
      :color="themeColor"
      width="280"
    >
      <v-list-item>
        <template v-slot:prepend>
          <v-icon class="mr-2">{{ headerIcon }}</v-icon>
        </template>
        <v-list-item-title class="text-h6">
          {{ headerTitle }}
        </v-list-item-title>
        <v-list-item-subtitle>{{ headerSubtitle }}</v-list-item-subtitle>
      </v-list-item>

      <v-divider></v-divider>

      <v-list dense nav>
        <v-list-item
          v-for="item in menuItems"
          :key="item.title"
          :prepend-icon="item.icon"
          :title="item.title"
          :active="isActiveRoute(item.route)"
          :disabled="isActiveRoute(item.route)"
          @click="navigateTo(item.route)"
        ></v-list-item>
      </v-list>
    </v-navigation-drawer>

    <!-- Верхняя панель -->
    <v-app-bar app :color="appBarColor" elevation="1">
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      
      <v-toolbar-title class="text-h6 font-weight-bold" :class="appBarTitleClass">
        {{ appBarTitle }}
      </v-toolbar-title>
      
      <v-spacer></v-spacer>
      
      <!-- Уведомления -->
      <v-btn icon class="mr-2">
        <v-badge :content="notifications.length" :value="notifications.length" color="error">
          <v-icon>mdi-bell</v-icon>
        </v-badge>
      </v-btn>
      
      <!-- Профиль -->
      <v-menu offset-y>
        <template v-slot:activator="{ props }">
          <v-btn icon v-bind="props">
            <v-avatar size="32">
              <v-img v-if="userAvatar" :src="userAvatar" :alt="userName"></v-img>
              <v-icon v-else>mdi-account</v-icon>
            </v-avatar>
          </v-btn>
        </template>
        
        <v-list>
          <v-list-item @click="goToProfile">
            <template v-slot:prepend>
              <v-icon>mdi-account</v-icon>
            </template>
            <v-list-item-title>Профиль</v-list-item-title>
          </v-list-item>
          
          <v-list-item @click="goToSettings">
            <template v-slot:prepend>
              <v-icon>mdi-cog</v-icon>
            </template>
            <v-list-item-title>Настройки</v-list-item-title>
          </v-list-item>
          
          <v-divider></v-divider>
          
          <v-list-item @click="logout">
            <template v-slot:prepend>
              <v-icon>mdi-logout</v-icon>
            </template>
            <v-list-item-title>Выйти</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-app-bar>

    <!-- Основной контент -->
    <v-main>
      <v-container fluid>
        <slot></slot>
      </v-container>
    </v-main>

    <!-- Глобальные уведомления -->
    <NotificationSnackbar />

    <!-- Загрузка -->
    <v-overlay
      v-model="loading"
      class="align-center justify-center"
    >
      <v-progress-circular
        indeterminate
        size="64"
        color="primary"
      ></v-progress-circular>
    </v-overlay>
  </v-app>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import NotificationSnackbar from '../Components/NotificationSnackbar.vue'

const page = usePage()

// Props
const props = defineProps({
  role: {
    type: String,
    default: 'student',
    validator: (value) => ['admin', 'teacher', 'student'].includes(value)
  }
})

// Состояние
const drawer = ref(true)
const showNotifications = ref(false)
const loading = ref(false)

// Данные пользователя
const user = computed(() => page.props.auth?.user || {})
const userName = computed(() => user.value.name || 'Пользователь')
const userAvatar = computed(() => user.value.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(userName.value)}`)

// Количество непрочитанных сообщений
const unreadChatsCount = computed(() => {
  return page.props.unreadChatsCount || 0
})

// Уведомления
const notifications = ref([
  { id: 1, message: 'Новое уведомление', time: '2 мин назад' }
])

// Вычисляемые свойства для темы
const isDarkTheme = computed(() => props.role === 'admin' || props.role === 'teacher')
const themeColor = computed(() => isDarkTheme.value ? 'primary' : 'white')
const appBarColor = computed(() => isDarkTheme.value ? 'white' : 'primary')
const appBarTitleClass = computed(() => isDarkTheme.value ? 'primary--text' : 'white--text')

// Заголовки в зависимости от роли
const headerIcon = computed(() => {
  switch (props.role) {
    case 'admin': return 'mdi-shield-crown'
    case 'teacher': return 'mdi-teach'
    default: return 'mdi-school'
  }
})

const headerTitle = computed(() => {
  switch (props.role) {
    case 'admin': return 'Админ панель'
    case 'teacher': return 'Панель учителя'
    default: return 'IFTUT - Дистанционное обучение'
  }
})

const headerSubtitle = computed(() => {
  switch (props.role) {
    case 'admin': return 'Управление системой'
    case 'teacher': return 'Управление курсами и студентами'
    default: return 'Обучение'
  }
})

const appBarTitle = computed(() => {
  switch (props.role) {
    case 'admin': return 'Fosila Admin'
    case 'teacher': return 'Fosila Teacher'
    default: return 'Fosila Student'
  }
})

// Пункты меню в зависимости от роли
const menuItems = computed(() => {
  switch (props.role) {
    case 'admin':
      return [
        { title: 'Главная', icon: 'mdi-view-dashboard', route: '/admin' },
        { title: 'Пользователи', icon: 'mdi-account-group', route: '/admin/users' },
        //{ title: 'Курсы', icon: 'mdi-book-open-variant', route: '/admin/courses' },
        { title: 'Предметы', icon: 'mdi-book-education', route: '/admin/subjects' },
        { title: 'Силлабусы', icon: 'mdi-file-document-multiple', route: '/admin/syllabuses' },
        { title: 'Уроки', icon: 'mdi-teach', route: '/admin/lessons' },
        { title: 'Тесты', icon: 'mdi-help-circle', route: '/admin/tests' },
        { title: 'Оценки', icon: 'mdi-star', route: '/admin/grades' },
        { title: 'Расписание', icon: 'mdi-calendar-clock', route: '/admin/schedules' },
        { title: 'Задания', icon: 'mdi-clipboard-text', route: '/admin/assignments' },
        { title: 'Библиотека', icon: 'mdi-library', route: '/admin/library' },
        { title: 'Отчеты', icon: 'mdi-chart-bar', route: '/admin/reports' },
        { title: 'Чат', icon: 'mdi-chat', route: '/admin/chat' },
        { title: 'Настройки', icon: 'mdi-cog', route: '/admin/settings' }
      ]
    
    case 'teacher':
      return [
        { title: 'Главная', icon: 'mdi-view-dashboard', route: '/teacher' },
        //{ title: 'Мои курсы', icon: 'mdi-book-open-variant', route: '/teacher/courses' },
        { title: 'Мои уроки', icon: 'mdi-teach', route: '/teacher/lessons' },
        { title: 'Мои тесты', icon: 'mdi-help-circle', route: '/teacher/tests' },
        { title: 'Оценки', icon: 'mdi-star', route: '/teacher/grades' },
        { title: 'Мои студенты', icon: 'mdi-account-group', route: '/teacher/students' },
        { title: 'Расписание', icon: 'mdi-calendar-clock', route: '/teacher/schedule' },
        { title: 'Силлабусы', icon: 'mdi-file-document-multiple', route: '/teacher/syllabuses' },
        //{ title: 'Материалы', icon: 'mdi-file-multiple', route: '/teacher/materials' },
        //{ title: 'Отчеты', icon: 'mdi-chart-bar', route: '/teacher/reports' },
        //{ title: 'Профиль', icon: 'mdi-account', route: '/teacher/profile' },
        //{ title: 'Настройки', icon: 'mdi-cog', route: '/teacher/settings' },
        { title: 'Чат', icon: 'mdi-chat', route: '/teacher/chat' },
      ]
    
    default: // student
      return [
        { title: 'Главная', icon: 'mdi-view-dashboard', route: '/student/' },
        //{ title: 'Мои курсы', icon: 'mdi-book-open-variant', route: '/student/courses' },
        { title: 'Расписание', icon: 'mdi-calendar-clock', route: '/student/schedule' },
        { title: 'Экзамены', icon: 'mdi-file-document-edit', route: '/student/tests' },
        { title: 'Задания', icon: 'mdi-clipboard-text', route: '/student/assignments' },
        { title: 'Чат', icon: 'mdi-chat', route: '/student/chat' },
        { title: 'Библиотека', icon: 'mdi-library', route: '/student/library' },
        { title: 'Оценки', icon: 'mdi-star', route: '/student/grades' }
      ]
  }
})

// Методы
const navigateTo = (route) => {
  router.visit(route)
}

const isActiveRoute = (route) => {
  const currentPath = window.location.pathname
  // Для точного совпадения
  if (currentPath === route) {
    return true
  }
  // Для маршрута '/' проверяем точное совпадение
  if (route === '/student/' && currentPath === '/student') {
    return true
  }
  return false
}

const goToProfile = () => {
  switch (props.role) {
    case 'admin':
      router.visit(route('admin.profile'))
      break
    case 'teacher':
      router.visit(route('teacher.profile.index'))
      break
    default:
      router.visit(route('profile.index'))
  }
}

const goToSettings = () => {
  switch (props.role) {
    case 'admin':
      router.visit('/admin/settings')
      break
    case 'teacher':
      router.visit('/teacher/settings')
      break
    default:
      router.visit('/settings')
  }
}

const logout = () => {
  router.visit(route('logout'), {
    method: 'post'
  })
}
</script>

<style scoped>
.v-navigation-drawer {
  border-right: 1px solid rgba(0, 0, 0, 0.12);
}

.v-navigation-drawer--dark {
  border-right: 1px solid rgba(255, 255, 255, 0.12);
}

.v-list-item {
  margin: 4px 8px;
  border-radius: 8px;
}

.v-list-item:hover {
  background-color: rgba(var(--v-theme-primary), 0.1);
}

.v-navigation-drawer--dark .v-list-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.v-list-item--active {
  background-color: rgba(var(--v-theme-primary), 0.2) !important;
  border-left: 4px solid rgb(var(--v-theme-primary));
}

.v-navigation-drawer--dark .v-list-item--active {
  background-color: rgba(255, 255, 255, 0.2) !important;
  border-left: 4px solid white;
}

.v-list-item--disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.v-app-bar {
  border-bottom: 1px solid #e0e0e0;
}

.v-app-bar--dark {
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}
</style>
