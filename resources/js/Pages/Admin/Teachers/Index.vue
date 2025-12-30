<template>
  <AdminLayout>
    <v-row>
      <v-col cols="12">
        <div class="d-flex justify-space-between align-center mb-4">
          <h1 class="text-h4">{{ t('admin.teachers.title') }}</h1>
          <v-btn
            color="primary"
            prepend-icon="mdi-plus"
            :href="route('admin.teachers.create')"
          >
            {{ t('admin.teachers.add_teacher') }}
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
                  :label="t('admin.teachers.search_placeholder')"
                  prepend-icon="mdi-magnify"
                  clearable
                  @keyup.enter="applyFilters"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="4">
                <v-select
                  v-model="filters.department_id"
                  :label="t('admin.teachers.department')"
                  :items="departments"
                  item-title="name"
                  item-value="id"
                  clearable
                ></v-select>
              </v-col>
              
              <v-col cols="12" md="4">
                <v-select
                  v-model="filters.status"
                  :label="t('admin.teachers.status')"
                  :items="statusItems"
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
                  {{ t('admin.teachers.reset') }}
                </v-btn>
                <v-btn
                  color="primary"
                  @click="applyFilters"
                >
                  <v-icon start>mdi-filter</v-icon>
                  {{ t('admin.teachers.apply') }}
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
            :footer-props="{
              'items-per-page-options': [10, 15, 25, 50, 100],
              'show-first-last-page': true
            }"
            :pagination-props="{
              'total-visible': 5
            }"
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
                {{ item.raw.is_active ? t('admin.teachers.active_status') : t('admin.teachers.inactive_status') }}
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
        <v-card-title>{{ t('admin.teachers.delete_confirmation') }}</v-card-title>
        <v-card-text>
          {{ t('admin.teachers.delete_confirmation_text') }}
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="deleteDialog = false">{{ t('messages.cancel') }}</v-btn>
          <v-btn color="error" @click="confirmDelete">{{ t('messages.delete') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'

const { t } = useI18n()

const props = defineProps({
  teachers: Object,
  departments: Array,
  filters: Object,
})

const loading = ref(false)
const deleteDialog = ref(false)
const teacherToDelete = ref(null)

const headers = computed(() => [
  { title: t('admin.teachers.name_email'), key: 'name', sortable: true },
  { title: t('admin.teachers.department'), key: 'department', sortable: false },
  { title: t('admin.teachers.status'), key: 'is_active', sortable: true },
  { title: t('admin.teachers.registration_date'), key: 'created_at', sortable: true },
  { title: t('messages.actions'), key: 'actions', sortable: false },
])

const statusItems = computed(() => [
  { title: t('admin.teachers.active'), value: 'active' },
  { title: t('admin.teachers.inactive'), value: 'inactive' }
])

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
