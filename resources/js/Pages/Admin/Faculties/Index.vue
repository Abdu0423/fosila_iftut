<template>
  <Layout role="admin">
    <v-container fluid class="pa-6">
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">Факультеты</h1>
          <p class="text-body-1 text-medium-emphasis">Список факультетов и добавление новых</p>
        </div>
        <v-btn color="primary" prepend-icon="mdi-plus" @click="router.visit('/admin/faculties/create')">
          Добавить
        </v-btn>
      </div>

      <v-alert v-if="page.props.flash?.success" type="success" variant="tonal" class="mb-4">
        {{ page.props.flash.success }}
      </v-alert>
      <v-alert v-if="page.props.flash?.error" type="error" variant="tonal" class="mb-4">
        {{ page.props.flash.error }}
      </v-alert>

      <ExcelSheet
        :columns="columns"
        :rows="rows"
        can-add
        search-placeholder="Поиск по факультету, кафедре, пользователю"
        empty-text="Факультеты не найдены"
        add-label="Добавить"
        @create="createFaculty"
        @commit="updateFaculty"
      >
        <template #cell-is_active="{ row }">
          <v-chip :color="row.is_active ? 'success' : 'error'" size="small" @click.stop="toggleStatus(row)">
            {{ row.is_active ? 'Активен' : 'Неактивен' }}
          </v-chip>
        </template>
        <template #cell-actions="{ row }">
          <v-btn icon variant="text" size="small" :color="row.is_active ? 'success' : 'error'" @click.stop="toggleStatus(row)">
            <v-icon>{{ row.is_active ? 'mdi-toggle-switch' : 'mdi-toggle-switch-off-outline' }}</v-icon>
          </v-btn>
          <v-btn icon variant="text" size="small" @click.stop="router.visit(`/admin/faculties/${row.id}`)">
            <v-icon>mdi-eye</v-icon>
          </v-btn>
          <v-btn icon variant="text" color="primary" size="small" @click.stop="router.visit(`/admin/faculties/${row.id}/edit`)">
            <v-icon>mdi-pencil</v-icon>
          </v-btn>
          <v-btn icon variant="text" color="error" size="small" @click.stop="removeFaculty(row)">
            <v-icon>mdi-delete</v-icon>
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
const props = defineProps({
  faculties: { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
})

const rows = computed(() => props.faculties || [])

function personName(user) {
  if (!user) return ''
  return [user.last_name, user.name, user.middle_name].filter(Boolean).join(' ')
}

const departmentOptions = computed(() => (props.departments || []).map((item) => ({
  title: item.name,
  value: item.id,
})))

const userOptions = computed(() => (props.users || []).map((user) => ({
  title: personName(user),
  value: user.id,
})))

const columns = computed(() => [
  { key: 'name', title: 'Название', editable: true, editor: 'text', inherit: true, required: true, width: '280px' },
  { key: 'code', title: 'Код', editable: true, editor: 'text', inherit: true },
  {
    key: 'department_id',
    title: 'Кафедра',
    editable: true,
    editor: 'select',
    options: departmentOptions.value,
    get: (row) => row.departments?.[0]?.id ?? null,
    text: (row) => (row.departments || []).map((item) => item.name).filter(Boolean).join(', '),
  },
  {
    key: 'user_id',
    title: 'Пользователь',
    editable: true,
    editor: 'select',
    options: userOptions.value,
    get: (row) => row.user_id,
    text: (row) => personName(row.user),
  },
  {
    key: 'is_active',
    title: 'Статус',
    editor: 'slot',
    text: (row) => row.is_active ? 'Активен' : 'Неактивен',
  },
  { key: 'actions', title: 'Действия', editor: 'slot', text: () => '' },
])

const payload = (source) => ({
  name: source.name,
  code: source.code || null,
  user_id: source.user_id || null,
  department_id: source.department_id ?? source.departments?.[0]?.id ?? null,
  address: source.address || null,
  description: source.description || null,
  is_active: source.is_active ?? true,
})

const createFaculty = (draft) => {
  router.post('/admin/faculties', payload({ ...draft, is_active: true }))
}

const updateFaculty = ({ row, column, value }) => {
  router.put(`/admin/faculties/${row.id}`, payload({ ...row, [column.key]: value }))
}

const toggleStatus = (item) => {
  const previous = item.is_active
  item.is_active = !item.is_active
  router.post(`/admin/faculties/${item.id}/toggle-status`, {}, {
    onError: () => {
      item.is_active = previous
    },
  })
}

const removeFaculty = (row) => {
  if (!confirm(`Удалить факультет «${row.name}»?`)) {
    return
  }
  router.delete(`/admin/faculties/${row.id}`)
}
</script>
