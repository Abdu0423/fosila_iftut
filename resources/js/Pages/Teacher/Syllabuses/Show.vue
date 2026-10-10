<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ syllabus.name }}</h1>
              <p class="text-body-1 text-medium-emphasis">Информация о силлабусе</p>
            </div>
            <div class="d-flex gap-2">
              <v-btn
                color="secondary"
                variant="outlined"
                @click="navigateTo('/teacher/syllabuses')"
                prepend-icon="mdi-arrow-left"
              >
                Назад к списку
              </v-btn>
              <v-btn
                color="primary"
                @click="navigateTo(`/teacher/syllabuses/${syllabus.id}/edit`)"
                prepend-icon="mdi-pencil"
              >
                Редактировать
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Информация о силлабусе -->
      <v-row>
        <v-col cols="12" md="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-file-document</v-icon>
              Основная информация
            </v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Название</div>
                    <div class="text-body-1">{{ syllabus.name }}</div>
                  </div>
                  
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Предмет</div>
                    <v-chip color="primary" variant="tonal">
                      {{ syllabus.subject_name }}
                    </v-chip>
                  </div>
                  
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Год создания</div>
                    <v-chip color="secondary" variant="tonal">
                      {{ syllabus.creation_year }}
                    </v-chip>
                  </div>
                </v-col>
                
                <v-col cols="12" md="6">
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Дата создания</div>
                    <div class="text-body-1">{{ syllabus.created_at }}</div>
                  </div>
                  
                  <div class="mb-4">
                    <div class="text-subtitle-2 text-medium-emphasis mb-1">Последнее обновление</div>
                    <div class="text-body-1">{{ syllabus.updated_at }}</div>
                  </div>
                </v-col>
              </v-row>
              
              <div v-if="syllabus.description" class="mt-4">
                <div class="text-subtitle-2 text-medium-emphasis mb-2">Описание</div>
                <div class="text-body-1">{{ syllabus.description }}</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
        
        <v-col cols="12" md="4">
          <!-- Файл -->
          <v-card class="mb-4">
            <v-card-title class="text-h6">
              <v-icon start>mdi-file</v-icon>
              Файл
            </v-card-title>
            <v-card-text>
              <div v-if="syllabus.file_name" class="text-center">
                <v-icon size="64" color="primary" class="mb-4">mdi-file-document</v-icon>
                <div class="text-body-1 font-weight-medium mb-2">{{ syllabus.file_name }}</div>
                <div class="text-caption text-medium-emphasis mb-4">
                  {{ syllabus.file_size_formatted }} • {{ syllabus.file_type }}
                </div>
                
                <div class="d-flex flex-column gap-2">
                  <v-btn
                    color="primary"
                    variant="outlined"
                    @click="downloadFile"
                    prepend-icon="mdi-download"
                    block
                  >
                    Скачать файл
                  </v-btn>
                  
                  <v-btn
                    color="secondary"
                    variant="outlined"
                    @click="previewFile"
                    prepend-icon="mdi-eye"
                    block
                  >
                    Предварительный просмотр
                  </v-btn>
                </div>
              </div>
              
              <div v-else class="text-center text-medium-emphasis">
                <v-icon size="64" color="grey" class="mb-4">mdi-file-remove</v-icon>
                <div>Файл не загружен</div>
              </div>
            </v-card-text>
          </v-card>
          
          <!-- Действия -->
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-cog</v-icon>
              Действия
            </v-card-title>
            <v-card-text>
              <div class="d-flex flex-column gap-2">
                <v-btn
                  color="primary"
                  @click="navigateTo(`/teacher/syllabuses/${syllabus.id}/edit`)"
                  prepend-icon="mdi-pencil"
                  block
                >
                  Редактировать
                </v-btn>
                
                <v-btn
                  color="error"
                  variant="outlined"
                  @click="confirmDelete"
                  prepend-icon="mdi-delete"
                  block
                >
                  Удалить
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Диалог подтверждения удаления -->
      <v-dialog v-model="deleteDialog" max-width="400">
        <v-card>
          <v-card-title>Подтверждение удаления</v-card-title>
          <v-card-text>
            Вы уверены, что хотите удалить силлабус "{{ syllabus.name }}"?
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
import { ref } from 'vue'
import { router } from '@/compat/inertia'
import Layout from '../../Layout.vue'

// Props из Inertia
const props = defineProps({
  syllabus: {
    type: Object,
    required: true
  }
})

// Состояние
const deleteDialog = ref(false)
const deleting = ref(false)

// Методы
const navigateTo = (route) => {
  router.visit(route)
}

const downloadFile = () => {
  window.open(`/teacher/syllabuses/${props.syllabus.id}/download`, '_blank')
}

const previewFile = () => {
  window.open(`/teacher/syllabuses/${props.syllabus.id}/preview`, '_blank')
}

const confirmDelete = () => {
  deleteDialog.value = true
}

const deleteSyllabus = async () => {
  deleting.value = true
  try {
    await router.delete(`/teacher/syllabuses/${props.syllabus.id}`)
    deleteDialog.value = false
  } catch (error) {
    console.error('Ошибка при удалении силлабуса:', error)
  } finally {
    deleting.value = false
  }
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
