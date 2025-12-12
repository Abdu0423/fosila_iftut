<template>
  <AdminLayout>
    <v-row>
      <v-col cols="12">
        <div class="d-flex justify-space-between align-center mb-4">
          <h1 class="text-h4">Учителя</h1>
          <v-btn
            color="primary"
            prepend-icon="mdi-plus"
            :href="route('admin.teachers.create')"
          >
            Добавить учителя
          </v-btn>
        </div>
      </v-col>
    </v-row>

    <!-- Фильтры -->
    <v-row>
      <v-col cols="12">
        <v-card>
          <v-card-text>
            <v-row>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="filters.search"
                  label="Поиск по имени или email"
                  prepend-icon="mdi-magnify"
                  clearable
                  @keyup.enter="applyFilters"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="4">
                <v-select
                  v-model="filters.department_id"
                  label="Кафедра"
                  :items="departments"
                  item-title="name"
                  item-value="id"
                  clearable
                ></v-select>
              </v-col>
              
              <v-col cols="12" md="4">
                <v-select
                  v-model="filters.status"
                  label="Статус"
                  :items="[
                    { title: 'Активные', value: 'active' },
                    { title: 'Неактивные', value: 'inactive' }
                  ]"
                  item-title="title"
                  item-value="value"
                  clearable
                ></v-select>
              </v-col>
            </v-row>
            
            <v-row class="mt-4">
              <v-col cols="12" class="d-flex justify-end">
                <v-btn
                  variant="outlined"
                  color="secondary"
                  @click="clearFilters"
                  class="mr-3"
                >
                  <v-icon start>mdi-refresh</v-icon>
                  Сбросить
                </v-btn>
                <v-btn
                  color="primary"
                  @click="applyFilters"
                >
                  <v-icon start>mdi-filter</v-icon>
                  Применить
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Таблица учителей -->
    <v-row>
      <v-col cols="12">
        <v-card>
          <v-data-table
            :headers="headers"
            :items="teachers.data"
            :loading="loading"
            :items-per-page="teachers.per_page"
            :total-items="teachers.total"
            @update:options="handleTableUpdate"
          >
            <template v-slot:item.name="{ item }">
              <div class="d-flex align-center">
                <v-avatar size="32" class="mr-2">
                  <v-img
                    :src="`https://ui-avatars.com/api/?name=${item.raw.name}&background=random`"
                    :alt="item.raw.name"
                  ></v-img>
                </v-avatar>
                <div>
                  <div class="font-weight-medium">{{ item.raw.name }}</div>
                  <div class="text-caption">{{ item.raw.email }}</div>
                </div>
              </div>
            </template>

            <template v-slot:item.department="{ item }">
              {{ item.raw.department?.name }}
            </template>

            <template v-slot:item.is_active="{ item }">
              <v-chip
                :color="item.raw.is_active ? 'success' : 'error'"
                size="small"
              >
                {{ item.raw.is_active ? 'Активен' : 'Неактивен' }}
              </v-chip>
            </template>

            <template v-slot:item.actions="{ item }">
              <v-btn
                icon="mdi-eye"
                variant="text"
                size="small"
                color="info"
                :href="route('admin.teachers.show', item.raw.id)"
              ></v-btn>
              
              <v-btn
                icon="mdi-pencil"
                variant="text"
                size="small"
                color="warning"
                :href="route('admin.teachers.edit', item.raw.id)"
              ></v-btn>
              
              <v-btn
                icon="mdi-delete"
                variant="text"
                size="small"
                color="error"
                @click="deleteTeacher(item.raw.id)"
              ></v-btn>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>

    <!-- Диалог подтверждения удаления -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title>Подтверждение удаления</v-card-title>
        <v-card-text>
          Вы действительно хотите удалить этого учителя?
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
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

const props = defineProps({
  teachers: Object,
  departments: Array,
  filters: Object,
})

const loading = ref(false)
const deleteDialog = ref(false)
const teacherToDelete = ref(null)

const headers = [
  { title: 'Имя и Email', key: 'name', sortable: true },
  { title: 'Кафедра', key: 'department', sortable: false },
  { title: 'Статус', key: 'is_active', sortable: true },
  { title: 'Дата регистрации', key: 'created_at', sortable: true },
  { title: 'Действия', key: 'actions', sortable: false },
]

const filters = reactive({
  search: props.filters?.search,
  department_id: props.filters?.department_id || null,
  status: props.filters?.status || null,
})

const applyFilters = () => {
  if (loading.value) return
  
  loading.value = true
  router.get(route('admin.teachers.index'), filters, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    onFinish: () => {
      loading.value = false
    }
  })
}

const clearFilters = () => {
  Object.keys(filters).forEach(key => {
    filters[key] = null
  })
  filters.search = ''
  
  loading.value = true
  router.get(route('admin.teachers.clearFilters'), {}, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    onFinish: () => {
      loading.value = false
    }
  })
}

const handleTableUpdate = (options) => {
  if (loading.value) return
  
  loading.value = true
  router.get(route('admin.teachers.index'), {
    ...filters,
    page: options.page,
    sortBy: options.sortBy,
    sortDesc: options.sortDesc,
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    onFinish: () => {
      loading.value = false
    }
  })
}

const deleteTeacher = (id) => {
  teacherToDelete.value = id
  deleteDialog.value = true
}

const confirmDelete = () => {
  router.delete(route('admin.teachers.destroy', teacherToDelete.value), {
    onSuccess: () => {
      deleteDialog.value = false
      teacherToDelete.value = null
    }
  })
}
</script>
