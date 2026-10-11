<template>
  <Layout role="admin">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Силлабусы</h1>
              <p class="text-body-1 text-medium-emphasis">Управление учебными программами</p>
            </div>
            <v-btn
              color="primary"
              @click="navigateTo('/admin/syllabuses/create')"
              prepend-icon="mdi-plus"
            >
              Создать силлабус
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Статистика -->
      <v-row class="mb-6">
        <v-col cols="12" sm="6" md="3">
          <v-card>
            <v-card-text>
              <div class="text-h6">{{ stats.total }}</div>
              <div class="text-body-2 text-medium-emphasis">Всего силлабусов</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card>
            <v-card-text>
              <div class="text-h6">{{ stats.this_year }}</div>
              <div class="text-body-2 text-medium-emphasis">За этот год</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card>
            <v-card-text>
              <div class="text-h6">{{ stats.last_year }}</div>
              <div class="text-body-2 text-medium-emphasis">За прошлый год</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card>
            <v-card-text>
              <div class="text-h6">{{ stats.with_files }}</div>
              <div class="text-body-2 text-medium-emphasis">С файлами</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Таблица силлабусов -->
      <v-card>
        <v-card-title>
          <v-text-field
            v-model="search"
            prepend-inner-icon="mdi-magnify"
            label="Поиск"
            single-line
            hide-details
            variant="outlined"
            density="compact"
            class="max-width-300"
          ></v-text-field>
        </v-card-title>

        <ExcelSheet :headers="headers" :rows="syllabuses">
          <template #cell-name="{ row }">
            <div class="font-weight-medium">{{ row.name }}</div>
            <div class="text-caption text-medium-emphasis">{{ row.description }}</div>
          </template>

          <template #cell-lesson_name="{ row }">
            <v-chip
              :color="row.lesson_name !== 'Не указан' ? 'primary' : 'grey'"
              variant="tonal"
              size="small"
            >
              {{ row.lesson_name }}
            </v-chip>
          </template>

          <template #cell-file_name="{ row }">
            <div v-if="row.file_name">
              <v-icon size="small" class="mr-1">mdi-file</v-icon>
              {{ row.file_name }}
              <div class="text-caption text-medium-emphasis">
                {{ row.file_size_formatted }}
              </div>
            </div>
            <span v-else class="text-medium-emphasis">Файл не загружен</span>
          </template>

          <template #cell-creation_year="{ row }">
            <v-chip
              color="secondary"
              variant="tonal"
              size="small"
            >
              {{ row.creation_year }}
            </v-chip>
          </template>

          <template #cell-created_at="{ row }">
            {{ row.created_at }}
          </template>

          <template #cell-actions="{ row }">
            <v-menu>
              <template v-slot:activator="{ props }">
                <v-btn
                  icon="mdi-dots-vertical"
                  variant="text"
                  size="small"
                  v-bind="props"
                ></v-btn>
              </template>
              <v-list>
                <v-list-row
                  @click="navigateTo(`/admin/syllabuses/${row.id}`)"
                  prepend-icon="mdi-eye"
                >
                  <v-list-row-title>Просмотр</v-list-row-title>
                </v-list-row>
                <v-list-row
                  @click="navigateTo(`/admin/syllabuses/${row.id}/edit`)"
                  prepend-icon="mdi-pencil"
                >
                  <v-list-row-title>Редактировать</v-list-row-title>
                </v-list-row>
                <v-list-row
                  v-if="row.download_url"
                  @click="downloadFile(row.download_url)"
                  prepend-icon="mdi-download"
                >
                  <v-list-row-title>Скачать</v-list-row-title>
                </v-list-row>
                <v-list-row
                  v-if="row.preview_url"
                  @click="previewFile(row.preview_url)"
                  prepend-icon="mdi-eye"
                >
                  <v-list-row-title>Предварительный просмотр</v-list-row-title>
                </v-list-row>
                <v-divider></v-divider>
                <v-list-row
                  @click="confirmDelete(row)"
                  prepend-icon="mdi-delete"
                  color="error"
                >
                  <v-list-row-title>Удалить</v-list-row-title>
                </v-list-row>
              </v-list>
            </v-menu>
          </template>
        </ExcelSheet>
      </v-card>

      <!-- Диалог подтверждения удаления -->
      <v-dialog v-model="deleteDialog" max-width="400">
        <v-card>
          <v-card-title>Подтверждение удаления</v-card-title>
          <v-card-text>
            Вы уверены, что хотите удалить силлабус "{{ syllabusToDelete?.name }}"?
            Это действие нельзя отменить.
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="secondary"
              variant="outlined"
              @click="deleteDialog = false"
            >
              Отмена
            </v-btn>
            <v-btn
              color="error"
              @click="deleteSyllabus"
              :loading="deleting"
            >
              Удалить
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </Layout>
</template>

<script setup lang="ts">
import ExcelSheet from '@/Components/ExcelSheet.vue'
import { ref, computed } from 'vue'
import { router } from '@/compat/inertia'
import Layout from '../../Layout.vue'

// Props из Inertia
const props = defineProps({
  syllabuses: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

// Состояние
const search = ref('')
const loading = ref(false)
const deleteDialog = ref(false)
const deleting = ref(false)
const syllabusToDelete = ref(null)

// Заголовки таблицы
const headers = [
  { title: 'Название', key: 'name', sortable: true },
  { title: 'Урок', key: 'lesson_name', sortable: true },
  { title: 'Файл', key: 'file_name', sortable: false },
  { title: 'Год создания', key: 'creation_year', sortable: true },
  { title: 'Создатель', key: 'creator_name', sortable: true },
  { title: 'Дата создания', key: 'created_at', sortable: true },
  { title: 'Действия', key: 'actions', sortable: false, width: '100px' }
]

// Методы
const navigateTo = (route) => {
  router.visit(route)
}

const downloadFile = (url) => {
  window.open(url, '_blank')
}

const previewFile = (url) => {
  window.open(url, '_blank')
}

const confirmDelete = (syllabus) => {
  syllabusToDelete.value = syllabus
  deleteDialog.value = true
}

const deleteSyllabus = async () => {
  if (!syllabusToDelete.value) return

  deleting.value = true
  try {
    await router.delete(`/admin/syllabuses/${syllabusToDelete.value.id}`)
    deleteDialog.value = false
    syllabusToDelete.value = null
  } catch (error) {
    console.error('Ошибка при удалении силлабуса:', error)
  } finally {
    deleting.value = false
  }
}
</script>

<style scoped>
.max-width-300 {
  max-width: 300px;
}
</style>
