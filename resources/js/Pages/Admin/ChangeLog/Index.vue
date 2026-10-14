<template>
  <Layout role="admin">
    <v-container fluid>
      <div class="mb-4">
        <h1 class="text-h4 font-weight-bold mb-1">Журнал изменений</h1>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Добавления, изменения и удаления во всех рабочих таблицах
        </p>
      </div>

      <v-alert v-if="!ready" type="warning" variant="tonal" class="mb-4">
        Таблица журнала ещё не создана. На сервере нужно выполнить миграцию.
      </v-alert>

      <v-row dense class="mb-2">
        <v-col cols="12" md="3">
          <v-text-field
            v-model="form.user"
            label="Пользователь"
            density="compact"
            variant="outlined"
            hide-details
            clearable
            @keyup.enter="apply(1)"
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-select
            v-model="form.table"
            :items="tables"
            label="Таблица"
            density="compact"
            variant="outlined"
            hide-details
            clearable
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-select
            v-model="form.action"
            :items="actions"
            label="Действие"
            density="compact"
            variant="outlined"
            hide-details
            clearable
          />
        </v-col>
        <v-col cols="6" md="2">
          <v-text-field v-model="form.from" label="С" type="date" density="compact" variant="outlined" hide-details />
        </v-col>
        <v-col cols="6" md="2">
          <v-text-field v-model="form.to" label="По" type="date" density="compact" variant="outlined" hide-details />
        </v-col>
      </v-row>
      <div class="d-flex align-center ga-2 mb-4">
        <v-btn color="primary" @click="apply(1)">Показать</v-btn>
        <span class="text-body-2 text-medium-emphasis">Записей: {{ pagination.total || 0 }}</span>
      </div>

      <v-card>
        <v-table density="compact">
          <thead>
            <tr>
              <th>Время</th>
              <th>Кто</th>
              <th>Действие</th>
              <th>Таблица</th>
              <th>Запись</th>
              <th>Было</th>
              <th>Стало</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!logs.length">
              <td colspan="7" class="text-center text-medium-emphasis py-6">Записей нет</td>
            </tr>
            <tr v-for="row in logs" :key="row.id">
              <td class="text-no-wrap">{{ row.created_at }}</td>
              <td>
                <div>{{ row.user_name }}</div>
                <div class="text-caption text-medium-emphasis">{{ row.role_name }}</div>
              </td>
              <td>{{ row.action }}</td>
              <td>{{ row.table_name }}</td>
              <td>{{ row.record_id || '—' }}</td>
              <td class="change-cell">{{ row.before || '—' }}</td>
              <td class="change-cell">{{ row.after || '—' }}</td>
            </tr>
          </tbody>
        </v-table>
      </v-card>

      <div class="d-flex justify-end align-center ga-2 mt-4">
        <v-btn variant="text" :disabled="pagination.current_page <= 1" @click="apply(pagination.current_page - 1)">
          Назад
        </v-btn>
        <span class="text-body-2">{{ pagination.current_page }} / {{ pagination.last_page }}</span>
        <v-btn variant="text" :disabled="pagination.current_page >= pagination.last_page" @click="apply(pagination.current_page + 1)">
          Дальше
        </v-btn>
      </div>
    </v-container>
  </Layout>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@/compat/inertia'
import Layout from '../../Layout.vue'

const props = defineProps({
  ready: { type: Boolean, default: false },
  logs: { type: Array, default: () => [] },
  pagination: { type: Object, default: () => ({ current_page: 1, last_page: 1, total: 0 }) },
  filters: { type: Object, default: () => ({}) },
  tables: { type: Array, default: () => [] },
  actions: { type: Array, default: () => [] },
})

const form = reactive({
  user: props.filters?.user || '',
  table: props.filters?.table || null,
  action: props.filters?.action || null,
  from: props.filters?.from || '',
  to: props.filters?.to || '',
})

const apply = (page) => {
  router.get('/admin/change-log', {
    user: form.user || '',
    table: form.table || '',
    action: form.action || '',
    from: form.from || '',
    to: form.to || '',
    page,
  })
}
</script>

<style scoped>
.change-cell {
  white-space: pre-wrap;
  max-width: 280px;
  vertical-align: top;
}
</style>
