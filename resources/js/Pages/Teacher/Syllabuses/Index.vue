<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('navigation.my_syllabuses') }}</h1>
              <p class="text-body-1 text-medium-emphasis">{{ t('teacher.syllabuses.subtitle', {}, { default: 'Управление учебными программами по моим предметам' }) }}</p>
            </div>
            <v-btn
              color="primary"
              @click="navigateTo('/teacher/syllabuses/create')"
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

        <v-data-table
          :headers="headers"
          :items="syllabuses"
          :search="search"
          :loading="loading"
          class="elevation-1"
        >
          <template v-slot:item.name="{ item }">
            <div class="font-weight-medium">{{ item.name }}</div>
            <div class="text-caption text-medium-emphasis">{{ item.description }}</div>
          </template>

          <template v-slot:item.subject_name="{ item }">
            <v-chip
              :color="item.subject_name !== 'Не указан' ? 'primary' : 'grey'"
              variant="tonal"
              size="small"
            >
              {{ item.subject_name }}
            </v-chip>
          </template>

          <template v-slot:item.file_name="{ item }">
            <div v-if="item.file_name">
              <v-icon size="small" class="mr-1">mdi-file</v-icon>
              {{ item.file_name }}
              <div class="text-caption text-medium-emphasis">
                {{ item.file_size_formatted }}
              </div>
            </div>
            <span v-else class="text-medium-emphasis">Файл не загружен</span>
          </template>

          <template v-slot:item.creation_year="{ item }">
            <v-chip
              color="secondary"
              variant="tonal"
              size="small"
            >
              {{ item.creation_year }}
            </v-chip>
          </template>

          <template v-slot:item.created_at="{ item }">
            {{ item.created_at }}
          </template>

          <template v-slot:item.actions="{ item }">
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
                <v-list-item
                  @click="navigateTo(`/teacher/syllabuses/${item.id}`)"
                  prepend-icon="mdi-eye"
                >
                  <v-list-item-title>Просмотр</v-list-item-title>
                </v-list-item>
                <v-list-item
                  @click="navigateTo(`/teacher/syllabuses/${item.id}/edit`)"
                  prepend-icon="mdi-pencil"
                >
                  <v-list-item-title>Редактировать</v-list-item-title>
                </v-list-item>
                <v-list-item
                  v-if="item.download_url"
                  @click="downloadFile(item.download_url)"
                  prepend-icon="mdi-download"
                >
                  <v-list-item-title>Скачать</v-list-item-title>
                </v-list-item>
                <v-list-item
                  v-if="item.preview_url"
                  @click="previewFile(item.preview_url)"
                  prepend-icon="mdi-eye"
                >
                  <v-list-item-title>Предварительный просмотр</v-list-item-title>
                </v-list-item>
                <v-divider></v-divider>
                <v-list-item
                  @click="confirmDelete(item)"
                  prepend-icon="mdi-delete"
                  color="error"
                >
                  <v-list-item-title>Удалить</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </template>
        </v-data-table>
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

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'

const { t } = useI18n()

// Props из Inertia
const props = defineProps({
  syllabuses: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({})
  },
  schedules: {
    type: Array,
    default: () => []
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
  { title: 'Предмет', key: 'subject_name', sortable: true },
  { title: 'Файл', key: 'file_name', sortable: false },
  { title: 'Год создания', key: 'creation_year', sortable: true },
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
    await router.delete(`/teacher/syllabuses/${syllabusToDelete.value.id}`)
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
