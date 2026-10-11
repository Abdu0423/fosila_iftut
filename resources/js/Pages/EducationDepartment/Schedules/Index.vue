<template>
  <Layout :role="getRole">
    <v-container fluid class="pa-6">
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">
            {{ translations.education_department?.schedules_title || 'Расписание' }}
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            {{ translations.education_department?.schedules_subtitle || 'Просмотр всех расписаний' }}
          </p>
        </div>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="router.visit(`/${getRoutePrefix()}/schedules/create`)"
        >
          {{ translations.messages?.add || 'Добавить' }}
        </v-btn>
      </div>

      <ExcelSheet
        :columns="columns"
        :rows="schedules"
        can-add
        :search-placeholder="translations.messages?.search || 'Поиск по предмету, группе, преподавателю'"
        :empty-text="translations.education_department?.no_schedules || 'Расписания не найдены'"
        :add-label="translations.messages?.add || 'Добавить'"
        @create="createSchedule"
        @commit="updateSchedule"
      >
        <template #cell-status="{ row }">
          <v-chip
            :color="row.is_active ? 'success' : 'error'"
            size="small"
            variant="flat"
            class="status-toggle"
            @click.stop="toggleStatus(row)"
          >
            {{ row.is_active ? (translations.messages?.active || 'Активен') : (translations.messages?.inactive || 'Неактивен') }}
          </v-chip>
        </template>
        <template #cell-actions="{ row }">
          <v-btn
            icon
            variant="text"
            :color="row.is_active ? 'success' : 'error'"
            size="small"
            @click.stop="toggleStatus(row)"
          >
            <v-icon>{{ row.is_active ? 'mdi-toggle-switch' : 'mdi-toggle-switch-off-outline' }}</v-icon>
          </v-btn>
          <v-btn
            icon
            variant="text"
            color="primary"
            size="small"
            @click.stop="router.visit(`/${getRoutePrefix()}/schedules/${row.id}/edit`)"
          >
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
  schedules: {
    type: Array,
    default: () => []
  },
  subjects: {
    type: Array,
    default: () => []
  },
  groups: {
    type: Array,
    default: () => []
  },
  teachers: {
    type: Array,
    default: () => []
  }
})

const subjectOptions = computed(() => (props.subjects || []).map((subject) => ({
  title: subject.name,
  value: subject.id
})))

const groupOptions = computed(() => (props.groups || []).map((group) => ({
  title: group.name,
  value: group.id
})))

const teacherOptions = computed(() => (props.teachers || []).map((teacher) => ({
  title: `${teacher.name || ''} ${teacher.last_name || ''}`.trim(),
  value: teacher.id
})))

const columns = computed(() => [
  {
    key: 'subject_id',
    title: translations.value.education_department?.schedule_subject || 'Предмет',
    editable: true,
    editor: 'select',
    inherit: true,
    required: true,
    options: subjectOptions.value,
    get: (row) => row.subject_id,
    text: (row) => row.subject?.name || ''
  },
  {
    key: 'group_id',
    title: translations.value.education_department?.schedule_group || 'Группа',
    editable: true,
    editor: 'select',
    inherit: true,
    required: true,
    options: groupOptions.value,
    get: (row) => row.group_id,
    text: (row) => row.group?.name || ''
  },
  {
    key: 'teacher_id',
    title: translations.value.education_department?.schedule_teacher || 'Преподаватель',
    editable: true,
    editor: 'select',
    inherit: true,
    required: true,
    options: teacherOptions.value,
    get: (row) => row.teacher_id,
    text: (row) => row.teacher ? `${row.teacher.name || ''} ${row.teacher.last_name || ''}`.trim() : ''
  },
  {
    key: 'semester',
    title: translations.value.education_department?.schedule_semester || 'Семестр',
    editable: true,
    editor: 'number',
    inherit: true,
    required: true,
    get: (row) => row.semester,
    text: (row) => row.semester ? String(row.semester) : ''
  },
  {
    key: 'study_year',
    title: translations.value.education_department?.schedule_study_year || 'Год обучения',
    editable: true,
    editor: 'number',
    inherit: true,
    required: true,
    get: (row) => row.study_year,
    text: (row) => row.study_year ? String(row.study_year) : ''
  },
  {
    key: 'status',
    title: translations.value.messages?.status || 'Статус',
    editor: 'slot',
    text: (row) => row.is_active
      ? (translations.value.messages?.active || 'Активен')
      : (translations.value.messages?.inactive || 'Неактивен')
  },
  {
    key: 'actions',
    title: translations.value.messages?.actions || 'Действия',
    editor: 'slot',
    text: () => ''
  }
])

const getRoutePrefix = () => {
  const path = window.location.pathname
  if (path.startsWith('/registration')) {
    return 'registration'
  }
  return 'education'
}

const getRole = computed(() => {
  return getRoutePrefix() === 'registration' ? 'registration_center' : 'education_department'
})

const creditsFor = (groupId, fallback) => {
  const sibling = (props.schedules || []).find((item) => item.group_id === groupId && item.credits)
  return sibling?.credits || fallback || 1
}

const schedulePayload = (source) => ({
  subject_id: source.subject_id,
  teacher_id: source.teacher_id,
  group_id: source.group_id,
  semester: Number(source.semester),
  study_year: Number(source.study_year),
  credits: Number(source.credits || creditsFor(source.group_id, 1)),
  is_active: source.is_active ?? true,
  scheduled_at: source.scheduled_at || null,
  distance_control_1_date: source.distance_control_1_date || null,
  distance_control_2_date: source.distance_control_2_date || null,
  has_coursework: source.has_coursework ?? false
})

const createSchedule = (draft) => {
  router.post(`/${getRoutePrefix()}/schedules`, schedulePayload({
    ...draft,
    credits: creditsFor(draft.group_id, 1),
    is_active: true
  }))
}

const updateSchedule = ({ row, column, value }) => {
  router.put(`/${getRoutePrefix()}/schedules/${row.id}`, schedulePayload({
    ...row,
    [column.key]: value
  }))
}

const toggleStatus = (item) => {
  const previous = item.is_active
  item.is_active = !item.is_active
  router.post(`/${getRoutePrefix()}/schedules/${item.id}/toggle-status`, {}, {
    onError: () => {
      item.is_active = previous
    }
  })
}
</script>

<style scoped>
.status-toggle {
  cursor: pointer;
}
</style>
