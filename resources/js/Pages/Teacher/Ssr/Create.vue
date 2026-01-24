<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('ssr.create_task', {}, { default: 'Создать ССР' }) }}</h1>
              <p class="text-body-1 text-medium-emphasis">{{ t('ssr.create_subtitle', {}, { default: 'Создайте задание и добавьте список тем для выбора студентами' }) }}</p>
            </div>
            <v-btn
              color="secondary"
              variant="text"
              prepend-icon="mdi-arrow-left"
              @click="$inertia.visit('/teacher/ssr')"
            >
              {{ t('common.back', {}, { default: 'Назад' }) }}
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <v-form @submit.prevent="submitForm">
        <v-row>
          <!-- Основная информация -->
          <v-col cols="12" md="8">
            <v-card>
              <v-card-title>
                <v-icon start>mdi-information</v-icon>
                {{ t('ssr.basic_info', {}, { default: 'Основная информация' }) }}
              </v-card-title>
              <v-card-text>
                <v-text-field
                  v-model="form.title"
                  :label="t('ssr.task_title', {}, { default: 'Название ССР' }) + ' *'"
                  variant="outlined"
                  density="comfortable"
                  :error-messages="form.errors.title"
                  class="mb-4"
                ></v-text-field>

                <v-select
                  v-model="form.schedule_id"
                  :items="schedules"
                  item-title="label"
                  item-value="id"
                  :label="t('ssr.schedule', {}, { default: 'Расписание (группа - предмет)' })"
                  variant="outlined"
                  density="comfortable"
                  clearable
                  :error-messages="form.errors.schedule_id"
                  class="mb-4"
                ></v-select>

                <v-textarea
                  v-model="form.description"
                  :label="t('ssr.description', {}, { default: 'Описание задания' })"
                  variant="outlined"
                  density="comfortable"
                  rows="3"
                  :error-messages="form.errors.description"
                  class="mb-4"
                ></v-textarea>

                <v-textarea
                  v-model="form.requirements"
                  :label="t('ssr.requirements', {}, { default: 'Требования к работе' })"
                  variant="outlined"
                  density="comfortable"
                  rows="3"
                  :error-messages="form.errors.requirements"
                  :hint="t('ssr.requirements_hint', {}, { default: 'Опишите требования к оформлению, объему, структуре работы' })"
                  persistent-hint
                  class="mb-4"
                ></v-textarea>

                <v-text-field
                  v-model="form.deadline"
                  :label="t('ssr.deadline', {}, { default: 'Срок сдачи' })"
                  type="date"
                  variant="outlined"
                  density="comfortable"
                  :error-messages="form.errors.deadline"
                ></v-text-field>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Темы -->
          <v-col cols="12" md="4">
            <v-card>
              <v-card-title class="d-flex align-center">
                <v-icon start>mdi-format-list-bulleted</v-icon>
                {{ t('ssr.topics_list', {}, { default: 'Список тем' }) }} *
                <v-spacer></v-spacer>
                <v-chip color="primary" size="small">{{ form.topics.length }}</v-chip>
              </v-card-title>
              <v-card-text>
                <v-alert
                  type="info"
                  variant="tonal"
                  density="compact"
                  class="mb-4"
                >
                  {{ t('ssr.topics_info', {}, { default: 'Каждую тему может выбрать только один студент' }) }}
                </v-alert>

                <div v-for="(topic, index) in form.topics" :key="index" class="mb-3">
                  <v-text-field
                    v-model="form.topics[index]"
                    :label="`${t('ssr.topic', {}, { default: 'Тема' })} ${index + 1}`"
                    variant="outlined"
                    density="compact"
                    hide-details
                  >
                    <template v-slot:append>
                      <v-btn
                        icon
                        variant="text"
                        color="error"
                        size="small"
                        @click="removeTopic(index)"
                        :disabled="form.topics.length <= 1"
                      >
                        <v-icon>mdi-close</v-icon>
                      </v-btn>
                    </template>
                  </v-text-field>
                </div>

                <v-btn
                  variant="tonal"
                  color="primary"
                  block
                  prepend-icon="mdi-plus"
                  @click="addTopic"
                >
                  {{ t('ssr.add_topic', {}, { default: 'Добавить тему' }) }}
                </v-btn>

                <v-alert
                  v-if="form.errors.topics"
                  type="error"
                  variant="tonal"
                  density="compact"
                  class="mt-4"
                >
                  {{ form.errors.topics }}
                </v-alert>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <!-- Кнопки действий -->
        <v-row>
          <v-col cols="12">
            <div class="d-flex justify-end gap-3">
              <v-btn
                variant="outlined"
                @click="$inertia.visit('/teacher/ssr')"
              >
                {{ t('common.cancel', {}, { default: 'Отмена' }) }}
              </v-btn>
              <v-btn
                type="submit"
                color="primary"
                :loading="form.processing"
                prepend-icon="mdi-content-save"
              >
                {{ t('common.save', {}, { default: 'Сохранить' }) }}
              </v-btn>
            </div>
          </v-col>
        </v-row>
      </v-form>
    </v-container>
  </Layout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'

const { t } = useI18n()

defineProps({
  schedules: {
    type: Array,
    default: () => []
  }
})

const form = useForm({
  title: '',
  description: '',
  requirements: '',
  schedule_id: null,
  deadline: null,
  topics: ['', '', ''], // Начинаем с 3 пустых тем
})

const addTopic = () => {
  form.topics.push('')
}

const removeTopic = (index) => {
  if (form.topics.length > 1) {
    form.topics.splice(index, 1)
  }
}

const submitForm = () => {
  // Фильтруем пустые темы
  const filteredTopics = form.topics.filter(t => t.trim())
  
  if (filteredTopics.length === 0) {
    form.errors.topics = t('ssr.topics_required', {}, { default: 'Добавьте хотя бы одну тему' })
    return
  }

  form.transform(data => ({
    ...data,
    topics: filteredTopics
  })).post('/teacher/ssr')
}
</script>
