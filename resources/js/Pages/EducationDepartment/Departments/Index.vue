<template>
  <Layout :role="getRole">
    <v-container fluid class="pa-6">
      <div class="mb-6">
        <h1 class="text-h4 font-weight-bold mb-2">
          {{ translations.education_department?.departments_title || 'Кафедры' }}
        </h1>
        <p class="text-body-1 text-medium-emphasis">
          {{ translations.education_department?.departments_subtitle || 'Управление кафедрами' }}
        </p>
      </div>

      <ExcelSheet
        :columns="columns"
        :rows="departmentRows"
        can-add
        :search-placeholder="translations.messages?.search || 'Поиск'"
        :empty-text="translations.education_department?.no_departments || 'Кафедры не найдены'"
        :add-label="translations.messages?.add || 'Добавить'"
        @create="createDepartment"
        @commit="updateDepartment"
      >
        <template #cell-is_active="{ row }">
          <v-chip :color="row.is_active ? 'success' : 'error'" size="small" @click.stop="toggleStatus(row)">
            {{ row.is_active ? (translations.messages?.active || 'Активна') : (translations.messages?.inactive || 'Неактивна') }}
          </v-chip>
        </template>
        <template #cell-actions="{ row }">
          <v-btn icon variant="text" size="small" :color="row.is_active ? 'success' : 'error'" @click.stop="toggleStatus(row)">
            <v-icon>{{ row.is_active ? 'mdi-toggle-switch' : 'mdi-toggle-switch-off-outline' }}</v-icon>
          </v-btn>
          <v-btn icon variant="text" color="primary" size="small" @click.stop="router.visit(`/${prefix()}/departments/${row.id}/edit`)">
            <v-icon>mdi-pencil</v-icon>
          </v-btn>
        </template>
      </ExcelSheet>
    </v-container>
  </Layout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { usePage, router } from '@/compat/inertia'
import Layout from '../../Layout.vue'
import ExcelSheet from '@/Components/ExcelSheet.vue'

const page = usePage()
const translations = computed(() => page.props.translations || {})
const props = defineProps({
  departments: { type: [Array, Object], default: () => [] }
})

const departmentRows = computed(() => props.departments?.data || props.departments || [])
const prefix = () => window.location.pathname.startsWith('/registration') ? 'registration' : 'education'
const getRole = computed(() => prefix() === 'registration' ? 'registration_center' : 'education_department')

const columns = computed(() => [
  { key: 'name', title: translations.value.education_department?.department_name || 'Название', editable: true, editor: 'text', inherit: true, required: true },
  { key: 'code', title: 'Код', editable: true, editor: 'text', inherit: true },
  { key: 'groups_count', title: translations.value.education_department?.groups_count || 'Группы', text: (row) => String(row.groups_count || 0) },
  {
    key: 'is_active',
    title: translations.value.messages?.status || 'Статус',
    editor: 'slot',
    text: (row) => row.is_active ? (translations.value.messages?.active || 'Активна') : (translations.value.messages?.inactive || 'Неактивна')
  },
  { key: 'actions', title: translations.value.messages?.actions || 'Действия', editor: 'slot', text: () => '' }
])

const payload = (source) => ({
  name: source.name,
  code: source.code || null,
  description: source.description || null,
  is_active: source.is_active ?? true
})

const createDepartment = (draft) => {
  router.post(`/${prefix()}/departments`, payload({ ...draft, is_active: true }))
}

const updateDepartment = ({ row, column, value }) => {
  router.put(`/${prefix()}/departments/${row.id}`, payload({ ...row, [column.key]: value }))
}

const toggleStatus = (item) => {
  const previous = item.is_active
  item.is_active = !item.is_active
  router.post(`/${prefix()}/departments/${item.id}/toggle-status`, {}, {
    onError: () => {
      item.is_active = previous
    }
  })
}
</script>
