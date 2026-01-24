<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('ssr.title', {}, { default: 'ССР (Самостоятельная работа)' }) }}</h1>
              <p class="text-body-1 text-medium-emphasis">{{ t('ssr.teacher_subtitle', {}, { default: 'Управление самостоятельными работами студентов' }) }}</p>
            </div>
            <v-btn
              color="primary"
              prepend-icon="mdi-plus"
              @click="$inertia.visit('/teacher/ssr/create')"
            >
              {{ t('ssr.create_task', {}, { default: 'Создать ССР' }) }}
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Уведомления -->
      <v-row v-if="$page.props.flash?.success">
        <v-col cols="12">
          <v-alert type="success" variant="tonal" closable>
            {{ $page.props.flash.success }}
          </v-alert>
        </v-col>
      </v-row>

      <!-- Список ССР -->
      <v-row v-if="tasks.length > 0">
        <v-col v-for="task in tasks" :key="task.id" cols="12" md="6" lg="4">
          <v-card class="h-100" :class="{ 'border-warning': !task.is_active }">
            <v-card-title class="d-flex align-center">
              <v-icon start :color="task.is_active ? 'primary' : 'grey'">mdi-file-document-edit</v-icon>
              <span class="text-truncate">{{ task.title }}</span>
            </v-card-title>
            
            <v-card-subtitle v-if="task.schedule">
              {{ task.schedule.group }} - {{ task.schedule.subject }}
            </v-card-subtitle>

            <v-card-text>
              <p v-if="task.description" class="text-body-2 mb-3 description-text">
                {{ task.description.length > 100 ? task.description.substring(0, 100) + '...' : task.description }}
              </p>

              <div class="d-flex flex-wrap gap-2 mb-3">
                <v-chip size="small" color="info" variant="tonal">
                  <v-icon start size="small">mdi-format-list-bulleted</v-icon>
                  {{ t('ssr.topics', {}, { default: 'Тем' }) }}: {{ task.topics_count }}
                </v-chip>
                <v-chip size="small" color="success" variant="tonal">
                  <v-icon start size="small">mdi-check</v-icon>
                  {{ t('ssr.taken', {}, { default: 'Выбрано' }) }}: {{ task.taken_topics_count }}
                </v-chip>
                <v-chip size="small" color="warning" variant="tonal">
                  <v-icon start size="small">mdi-file-document</v-icon>
                  {{ t('ssr.submissions', {}, { default: 'Работ' }) }}: {{ task.submissions_count }}
                </v-chip>
              </div>

              <div v-if="task.deadline" class="d-flex align-center text-body-2">
                <v-icon size="small" class="mr-1">mdi-calendar</v-icon>
                {{ t('ssr.deadline', {}, { default: 'Срок' }) }}: {{ task.deadline }}
              </div>

              <v-chip v-if="!task.is_active" color="warning" size="small" class="mt-2">
                {{ t('ssr.inactive', {}, { default: 'Неактивно' }) }}
              </v-chip>
            </v-card-text>

            <v-card-actions>
              <v-btn
                variant="text"
                color="primary"
                @click="$inertia.visit(`/teacher/ssr/${task.id}`)"
              >
                {{ t('common.view', {}, { default: 'Просмотр' }) }}
              </v-btn>
              <v-btn
                variant="text"
                color="secondary"
                @click="$inertia.visit(`/teacher/ssr/${task.id}/edit`)"
              >
                {{ t('common.edit', {}, { default: 'Редактировать' }) }}
              </v-btn>
              <v-spacer></v-spacer>
              <v-btn
                icon
                variant="text"
                color="error"
                size="small"
                @click="confirmDelete(task)"
              >
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>

      <!-- Пустое состояние -->
      <v-row v-else>
        <v-col cols="12">
          <v-card>
            <v-card-text class="text-center py-12">
              <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-file-document-plus-outline</v-icon>
              <h3 class="text-h6 mb-2">{{ t('ssr.no_tasks', {}, { default: 'Нет ССР заданий' }) }}</h3>
              <p class="text-body-2 text-grey mb-4">{{ t('ssr.create_first_task', {}, { default: 'Создайте первое задание для самостоятельной работы студентов' }) }}</p>
              <v-btn color="primary" @click="$inertia.visit('/teacher/ssr/create')">
                {{ t('ssr.create_task', {}, { default: 'Создать ССР' }) }}
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>

    <!-- Диалог удаления -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title>{{ t('ssr.confirm_delete', {}, { default: 'Удалить ССР?' }) }}</v-card-title>
        <v-card-text>
          {{ t('ssr.delete_warning', {}, { default: 'Это действие нельзя отменить. Все темы и работы будут удалены.' }) }}
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="deleteDialog = false">{{ t('common.cancel', {}, { default: 'Отмена' }) }}</v-btn>
          <v-btn color="error" variant="flat" @click="deleteTask">{{ t('common.delete', {}, { default: 'Удалить' }) }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </Layout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'

const { t } = useI18n()

defineProps({
  tasks: {
    type: Array,
    default: () => []
  }
})

const deleteDialog = ref(false)
const taskToDelete = ref(null)

const confirmDelete = (task) => {
  taskToDelete.value = task
  deleteDialog.value = true
}

const deleteTask = () => {
  if (taskToDelete.value) {
    router.delete(`/teacher/ssr/${taskToDelete.value.id}`)
  }
  deleteDialog.value = false
}
</script>

<style scoped>
.description-text {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
