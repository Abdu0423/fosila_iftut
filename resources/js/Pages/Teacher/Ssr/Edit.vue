<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('ssr.edit_task', {}, { default: 'Редактировать ССР' }) }}</h1>
              <p class="text-body-1 text-medium-emphasis">{{ task.title }}</p>
            </div>
            <v-btn
              color="secondary"
              variant="text"
              prepend-icon="mdi-arrow-left"
              @click="$inertia.visit(`/teacher/ssr/${task.id}`)"
            >
              {{ t('common.back', {}, { default: 'Назад' }) }}
            </v-btn>
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
                  class="mb-4"
                ></v-textarea>

                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.deadline"
                      :label="t('ssr.deadline', {}, { default: 'Срок сдачи' })"
                      type="date"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="form.errors.deadline"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-switch
                      v-model="form.is_active"
                      :label="t('ssr.is_active', {}, { default: 'Активно (доступно студентам)' })"
                      color="success"
                    ></v-switch>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Темы -->
          <v-col cols="12" md="6">
            <v-card>
              <v-card-title class="d-flex align-center">
                <v-icon start>mdi-format-list-bulleted</v-icon>
                {{ t('ssr.topics_list', {}, { default: 'Список тем' }) }} *
                <v-spacer></v-spacer>
                <v-chip color="primary" size="small">{{ form.topics.length }}</v-chip>
              </v-card-title>
              <v-card-text>
                <v-alert
                  type="warning"
                  variant="tonal"
                  density="compact"
                  class="mb-4"
                >
                  {{ t('ssr.edit_topics_warning', {}, { default: 'Занятые темы нельзя редактировать или удалять' }) }}
                </v-alert>

                <div v-for="(topic, index) in form.topics" :key="index" class="mb-3">
                  <v-text-field
                    v-model="topic.topic_text"
                    :label="`${t('ssr.topic', {}, { default: 'Тема' })} ${index + 1}`"
                    variant="outlined"
                    density="compact"
                    hide-details
                    :disabled="topic.is_taken"
                    :class="{ 'topic-taken': topic.is_taken }"
                  >
                    <template v-slot:prepend-inner>
                      <v-icon v-if="topic.is_taken" color="success" size="small">mdi-lock</v-icon>
                    </template>
                    <template v-slot:append>
                      <v-btn
                        icon
                        variant="text"
                        color="error"
                        size="small"
                        @click="removeTopic(index)"
                        :disabled="form.topics.length <= 1 || topic.is_taken"
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
                @click="$inertia.visit(`/teacher/ssr/${task.id}`)"
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
  task: {
    type: Object,
    required: true
  },
  schedules: {
    type: Array,
    default: () => []
  }
})

const form = useForm({
  title: props.task.title,
  description: props.task.description || '',
  requirements: props.task.requirements || '',
  schedule_id: props.task.schedule_id,
  deadline: props.task.deadline,
  is_active: props.task.is_active,
  topics: props.task.topics.map(t => ({
    id: t.id,
    topic_text: t.topic_text,
    is_taken: t.is_taken
  }))
})

const addTopic = () => {
  form.topics.push({
    id: null,
    topic_text: '',
    is_taken: false
  })
}

const removeTopic = (index) => {
  if (form.topics.length > 1 && !form.topics[index].is_taken) {
    form.topics.splice(index, 1)
  }
}

const submitForm = () => {
  // Фильтруем пустые темы (только новые)
  const validTopics = form.topics.filter(t => t.topic_text.trim() || t.id)
  
  if (validTopics.length === 0) {
    form.errors.topics = t('ssr.topics_required', {}, { default: 'Добавьте хотя бы одну тему' })
    return
  }

  form.transform(data => ({
    ...data,
    topics: validTopics.map(t => ({
      id: t.id,
      topic_text: t.topic_text
    }))
  })).put(`/teacher/ssr/${props.task.id}`)
}
</script>

<style scoped>
.topic-taken {
  opacity: 0.7;
}
</style>
