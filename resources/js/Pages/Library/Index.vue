<template>
  <Layout>
    <v-container fluid>
      <div class="library-page">
        <v-row>
        <v-col cols="12">
          <h1 class="text-h4 mb-6">Библиотека</h1>
        </v-col>
      </v-row>
      
      <!-- Поиск и фильтры -->
      <v-row>
        <v-col cols="12" md="8">
          <v-text-field
            v-model="searchQuery"
            label="Поиск по библиотеке..."
            prepend-inner-icon="mdi-magnify"
            outlined
            dense
            clearable
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="4">
          <v-select
            v-model="selectedCategory"
            :items="categories"
            label="Категория"
            outlined
            dense
            clearable
          ></v-select>
        </v-col>
      </v-row>
      
      <!-- Результаты поиска -->
      <v-row>
        <v-col cols="12">
          <v-alert v-if="filteredResources.length === 0" type="info" class="mb-4">
            Файлы не найдены. Попробуйте изменить параметры поиска.
          </v-alert>
          
          <v-card v-for="file in filteredResources" :key="file.id" class="mb-4">
            <v-card-title class="d-flex align-center">
              <v-icon class="mr-3" :color="getResourceColor(file.type)" size="large">
                {{ getResourceIcon(file.type) }}
              </v-icon>
              {{ file.name }}
              <v-spacer></v-spacer>
              <v-chip :color="getResourceColor(file.type)" size="small">
                {{ file.extension.toUpperCase() }}
              </v-chip>
            </v-card-title>
            
            <v-card-text>
              <v-list dense>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon>mdi-folder</v-icon>
                  </template>
                  <v-list-item-title>Категория: {{ file.category }}</v-list-item-title>
                </v-list-item>
                
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon>mdi-file-document</v-icon>
                  </template>
                  <v-list-item-title>Тип: {{ file.type }}</v-list-item-title>
                </v-list-item>
                
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon>mdi-weight</v-icon>
                  </template>
                  <v-list-item-title>Размер: {{ formatFileSize(file.size) }}</v-list-item-title>
                </v-list-item>
                
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon>mdi-calendar</v-icon>
                  </template>
                  <v-list-item-title>Дата загрузки: {{ formatDate(file.modified) }}</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-card-text>
            
            <v-card-actions>
              <v-btn color="primary" @click="downloadResource(file)">
                <v-icon start>mdi-download</v-icon>
                Скачать
              </v-btn>
              <v-btn color="secondary" variant="outlined" @click="previewResource(file)">
                <v-icon start>mdi-eye</v-icon>
                Информация
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
      
      <!-- Пагинация -->
      <v-row>
        <v-col cols="12" class="text-center">
          <v-pagination
            v-model="currentPage"
            :length="totalPages"
            :total-visible="7"
          ></v-pagination>
        </v-col>
      </v-row>
      </div>
    </v-container>
    
    <!-- Диалог предварительного просмотра -->
    <v-dialog v-model="previewDialog" max-width="800">
      <v-card>
        <v-card-title>
          {{ selectedResource?.title }}
          <v-spacer></v-spacer>
          <v-btn icon @click="previewDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <div v-if="selectedResource">
            <p>{{ selectedResource.description }}</p>
            <v-divider class="my-4"></v-divider>
            <h3 class="text-h6 mb-3">Детали ресурса</h3>
            <v-list dense>
              <v-list-item>
                <v-list-item-title>Тип: {{ selectedResource.type }}</v-list-item-title>
              </v-list-item>
              <v-list-item>
                <v-list-item-title>Размер: {{ selectedResource.size }}</v-list-item-title>
              </v-list-item>
              <v-list-item>
                <v-list-item-title>Формат: {{ selectedResource.format }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </div>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" @click="downloadResource(selectedResource)">
            Скачать
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </Layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '../Layout.vue'

const props = defineProps({
  files: Object,
  filters: Object,
  categories: Array,
  types: Array
})

const searchQuery = ref(props.filters?.search || '')
const selectedCategory = ref(props.filters?.category || null)
const selectedType = ref(props.filters?.type || null)
const previewDialog = ref(false)
const selectedResource = ref(null)

const filteredResources = computed(() => {
  return props.files?.data || []
})

const totalPages = computed(() => {
  return props.files?.last_page || 1
})

const currentPage = computed({
  get: () => props.files?.current_page || 1,
  set: (value) => {
    applyFilters({ page: value })
  }
})

const getResourceIcon = (type) => {
  const icons = {
    'document': 'mdi-file-document',
    'image': 'mdi-image',
    'video': 'mdi-video',
    'audio': 'mdi-music',
    'archive': 'mdi-archive',
    'other': 'mdi-file'
  }
  return icons[type] || 'mdi-file'
}

const getResourceColor = (type) => {
  const colors = {
    'document': 'primary',
    'image': 'success',
    'video': 'info',
    'audio': 'warning',
    'archive': 'secondary',
    'other': 'grey'
  }
  return colors[type] || 'grey'
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

const formatDate = (timestamp) => {
  const date = new Date(timestamp * 1000)
  return date.toLocaleDateString('ru-RU')
}

const applyFilters = (additionalParams = {}) => {
  router.get(route('student.library.index'), {
    search: searchQuery.value,
    category: selectedCategory.value,
    type: selectedType.value,
    ...additionalParams
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const downloadResource = (resource) => {
  window.location.href = route('student.library.download', { path: resource.path })
}

const previewResource = (resource) => {
  selectedResource.value = resource
  previewDialog.value = true
}

const clearFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = null
  selectedType.value = null
  applyFilters()
}
</script>
