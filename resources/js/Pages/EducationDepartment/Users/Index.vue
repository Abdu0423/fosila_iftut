<template>
  <Layout role="education_department">
    <v-container fluid class="pa-6">
      <!-- Заголовок -->
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.users_title || 'Пользователи' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.users_subtitle || 'Просмотр всех пользователей системы' }}
          </p>
        </div>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="createUser"
        >
          {{ translations.messages?.add || 'Добавить' }}
        </v-btn>
      </div>

      <!-- Фильтры и поиск -->
      <v-card class="mb-6">
        <v-card-text>
          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="searchQuery"
                :label="translations.messages?.search || 'Поиск'"
                prepend-inner-icon="mdi-magnify"
                clearable
                variant="outlined"
                density="comfortable"
                @update:model-value="handleSearch"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
              <v-select
                v-model="roleFilter"
                :items="roleOptions"
                :label="translations.education_department?.filter_by_role || 'Фильтр по роли'"
                prepend-inner-icon="mdi-filter"
                clearable
                variant="outlined"
                density="comfortable"
                @update:model-value="handleRoleFilter"
              ></v-select>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>

      <!-- Таблица пользователей -->
      <v-card>
        <v-data-table
          :headers="headers"
          :items="users.data"
          :items-per-page="users.per_page"
          hide-default-footer
        >
          <template v-slot:item.name="{ item }">
            <div class="d-flex align-center py-2">
              <v-avatar color="primary" size="40" class="mr-3">
                <span class="text-white">{{ getInitials(item) }}</span>
              </v-avatar>
              <div>
                <div class="font-weight-medium">{{ item.name }} {{ item.last_name }}</div>
                <div class="text-caption text-medium-emphasis">{{ item.email }}</div>
              </div>
            </div>
          </template>

          <template v-slot:item.role="{ item }">
            <v-chip
              :color="getRoleColor(item.role?.name)"
              size="small"
            >
              {{ getRoleLabel(item.role?.name) }}
            </v-chip>
          </template>

          <template v-slot:item.group="{ item }">
            <span v-if="item.group">{{ item.group.name }}</span>
            <span v-else class="text-medium-emphasis">—</span>
          </template>

          <template v-slot:item.actions="{ item }">
            <div class="d-flex gap-2">
              <v-btn
                icon
                variant="text"
                color="primary"
                size="small"
                @click="editUser(item)"
                :title="translations.messages?.edit || 'Редактировать'"
              >
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
            </div>
          </template>
        </v-data-table>

        <!-- Пагинация -->
        <v-divider></v-divider>
        <div class="d-flex justify-center pa-4">
          <v-pagination
            :length="Math.ceil(users.total / users.per_page)"
            :model-value="users.current_page"
            @update:model-value="handlePageChange"
          ></v-pagination>
        </div>
      </v-card>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const page = usePage()
const translations = computed(() => page.props.translations || {})

const props = defineProps({
  users: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const searchQuery = ref(props.filters.search)
const roleFilter = ref(props.filters.role || null)

const headers = computed(() => [
  { title: translations.value.education_department?.user_name || 'Имя', key: 'name', sortable: false },
  { title: translations.value.education_department?.user_role || 'Роль', key: 'role', sortable: false },
  { title: translations.value.education_department?.user_group || 'Группа', key: 'group', sortable: false },
  { title: translations.value.education_department?.user_phone || 'Телефон', key: 'phone', sortable: false },
  { title: translations.value.messages?.actions || 'Действия', key: 'actions', sortable: false }
])

// Только преподаватели и студенты для роли образования
const roleOptions = computed(() => [
  { title: translations.value.navigation?.teacher || 'Преподаватель', value: 'teacher' },
  { title: translations.value.navigation?.student || 'Студент', value: 'student' }
])

const getInitials = (user) => {
  const first = user.name?.charAt(0)
  const last = user.last_name?.charAt(0)
  return (first + last).toUpperCase()
}

const getRoleColor = (role) => {
  const colors = {
    admin: 'error',
    teacher: 'success',
    student: 'info',
    education_department: 'warning'
  }
  return colors[role]
}

const getRoleLabel = (role) => {
  const labels = {
    admin: translations.value.navigation?.admin_panel || 'Администратор',
    teacher: translations.value.navigation?.teacher || 'Преподаватель',
    student: translations.value.navigation?.student || 'Студент',
    education_department: translations.value.education_department?.role_name || 'Отдел образования'
  }
  return labels[role] || role
}

const handleSearch = () => {
  router.get(route('education.users.index'), {
    search: searchQuery.value,
    role: roleFilter.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const handleRoleFilter = () => {
  router.get(route('education.users.index'), {
    search: searchQuery.value,
    role: roleFilter.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const handlePageChange = (page) => {
  router.get(route('education.users.index'), {
    page: page,
    search: searchQuery.value,
    role: roleFilter.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const createUser = () => {
  router.visit('/education/users/create')
}

const editUser = (user) => {
  router.visit(`/education/users/${user.id}/edit`)
}
</script>

