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
const editing = ref<{ id: string | number; key: string } | null>(null)
const editValue = ref<unknown>(null)

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

const filteredRows = computed(() => {
  const query = search.value.trim().toLowerCase()
  const source = props.rows || []
  if (!query) {
    return source
  }
  return source.filter((row) => sheetColumns.value.some((column) => cellText(row, column).toLowerCase().includes(query)))
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

const contextChips = computed(() => sheetColumns.value
  .filter((column) => inherited.value[column.key])
  .map((column) => ({
    key: column.key,
    title: column.title,
    label: inherited.value[column.key].title,
  })))

watch(inherited, (map) => {
  Object.entries(map).forEach(([key, option]) => {
    if (!dirty.value[key]) {
      draft[key] = option.value
    }
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

function clearContext(key: string) {
  dismissed.value = { ...dismissed.value, [key]: true }
  delete draft[key]
  dirty.value = { ...dirty.value, [key]: false }
}

function markDirty(key: string) {
  dirty.value = { ...dirty.value, [key]: true }
}

function inheritedLabel(column: SheetColumn) {
  return inherited.value[column.key]?.title || ''
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
    if (inherited.value[column.key] && !dirty.value[column.key]) {
      draft[column.key] = inherited.value[column.key].value
      return
    }
    if (!inherited.value[column.key]) {
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
  z-index: 2;
  background: #f7f4ef;
  font-weight: 600;
  text-align: left;
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
