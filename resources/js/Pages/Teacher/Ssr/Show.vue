<template>
  <Layout role="teacher">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-4">
            <div>
              <v-btn
                variant="text"
                color="secondary"
                prepend-icon="mdi-arrow-left"
                @click="$inertia.visit('/teacher/ssr')"
                class="mb-2"
              >
                {{ t('common.back', {}, { default: 'Назад' }) }}
              </v-btn>
              <h1 class="text-h4 font-weight-bold">{{ task.title }}</h1>
              <div class="d-flex align-center gap-2 mt-2">
                <v-chip v-if="task.schedule" color="primary" size="small">
                  {{ task.schedule.group }} - {{ task.schedule.subject }}
                </v-chip>
                <v-chip v-if="task.deadline" :color="isOverdue ? 'error' : 'info'" size="small">
                  <v-icon start size="small">mdi-calendar</v-icon>
                  {{ task.deadline }}
                </v-chip>
                <v-chip :color="task.is_active ? 'success' : 'warning'" size="small">
                  {{ task.is_active ? t('ssr.active', {}, { default: 'Активно' }) : t('ssr.inactive', {}, { default: 'Неактивно' }) }}
                </v-chip>
              </div>
            </div>
            <v-btn
              color="primary"
              variant="outlined"
              prepend-icon="mdi-pencil"
              @click="$inertia.visit(`/teacher/ssr/${task.id}/edit`)"
            >
              {{ t('common.edit', {}, { default: 'Редактировать' }) }}
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

      <v-row>
        <!-- Описание и требования -->
        <v-col cols="12" md="4">
          <v-card class="mb-4">
            <v-card-title>
              <v-icon start>mdi-information</v-icon>
              {{ t('ssr.description', {}, { default: 'Описание' }) }}
            </v-card-title>
            <v-card-text>
              <p v-if="task.description" class="text-body-1 mb-0" style="white-space: pre-wrap;">{{ task.description }}</p>
              <p v-else class="text-grey">{{ t('ssr.no_description', {}, { default: 'Описание не указано' }) }}</p>
            </v-card-text>
          </v-card>

          <v-card v-if="task.requirements">
            <v-card-title>
              <v-icon start>mdi-clipboard-list</v-icon>
              {{ t('ssr.requirements', {}, { default: 'Требования' }) }}
            </v-card-title>
            <v-card-text>
              <p class="text-body-1 mb-0" style="white-space: pre-wrap;">{{ task.requirements }}</p>
            </v-card-text>
          </v-card>

          <!-- Статистика -->
          <v-card class="mt-4">
            <v-card-title>
              <v-icon start>mdi-chart-bar</v-icon>
              {{ t('ssr.statistics', {}, { default: 'Статистика' }) }}
            </v-card-title>
            <v-card-text>
              <v-list density="compact">
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="info">mdi-format-list-bulleted</v-icon>
                  </template>
                  <v-list-item-title>{{ t('ssr.total_topics', {}, { default: 'Всего тем' }) }}</v-list-item-title>
                  <template v-slot:append>
                    <v-chip size="small">{{ topics.length }}</v-chip>
                  </template>
                </v-list-item>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="success">mdi-check-circle</v-icon>
                  </template>
                  <v-list-item-title>{{ t('ssr.taken_topics', {}, { default: 'Выбрано тем' }) }}</v-list-item-title>
                  <template v-slot:append>
                    <v-chip size="small" color="success">{{ takenTopicsCount }}</v-chip>
                  </template>
                </v-list-item>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="warning">mdi-clock-outline</v-icon>
                  </template>
                  <v-list-item-title>{{ t('ssr.available_topics', {}, { default: 'Свободно тем' }) }}</v-list-item-title>
                  <template v-slot:append>
                    <v-chip size="small" color="warning">{{ topics.length - takenTopicsCount }}</v-chip>
                  </template>
                </v-list-item>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="primary">mdi-file-document</v-icon>
                  </template>
                  <v-list-item-title>{{ t('ssr.submitted_works', {}, { default: 'Отправлено работ' }) }}</v-list-item-title>
                  <template v-slot:append>
                    <v-chip size="small" color="primary">{{ submittedCount }}</v-chip>
                  </template>
                </v-list-item>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="success">mdi-check-decagram</v-icon>
                  </template>
                  <v-list-item-title>{{ t('ssr.checked_works', {}, { default: 'Проверено работ' }) }}</v-list-item-title>
                  <template v-slot:append>
                    <v-chip size="small" color="success">{{ checkedCount }}</v-chip>
                  </template>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Список тем и работ -->
        <v-col cols="12" md="8">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon start>mdi-format-list-bulleted</v-icon>
              {{ t('ssr.topics_and_works', {}, { default: 'Темы и работы студентов' }) }}
            </v-card-title>
            <v-card-text>
              <v-table>
                <thead>
                  <tr>
                    <th style="width: 50px;">#</th>
                    <th>{{ t('ssr.topic', {}, { default: 'Тема' }) }}</th>
                    <th>{{ t('ssr.student', {}, { default: 'Студент' }) }}</th>
                    <th class="text-center">{{ t('ssr.status', {}, { default: 'Статус' }) }}</th>
                    <th class="text-center">{{ t('ssr.grade', {}, { default: 'Оценка' }) }}</th>
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
                        {{ t('ssr.not_taken', {}, { default: 'Не выбрана' }) }}
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
                      <span v-else-if="topic.is_taken" class="text-grey">-</span>
                      <span v-else class="text-grey">-</span>
                    </td>
                    <td class="text-center">
                      <v-chip
                        v-if="topic.submission?.grade !== null && topic.submission?.grade !== undefined"
                        :color="getGradeColor(topic.submission.grade)"
                        size="small"
                        variant="flat"
                        class="font-weight-bold"
                      >
                        {{ topic.submission.grade }}
                      </v-chip>
                      <span v-else class="text-grey">-</span>
                    </td>
                    <td>
                      <v-btn
                        v-if="topic.submission && topic.submission.status !== 'draft'"
                        variant="text"
                        color="primary"
                        size="small"
                        @click="$inertia.visit(`/teacher/ssr/submission/${topic.submission.id}`)"
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
    </v-container>
  </Layout>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import Layout from '../../Layout.vue'

const { t } = useI18n()

const props = defineProps({
  task: {
    type: Object,
    required: true
  },
  topics: {
    type: Array,
    default: () => []
  }
})

const isOverdue = computed(() => {
  if (!props.task.deadline) return false
  return new Date(props.task.deadline) < new Date()
})

const takenTopicsCount = computed(() => {
  return props.topics.filter(t => t.is_taken).length
})

const submittedCount = computed(() => {
  return props.topics.filter(t => t.submission && t.submission.status !== 'draft').length
})

const checkedCount = computed(() => {
  return props.topics.filter(t => t.submission && t.submission.status === 'checked').length
})

const getInitials = (student) => {
  if (!student) return '?'
  const last = student.last_name ? student.last_name[0] : ''
  const first = student.name ? student.name[0] : ''
  return (last + first).toUpperCase()
}

const getStatusColor = (status) => {
  const colors = {
    draft: 'grey',
    submitted: 'warning',
    checked: 'success',
    returned: 'error'
  }
  return colors[status] || 'grey'
}

const getStatusText = (status) => {
  const texts = {
    draft: t('ssr.status_draft', {}, { default: 'Черновик' }),
    submitted: t('ssr.status_submitted', {}, { default: 'Отправлено' }),
    checked: t('ssr.status_checked', {}, { default: 'Проверено' }),
    returned: t('ssr.status_returned', {}, { default: 'На доработку' })
  }
  return texts[status] || status
}

const getGradeColor = (grade) => {
  if (grade >= 90) return 'success'
  if (grade >= 75) return 'info'
  if (grade >= 60) return 'warning'
  return 'error'
}
</script>
