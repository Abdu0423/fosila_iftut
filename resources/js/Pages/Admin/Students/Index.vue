<template>
  <AdminLayout>
    <v-row>
      <v-col cols="12">
        <div class="d-flex justify-space-between align-center mb-4">
          <h1 class="text-h4">{{ t('admin.students.title') }}</h1>
          <v-btn
            color="primary"
            prepend-icon="mdi-plus"
            :href="route('admin.students.create')"
          >
            {{ t('admin.students.add_student') }}
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
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="filters.search"
                  :label="t('admin.students.search_placeholder')"
                  prepend-icon="mdi-magnify"
                  clearable
                  @keyup.enter="applyFilters"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="3">
                <v-select
                  v-model="filters.group_id"
                  :label="t('admin.students.group')"
                  :items="groups"
                  item-title="name"
                  item-value="id"
                  clearable
                ></v-select>
              </v-col>
              
              <v-col cols="12" md="3">
                <v-select
                  v-model="filters.course"
                  :label="t('admin.students.course')"
                  :items="[1, 2, 3, 4, 5, 6]"
                  clearable
                ></v-select>
              </v-col>
              
              <v-col cols="12" md="3">
                <v-select
                  v-model="filters.status"
                  :label="t('admin.students.status')"
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
                  {{ t('admin.students.reset') }}
                </v-btn>
                <v-btn
                  color="primary"
                  @click="applyFilters"
                >
                  <v-icon start>mdi-filter</v-icon>
                  {{ t('admin.students.apply') }}
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Таблица студентов -->
    <v-row>
      <v-col cols="12">
        <v-card>
          <v-data-table
            :headers="headers"
            :items="students.data"
            :loading="loading"
            :items-per-page="students.per_page"
            :total-items="students.total"
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

            <template v-slot:item.group="{ item }">
              {{ item.raw.group?.name }}
            </template>

            <template v-slot:item.course="{ item }">
              <v-chip color="primary" size="small">
                {{ item.raw.course }} {{ t('admin.students.course_label') }}
              </v-chip>
            </template>

            <template v-slot:item.is_active="{ item }">
              <v-chip
                :color="item.raw.is_active ? 'success' : 'error'"
                size="small"
              >
                {{ item.raw.is_active ? t('admin.students.active_status') : t('admin.students.inactive_status') }}
              </v-chip>
            </template>

            <template v-slot:item.actions="{ item }">
              <v-btn
                icon="mdi-eye"
                variant="text"
                size="small"
                color="info"
                :href="route('admin.students.show', item.raw.id)"
              ></v-btn>
              
              <v-btn
                icon="mdi-pencil"
                variant="text"
                size="small"
                color="warning"
                :href="route('admin.students.edit', item.raw.id)"
              ></v-btn>
              
              <v-btn
                icon="mdi-delete"
                variant="text"
                size="small"
                color="error"
                @click="deleteStudent(item.raw.id)"
              ></v-btn>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>

    <!-- Диалог подтверждения удаления -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title>{{ t('admin.students.delete_confirmation') }}</v-card-title>
        <v-card-text>
          {{ t('admin.students.delete_confirmation_text') }}
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
  students: Object,
  groups: Array,
  filters: Object,
})

const loading = ref(false)
const deleteDialog = ref(false)
const studentToDelete = ref(null)

const headers = computed(() => [
  { title: t('admin.students.name_email'), key: 'name', sortable: true },
  { title: t('admin.students.group'), key: 'group', sortable: false },
  { title: t('admin.students.course'), key: 'course', sortable: true },
  { title: t('admin.students.status'), key: 'is_active', sortable: true },
  { title: t('admin.students.registration_date'), key: 'created_at', sortable: true },
  { title: t('messages.actions'), key: 'actions', sortable: false },
])

const statusItems = computed(() => [
  { title: t('admin.students.active'), value: 'active' },
  { title: t('admin.students.inactive'), value: 'inactive' }
])

const filters = reactive({
  search: props.filters?.search,
  group_id: props.filters?.group_id || null,
  course: props.filters?.course || null,
  status: props.filters?.status || null,
})

const applyFilters = () => {
  if (loading.value) return
  
  loading.value = true
  router.get(route('admin.students.index'), filters, {
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
  router.get(route('admin.students.clearFilters'), {}, {
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
  router.get(route('admin.students.index'), {
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

const deleteStudent = (id) => {
  studentToDelete.value = id
  deleteDialog.value = true
}

const confirmDelete = () => {
  router.delete(route('admin.students.destroy', studentToDelete.value), {
    onSuccess: () => {
      deleteDialog.value = false
      studentToDelete.value = null
    }
  })
}
</script>
