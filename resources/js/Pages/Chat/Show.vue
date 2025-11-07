<template>
  <Layout :role="userRole">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex align-center mb-6">
            <v-btn
              color="secondary"
              variant="outlined"
              @click="router.visit('/chat')"
              prepend-icon="mdi-arrow-left"
              class="mr-4"
            >
              Назад к чатам
            </v-btn>
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ chat.display_name }}</h1>
              <p class="text-body-1 text-medium-emphasis">
                {{ chat.type === 'private' ? 'Приватный чат' : 'Групповой чат' }}
              </p>
            </div>
            <v-spacer></v-spacer>
            <v-btn
              icon="mdi-account-minus"
              variant="text"
              @click="leaveChat"
              color="error"
            >
              <v-icon>mdi-exit-to-app</v-icon>
              <v-tooltip activator="parent" location="bottom">
                Покинуть чат
              </v-tooltip>
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Чат -->
      <v-row>
        <v-col cols="12">
          <v-card class="chat-container">
            <!-- Заголовок чата -->
            <v-card-title class="d-flex align-center">
              <v-avatar
                :color="chat.display_avatar ? 'transparent' : 'primary'"
                size="48"
                class="mr-3"
              >
                <v-img
                  v-if="chat.display_avatar"
                  :src="chat.display_avatar"
                  :alt="chat.display_name"
                ></v-img>
                <span v-else class="text-white font-weight-bold">
                  {{ getInitials(chat.display_name) }}
                </span>
              </v-avatar>
              <div>
                <div class="text-h6">{{ chat.display_name }}</div>
                <div class="text-caption text-medium-emphasis">
                  {{ chat.users?.length || 0 }} участников
                </div>
              </div>
            </v-card-title>

            <!-- Сообщения -->
            <v-card-text class="chat-messages pa-0" ref="messagesContainer">
              <div v-if="messages.length === 0" class="text-center pa-8">
                <v-icon size="64" color="grey-lighten-1">mdi-chat-outline</v-icon>
                <div class="text-h6 text-medium-emphasis mt-4">Начните общение</div>
                <div class="text-body-2 text-medium-emphasis">
                  Отправьте первое сообщение в этом чате
                </div>
              </div>

              <div v-else class="messages-list">
                <div
                  v-for="message in messages"
                  :key="message.id"
                  class="message-item"
                  :class="{ 'message-own': message.is_from_current_user }"
                >
                  <div class="message-content">
                    <div class="message-header" v-if="!message.is_from_current_user">
                      <span class="message-author">{{ message.user.name }}</span>
                      <span class="message-time">{{ formatTime(message.created_at) }}</span>
                    </div>
                    <div class="message-text">{{ message.message }}</div>
                    <div class="message-status" v-if="message.is_from_current_user">
                      <v-icon
                        size="16"
                        :color="message.status === 'read' ? 'primary' : 'grey'"
                      >
                        {{ message.status === 'read' ? 'mdi-check-all' : 'mdi-check' }}
                      </v-icon>
                    </div>
                  </div>
                </div>
              </div>
            </v-card-text>

            <!-- Поле ввода сообщения -->
            <v-card-actions class="pa-4">
              <v-textarea
                v-model="newMessage"
                placeholder="Введите сообщение..."
                variant="outlined"
                density="compact"
                rows="1"
                auto-grow
                hide-details
                @keydown.enter.prevent="sendMessage"
                class="flex-grow-1 mr-3"
              ></v-textarea>
              <v-btn
                color="primary"
                variant="elevated"
                @click="sendMessage"
                :disabled="!newMessage.trim()"
                icon="mdi-send"
              >
                <v-icon>mdi-send</v-icon>
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '../Layout.vue'

// Определяем роль пользователя на основе URL
const userRole = computed(() => {
  const path = window.location.pathname
  if (path.startsWith('/admin')) {
    return 'admin'
  } else if (path.startsWith('/teacher')) {
    return 'teacher'
  } else {
    return 'student'
  }
})

// Props
const props = defineProps({
  chat: {
    type: Object,
    required: true
  },
  messages: {
    type: Object,
    required: true
  }
})

// Состояние
const newMessage = ref('')
const messagesContainer = ref(null)
const messages = ref(props.messages.data || [])

// Методы
const sendMessage = async () => {
  if (!newMessage.value.trim()) return

  try {
    const response = await fetch(`/chat/${props.chat.id}/messages`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        message: newMessage.value.trim()
      })
    })

    const data = await response.json()
    
    if (data.success) {
      messages.value.push(data.message)
      newMessage.value = ''
      await nextTick()
      scrollToBottom()
    }
  } catch (error) {
    console.error('Ошибка при отправке сообщения:', error)
  }
}

const leaveChat = () => {
  if (confirm('Вы уверены, что хотите покинуть этот чат?')) {
    router.delete(`/chat/${props.chat.id}/leave`)
  }
}

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const formatTime = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleTimeString('ru-RU', { 
    hour: '2-digit', 
    minute: '2-digit' 
  })
}

const getInitials = (name) => {
  return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase().slice(0, 2)
}

// Жизненный цикл
onMounted(() => {
  scrollToBottom()
})

// Отслеживаем изменения в сообщениях
watch(() => props.messages, (newMessages) => {
  messages.value = newMessages.data || []
  nextTick(() => {
    scrollToBottom()
  })
}, { deep: true })
</script>

<style scoped>
.chat-container {
  height: 600px;
  display: flex;
  flex-direction: column;
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  max-height: 400px;
}

.messages-list {
  padding: 16px;
}

.message-item {
  margin-bottom: 16px;
  display: flex;
}

.message-item.message-own {
  justify-content: flex-end;
}

.message-content {
  max-width: 70%;
  padding: 12px 16px;
  border-radius: 18px;
  background-color: rgb(var(--v-theme-surface-variant));
}

.message-item.message-own .message-content {
  background-color: rgb(var(--v-theme-primary));
  color: white;
}

.message-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.message-author {
  font-weight: 600;
  font-size: 0.875rem;
}

.message-time {
  font-size: 0.75rem;
  opacity: 0.7;
}

.message-text {
  line-height: 1.4;
}

.message-status {
  display: flex;
  justify-content: flex-end;
  margin-top: 4px;
}
</style>
