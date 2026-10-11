<template>
  <Layout :role="getRole">
    <v-container fluid class="pa-6">
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.subjects_title || 'Предметы' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.subjects_subtitle || 'Список всех предметов' }}
          </p>
        </div>
      </div>

      <ExcelSheet
        :columns="columns"
        :rows="subjectRows"
        can-add
        :search-placeholder="translations.messages?.search || 'Поиск'"
        :empty-text="translations.education_department?.no_subjects || 'Предметы не найдены'"
        :add-label="translations.messages?.add || 'Добавить'"
        @create="createSubject"
        @commit="updateSubject"
      >
        <template #cell-is_active="{ row }">
          <v-chip :color="row.is_active ? 'success' : 'error'" size="small" @click.stop="toggleStatus(row)">
            {{ row.is_active ? (translations.messages?.active || 'Фаъол') : (translations.messages?.inactive || 'Ғайрифаъол') }}
          </v-chip>
        </template>
        <template #cell-actions="{ row }">
          <v-btn icon variant="text" size="small" :color="row.is_active ? 'success' : 'error'" @click.stop="toggleStatus(row)">
            <v-icon>{{ row.is_active ? 'mdi-toggle-switch' : 'mdi-toggle-switch-off-outline' }}</v-icon>
          </v-btn>
          <v-btn icon variant="text" color="primary" size="small" @click.stop="router.visit(`/${prefix()}/subjects/${row.id}/edit`)">
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
  subjects: { type: [Array, Object], default: () => [] },
  departments: { type: Array, default: () => [] }
})

const subjectRows = computed(() => props.subjects?.data || props.subjects || [])
const prefix = () => window.location.pathname.startsWith('/registration') ? 'registration' : 'education'
const getRole = computed(() => prefix() === 'registration' ? 'registration_center' : 'education_department')
const departmentOptions = computed(() => (props.departments || []).map((item) => ({ title: item.name, value: item.id })))

const columns = computed(() => [
  { key: 'name', title: translations.value.education_department?.subject_name || 'Название', editable: true, editor: 'text', inherit: true, required: true },
  {
    key: 'department_id',
    title: translations.value.education_department?.department || 'Кафедра',
    editable: true,
    editor: 'select',
    inherit: true,
    options: departmentOptions.value,
    get: (row) => row.department_id,
    text: (row) => row.department?.name || ''
  },
  {
    key: 'is_active',
    title: translations.value.messages?.status || 'Статус',
    editor: 'slot',
    text: (row) => row.is_active ? (translations.value.messages?.active || 'Фаъол') : (translations.value.messages?.inactive || 'Ғайрифаъол')
  },
  { key: 'actions', title: translations.value.messages?.actions || 'Действия', editor: 'slot', text: () => '' }
])

const payload = (source) => ({
  name: source.name,
  department_id: source.department_id || null,
  description: source.description || null,
  is_active: source.is_active ?? true
})

const createSubject = (draft) => {
  router.post(`/${prefix()}/subjects`, payload({ ...draft, is_active: true }))
}

const updateSubject = ({ row, column, value }) => {
  router.put(`/${prefix()}/subjects/${row.id}`, payload({ ...row, [column.key]: value }))
}

const toggleStatus = (item) => {
  const previous = item.is_active
  item.is_active = !item.is_active
  router.post(`/${prefix()}/subjects/${item.id}/toggle-status`, {}, {
    onError: () => {
      item.is_active = previous
    }
  })
}
</script>
