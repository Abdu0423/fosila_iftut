<template>
  <AdminApp>
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ syllabus.name }}</h1>
              <p class="text-body-1 text-medium-emphasis">Детальная информация о силлабусе</p>
            </div>
            <div class="d-flex gap-3">
              <v-btn
                v-if="syllabus.preview_url"
                color="info"
                variant="outlined"
                @click="previewFile"
                prepend-icon="mdi-eye"
              >
                Предварительный просмотр
              </v-btn>
              <v-btn
                v-if="syllabus.download_url"
                color="primary"
                variant="outlined"
                @click="downloadFile"
                prepend-icon="mdi-download"
              >
                Скачать
              </v-btn>
              <v-btn
                color="secondary"
                variant="outlined"
                @click="navigateTo(`/admin/syllabuses/${syllabus.id}/edit`)"
                prepend-icon="mdi-pencil"
              >
                Редактировать
              </v-btn>
              <v-btn
                color="secondary"
                variant="outlined"
                @click="navigateTo('/admin/syllabuses')"
                prepend-icon="mdi-arrow-left"
              >
                Назад к списку
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Основная информация -->
      <v-row>
        <v-col cols="12" md="8">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-information</v-icon>
              Основная информация
            </v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="6">
                  <div class="text-body-2 mb-4">
                    <strong>Название:</strong>
                    <div class="mt-1">{{ syllabus.name }}</div>
                  </div>
                  
                  <div class="text-body-2 mb-4">
                    <strong>Описание:</strong>
                    <div class="mt-1">{{ syllabus.description }}</div>
                  </div>
                  
                  <div class="text-body-2 mb-4">
                    <strong>Урок:</strong>
                    <v-chip
                      :color="syllabus.subject_name !== 'Не указан' ? 'primary' : 'grey'"
                      variant="tonal"
                      size="small"
                      class="mt-1"
                    >
                      {{ syllabus.subject_name }}
                    </v-chip>
                  </div>
                  
                  <div class="text-body-2 mb-4">
                    <strong>Год создания:</strong>
                    <v-chip
                      color="secondary"
                      variant="tonal"
                      size="small"
                      class="mt-1"
                    >
                      {{ syllabus.creation_year }}
                    </v-chip>
                  </div>
                </v-col>
                
                <v-col cols="12" md="6">
                  <div class="text-body-2 mb-4">
                    <strong>Создатель:</strong>
                    <div class="mt-1">{{ syllabus.creator_name }}</div>
                  </div>
                  
                  <div class="text-body-2 mb-4">
                    <strong>Дата создания:</strong>
                    <div class="mt-1">{{ syllabus.created_at }}</div>
                  </div>
                  
                  <div class="text-body-2 mb-4">
                    <strong>Последнее обновление:</strong>
                    <div class="mt-1">{{ syllabus.updated_at }}</div>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Информация о файле -->
        <v-col cols="12" md="4">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-file</v-icon>
              Файл
            </v-card-title>
            <v-card-text>
              <div v-if="syllabus.file_name" class="text-center">
                <v-icon size="64" color="primary" class="mb-4">mdi-file-document</v-icon>
                
                <div class="text-body-2 mb-2">
                  <strong>Имя файла:</strong>
                  <div class="mt-1 text-truncate">{{ syllabus.file_name }}</div>
                </div>
                
                <div class="text-body-2 mb-2">
                  <strong>Размер:</strong>
                  <div class="mt-1">{{ syllabus.file_size_formatted }}</div>
                </div>
                
                <div class="text-body-2 mb-4">
                  <strong>Тип:</strong>
                  <div class="mt-1">{{ syllabus.file_type }}</div>
                </div>
                
                                 <div class="d-flex flex-column gap-2">
                   <v-btn
                     v-if="syllabus.download_url"
                     color="primary"
                     block
                     @click="downloadFile"
                     prepend-icon="mdi-download"
                   >
                     Скачать файл
                   </v-btn>
                   
                   <v-btn
                     v-if="canViewPdf"
                     color="error"
                     variant="outlined"
                     block
                     @click="openPdfViewer"
                     prepend-icon="mdi-file-pdf-box"
                   >
                     Просмотр PDF
                   </v-btn>
                   
                   <v-btn
                     v-if="canViewWord"
                     color="info"
                     variant="outlined"
                     block
                     @click="openWordViewer"
                     prepend-icon="mdi-file-word-box"
                   >
                     Просмотр Word
                   </v-btn>
                   
                   <v-btn
                     v-if="syllabus.preview_url"
                     color="info"
                     variant="outlined"
                     block
                     @click="previewFile"
                     prepend-icon="mdi-eye"
                   >
                     Предварительный просмотр
                   </v-btn>
                 </div>
              </div>
              
              <div v-else class="text-center">
                <v-icon size="64" color="grey" class="mb-4">mdi-file-remove</v-icon>
                <div class="text-body-2 text-medium-emphasis">
                  Файл не загружен
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Просмотр содержимого файла -->
      <v-row v-if="showFileContent" class="mt-6">
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h6 d-flex justify-space-between align-center">
              <div>
                <v-icon start>mdi-file-document-outline</v-icon>
                Содержимое файла: {{ fileContentInfo.file_name }}
              </div>
              <v-btn
                icon
                @click="closeFileContent"
                prepend-icon="mdi-close"
              >
                <v-icon>mdi-close</v-icon>
              </v-btn>
            </v-card-title>
            <v-card-text>
              <div v-if="fileContentLoading" class="text-center py-8">
                <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
                <div class="mt-4 text-body-2">Загрузка содержимого файла...</div>
              </div>
              
              <div v-else-if="fileContentError" class="text-center py-8">
                <v-icon size="64" color="error" class="mb-4">mdi-alert-circle</v-icon>
                <div class="text-h6 text-error mb-2">Ошибка загрузки</div>
                <div class="text-body-2 text-medium-emphasis">{{ fileContentError }}</div>
                <v-btn
                  color="primary"
                  variant="outlined"
                  class="mt-4"
                  @click="loadFileContent"
                >
                  Попробовать снова
                </v-btn>
              </div>
              
              <div v-else class="file-content-viewer">
                <div class="d-flex justify-space-between align-center mb-4">
                  <div class="text-caption text-medium-emphasis">
                    Размер: {{ formatFileSize(fileContentInfo.file_size) }} | 
                    Тип: {{ fileContentInfo.file_type }} | 
                    Кодировка: {{ fileContentInfo.encoding }}
                  </div>
                  <div class="d-flex gap-2">
                    <v-btn
                      size="small"
                      variant="outlined"
                      @click="copyToClipboard"
                      prepend-icon="mdi-content-copy"
                    >
                      Копировать
                    </v-btn>
                    <v-btn
                      size="small"
                      variant="outlined"
                      @click="downloadFile"
                      prepend-icon="mdi-download"
                    >
                      Скачать
                    </v-btn>
                  </div>
                </div>
                
                <v-textarea
                  v-model="fileContent"
                  readonly
                  variant="outlined"
                  rows="20"
                  auto-grow
                  class="file-content-textarea"
                  :class="getFileTypeClass()"
                ></v-textarea>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Действия -->
      <v-row class="mt-6">
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-cog</v-icon>
              Действия
            </v-card-title>
            <v-card-text>
              <div class="d-flex gap-3 flex-wrap">
                <v-btn
                  color="primary"
                  variant="outlined"
                  @click="navigateTo(`/admin/syllabuses/${syllabus.id}/edit`)"
                  prepend-icon="mdi-pencil"
                >
                  Редактировать
                </v-btn>
                
                <v-btn
                  v-if="canViewFileContent"
                  color="info"
                  variant="outlined"
                  @click="loadFileContent"
                  prepend-icon="mdi-file-document-outline"
                >
                  Просмотреть содержимое
                </v-btn>
                
                <v-btn
                  color="error"
                  variant="outlined"
                  @click="confirmDelete"
                  prepend-icon="mdi-delete"
                >
                  Удалить
                </v-btn>
                
                <v-btn
                  color="secondary"
                  variant="outlined"
                  @click="navigateTo('/admin/syllabuses')"
                  prepend-icon="mdi-arrow-left"
                >
                  Назад к списку
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

             <!-- Диалог просмотра PDF -->
       <v-dialog v-model="pdfViewerDialog" fullscreen hide-overlay>
         <v-card>
           <v-toolbar color="error" dark>
             <v-btn icon @click="closePdfViewer">
               <v-icon>mdi-close</v-icon>
             </v-btn>
             <v-toolbar-title>Просмотр PDF: {{ pdfViewerInfo.file_name }}</v-toolbar-title>
             <v-spacer></v-spacer>
             <v-btn icon @click="downloadFile">
               <v-icon>mdi-download</v-icon>
             </v-btn>
           </v-toolbar>
           <v-card-text class="pa-0" style="height: calc(100vh - 64px);">
             <iframe
               v-if="pdfViewerInfo.pdf_url"
               :src="pdfViewerInfo.pdf_url"
               width="100%"
               height="100%"
               frameborder="0"
               style="border: none;"
             ></iframe>
             <div v-else class="d-flex align-center justify-center" style="height: 100%;">
               <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
             </div>
           </v-card-text>
         </v-card>
       </v-dialog>

               <!-- Диалог просмотра Word -->
        <v-dialog v-model="wordViewerDialog" fullscreen hide-overlay>
          <v-card>
            <v-toolbar color="info" dark>
              <v-btn icon @click="closeWordViewer">
                <v-icon>mdi-close</v-icon>
              </v-btn>
              <v-toolbar-title>Просмотр Word: {{ wordViewerInfo.file_name }}</v-toolbar-title>
              <v-spacer></v-spacer>
              
                             <!-- Переключатель просмотрщиков -->
               <v-btn-toggle
                 v-model="currentWordViewer"
                 color="white"
                 group
                 class="mr-4"
                 density="compact"
               >
                 <v-btn value="html" size="small">
                   <v-icon start>mdi-file-document-outline</v-icon>
                   HTML
                 </v-btn>
                 <v-btn value="google" size="small">
                   <v-icon start>mdi-google</v-icon>
                   Google
                 </v-btn>
                 <v-btn value="office" size="small">
                   <v-icon start>mdi-microsoft-office</v-icon>
                   Office
                 </v-btn>
               </v-btn-toggle>
              
              <v-btn icon @click="downloadFile">
                <v-icon>mdi-download</v-icon>
              </v-btn>
            </v-toolbar>
            
            <v-card-text class="pa-0" style="height: calc(100vh - 64px);">
              <!-- Ошибка загрузки -->
              <div v-if="wordViewerError" class="d-flex align-center justify-center flex-column" style="height: 100%;">
                <v-icon size="64" color="error" class="mb-4">mdi-alert-circle</v-icon>
                <div class="text-h6 text-error mb-2">Ошибка загрузки документа</div>
                <div class="text-body-2 text-medium-emphasis mb-4">{{ wordViewerError }}</div>
                <div class="d-flex gap-2">
                  <v-btn
                    color="primary"
                    variant="outlined"
                    @click="retryWordViewer"
                    prepend-icon="mdi-refresh"
                  >
                    Попробовать снова
                  </v-btn>
                  <v-btn
                    color="secondary"
                    variant="outlined"
                    @click="downloadFile"
                    prepend-icon="mdi-download"
                  >
                    Скачать файл
                  </v-btn>
                </div>
              </div>
              
              <!-- Загрузка -->
              <div v-else-if="wordViewerLoading" class="d-flex align-center justify-center" style="height: 100%;">
                <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
              </div>
              
              <!-- HTML просмотр -->
              <div v-else-if="currentWordViewer === 'html' && wordViewerInfo.html_content" class="word-html-viewer">
                <div v-html="wordViewerInfo.html_content"></div>
              </div>
              
              <!-- Внешний просмотрщик -->
              <iframe
                v-else-if="currentWordViewerUrl && currentWordViewer !== 'html'"
                :src="currentWordViewerUrl"
                width="100%"
                height="100%"
                frameborder="0"
                style="border: none;"
                @load="onWordViewerLoad"
                @error="onWordViewerError"
              ></iframe>
            </v-card-text>
          </v-card>
        </v-dialog>

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
  </AdminApp>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminApp from '../AdminApp.vue'

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
const showFileContent = ref(false)
const fileContent = ref('')
const fileContentInfo = ref({})
const fileContentLoading = ref(false)
const fileContentError = ref('')
const pdfViewerDialog = ref(false)
const pdfViewerInfo = ref({})
const wordViewerDialog = ref(false)
const wordViewerInfo = ref({})
const currentWordViewer = ref('google')
const wordViewerLoading = ref(false)
const wordViewerError = ref('')

// Вычисляемые свойства
const canViewFileContent = computed(() => {
  if (!props.syllabus.file_name) return false
  
  const fileExtension = props.syllabus.file_name.split('.').pop()?.toLowerCase()
  const supportedFormats = ['txt', 'md', 'html', 'css', 'js', 'json', 'xml', 'csv', 'log']
  
  return supportedFormats.includes(fileExtension)
})

const canViewPdf = computed(() => {
  if (!props.syllabus.file_name) return false
  const fileExtension = props.syllabus.file_name.split('.').pop()?.toLowerCase()
  return fileExtension === 'pdf'
})

const canViewWord = computed(() => {
  if (!props.syllabus.file_name) return false
  const fileExtension = props.syllabus.file_name.split('.').pop()?.toLowerCase()
  return ['doc', 'docx'].includes(fileExtension)
})

const currentWordViewerUrl = computed(() => {
  if (!wordViewerInfo.value.viewer_urls) return null
  return wordViewerInfo.value.viewer_urls[currentWordViewer.value] || null
})

// Методы
const navigateTo = (route) => {
  router.visit(route)
}

const downloadFile = () => {
  if (props.syllabus.download_url) {
    window.open(props.syllabus.download_url, '_blank')
  }
}

const previewFile = () => {
  if (props.syllabus.preview_url) {
    window.open(props.syllabus.preview_url, '_blank')
  }
}

const loadFileContent = async () => {
  if (!canViewFileContent.value) return
  
  fileContentLoading.value = true
  fileContentError.value = ''
  showFileContent.value = true
  
  try {
    const response = await fetch(`/admin/syllabuses/${props.syllabus.id}/content`)
    const data = await response.json()
    
    if (!response.ok) {
      throw new Error(data.error)
    }
    
    fileContent.value = data.content
    fileContentInfo.value = {
      file_name: data.file_name,
      file_type: data.file_type,
      file_size: data.file_size,
      encoding: data.encoding
    }
  } catch (error) {
    console.error('Ошибка при загрузке содержимого файла:', error)
    fileContentError.value = error.message
  } finally {
    fileContentLoading.value = false
  }
}

const closeFileContent = () => {
  showFileContent.value = false
  fileContent.value = ''
  fileContentInfo.value = {}
  fileContentError.value = ''
}

const copyToClipboard = async () => {
  try {
    await navigator.clipboard.writeText(fileContent.value)
    // Можно добавить уведомление об успешном копировании
  } catch (error) {
    console.error('Ошибка при копировании в буфер обмена:', error)
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const getFileTypeClass = () => {
  if (!fileContentInfo.value.file_name) return ''
  
  const fileExtension = fileContentInfo.value.file_name.split('.').pop()?.toLowerCase()
  const classMap = {
    'json': 'language-json',
    'xml': 'language-xml',
    'html': 'language-html',
    'css': 'language-css',
    'js': 'language-javascript',
    'md': 'language-markdown'
  }
  
  return classMap[fileExtension]
}

const openPdfViewer = async () => {
  if (!canViewPdf.value) return
  
  pdfViewerDialog.value = true
  pdfViewerInfo.value = {}
  
  try {
    const response = await fetch(`/admin/syllabuses/${props.syllabus.id}/pdf-viewer`)
    const data = await response.json()
    
    if (!response.ok) {
      throw new Error(data.error)
    }
    
    pdfViewerInfo.value = {
      pdf_url: data.pdf_url,
      file_name: data.file_name,
      file_type: data.file_type,
      file_size: data.file_size
    }
  } catch (error) {
    console.error('Ошибка при загрузке PDF:', error)
    // Можно добавить уведомление об ошибке
  }
}

const closePdfViewer = () => {
  pdfViewerDialog.value = false
  pdfViewerInfo.value = {}
}

const openWordViewer = async () => {
  if (!canViewWord.value) return
  
  wordViewerDialog.value = true
  wordViewerInfo.value = {}
  wordViewerLoading.value = true
  wordViewerError.value = ''
  currentWordViewer.value = 'html' // Начинаем с HTML просмотра
  
  try {
    // Сначала пытаемся загрузить HTML версию
    const htmlResponse = await fetch(`/admin/syllabuses/${props.syllabus.id}/word-html`)
    if (htmlResponse.ok) {
      const htmlData = await htmlResponse.json()
      wordViewerInfo.value = {
        html_content: htmlData.html_content,
        file_name: htmlData.file_name,
        file_type: htmlData.file_type,
        file_size: htmlData.file_size
      }
      currentWordViewer.value = 'html'
    } else {
      // Если HTML не удалось, загружаем внешние просмотрщики
      const response = await fetch(`/admin/syllabuses/${props.syllabus.id}/word-viewer`)
      const data = await response.json()
      
      if (!response.ok) {
        throw new Error(data.error)
      }
      
      wordViewerInfo.value = {
        viewer_urls: data.viewer_urls,
        file_url: data.file_url,
        file_name: data.file_name,
        file_type: data.file_type,
        file_size: data.file_size,
        primary_viewer: data.primary_viewer
      }
      
      // Устанавливаем основной просмотрщик
      currentWordViewer.value = data.primary_viewer
    }
    
  } catch (error) {
    console.error('Ошибка при загрузке Word документа:', error)
    wordViewerError.value = error.message
  } finally {
    wordViewerLoading.value = false
  }
}

const closeWordViewer = () => {
  wordViewerDialog.value = false
  wordViewerInfo.value = {}
  wordViewerLoading.value = false
  wordViewerError.value = ''
  currentWordViewer.value = 'google'
}

const retryWordViewer = () => {
  wordViewerError.value = ''
  wordViewerLoading.value = true
  
  // Переключаемся на другой просмотрщик при ошибке
  if (currentWordViewer.value === 'google') {
    currentWordViewer.value = 'office'
  } else {
    currentWordViewer.value = 'google'
  }
  
  wordViewerLoading.value = false
}

const onWordViewerLoad = () => {
  wordViewerLoading.value = false
  wordViewerError.value = ''
}

const onWordViewerError = () => {
  wordViewerLoading.value = false
  wordViewerError.value = 'Не удалось загрузить документ. Попробуйте другой просмотрщик или скачайте файл.'
}

const confirmDelete = () => {
  deleteDialog.value = true
}

const deleteSyllabus = async () => {
  deleting.value = true
  try {
    await router.delete(`/admin/syllabuses/${props.syllabus.id}`)
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

.file-content-viewer {
  max-height: 600px;
  overflow: hidden;
}

.file-content-textarea {
  font-family: 'Courier New', monospace;
  font-size: 14px;
  line-height: 1.5;
  background-color: #f8f9fa;
  border: 1px solid #e9ecef;
}

.file-content-textarea :deep(.v-field__input) {
  font-family: 'Courier New', monospace;
  font-size: 14px;
  line-height: 1.5;
  background-color: #f8f9fa;
  color: #212529;
}

/* Стили для разных типов файлов */
.language-json :deep(.v-field__input) {
  color: #d63384;
}

.language-xml :deep(.v-field__input) {
  color: #198754;
}

.language-html :deep(.v-field__input) {
  color: #0d6efd;
}

.language-css :deep(.v-field__input) {
  color: #fd7e14;
}

.language-javascript :deep(.v-field__input) {
  color: #6f42c1;
}

.language-markdown :deep(.v-field__input) {
  color: #495057;
}

.word-html-viewer {
  height: 100%;
  overflow-y: auto;
  padding: 20px;
  background-color: white;
}

.word-html-viewer :deep(h1),
.word-html-viewer :deep(h2),
.word-html-viewer :deep(h3),
.word-html-viewer :deep(h4),
.word-html-viewer :deep(h5),
.word-html-viewer :deep(h6) {
  margin-top: 1.5em;
  margin-bottom: 0.5em;
  font-weight: bold;
}

.word-html-viewer :deep(p) {
  margin-bottom: 1em;
  line-height: 1.6;
}

.word-html-viewer :deep(table) {
  border-collapse: collapse;
  width: 100%;
  margin: 1em 0;
}

.word-html-viewer :deep(th),
.word-html-viewer :deep(td) {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

.word-html-viewer :deep(th) {
  background-color: #f2f2f2;
  font-weight: bold;
}

.word-html-viewer :deep(ul),
.word-html-viewer :deep(ol) {
  margin: 1em 0;
  padding-left: 2em;
}

.word-html-viewer :deep(li) {
  margin-bottom: 0.5em;
}

.word-html-viewer :deep(strong),
.word-html-viewer :deep(b) {
  font-weight: bold;
}

.word-html-viewer :deep(em),
.word-html-viewer :deep(i) {
  font-style: italic;
}
</style>
