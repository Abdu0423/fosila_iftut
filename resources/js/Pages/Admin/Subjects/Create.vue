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
              <h1 class="text-h4 font-weight-bold mb-2">Создание предмета</h1>
              <p class="text-body-1 text-medium-emphasis">Добавление нового учебного предмета</p>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Отладочная информация -->
      <v-row>
        <v-col cols="12">
          <v-alert type="info" class="mb-4">
            Отделений загружено: {{ departments?.length || 0 }}
            | Ошибок: {{ Object.keys(errors || {}).length }}
          </v-alert>
        </v-col>
      </v-row>

      <!-- Форма создания -->
      <v-row>
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

                  <!-- Отделение -->
                  <v-col cols="12" md="4">
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
                    :disabled="processing"
                  >
                    Создать предмет
                  </v-btn>
                </div>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Информационная панель -->
        <v-col cols="12" lg="4">
          <v-card>
            <v-card-title class="text-h6 pa-4">
              <v-icon icon="mdi-information" class="mr-2"></v-icon>
              Справка
            </v-card-title>
            <v-card-text class="pa-4">
              <v-list density="compact">
                <v-list-item>
                  <v-list-item-title class="text-body-2 font-weight-medium">
                    Название предмета
                  </v-list-item-title>
                  <v-list-item-subtitle class="text-caption">
                    Укажите полное название учебного предмета
                  </v-list-item-subtitle>
                </v-list-item>

                <v-list-item>
                  <v-list-item-title class="text-body-2 font-weight-medium">
                    Статус
                  </v-list-item-title>
                  <v-list-item-subtitle class="text-caption">
                    Активные предметы доступны для создания уроков и расписания
                  </v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import Layout from '../../Layout.vue'

// Props
const props = defineProps({
  departments: {
    type: Array,
    default: () => []
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

// Form data
const form = useForm({
  name: '',
  department_id: null,
  content: '',
  description: '',
  is_active: true
})

const processing = ref(false)

// Methods
const submit = () => {
  processing.value = true
  
  form.post('/admin/subjects', {
    onSuccess: () => {
      processing.value = false
    },
    onError: () => {
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

// Debug информация может быть включена при необходимости
// console.log('Create.vue - Departments:', props.departments)
// console.log('Create.vue - Errors:', props.errors)
</script>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
