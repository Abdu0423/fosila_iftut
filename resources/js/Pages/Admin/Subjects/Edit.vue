<template>
  <Layout role="admin">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex align-center mb-6">
            <v-btn
              icon="mdi-arrow-left"
              variant="text"
              @click="goBack"
              class="mr-3"
            />
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Редактирование предмета</h1>
              <p class="text-body-1 text-medium-emphasis">{{ subject?.name }}</p>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Отладочная информация (скрыта) -->
      <!-- 
      <v-row>
        <v-col cols="12">
          <v-alert type="info" class="mb-4">
            Subject ID: {{ subject?.id }}
            | Отделений загружено: {{ departments?.length || 0 }}
            | Ошибок: {{ Object.keys(errors || {}).length }}
          </v-alert>
        </v-col>
      </v-row>
      -->

      <!-- Форма редактирования -->
      <v-row v-if="subject?.id">
        <v-col cols="12" lg="8">
          <v-card>
            <v-card-title class="text-h5 pa-6">
              Информация о предмете
            </v-card-title>
            <v-card-text class="pa-6">
              <v-form @submit.prevent="submit">
                <v-row>
                  <!-- Название предмета -->
                  <v-col cols="12" md="8">
                    <v-text-field
                      v-model="form.name"
                      label="Название предмета *"
                      variant="outlined"
                      :error-messages="errors.name"
                      required
                    />
                  </v-col>

                  <!-- Код предмета -->
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="form.code"
                      label="Код предмета"
                      placeholder="например: MATH001"
                      variant="outlined"
                      :error-messages="errors.code"
                    />
                  </v-col>

                  <!-- Отделение -->
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.department_id"
                      label="Отделение"
                      :items="departments || []"
                      item-title="name"
                      item-value="id"
                      variant="outlined"
                      clearable
                      :error-messages="errors.department_id"
                    />
                  </v-col>

                  <!-- Количество кредитов -->
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model.number="form.credits"
                      label="Количество кредитов"
                      type="number"
                      min="1"
                      max="10"
                      variant="outlined"
                      :error-messages="errors.credits"
                    />
                  </v-col>

                  <!-- Краткое описание -->
                  <v-col cols="12">
                    <v-textarea
                      v-model="form.content"
                      label="Краткое описание"
                      variant="outlined"
                      rows="3"
                      :error-messages="errors.content"
                    />
                  </v-col>

                  <!-- Подробное описание -->
                  <v-col cols="12">
                    <v-textarea
                      v-model="form.description"
                      label="Подробное описание"
                      variant="outlined"
                      rows="4"
                      :error-messages="errors.description"
                    />
                  </v-col>

                  <!-- Статус активности -->
                  <v-col cols="12">
                    <v-switch
                      v-model="form.is_active"
                      label="Активный предмет"
                      color="primary"
                      inset
                    />
                  </v-col>
                </v-row>

                <!-- Действия -->
                <v-divider class="my-6"></v-divider>
                <div class="d-flex justify-end gap-3">
                  <v-btn
                    variant="outlined"
                    @click="goBack"
                    :disabled="processing"
                  >
                    Отмена
                  </v-btn>
                  <v-btn
                    type="submit"
                    color="primary"
                    :loading="processing"
                    :disabled="processing || !subject?.id"
                  >
                    Сохранить изменения
                  </v-btn>
                </div>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Информационная панель -->
        <v-col cols="12" lg="4">
          <v-card class="mb-4">
            <v-card-title class="text-h6 pa-4">
              <v-icon icon="mdi-chart-bar" class="mr-2"></v-icon>
              Статистика
            </v-card-title>
            <v-card-text class="pa-4">
              <v-list density="compact">
                <v-list-item>
                  <v-list-item-title class="text-body-2">
                    Уроки: {{ subject?.lessons?.length || 0 }}
                  </v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title class="text-body-2">
                    Расписания: {{ subject?.schedules?.length || 0 }}
                  </v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title class="text-body-2">
                    Создан: {{ subject?.created_at ? formatDate(subject.created_at) : 'Н/Д' }}
                  </v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title class="text-body-2">
                    Обновлен: {{ subject?.updated_at ? formatDate(subject.updated_at) : 'Н/Д' }}
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>

          <v-card>
            <v-card-title class="text-h6 pa-4">
              <v-icon icon="mdi-information" class="mr-2"></v-icon>
              Справка
            </v-card-title>
            <v-card-text class="pa-4">
              <v-list density="compact">
                <v-list-item>
                  <v-list-item-title class="text-body-2 font-weight-medium">
                    Осторожно!
                  </v-list-item-title>
                  <v-list-item-subtitle class="text-caption">
                    Изменение кода предмета может повлиять на связанные уроки и расписания
                  </v-list-item-subtitle>
                </v-list-item>

                <v-list-item>
                  <v-list-item-title class="text-body-2 font-weight-medium">
                    Деактивация
                  </v-list-item-title>
                  <v-list-item-subtitle class="text-caption">
                    Неактивные предметы скрыты при создании новых уроков
                  </v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Заглушка когда данные не загружены -->
      <v-row v-else>
        <v-col cols="12">
          <v-card>
            <v-card-text class="text-center pa-6">
              <v-progress-circular
                indeterminate
                color="primary"
                size="64"
                class="mb-4"
              />
              <h3>Загрузка данных предмета...</h3>
              <p class="text-medium-emphasis mt-2">
                Если загрузка занимает слишком много времени, проверьте консоль браузера для отладочной информации.
              </p>
              <v-btn @click="goBack" class="mt-4">
                Вернуться к списку
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

// Props
const props = defineProps({
  subject: {
    type: Object,
    default: () => ({})
  },
  departments: {
    type: Array,
    default: () => []
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

// Debug информация может быть включена при необходимости
// console.log('Edit.vue - Subject:', props.subject)
// console.log('Edit.vue - Departments:', props.departments)
// console.log('Edit.vue - Errors:', props.errors)

// Form data
const form = useForm({
  name: props.subject?.name,
  code: props.subject?.code,
  department_id: props.subject?.department_id || null,
  content: props.subject?.content,
  description: props.subject?.description,
  credits: props.subject?.credits || 3,
  is_active: props.subject?.is_active ?? true
})

const processing = ref(false)

// Methods
const submit = () => {
  if (!props.subject?.id) {
    console.error('Cannot submit - subject ID not available', props.subject)
    return
  }
  
  // console.log('Submitting form with data:', form.data())
  // console.log('Subject ID:', props.subject.id)
  // console.log('URL will be:', `/admin/subjects/${props.subject.id}`)
  
  processing.value = true
  
  form.patch(`/admin/subjects/${props.subject.id}`, {
    onSuccess: (page) => {
      // console.log('Update successful:', page)
      processing.value = false
      // Программный редирект на список предметов
      router.visit('/admin/subjects', { 
        method: 'get',
        data: {},
        preserveState: false,
        preserveScroll: false,
        onSuccess: () => {
          // Можно показать уведомление об успехе
          // console.log('Redirected to subjects list')
        }
      })
    },
    onError: (errors) => {
      console.error('Update failed:', errors)
      processing.value = false
    },
    onFinish: () => {
      processing.value = false
    }
  })
}

const goBack = () => {
  router.visit('/admin/subjects')
}

const formatDate = (date) => {
  if (!date) return 'Н/Д'
  return new Date(date).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
