<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <v-btn
                variant="text"
                color="secondary"
                prepend-icon="mdi-arrow-left"
                @click="$inertia.visit('/teacher/coursework')"
                class="mb-2"
              >
                {{ t('common.back', {}, { default: 'Назад' }) }}
              </v-btn>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('coursework.setup_coursework', {}, { default: 'Настройка курсовой работы' }) }}</h1>
              <p class="text-body-1 text-medium-emphasis">
                {{ schedule.group }} - {{ schedule.subject }}
              </p>
            </div>
          </div>
        </v-col>
      </v-row>

      <v-form @submit.prevent="submitForm">
        <v-row>
          <!-- Основная информация -->
          <v-col cols="12" md="6">
            <v-card>
              <v-card-title>
                <v-icon start>mdi-information</v-icon>
                {{ t('coursework.basic_info', {}, { default: 'Основная информация' }) }}
              </v-card-title>
              <v-card-text>
                <v-text-field
                  v-model="form.title"
                  :label="t('coursework.work_title', {}, { default: 'Название курсовой работы' })"
                  variant="outlined"
                  density="comfortable"
                  :error-messages="form.errors.title"
                  class="mb-4"
                ></v-text-field>

                <v-textarea
                  v-model="form.description"
                  :label="t('coursework.description', {}, { default: 'Описание' })"
                  variant="outlined"
                  density="comfortable"
                  rows="3"
                  :error-messages="form.errors.description"
                  class="mb-4"
                ></v-textarea>

                <v-textarea
                  v-model="form.requirements"
                  :label="t('coursework.requirements', {}, { default: 'Требования к работе' })"
                  variant="outlined"
                  density="comfortable"
                  rows="3"
                  :error-messages="form.errors.requirements"
                  :hint="t('coursework.requirements_hint', {}, { default: 'Опишите требования к оформлению, объему, структуре работы' })"
                  persistent-hint
                  class="mb-4"
                ></v-textarea>

                <v-text-field
                  v-model="form.deadline"
                  :label="t('coursework.deadline', {}, { default: 'Срок сдачи' })"
                  type="date"
                  variant="outlined"
                  density="comfortable"
                  :error-messages="form.errors.deadline"
                ></v-text-field>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Темы -->
          <v-col cols="12" md="6">
            <v-card>
              <v-card-title class="d-flex align-center">
                <v-icon start>mdi-format-list-bulleted</v-icon>
                {{ t('coursework.topics_list', {}, { default: 'Список тем' }) }} *
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
                  {{ t('coursework.topics_info', {}, { default: 'Каждую тему может выбрать только один студент' }) }}
                </v-alert>

                <div v-for="(topic, index) in form.topics" :key="index" class="mb-3">
                  <v-text-field
                    v-model="form.topics[index]"
                    :label="`${t('coursework.topic', {}, { default: 'Тема' })} ${index + 1}`"
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
                  {{ t('coursework.add_topic', {}, { default: 'Добавить тему' }) }}
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
                @click="$inertia.visit('/teacher/coursework')"
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

const props = defineProps({
  schedule: {
    type: Object,
    required: true
  }
})

const form = useForm({
  title: '',
  description: '',
  requirements: '',
  deadline: null,
  topics: ['', '', ''],
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
  const filteredTopics = form.topics.filter(t => t.trim())
  
  if (filteredTopics.length === 0) {
    form.errors.topics = t('coursework.topics_required', {}, { default: 'Добавьте хотя бы одну тему' })
    return
  }

  form.transform(data => ({
    ...data,
    topics: filteredTopics
  })).post(`/teacher/coursework/${props.schedule.id}`)
}
</script>
