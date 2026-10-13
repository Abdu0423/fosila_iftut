<template>
  <Layout role="admin">
    <v-container fluid class="access-page pa-4 pa-md-6">
      <div class="d-flex flex-wrap align-center justify-space-between ga-4 mb-4">
        <div>
          <h1 class="text-h4 font-weight-bold mb-1">Доступ к страницам</h1>
          <p class="text-body-2 text-medium-emphasis mb-0">
            Сотрудникам можно включить любую их страницу. Страницы студента с ними не смешиваются.
          </p>
        </div>
        <v-text-field
          v-model="query"
          prepend-inner-icon="mdi-magnify"
          label="Найти страницу"
          variant="outlined"
          density="comfortable"
          hide-details
          clearable
          class="access-search"
        />
      </div>

      <v-alert v-if="page.props.flash?.success" type="success" variant="tonal" class="mb-4" closable>
        {{ page.props.flash.success }}
      </v-alert>

      <v-tabs v-model="tab" color="primary" class="mb-4">
        <v-tab value="staff">
          Сотрудники
          <v-chip size="x-small" class="ml-2" variant="tonal">{{ staffRoles.length }}</v-chip>
        </v-tab>
        <v-tab value="student">
          Студент
          <v-chip size="x-small" class="ml-2" variant="tonal">{{ studentPages.length }}</v-chip>
        </v-tab>
      </v-tabs>

      <v-window v-model="tab">
        <v-window-item value="staff">
          <v-card class="access-card" rounded="lg">
            <div class="matrix-scroll">
              <table class="access-matrix">
                <thead>
                  <tr>
                    <th class="sticky-col">Страница</th>
                    <th v-for="role in staffRoles" :key="role.id" class="role-col">
                      <div class="role-head">
                        <v-icon :icon="roleIcon(role.name)" size="18" />
                        <span class="role-title">{{ role.description || role.name }}</span>
                        <v-chip size="x-small" variant="tonal" color="primary">
                          {{ enabledCount(role.id) }}/{{ pagesOf(role.id).length }}
                        </v-chip>
                        <v-checkbox-btn
                          :model-value="columnState(role.id) === true"
                          :indeterminate="columnState(role.id) === null"
                          color="primary"
                          density="compact"
                          aria-label="Все страницы роли"
                          @update:model-value="setColumn(role.id, $event)"
                        />
                      </div>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <template v-for="group in visibleGroups" :key="group.title">
                    <tr class="group-row">
                      <th class="sticky-col" scope="row">
                        <v-icon :icon="group.icon" size="16" class="mr-1" />
                        {{ group.title }}
                      </th>
                      <td v-for="role in staffRoles" :key="`${group.title}-${role.id}`">
                        <v-checkbox-btn
                          :model-value="groupState(role.id, group.pages.map((item) => item.key)) === true"
                          :indeterminate="groupState(role.id, group.pages.map((item) => item.key)) === null"
                          color="primary"
                          density="compact"
                          :aria-label="`${group.title}, ${role.description || role.name}`"
                          @update:model-value="setGroup(role.id, group.pages.map((item) => item.key), $event)"
                      </td>
                    </tr>
                    <tr v-for="item in group.pages" :key="item.key">
                      <th class="sticky-col page-cell" scope="row">
                        <div class="page-title">{{ item.title }}</div>
                        <div class="page-route">{{ item.route }}</div>
                      </th>
                      <td v-for="role in staffRoles" :key="`${item.key}-${role.id}`">
                        <v-checkbox-btn
                          :model-value="isOn(role.id, item.key)"
                          color="primary"
                          density="compact"
                          :aria-label="`${item.title}, ${role.description || role.name}`"
                          @update:model-value="setOne(role.id, item.key, $event)"
                        />
                      </td>
                    </tr>
                  </template>
                  <tr v-if="!visibleGroups.length">
                    <td :colspan="staffRoles.length + 1" class="text-center text-medium-emphasis pa-8">
                      Ничего не найдено
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </v-card>
        </v-window-item>

        <v-window-item value="student">
          <v-card v-if="studentRole" rounded="lg" class="pa-4">
            <div class="d-flex align-center justify-space-between mb-4">
              <div class="d-flex align-center ga-2">
                <v-icon icon="mdi-school" />
                <span class="text-subtitle-1 font-weight-medium">{{ studentRole.description || 'Студент' }}</span>
                <v-chip size="small" variant="tonal" color="primary">
                  {{ enabledCount(studentRole.id) }}/{{ studentPages.length }}
                </v-chip>
              </div>
              <v-btn variant="text" color="primary" @click="setColumn(studentRole.id, columnState(studentRole.id) !== true)">
                {{ columnState(studentRole.id) === true ? 'Снять все' : 'Включить все' }}
              </v-btn>
            </div>
            <div v-if="filteredStudentPages.length" class="student-grid">
              <button
                v-for="item in filteredStudentPages"
                :key="item.key"
                type="button"
                class="student-tile"
                :class="{ 'student-tile--on': isOn(studentRole.id, item.key) }"
                @click="setOne(studentRole.id, item.key, !isOn(studentRole.id, item.key))"
              >
                <v-checkbox-btn
                  :model-value="isOn(studentRole.id, item.key)"
                  color="primary"
                  density="compact"
                  tabindex="-1"
                  @click.stop
                  @update:model-value="setOne(studentRole.id, item.key, $event)"
                />
                <span>
                  <span class="d-block font-weight-medium">{{ item.title }}</span>
                  <span class="page-route">{{ item.route }}</span>
                </span>
              </button>
            </div>
            <div v-else class="text-center text-medium-emphasis pa-8">Ничего не найдено</div>
          </v-card>
        </v-window-item>
      </v-window>

      <v-slide-y-reverse-transition>
        <div v-if="dirtyRoleIds.length" class="save-bar">
          <span>Изменения у ролей: {{ dirtyRoleIds.length }}</span>
          <v-btn variant="text" :disabled="saving" @click="discard">Отменить</v-btn>
          <v-btn color="primary" :loading="saving" @click="save">Сохранить</v-btn>
        </div>
      </v-slide-y-reverse-transition>
    </v-container>
  </Layout>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { usePage, router } from '@/compat/inertia'
import Layout from '../../Layout.vue'

type RoleItem = { id: number, name: string, description?: string }
type PageItem = { key: string, title: string, route?: string, enabled: boolean }

const page = usePage()
const props = defineProps({
  roles: { type: Array, default: () => [] },
  pagesByRole: { type: Object, default: () => ({}) },
})

const staffGroups = [
  { title: 'Основное', icon: 'mdi-view-dashboard', keys: ['dashboard'] },
  { title: 'Учёба', icon: 'mdi-teach', keys: ['lessons', 'tests', 'ssr', 'coursework', 'grades', 'students', 'my_schedule', 'syllabuses', 'chat'] },
  { title: 'Каталог', icon: 'mdi-bookshelf', keys: ['users', 'schedules', 'groups', 'subjects', 'catalog_departments', 'faculties', 'specialties', 'students_management', 'transfers'] },
  { title: 'Сервис', icon: 'mdi-cog', keys: ['assignments', 'library', 'reports', 'sms', 'settings'] },
]

const tab = ref('staff')
const query = ref('')
const saving = ref(false)
const draft = ref<Record<string, Record<string, boolean>>>({})
const saved = ref<Record<string, Record<string, boolean>>>({})

const roles = computed(() => props.roles as RoleItem[])
const staffRoles = computed(() => roles.value.filter((role) => role.name !== 'student'))
const studentRole = computed(() => roles.value.find((role) => role.name === 'student') || null)

const pagesOf = (roleId: number): PageItem[] => {
  const bag = props.pagesByRole as Record<string, PageItem[]>
  return bag?.[roleId] || bag?.[String(roleId)] || []
}

const studentPages = computed(() => studentRole.value ? pagesOf(studentRole.value.id) : [])

const snapshot = () => {
  const next: Record<string, Record<string, boolean>> = {}
  roles.value.forEach((role) => {
    next[role.id] = Object.fromEntries(pagesOf(role.id).map((item) => [item.key, !!item.enabled]))
  })
  saved.value = structuredClone(next)
  draft.value = structuredClone(next)
}

watch(() => JSON.stringify(props.pagesByRole), snapshot, { immediate: true })

const isOn = (roleId: number, key: string) => !!draft.value[roleId]?.[key]

const setOne = (roleId: number, key: string, value: boolean | null) => {
  if (!draft.value[roleId]) {
    draft.value[roleId] = {}
  }
  draft.value[roleId][key] = !!value
}

const setKeys = (roleId: number, keys: string[], value: boolean) => {
  keys.forEach((key) => setOne(roleId, key, value))
}

const enabledCount = (roleId: number) => pagesOf(roleId).filter((item) => isOn(roleId, item.key)).length

const columnState = (roleId: number): boolean | null => {
  const pages = pagesOf(roleId)
  const on = pages.filter((item) => isOn(roleId, item.key)).length
  if (!pages.length || on === 0) return false
  if (on === pages.length) return true
  return null
}

const setColumn = (roleId: number, value: boolean | null) => {
  setKeys(roleId, pagesOf(roleId).map((item) => item.key), !!value)
}

const groupState = (roleId: number, keys: string[]): boolean | null => {
  const known = keys.filter((key) => key in (draft.value[roleId] || {}))
  const on = known.filter((key) => isOn(roleId, key)).length
  if (!known.length || on === 0) return false
  if (on === known.length) return true
  return null
}

const setGroup = (roleId: number, keys: string[], value: boolean | null) => {
  setKeys(roleId, keys.filter((key) => key in (draft.value[roleId] || {})), !!value)
}

const needle = computed(() => (query.value || '').trim().toLowerCase())

const matches = (item: PageItem) => {
  if (!needle.value) return true
  return `${item.title} ${item.route || ''} ${item.key}`.toLowerCase().includes(needle.value)
}

const visibleGroups = computed(() => {
  const sample = staffRoles.value[0]
  const catalog = sample ? pagesOf(sample.id) : []
  const byKey = Object.fromEntries(catalog.map((item) => [item.key, item]))
  const used = new Set<string>()

  const groups = staffGroups.map((group) => {
    const pages = group.keys.map((key) => byKey[key]).filter((item) => item && matches(item))
    group.keys.forEach((key) => used.add(key))
    return { ...group, pages }
  }).filter((group) => group.pages.length)

  const rest = catalog.filter((item) => !used.has(item.key) && matches(item))
  if (rest.length) {
    groups.push({ title: 'Другое', icon: 'mdi-dots-horizontal', keys: rest.map((item) => item.key), pages: rest })
  }

  return groups
})

const filteredStudentPages = computed(() => studentPages.value.filter(matches))

const dirtyRoleIds = computed(() => roles.value
  .filter((role) => JSON.stringify(draft.value[role.id] || {}) !== JSON.stringify(saved.value[role.id] || {}))
  .map((role) => role.id))

const discard = () => {
  draft.value = structuredClone(saved.value)
}

const save = () => {
  if (!dirtyRoleIds.value.length) return
  saving.value = true
  const grants = dirtyRoleIds.value.map((roleId) => ({
    role_id: roleId,
    keys: pagesOf(roleId).filter((item) => isOn(roleId, item.key)).map((item) => item.key),
  }))
  router.post('/admin/access', { grants }, {
    onFinish: () => {
      saving.value = false
    },
  })
}

const roleIcon = (name: string) => {
  const icons: Record<string, string> = {
    teacher: 'mdi-human-male-board',
    education_department: 'mdi-domain',
    registration_center: 'mdi-clipboard-account',
    department: 'mdi-office-building',
    faculty: 'mdi-town-hall',
    student: 'mdi-school',
  }
  return icons[name] || 'mdi-account'
}
</script>

<style scoped>
.access-search {
  width: min(100%, 320px);
}

.access-card {
  overflow: hidden;
}

.matrix-scroll {
  overflow: auto;
  max-height: calc(100vh - 250px);
}

.access-matrix {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  min-width: 760px;
}

.access-matrix th,
.access-matrix td {
  padding: 8px 10px;
  border-bottom: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  text-align: center;
  vertical-align: middle;
  background: rgb(var(--v-theme-surface));
}

.access-matrix thead th {
  position: sticky;
  top: 0;
  z-index: 2;
  font-weight: 600;
}

.sticky-col {
  position: sticky;
  left: 0;
  z-index: 1;
  text-align: left !important;
  min-width: 220px;
  box-shadow: 8px 0 12px -12px rgba(0, 0, 0, 0.45);
}

.access-matrix thead .sticky-col {
  z-index: 3;
}

.role-col {
  min-width: 132px;
}

.role-head {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.role-title {
  font-size: 0.82rem;
  line-height: 1.2;
}

.group-row th,
.group-row td {
  background: rgba(var(--v-theme-primary), 0.08);
  font-size: 0.78rem;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.page-title {
  font-weight: 500;
}

.page-route {
  color: rgba(var(--v-theme-on-surface), 0.55);
  font-size: 0.75rem;
  font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
}

.student-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 12px;
}

.student-tile {
  display: flex;
  align-items: center;
  gap: 8px;
  text-align: left;
  padding: 12px;
  border-radius: 12px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  background: rgb(var(--v-theme-surface));
  cursor: pointer;
}

.student-tile--on {
  border-color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.08);
}

.save-bar {
  position: sticky;
  bottom: 16px;
  margin-top: 16px;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
  padding: 12px 16px;
  border-radius: 16px;
  background: rgb(var(--v-theme-surface));
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18);
  z-index: 4;
}

.save-bar span {
  margin-right: auto;
}
</style>
