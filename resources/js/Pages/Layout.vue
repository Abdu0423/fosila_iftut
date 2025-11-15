<template>
  <v-app>
    <!-- Боковое меню -->
    <v-navigation-drawer
      v-model="drawer"
      app
      :temporary="isMobile"
      :permanent="!isMobile"
      :color="navDrawerColor"
      :width="navDrawerWidth"
      class="modern-drawer"
      :class="{ 'drawer-dark': isDarkTheme }"
    >
      <!-- Заголовок меню -->
      <div class="drawer-header" :class="{ 'drawer-header-dark': isDarkTheme }">
        <div class="d-flex align-center pa-4">
          <v-avatar :color="isDarkTheme ? 'white' : 'primary'" size="48" class="mr-3">
            <v-icon size="28" :color="isDarkTheme ? 'primary' : 'white'">{{ headerIcon }}</v-icon>
          </v-avatar>
          <div class="flex-grow-1">
            <div class="text-h6 font-weight-bold" :class="headerTitleClass">
              {{ headerTitle }}
            </div>
            <div class="text-caption" :class="headerSubtitleClass">
              {{ headerSubtitle }}
            </div>
          </div>
        </div>
      </div>

      <v-divider :class="{ 'divider-dark': isDarkTheme }"></v-divider>

      <!-- Информация о пользователе в меню -->
      <div class="user-info-panel pa-3 ma-3" :class="{ 'user-info-dark': isDarkTheme }">
        <div class="d-flex align-center">
          <v-avatar size="40" class="mr-3">
            <v-img v-if="userAvatar" :src="userAvatar" :alt="userName"></v-img>
            <v-icon v-else>mdi-account-circle</v-icon>
          </v-avatar>
          <div class="flex-grow-1 text-truncate">
            <div class="text-body-2 font-weight-medium" :class="userInfoTextClass">{{ userName }}</div>
            <div class="text-caption" :class="userInfoSubtextClass">{{ userRole }}</div>
          </div>
        </div>
      </div>

      <!-- Пункты меню -->
      <v-list class="menu-list pa-2" nav>
        <v-list-item
          v-for="item in menuItems"
          :key="item.title"
          :prepend-icon="item.icon"
          :title="item.title"
          :active="isActiveRoute(item.route)"
          :class="[
            'menu-item',
            { 'menu-item-active': isActiveRoute(item.route) },
            { 'menu-item-dark': isDarkTheme }
          ]"
          @click="navigateTo(item.route)"
          rounded="lg"
        >
          <template v-slot:prepend>
            <v-icon :class="{ 'active-icon': isActiveRoute(item.route) }">{{ item.icon }}</v-icon>
          </template>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <!-- Верхняя панель -->
    <v-app-bar app :color="appBarColor" :elevation="appBarElevation" class="modern-appbar">
      <v-app-bar-nav-icon 
        @click="drawer = !drawer"
        :color="appBarIconColor"
        class="appbar-nav-icon"
      ></v-app-bar-nav-icon>
      
      <v-toolbar-title class="text-h6 font-weight-bold d-flex align-center" :class="appBarTitleClass">
        <span class="d-none d-sm-inline">{{ appBarTitle }}</span>
        <span class="d-inline d-sm-none">{{ appBarTitleShort }}</span>
      </v-toolbar-title>
      
      <v-spacer></v-spacer>

      <!-- Поиск (только на десктопе) -->
      <v-text-field
        v-if="!isMobile"
        density="compact"
        variant="solo"
        label="Поиск..."
        prepend-inner-icon="mdi-magnify"
        single-line
        hide-details
        class="mr-4 search-field"
        style="max-width: 300px;"
      ></v-text-field>
      
      <!-- Уведомления -->
      <v-btn icon :color="appBarIconColor" class="mr-2 notification-btn">
        <v-badge 
          :content="notifications.length" 
          :model-value="notifications.length > 0" 
          color="error"
          overlap
        >
          <v-icon>mdi-bell</v-icon>
        </v-badge>
      </v-btn>
      
      <!-- Профиль -->
      <v-menu offset-y min-width="250">
        <template v-slot:activator="{ props }">
          <v-btn 
            :color="appBarIconColor" 
            v-bind="props" 
            class="profile-btn"
            :variant="isMobile ? 'icon' : 'text'"
          >
            <v-avatar size="32" class="mr-2">
              <v-img v-if="userAvatar" :src="userAvatar" :alt="userName"></v-img>
              <v-icon v-else>mdi-account</v-icon>
            </v-avatar>
            <span v-if="!isMobile" class="d-none d-md-inline text-none">{{ userName }}</span>
            <v-icon v-if="!isMobile" class="ml-1">mdi-menu-down</v-icon>
          </v-btn>
        </template>
        
        <v-list class="profile-menu">
          <v-list-item class="user-menu-header">
            <template v-slot:prepend>
              <v-avatar size="48">
                <v-img v-if="userAvatar" :src="userAvatar" :alt="userName"></v-img>
                <v-icon v-else>mdi-account-circle</v-icon>
              </v-avatar>
            </template>
            <v-list-item-title class="font-weight-bold">{{ userName }}</v-list-item-title>
            <v-list-item-subtitle>{{ userRole }}</v-list-item-subtitle>
          </v-list-item>

          <v-divider class="my-2"></v-divider>

          <v-list-item @click="goToProfile" class="menu-action-item">
            <template v-slot:prepend>
              <v-icon color="primary">mdi-account</v-icon>
            </template>
            <v-list-item-title>Профиль</v-list-item-title>
          </v-list-item>
          
          <v-list-item @click="goToSettings" class="menu-action-item">
            <template v-slot:prepend>
              <v-icon color="primary">mdi-cog</v-icon>
            </template>
            <v-list-item-title>Настройки</v-list-item-title>
          </v-list-item>
          
          <v-divider class="my-2"></v-divider>
          
          <v-list-item @click="logout" class="menu-action-item logout-item">
            <template v-slot:prepend>
              <v-icon color="error">mdi-logout</v-icon>
            </template>
            <v-list-item-title class="error--text">Выйти</v-list-item-title>
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { useDisplay } from 'vuetify'
import NotificationSnackbar from '../Components/NotificationSnackbar.vue'

const page = usePage()
const { mobile, xs, sm, mdAndDown } = useDisplay()

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
const windowWidth = ref(window.innerWidth)

// Адаптивность
const isMobile = computed(() => windowWidth.value < 960)
const navDrawerWidth = computed(() => isMobile.value ? '280' : '300')
const appBarElevation = computed(() => isMobile.value ? 2 : 1)

// Обработчик изменения размера окна
const handleResize = () => {
  windowWidth.value = window.innerWidth
  if (windowWidth.value < 960) {
    drawer.value = false
  } else {
    drawer.value = true
  }
}

onMounted(() => {
  window.addEventListener('resize', handleResize)
  handleResize()
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})

// Данные пользователя
const user = computed(() => page.props.auth?.user || {})
const userName = computed(() => user.value.name || 'Пользователь')
const userAvatar = computed(() => user.value.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(userName.value)}`)
const userRole = computed(() => {
  switch (props.role) {
    case 'admin': return 'Администратор'
    case 'teacher': return 'Преподаватель'
    default: return 'Студент'
  }
})

// Количество непрочитанных сообщений
const unreadChatsCount = computed(() => {
  return page.props.unreadChatsCount || 0
})

// Уведомления
const notifications = ref([
  { id: 1, message: 'Новое уведомление', time: '2 мин назад' }
])

// Текущий год
const currentYear = computed(() => new Date().getFullYear())

// Вычисляемые свойства для темы (единый дизайн учителя для всех)
const isDarkTheme = computed(() => true) // Темная тема для всех
const navDrawerColor = computed(() => 'indigo-darken-3') // Единый цвет для всех
const appBarColor = computed(() => 'indigo-darken-2') // Единый цвет для всех
const appBarIconColor = computed(() => 'white')
const appBarTitleClass = computed(() => 'text-white')
const headerTitleClass = computed(() => 'text-white')
const headerSubtitleClass = computed(() => 'text-grey-lighten-1')
const userInfoTextClass = computed(() => 'text-white')
const userInfoSubtextClass = computed(() => 'text-grey-lighten-1')
const footerTextClass = computed(() => 'text-grey-lighten-1')

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
    case 'admin': return 'ИФТУТ Админ'
    case 'teacher': return 'ИФТУТ Преподаватель'
    default: return 'ИФТУТ Студент'
  }
})

const appBarTitleShort = computed(() => {
  switch (props.role) {
    case 'admin': return 'Админ'
    case 'teacher': return 'Препод'
    default: return 'ИФТУТ'
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

// Определяем маршрут logout в зависимости от роли
const logoutRoute = computed(() => {
  switch (props.role) {
    case 'admin':
      return 'admin.logout'
    case 'teacher':
      return 'teacher.logout'
    case 'student':
      return 'student.logout'
    default:
      return 'logout'
  }
})

const logout = () => {
  router.visit(route(logoutRoute.value), {
    method: 'post'
  })
}
</script>

<style scoped>
/* Основные стили для навигационного меню */
.modern-drawer {
  box-shadow: 2px 0 12px rgba(0, 0, 0, 0.08);
}

.drawer-dark {
  background: linear-gradient(180deg, var(--v-theme-background) 0%, rgba(0, 0, 0, 0.2) 100%);
}

/* Заголовок меню */
.drawer-header {
  background: linear-gradient(135deg, rgba(33, 150, 243, 0.1) 0%, rgba(33, 150, 243, 0.05) 100%);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.drawer-header-dark {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

/* Панель информации о пользователе */
.user-info-panel {
  background: linear-gradient(135deg, rgba(33, 150, 243, 0.08) 0%, rgba(33, 150, 243, 0.03) 100%);
  border-radius: 12px;
  transition: all 0.3s ease;
}

.user-info-panel:hover {
  background: linear-gradient(135deg, rgba(33, 150, 243, 0.12) 0%, rgba(33, 150, 243, 0.06) 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.user-info-dark {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0.06) 100%);
}

.user-info-dark:hover {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0.1) 100%);
}

/* Пункты меню */
.menu-list {
  overflow-y: auto;
  max-height: calc(100vh - 280px);
}

.menu-list::-webkit-scrollbar {
  width: 6px;
}

.menu-list::-webkit-scrollbar-track {
  background: transparent;
}

.menu-list::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 3px;
}

.menu-list::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.3);
}

.menu-item {
  margin: 4px 0;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.menu-item:hover {
  background: linear-gradient(90deg, rgba(33, 150, 243, 0.1) 0%, rgba(33, 150, 243, 0.05) 100%);
  transform: translateX(4px);
}

.menu-item-dark:hover {
  background: linear-gradient(90deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.08) 100%);
}

.menu-item-active {
  background: linear-gradient(90deg, rgba(33, 150, 243, 0.2) 0%, rgba(33, 150, 243, 0.1) 100%);
  border-left: 4px solid rgb(33, 150, 243);
  font-weight: 600;
  transform: translateX(0);
}

.menu-item-active.menu-item-dark {
  background: linear-gradient(90deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
  border-left: 4px solid white;
}

.menu-item-active:hover {
  transform: translateX(0);
}

.active-icon {
  color: rgb(33, 150, 243);
  animation: iconPulse 1s ease-in-out;
}

@keyframes iconPulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

/* Разделители */
.divider-dark {
  border-color: rgba(255, 255, 255, 0.12);
}

/* App Bar стили */
.modern-appbar {
  backdrop-filter: blur(10px);
}

.appbar-nav-icon {
  transition: all 0.3s ease;
}

.appbar-nav-icon:hover {
  transform: rotate(90deg);
}

.search-field {
  transition: all 0.3s ease;
}

.search-field:focus-within {
  transform: scale(1.02);
}

.notification-btn {
  transition: all 0.3s ease;
}

.notification-btn:hover {
  transform: scale(1.1) rotate(15deg);
}

.profile-btn {
  transition: all 0.3s ease;
  border-radius: 24px !important;
}

.profile-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

/* Меню профиля */
.profile-menu {
  border-radius: 12px;
  overflow: hidden;
}

.user-menu-header {
  background: linear-gradient(135deg, rgba(33, 150, 243, 0.1) 0%, rgba(33, 150, 243, 0.05) 100%);
  padding: 16px;
}

.menu-action-item {
  transition: all 0.3s ease;
}

.menu-action-item:hover {
  background: rgba(33, 150, 243, 0.08);
  transform: translateX(4px);
}

.logout-item:hover {
  background: rgba(244, 67, 54, 0.08);
}

/* Адаптивные стили */
@media (max-width: 960px) {
  .menu-list {
    max-height: calc(100vh - 250px);
  }
}

@media (max-width: 600px) {
  .user-info-panel {
    margin: 12px 8px;
    padding: 8px;
  }

  .drawer-header .d-flex {
    padding: 16px !important;
  }

  .menu-list {
    max-height: calc(100vh - 230px);
  }
}

/* Анимации плавного появления */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modern-drawer,
.modern-appbar {
  animation: fadeIn 0.3s ease-out;
}

/* Улучшенный скроллбар для темной темы */
.drawer-dark .menu-list::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
}

.drawer-dark .menu-list::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}
</style>
