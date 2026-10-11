<template>
  <div class="excel-sheet">
    <v-text-field
      v-model="search"
      :label="searchPlaceholder"
      prepend-inner-icon="mdi-magnify"
      variant="outlined"
      density="comfortable"
      hide-details
      clearable
      class="excel-search mb-3"
      @update:model-value="onSearch"
    />

    <div v-if="contextChips.length" class="d-flex flex-wrap ga-2 mb-3">
      <v-chip
        v-for="chip in contextChips"
        :key="chip.key"
        color="primary"
        variant="tonal"
        closable
        @click:close="clearContext(chip.key)"
      >
        {{ chip.title }}: {{ chip.label }}
      </v-chip>
    </div>

    <div class="excel-scroll">
      <table class="excel-grid">
        <thead>
          <tr>
            <th class="excel-index">#</th>
            <th
              v-for="column in sheetColumns"
              :key="column.key"
              :style="column.width ? { width: column.width } : undefined"
            >
              {{ column.title }}
            </th>
          </tr>
          <tr class="excel-filter-row">
            <th class="excel-index"></th>
            <th v-for="column in sheetColumns" :key="`filter-${column.key}`">
              <template v-if="isFilterable(column)">
                <input
                  class="excel-filter-input"
                  :class="{ 'has-value': columnFilters[column.key] }"
                  :value="columnFilters[column.key] || ''"
                  placeholder="Фильтр"
                  :list="filterListId(column.key)"
                  @input="onColumnFilter(column.key, $event)"
                />
                <datalist :id="filterListId(column.key)">
                  <option
                    v-for="choice in choiceMap[column.key] || []"
                    :key="choice"
                    :value="choice"
                  />
                </datalist>
              </template>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in pageRows" :key="rowKey(row, index)">
            <td class="excel-index">{{ rowNumber(index) }}</td>
            <td
              v-for="column in sheetColumns"
              :key="column.key"
              :class="{ 'excel-editable': column.editable && column.editor !== 'slot' }"
              @click="beginEdit(row, column, index)"
            >
              <template v-if="isEditing(row, column, index)">
                <v-autocomplete
                  v-if="column.editor === 'select'"
                  :model-value="editValue"
                  :items="column.options || []"
                  item-title="title"
                  item-value="value"
                  density="compact"
                  variant="plain"
                  hide-details
                  autofocus
                  auto-select-first
                  @update:model-value="finishEdit(row, column, index, $event)"
                />
                <input
                  v-else
                  class="excel-input"
                  :type="column.editor === 'number' ? 'number' : 'text'"
                  :value="editValue ?? ''"
                  autofocus
                  @click.stop
                  @keydown.enter="finishFromInput(row, column, index, $event)"
                  @blur="finishFromInput(row, column, index, $event)"
                />
              </template>
              <slot
                v-else
                :name="`cell-${column.key}`"
                :row="row"
                :column="column"
                :text="cellText(row, column)"
              >
                <span>{{ cellText(row, column) || '—' }}</span>
              </slot>
            </td>
          </tr>

          <tr v-if="canAdd" class="excel-add-row">
            <td class="excel-index">+</td>
            <td v-for="column in sheetColumns" :key="column.key">
              <template v-if="column.editor === 'select'">
                <v-autocomplete
                  v-model="draft[column.key]"
                  :items="column.options || []"
                  item-title="title"
                  item-value="value"
                  density="compact"
                  variant="plain"
                  hide-details
                  :placeholder="column.title"
                  @update:model-value="markDirty(column.key)"
                />
              </template>
              <template v-else-if="column.editor === 'text' || column.editor === 'number'">
                <input
                  v-model="draft[column.key]"
                  class="excel-input"
                  :type="column.editor === 'number' ? 'number' : 'text'"
                  :placeholder="column.title"
                  @input="markDirty(column.key)"
                  @keydown.enter="submitAdd"
                />
              </template>
              <v-btn
                v-else-if="column.key === addActionKey"
                size="small"
                color="primary"
                variant="flat"
                :disabled="!canSubmit"
                @click="submitAdd"
              >
                {{ addLabel }}
              </v-btn>
              <span v-else class="excel-muted">{{ inheritedLabel(column) }}</span>
            </td>
          </tr>

          <tr v-if="!pageRows.length && !canAdd">
            <td :colspan="sheetColumns.length + 1" class="excel-empty">{{ emptyText }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="totalPages > 1" class="d-flex justify-center pa-3">
      <v-pagination v-model="page" :length="totalPages" :total-visible="5" density="comfortable" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'

type SheetOption = { title: string; value: string | number | boolean | null }
type SheetColumn = {
  key: string
  title: string
  width?: string
  editable?: boolean
  editor?: 'text' | 'number' | 'select' | 'slot'
  options?: SheetOption[]
  inherit?: boolean
  filter?: boolean
  required?: boolean
  text?: (row: Record<string, any>) => string
  get?: (row: Record<string, any>) => unknown
}

const props = withDefaults(defineProps<{
  columns?: SheetColumn[]
  headers?: { key?: string; title?: string; value?: string; text?: string }[]
  rows?: Record<string, any>[]
  canAdd?: boolean
  searchPlaceholder?: string
  emptyText?: string
  addLabel?: string
  pageSize?: number
  modelValue?: string
}>(), {
  columns: () => [],
  headers: () => [],
  rows: () => [],
  canAdd: false,
  searchPlaceholder: 'Поиск',
  emptyText: 'Ничего не найдено',
  addLabel: 'Добавить',
  pageSize: 25,
  modelValue: '',
})

const emit = defineEmits<{
  (event: 'update:modelValue', value: string): void
  (event: 'create', draft: Record<string, unknown>): void
  (event: 'commit', payload: { row: Record<string, any>; column: SheetColumn; value: unknown }): void
}>()

const search = ref(props.modelValue || '')
const page = ref(1)
const draft = reactive<Record<string, any>>({})
const dirty = ref<Record<string, boolean>>({})
const dismissed = ref<Record<string, boolean>>({})
const columnFilters = reactive<Record<string, string>>({})
const editing = ref<{ id: string | number; key: string } | null>(null)
const editValue = ref<unknown>(null)
const sheetId = `excel-${Math.random().toString(36).slice(2, 8)}`

const sheetColumns = computed<SheetColumn[]>(() => {
  if (props.columns?.length) {
    return props.columns
  }
  return (props.headers || []).map((header) => ({
    key: header.key || header.value,
    title: header.title || header.text,
    editor: 'slot' as const,
    text: (row: Record<string, any>) => {
      const value = row?.[header.key || header.value]
      if (value == null) {
        return ''
      }
      if (typeof value === 'object') {
        return value.name || value.title || value.label || ''
      }
      return String(value)
    },
  }))
})

const addActionKey = computed(() => {
  const slotColumn = sheetColumns.value.find((column) => column.editor === 'slot' || column.key === 'actions')
  return slotColumn?.key || sheetColumns.value[sheetColumns.value.length - 1]?.key
})

watch(() => props.modelValue, (value) => {
  if (value !== search.value) {
    search.value = value || ''
  }
})

function cellValue(row: Record<string, any>, column: SheetColumn) {
  if (column.get) {
    return column.get(row)
  }
  return row?.[column.key]
}

function cellText(row: Record<string, any>, column: SheetColumn) {
  if (column.text) {
    return column.text(row) ?? ''
  }
  const value = cellValue(row, column)
  if (column.editor === 'select' && column.options) {
    const found = column.options.find((option) => option.value === value)
    if (found) {
      return found.title
    }
  }
  if (value == null || value === '') {
    return ''
  }
  return String(value)
}

function isFilterable(column: SheetColumn) {
  return column.filter !== false && column.key !== 'actions'
}

function filterListId(key: string) {
  return `${sheetId}-${key.replace(/[^a-zA-Z0-9_-]/g, '_')}`
}

const choiceMap = computed(() => {
  const map: Record<string, string[]> = {}
  sheetColumns.value.forEach((column) => {
    if (!isFilterable(column)) {
      return
    }
    const values = new Set<string>()
    ;(props.rows || []).forEach((row) => {
      const text = cellText(row, column).trim()
      if (text) {
        values.add(text)
      }
    })
    map[column.key] = [...values].sort((left, right) => left.localeCompare(right, 'ru', { numeric: true }))
  })
  return map
})

function rowMatchesColumnFilter(row: Record<string, any>, column: SheetColumn, raw: string) {
  const query = raw.trim().toLowerCase()
  if (!query) {
    return true
  }
  const text = cellText(row, column).trim().toLowerCase()
  const known = (choiceMap.value[column.key] || []).some((choice) => choice.toLowerCase() === query)
  return known ? text === query : text.includes(query)
}

const filteredRows = computed(() => {
  const query = search.value.trim().toLowerCase()
  const active = sheetColumns.value
    .map((column) => ({ column, value: (columnFilters[column.key] || '').trim() }))
    .filter((item) => item.value)
  return (props.rows || []).filter((row) => {
    if (query && !sheetColumns.value.some((column) => cellText(row, column).toLowerCase().includes(query))) {
      return false
    }
    return active.every((item) => rowMatchesColumnFilter(row, item.column, item.value))
  })
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredRows.value.length / props.pageSize)))

const pageRows = computed(() => {
  const start = (page.value - 1) * props.pageSize
  return filteredRows.value.slice(start, start + props.pageSize)
})

function uniqueOption(column: SheetColumn, query: string) {
  const options = column.options || []
  const exact = options.filter((option) => String(option.title).toLowerCase() === query)
  if (exact.length === 1) {
    return exact[0]
  }
  const partial = options.filter((option) => String(option.title).toLowerCase().includes(query))
  return partial.length === 1 ? partial[0] : null
}

const inherited = computed(() => {
  const query = search.value.trim().toLowerCase()
  const result: Record<string, SheetOption> = {}
  if (!query) {
    return result
  }

  sheetColumns.value.filter((column) => column.inherit && !dismissed.value[column.key]).forEach((column) => {
    const matched = uniqueOption(column, query)
    if (matched) {
      result[column.key] = matched
      return
    }

    const visible = filteredRows.value
    if (!visible.length) {
      return
    }
    const values = visible.map((row) => cellValue(row, column))
    const first = values[0]
    const same = values.every((value) => value === first)
    const shown = cellText(visible[0], column).toLowerCase()
    if (!same || first == null || first === '' || !shown.includes(query)) {
      return
    }
    const option = (column.options || []).find((item) => item.value === first)
    result[column.key] = option || { title: cellText(visible[0], column), value: first as SheetOption['value'] }
  })

  return result
})

function prefillFor(column: SheetColumn, label: string): SheetOption | null {
  if (!column.editable || column.editor === 'slot') {
    return null
  }
  if (column.editor === 'select') {
    const lowered = label.toLowerCase()
    return (column.options || []).find((item) => String(item.title).toLowerCase() === lowered || String(item.value) === label) || null
  }
  if (column.editor === 'number') {
    const numeric = Number(String(label).replace(',', '.'))
    if (Number.isNaN(numeric)) {
      return null
    }
    return { title: label, value: numeric }
  }
  return { title: label, value: label }
}

const activeFilters = computed(() => {
  const result: Record<string, { label: string; prefill: SheetOption | null }> = {}
  sheetColumns.value.forEach((column) => {
    const label = (columnFilters[column.key] || '').trim()
    if (!label) {
      return
    }
    result[column.key] = { label, prefill: prefillFor(column, label) }
  })
  return result
})

const draftContext = computed(() => {
  const map: Record<string, SheetOption> = { ...inherited.value }
  sheetColumns.value.forEach((column) => {
    const filter = activeFilters.value[column.key]
    if (filter?.prefill) {
      map[column.key] = filter.prefill
    }
  })
  return map
})

const contextChips = computed(() => sheetColumns.value.flatMap((column) => {
  const filter = activeFilters.value[column.key]
  if (filter) {
    return [{ key: column.key, title: column.title, label: filter.label }]
  }
  const auto = inherited.value[column.key]
  if (!auto) {
    return []
  }
  return [{ key: column.key, title: column.title, label: auto.title }]
}))

watch(draftContext, (map) => {
  sheetColumns.value.forEach((column) => {
    if (dirty.value[column.key]) {
      return
    }
    draft[column.key] = map[column.key] ? map[column.key].value : null
  })
}, { immediate: true })

watch(search, () => {
  page.value = 1
  dismissed.value = {}
})

watch(totalPages, (length) => {
  if (page.value > length) {
    page.value = length
  }
})

const canSubmit = computed(() => sheetColumns.value
  .filter((column) => column.required)
  .every((column) => draft[column.key] !== undefined && draft[column.key] !== null && draft[column.key] !== ''))

function onSearch(value: string | null) {
  search.value = value || ''
  emit('update:modelValue', search.value)
}

function onColumnFilter(key: string, event: Event) {
  const value = (event.target as HTMLInputElement).value
  columnFilters[key] = value
  page.value = 1
  if (value.trim()) {
    dismissed.value = { ...dismissed.value, [key]: false }
    dirty.value = { ...dirty.value, [key]: false }
  }
}

function clearContext(key: string) {
  dismissed.value = { ...dismissed.value, [key]: true }
  columnFilters[key] = ''
  draft[key] = null
  dirty.value = { ...dirty.value, [key]: false }
}

function markDirty(key: string) {
  dirty.value = { ...dirty.value, [key]: true }
}

function inheritedLabel(column: SheetColumn) {
  return activeFilters.value[column.key]?.label || inherited.value[column.key]?.title || ''
}

function rowKey(row: Record<string, any>, index: number) {
  return row?.id ?? `row-${index}`
}

function rowNumber(index: number) {
  return (page.value - 1) * props.pageSize + index + 1
}

function beginEdit(row: Record<string, any>, column: SheetColumn, index: number) {
  if (!column.editable || column.editor === 'slot') {
    return
  }
  editing.value = { id: rowKey(row, index), key: column.key }
  editValue.value = column.editor === 'select' ? cellValue(row, column) : cellText(row, column)
}

function isEditing(row: Record<string, any>, column: SheetColumn, index: number) {
  return editing.value?.id === rowKey(row, index) && editing.value?.key === column.key
}

function finishFromInput(row: Record<string, any>, column: SheetColumn, index: number, event: Event) {
  finishEdit(row, column, index, (event.target as HTMLInputElement).value)
}

function finishEdit(row: Record<string, any>, column: SheetColumn, index: number, value: unknown) {
  if (!isEditing(row, column, index)) {
    return
  }
  const next = column.editor === 'number' && value !== '' && value != null ? Number(value) : value
  editing.value = null
  const previous = column.editor === 'select' ? cellValue(row, column) : cellText(row, column)
  if (next === previous || (next === '' && (previous == null || previous === ''))) {
    return
  }
  emit('commit', { row, column, value: next })
}

function submitAdd() {
  if (!canSubmit.value) {
    return
  }
  emit('create', { ...draft })
  sheetColumns.value.forEach((column) => {
    const kept = draftContext.value[column.key]
    if (kept && !dirty.value[column.key]) {
      draft[column.key] = kept.value
      return
    }
    if (!kept) {
      draft[column.key] = null
      dirty.value[column.key] = false
    }
  })
}
</script>

<style scoped>
.excel-scroll {
  overflow: auto;
  border: 1px solid #d9d3c9;
  border-radius: 12px;
  background: #fff;
}

.excel-grid {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  min-width: 720px;
}

.excel-grid th,
.excel-grid td {
  border-right: 1px solid #e7e2da;
  border-bottom: 1px solid #e7e2da;
  padding: 6px 10px;
  font-size: 14px;
  vertical-align: middle;
  background: #fff;
  white-space: nowrap;
}

.excel-grid th {
  position: sticky;
  top: 0;
  z-index: 3;
  background: #f7f4ef;
  font-weight: 600;
  text-align: left;
}

.excel-filter-row th {
  top: 36px;
  z-index: 2;
  padding: 4px 6px;
  background: #fff;
  font-weight: 400;
}

.excel-filter-input {
  width: 100%;
  border: 1px solid transparent;
  border-radius: 6px;
  background: #f7f4ef;
  font: inherit;
  font-size: 12px;
  line-height: 1.2;
  padding: 4px 8px;
  outline: none;
  color: #12151c;
}

.excel-filter-input::placeholder {
  color: #8a8175;
}

.excel-filter-input:focus,
.excel-filter-input.has-value {
  border-color: #3d4fd8;
  background: #fff;
}

.excel-index {
  width: 46px;
  text-align: center !important;
  color: #8a8175;
  background: #f7f4ef !important;
}

.excel-editable {
  cursor: cell;
}

.excel-add-row td {
  background: #fbfaf7;
}

.excel-input {
  width: 100%;
  border: 0;
  outline: none;
  background: transparent;
  font: inherit;
  padding: 0;
}

.excel-empty {
  text-align: center;
  padding: 28px !important;
  color: #8a8175;
}

.excel-muted {
  color: #8a8175;
}
</style>
