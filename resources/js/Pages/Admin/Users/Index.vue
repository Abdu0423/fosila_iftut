<template>
  <Layout role="admin">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('admin.user_management', {}, { default: 'Управление пользователями' }) }}</h1>
              <p class="text-body-1 text-medium-emphasis">{{ t('admin.create_manage_users', {}, { default: 'Создавайте и управляйте пользователями системы' }) }}</p>
            </div>
            <v-btn
              color="primary"
              size="large"
              prepend-icon="mdi-plus"
              @click="navigateTo('/admin/users/create')"
            >
              {{ t('admin.add_user', {}, { default: 'Добавить пользователя' }) }}
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Статистика -->
      <v-row class="mb-6">
        <v-col cols="12" md="2">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="primary" class="mb-4">mdi-account-group</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats.total }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ t('admin.total_users', {}, { default: 'Всего пользователей' }) }}</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="2">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="error" class="mb-4">mdi-shield-crown</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats.admins }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ t('admin.admins', {}, { default: 'Администраторов' }) }}</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="2">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="warning" class="mb-4">mdi-teach</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats.teachers }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ t('admin.teachers', {}, { default: 'Учителей' }) }}</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="2">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="info" class="mb-4">mdi-account</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats.students }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ t('admin.students', {}, { default: 'Студентов' }) }}</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="2">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="success" class="mb-4">mdi-check-circle</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats.verified }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ t('admin.verified', {}, { default: 'Подтвержденных' }) }}</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="2">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="warning" class="mb-4">mdi-clock</v-icon>
              <div class="text-h4 font-weight-bold">{{ stats.unverified }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ t('admin.unverified', {}, { default: 'Не подтвержденных' }) }}</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Flash сообщения -->
      <v-row v-if="flash && (flash.success || flash.error)" class="mb-6">
        <v-col cols="12">
                     <v-alert
             v-if="flash && flash.success"
             type="success"
             variant="tonal"
             closable
           >
             {{ flash.success }}
           </v-alert>
           <v-alert
             v-if="flash && flash.error"
             type="error"
             variant="tonal"
             closable
           >
             {{ flash.error }}
           </v-alert>
        </v-col>
      </v-row>

      <!-- Фильтры и поиск -->
      <v-row class="mb-6">
        <v-col cols="12" md="4">
          <v-text-field
            v-model="search"
            prepend-inner-icon="mdi-magnify"
            :label="t('admin.users.search_users', {}, { default: 'Поиск пользователей' })"
            variant="outlined"
            density="compact"
            clearable
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="3">
          <v-select
            v-model="roleFilter"
            :items="roleOptions"
            :label="t('admin.users.filter_by_role', {}, { default: 'Фильтр по роли' })"
            variant="outlined"
            density="compact"
            clearable
          ></v-select>
        </v-col>
        <v-col cols="12" md="3">
          <v-select
            v-model="statusFilter"
            :items="statusOptions"
            :label="t('admin.users.filter_by_status', {}, { default: 'Фильтр по статусу' })"
            variant="outlined"
            density="compact"
            clearable
          ></v-select>
        </v-col>
        <v-col cols="12" md="2">
          <v-btn
            color="secondary"
            variant="outlined"
            @click="clearFilters"
            prepend-icon="mdi-refresh"
          >
            {{ t('admin.users.reset', {}, { default: 'Сбросить' }) }}
          </v-btn>
        </v-col>
      </v-row>

      <!-- Таблица пользователей -->
      <v-card>
        <v-data-table
          :headers="headers"
          :items="filteredUsers"
          :search="search"
          :loading="loading"
          class="elevation-1"
        >
          <!-- Аватар пользователя -->
          <template v-slot:item.avatar="{ item }">
            <v-avatar size="40">
              <v-img :src="item.avatar" :alt="item.name"></v-img>
            </v-avatar>
          </template>

          <!-- Имя пользователя -->
          <template v-slot:item.name="{ item }">
            <div>
              <div class="font-weight-medium">{{ item.name }} {{ item.last_name }}</div>
              <div class="text-caption text-medium-emphasis">{{ item.email }}</div>
            </div>
          </template>

          <!-- Роль -->
          <template v-slot:item.role="{ item }">
            <v-chip
              :color="getRoleColor(item.role)"
              size="small"
              variant="tonal"
            >
              {{ getRoleDisplayName(item.role) }}
            </v-chip>
          </template>

          <!-- Статус -->
          <template v-slot:item.status="{ item }">
            <v-chip
              :color="item.status === t('admin.users.confirmed', {}, { default: 'Подтвержден' }) ? 'success' : 'warning'"
              size="small"
              variant="tonal"
            >
              {{ item.status }}
            </v-chip>
          </template>

          <!-- Дата создания -->
          <template v-slot:item.created_at="{ item }">
            <div class="text-caption">{{ item.created_at }}</div>
          </template>

          <!-- Действия -->
          <template v-slot:item.actions="{ item }">
            <div class="d-flex gap-2">
              <v-btn
                icon="mdi-eye"
                variant="text"
                size="small"
                color="info"
                @click="viewUser(item)"
                :title="t('admin.users.view', {}, { default: 'Просмотр' })"
              ></v-btn>
              <v-btn
                icon="mdi-pencil"
                variant="text"
                size="small"
                color="primary"
                @click="editUser(item)"
                :title="t('admin.users.edit', {}, { default: 'Редактировать' })"
              ></v-btn>
              <v-btn
                icon="mdi-delete"
                variant="text"
                size="small"
                color="error"
                @click="deleteUser(item)"
                :title="t('admin.users.delete', {}, { default: 'Удалить' })"
              ></v-btn>
            </div>
          </template>
        </v-data-table>
      </v-card>

      <!-- Диалог удаления -->
      <v-dialog v-model="deleteDialog" max-width="400">
        <v-card>
          <v-card-title class="text-h6">
            {{ t('admin.users.delete_confirmation', {}, { default: 'Подтверждение удаления' }) }}
          </v-card-title>
          <v-card-text>
            {{ t('admin.users.delete_confirmation_text', {}, { default: 'Вы действительно хотите удалить пользователя' }) }} <strong>{{ userToDelete?.name }} {{ userToDelete?.last_name }}</strong>?
            {{ t('admin.users.delete_warning', {}, { default: 'Это действие нельзя отменить.' }) }}
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="grey"
              variant="text"
              @click="deleteDialog = false"
            >
              {{ t('admin.users.cancel', {}, { default: 'Отмена' }) }}
            </v-btn>
            <v-btn
              color="error"
              variant="text"
              @click="confirmDelete"
              :loading="deleting"
            >
              {{ t('admin.users.delete', {}, { default: 'Удалить' }) }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'

const { t } = useI18n()

// Props из Inertia
const props = defineProps({
  users: {
    type: Array,
    default: () => []
  },
  roles: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

// Получаем flash сообщения
const page = usePage()
const flash = computed(() => page.props.flash || {})

// Состояние
const loading = ref(false)
const search = ref('')
const roleFilter = ref(null)
const statusFilter = ref(null)
const deleteDialog = ref(false)
const userToDelete = ref(null)
const deleting = ref(false)

// Заголовки таблицы
const headers = [
  { title: 'Аватар', key: 'avatar', sortable: false, width: '80px' },
  { title: 'Пользователь', key: 'name', sortable: true },
  { title: 'Роль', key: 'role', sortable: true },
  { title: 'Статус', key: 'status', sortable: true },
  { title: 'Дата регистрации', key: 'created_at', sortable: true },
  { title: 'Действия', key: 'actions', sortable: false, width: '100px' }
]

// Опции фильтров
const roleOptions = computed(() => {
  return props.roles.map(role => ({
    title: role.display_name,
    value: role.name
  }))
})

const statusOptions = computed(() => [
  { title: t('admin.users.confirmed', {}, { default: 'Подтвержден' }), value: 'Подтвержден' },
  { title: t('admin.users.not_confirmed', {}, { default: 'Не подтвержден' }), value: 'Не подтвержден' }
])

// Отфильтрованные пользователи
const filteredUsers = computed(() => {
  let filtered = props.users

  // Поиск по ФИО, email
  if (search.value) {
    const searchLower = search.value.toLowerCase()
    filtered = filtered.filter(user => 
      (user.name && user.name.toLowerCase().includes(searchLower)) ||
      (user.last_name && user.last_name.toLowerCase().includes(searchLower)) ||
      (user.middle_name && user.middle_name && user.middle_name.toLowerCase().includes(searchLower)) ||
      (user.email && user.email.toLowerCase().includes(searchLower))
    )
  }

  if (roleFilter.value) {
    filtered = filtered.filter(user => user.role === roleFilter.value)
  }

  if (statusFilter.value) {
    filtered = filtered.filter(user => user.status === statusFilter.value)
  }

  return filtered
})

// Методы
const navigateTo = (route) => {
  router.visit(route)
}

const getRoleColor = (role) => {
  const colors = {
    'admin': 'error',
    'teacher': 'warning',
    'student': 'primary'
  }
  return colors[role]
}

const getRoleDisplayName = (role) => {
  const displayNames = {
    'admin': 'Администратор',
    'teacher': 'Учитель',
    'student': 'Студент'
  }
  return displayNames[role] || role
}

const clearFilters = () => {
  search.value = ''
  roleFilter.value = null
  statusFilter.value = null
}

const viewUser = (user) => {
  router.visit(`/admin/users/${user.id}`)
}

const editUser = (user) => {
  router.visit(`/admin/users/${user.id}/edit`)
}

const deleteUser = (user) => {
  userToDelete.value = user
  deleteDialog.value = true
}

const confirmDelete = async () => {
  if (!userToDelete.value) return

  console.log('Начинаем удаление пользователя:', userToDelete.value.id)
  deleting.value = true
  
  try {
    await router.post(`/admin/users/${userToDelete.value.id}/delete`, {}, {
      onSuccess: () => {
        console.log('Пользователь успешно удален')
        deleteDialog.value = false
        userToDelete.value = null
      },
      onError: (errors) => {
        console.error('Ошибка при удалении пользователя:', errors)
      },
      onFinish: () => {
        deleting.value = false
      }
    })
  } catch (error) {
    console.error('Ошибка при удалении пользователя:', error)
    deleting.value = false
  }
}

onMounted(() => {
  console.log('Страница пользователей загружена')
  console.log('Flash сообщения:', flash.value)
  console.log('Page props:', page.props)
})
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
