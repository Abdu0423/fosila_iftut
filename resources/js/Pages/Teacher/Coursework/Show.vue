<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <v-btn
            variant="text"
            color="secondary"
            prepend-icon="mdi-arrow-left"
            @click="$inertia.visit('/teacher/coursework')"
            class="mb-2"
          >
            {{ t('common.back', {}, { default: 'Назад' }) }}
          </v-btn>
          <div class="d-flex justify-space-between align-center">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ t('coursework.title', {}, { default: 'Курсовая работа' }) }}</h1>
              <p class="text-body-1 text-medium-emphasis">
                {{ schedule.group }} - {{ schedule.subject }}
              </p>
            </div>
            <v-btn
              color="primary"
              variant="outlined"
              prepend-icon="mdi-pencil"
              @click="editMode = !editMode"
            >
              {{ editMode ? t('common.cancel', {}, { default: 'Отмена' }) : t('common.edit', {}, { default: 'Редактировать' }) }}
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

      <v-form @submit.prevent="updateTask">
        <v-row>
          <!-- Информация о задании -->
          <v-col cols="12" md="4">
            <v-card class="mb-4">
              <v-card-title>
                <v-icon start>mdi-information</v-icon>
                {{ t('coursework.task_info', {}, { default: 'Информация о задании' }) }}
              </v-card-title>
              <v-card-text>
                <template v-if="editMode">
                  <v-text-field
                    v-model="form.title"
                    :label="t('coursework.work_title', {}, { default: 'Название' })"
                    variant="outlined"
                    density="compact"
                    class="mb-3"
                  ></v-text-field>

                  <v-textarea
                    v-model="form.description"
                    :label="t('coursework.description', {}, { default: 'Описание' })"
                    variant="outlined"
                    density="compact"
                    rows="3"
                    class="mb-3"
                  ></v-textarea>

                  <v-textarea
                    v-model="form.requirements"
                    :label="t('coursework.requirements', {}, { default: 'Требования' })"
                    variant="outlined"
                    density="compact"
                    rows="3"
                    class="mb-3"
                  ></v-textarea>

                  <v-text-field
                    v-model="form.deadline"
                    :label="t('coursework.deadline', {}, { default: 'Срок сдачи' })"
                    type="date"
                    variant="outlined"
                    density="compact"
                    class="mb-3"
                  ></v-text-field>

                  <v-switch
                    v-model="form.is_active"
                    :label="t('coursework.is_active', {}, { default: 'Активно' })"
                    color="success"
                  ></v-switch>
                </template>
                <template v-else>
                  <div v-if="task.title" class="mb-3">
                    <div class="text-caption text-grey">{{ t('coursework.work_title', {}, { default: 'Название' }) }}</div>
                    <div class="font-weight-medium">{{ task.title }}</div>
                  </div>
                  
                  <div v-if="task.description" class="mb-3">
                    <div class="text-caption text-grey">{{ t('coursework.description', {}, { default: 'Описание' }) }}</div>
                    <div style="white-space: pre-wrap;">{{ task.description }}</div>
                  </div>

                  <div v-if="task.requirements" class="mb-3">
                    <div class="text-caption text-grey">{{ t('coursework.requirements', {}, { default: 'Требования' }) }}</div>
                    <div style="white-space: pre-wrap;">{{ task.requirements }}</div>
                  </div>

                  <div v-if="task.deadline" class="mb-3">
                    <div class="text-caption text-grey">{{ t('coursework.deadline', {}, { default: 'Срок сдачи' }) }}</div>
                    <div>{{ task.deadline }}</div>
                  </div>

                  <v-chip :color="task.is_active ? 'success' : 'warning'" size="small">
                    {{ task.is_active ? t('coursework.active', {}, { default: 'Активно' }) : t('coursework.inactive', {}, { default: 'Неактивно' }) }}
                  </v-chip>
                </template>
              </v-card-text>
            </v-card>

            <!-- Статистика -->
            <v-card>
              <v-card-title>
                <v-icon start>mdi-chart-bar</v-icon>
                {{ t('coursework.statistics', {}, { default: 'Статистика' }) }}
              </v-card-title>
              <v-card-text>
                <v-list density="compact">
                  <v-list-item>
                    <template v-slot:prepend>
                      <v-icon color="info">mdi-format-list-bulleted</v-icon>
                    </template>
                    <v-list-item-title>{{ t('coursework.total_topics', {}, { default: 'Всего тем' }) }}</v-list-item-title>
                    <template v-slot:append>
                      <v-chip size="small">{{ topics.length }}</v-chip>
                    </template>
                  </v-list-item>
                  <v-list-item>
                    <template v-slot:prepend>
                      <v-icon color="success">mdi-check-circle</v-icon>
                    </template>
                    <v-list-item-title>{{ t('coursework.taken_topics', {}, { default: 'Выбрано' }) }}</v-list-item-title>
                    <template v-slot:append>
                      <v-chip size="small" color="success">{{ takenTopicsCount }}</v-chip>
                    </template>
                  </v-list-item>
                  <v-list-item>
                    <template v-slot:prepend>
                      <v-icon color="primary">mdi-file-check</v-icon>
                    </template>
                    <v-list-item-title>{{ t('coursework.checked_works', {}, { default: 'Проверено' }) }}</v-list-item-title>
                    <template v-slot:append>
                      <v-chip size="small" color="primary">{{ checkedCount }}</v-chip>
                    </template>
                  </v-list-item>
                </v-list>
              </v-card-text>
            </v-card>

            <v-btn
              v-if="editMode"
              type="submit"
              color="primary"
              block
              class="mt-4"
              :loading="form.processing"
            >
              {{ t('common.save', {}, { default: 'Сохранить изменения' }) }}
            </v-btn>
          </v-col>

          <!-- Темы и работы -->
          <v-col cols="12" md="8">
            <v-card>
              <v-card-title class="d-flex align-center">
                <v-icon start>mdi-format-list-bulleted</v-icon>
                {{ t('coursework.topics_and_works', {}, { default: 'Темы и работы студентов' }) }}
              </v-card-title>
              <v-card-text>
                <!-- Форма добавления темы (в режиме редактирования) -->
                <div v-if="editMode" class="mb-4">
                  <div v-for="(topic, index) in form.topics" :key="index" class="mb-2">
                    <v-text-field
                      v-model="topic.topic_text"
                      :label="`${t('coursework.topic', {}, { default: 'Тема' })} ${index + 1}`"
                      variant="outlined"
                      density="compact"
                      hide-details
                      :disabled="topic.is_taken"
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
                    prepend-icon="mdi-plus"
                    @click="addTopic"
                    class="mt-2"
                  >
                    {{ t('coursework.add_topic', {}, { default: 'Добавить тему' }) }}
                  </v-btn>
                </div>

                <!-- Таблица тем (в режиме просмотра) -->
                <v-table v-else>
                  <thead>
                    <tr>
                      <th style="width: 50px;">#</th>
                      <th>{{ t('coursework.topic', {}, { default: 'Тема' }) }}</th>
                      <th>{{ t('coursework.student', {}, { default: 'Студент' }) }}</th>
                      <th class="text-center">{{ t('coursework.status', {}, { default: 'Статус' }) }}</th>
                      <th class="text-center">{{ t('coursework.grade', {}, { default: 'Оценка' }) }}</th>
                      <th style="width: 100px;"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(topic, index) in topics" :key="topic.id">
                      <td class="text-grey">{{ index + 1 }}</td>
                      <td>
                        <span class="font-weight-medium">{{ topic.topic_text }}</span>
                      </td>
                      <td>
                        <span v-if="topic.student" class="d-flex align-center">
                          <v-avatar size="28" color="primary" class="mr-2">
                            <span class="text-white text-caption">{{ getInitials(topic.student) }}</span>
                          </v-avatar>
                          {{ topic.student.full_name }}
                        </span>
                        <span v-else class="text-grey">
                          <v-icon size="small" class="mr-1">mdi-account-clock</v-icon>
                          {{ t('coursework.not_taken', {}, { default: 'Не выбрана' }) }}
                        </span>
                      </td>
                      <td class="text-center">
                        <v-chip
                          v-if="topic.submission"
                          :color="getStatusColor(topic.submission.status)"
                          size="small"
                          variant="tonal"
                        >
                          {{ getStatusText(topic.submission.status) }}
                        </v-chip>
                        <span v-else class="text-grey">-</span>
                      </td>
                      <td class="text-center">
                        <template v-if="topic.submission?.grade_100 !== null && topic.submission?.grade_100 !== undefined">
                          <v-chip :color="getGradeColor(topic.submission.grade_100)" size="small" variant="flat" class="font-weight-bold">
                            {{ topic.submission.grade_10 }} ({{ topic.submission.grade_letter }})
                          </v-chip>
                        </template>
                        <span v-else class="text-grey">-</span>
                      </td>
                      <td>
                        <v-btn
                          v-if="topic.submission && topic.submission.status !== 'draft'"
                          variant="text"
                          color="primary"
                          size="small"
                          @click="$inertia.visit(`/teacher/coursework/submission/${topic.submission.id}`)"
                        >
                          {{ t('common.view', {}, { default: 'Просмотр' }) }}
                        </v-btn>
                      </td>
                    </tr>
                  </tbody>
                </v-table>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-form>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'

const { t } = useI18n()

const props = defineProps({
  schedule: {
    type: Object,
    required: true
  },
  task: {
    type: Object,
    required: true
  },
  topics: {
    type: Array,
    default: () => []
  }
})

const editMode = ref(false)

const form = useForm({
  title: props.task.title || '',
  description: props.task.description || '',
  requirements: props.task.requirements || '',
  deadline: props.task.deadline_raw || null,
  is_active: props.task.is_active,
  topics: props.topics.map(t => ({
    id: t.id,
    topic_text: t.topic_text,
    is_taken: t.is_taken
  }))
})

const takenTopicsCount = computed(() => props.topics.filter(t => t.is_taken).length)
const checkedCount = computed(() => props.topics.filter(t => t.submission && t.submission.status === 'checked').length)

const getInitials = (student) => {
  if (!student) return '?'
  const last = student.last_name ? student.last_name[0] : ''
  const first = student.name ? student.name[0] : ''
  return (last + first).toUpperCase()
}

const getStatusColor = (status) => {
  const colors = { draft: 'grey', submitted: 'warning', checked: 'success', returned: 'error' }
  return colors[status] || 'grey'
}

const getStatusText = (status) => {
  const texts = {
    draft: t('coursework.status_draft', {}, { default: 'Черновик' }),
    submitted: t('coursework.status_submitted', {}, { default: 'Отправлено' }),
    checked: t('coursework.status_checked', {}, { default: 'Проверено' }),
    returned: t('coursework.status_returned', {}, { default: 'На доработку' })
  }
  return texts[status] || status
}

const getGradeColor = (grade) => {
  if (grade >= 90) return 'success'
  if (grade >= 75) return 'info'
  if (grade >= 60) return 'warning'
  return 'error'
}

const addTopic = () => {
  form.topics.push({ id: null, topic_text: '', is_taken: false })
}

const removeTopic = (index) => {
  if (form.topics.length > 1 && !form.topics[index].is_taken) {
    form.topics.splice(index, 1)
  }
}

const updateTask = () => {
  form.put(`/teacher/coursework/${props.schedule.id}`, {
    onSuccess: () => {
      editMode.value = false
    }
  })
}
</script>
