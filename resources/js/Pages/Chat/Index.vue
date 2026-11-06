<template>
  <Layout :role="userRole">
    <v-container fluid class="messenger-page">
      <v-card class="messenger-shell" rounded="lg" elevation="1">
        <aside class="chat-list">
          <div class="chat-list-scroll">
            <v-list class="py-0" bg-color="transparent">
              <v-list-item
                v-for="chat in chats"
                :key="chat.id"
                :active="selectedChatId === chat.id"
                @click="selectChat(chat.id)"
                class="chat-item"
                lines="two"
              >
                <template v-slot:prepend>
                  <v-avatar
                    :color="chat.display_avatar ? 'transparent' : 'primary'"
                    size="48"
                  >
                    <v-img
                      v-if="chat.display_avatar"
                      :src="chat.display_avatar"
                      :alt="getChatPartnerName(chat)"
                    ></v-img>
                    <span v-else class="text-white font-weight-bold">
                      {{ getInitials(getChatPartnerName(chat)) }}
                    </span>
                  </v-avatar>
                </template>

                <v-list-item-title class="font-weight-medium">
                  {{ getChatPartnerName(chat) }}
                </v-list-item-title>
                <v-list-item-subtitle class="text-truncate">
                  <template v-if="chat.last_message">
                    {{ chat.last_message.user_id === currentUserId ? 'Вы: ' : '' }}{{ chat.last_message.message }}
                  </template>
                  <span v-else>Нет сообщений</span>
                </v-list-item-subtitle>

                <template v-slot:append>
                  <div class="chat-item-meta">
                    <span v-if="chat.last_message" class="text-caption text-medium-emphasis">
                      {{ formatTime(chat.last_message.created_at) }}
                    </span>
                    <v-badge
                      v-if="chat.unread_count > 0"
                      :content="chat.unread_count"
                      color="primary"
                      inline
                    />
                  </div>
                </template>
              </v-list-item>

              <div v-if="chats.length === 0" class="chat-list-empty text-medium-emphasis">
                У вас пока нет чатов
              </div>
            </v-list>
          </div>

          <v-btn
            class="new-chat-fab"
            color="primary"
            icon
            size="large"
            elevation="8"
            @click="showNewChatDialog = true"
          >
            <v-icon>mdi-plus</v-icon>
          </v-btn>
        </aside>

        <section v-if="selectedChat" class="chat-thread">
          <div class="thread-header">
            <v-avatar
              :color="selectedChat.display_avatar ? 'transparent' : 'primary'"
              size="40"
              class="mr-3"
            >
              <v-img
                v-if="selectedChat.display_avatar"
                :src="selectedChat.display_avatar"
                :alt="selectedChat.display_name"
              ></v-img>
              <span v-else class="text-white font-weight-bold">
                {{ getInitials(chatPartnerName) }}
              </span>
            </v-avatar>
            <div class="flex-grow-1">
              <div class="text-subtitle-1 font-weight-medium">{{ chatPartnerName }}</div>
              <div v-if="selectedChat.type !== 'private'" class="text-caption text-medium-emphasis">
                Групповой чат
              </div>
            </div>
            <v-btn
              icon="mdi-exit-to-app"
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

          <div class="chat-messages" ref="messagesContainer">
            <div v-if="messages.length === 0" class="thread-empty">
              <v-icon size="56" color="grey-lighten-1">mdi-chat-outline</v-icon>
              <div class="text-subtitle-1 text-medium-emphasis mt-3">Начните общение</div>
            </div>

            <div v-else class="messages-list">
              <div
                v-for="message in messages"
                :key="message.id"
                class="message-item"
                :class="{ 'message-own': isOwnMessage(message) }"
              >
                <div class="message-content">
                  <div v-if="!isOwnMessage(message)" class="message-author">
                    {{ personName(message.user) }}
                  </div>
                  <div class="message-text">{{ message.message }}</div>
                  <div class="message-meta">
                    <span class="message-time">{{ formatTime(message.created_at) }}</span>
                    <v-icon
                      v-if="isOwnMessage(message)"
                      size="14"
                      :color="message.status === 'read' ? 'primary' : 'grey'"
                    >
                      {{ message.status === 'read' ? 'mdi-check-all' : 'mdi-check' }}
                    </v-icon>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="composer">
            <v-text-field
              v-model="newMessage"
              placeholder="Сообщение"
              variant="outlined"
              density="comfortable"
              hide-details
              rounded="pill"
              class="flex-grow-1"
              @keydown.enter.prevent="sendMessage"
            />
            <v-btn
              class="ml-2"
              color="primary"
              icon
              size="large"
              :disabled="!newMessage.trim()"
              @click="sendMessage"
            >
              <v-icon>mdi-send</v-icon>
            </v-btn>
          </div>
        </section>

        <section v-else class="chat-thread">
          <div class="thread-empty">
            <v-icon size="64" color="grey-lighten-1">mdi-chat-outline</v-icon>
            <div class="text-h6 text-medium-emphasis mt-4">Выберите чат</div>
            <div class="text-body-2 text-medium-emphasis">
              Выберите диалог слева или нажмите +
            </div>
          </div>
        </section>
      </v-card>

      <!-- Диалог создания нового чата -->
      <v-dialog v-model="showNewChatDialog" max-width="500">
        <v-card>
          <v-card-title class="text-h6">
            <v-icon start>mdi-plus</v-icon>
            Новый чат
          </v-card-title>
          <v-card-text>
            <v-autocomplete
              :model-value="newChatForm.user_id"
              :items="chatUsers"
              item-title="displayName"
              item-value="id"
              :custom-filter="filterChatUser"
              label="Поиск собеседника"
              placeholder="Введите имя или email..."
              variant="outlined"
              density="compact"
              prepend-inner-icon="mdi-magnify"
              autofocus
              no-data-text="Пользователи не найдены"
              @update:model-value="openChatWith"
            >
              <template v-slot:item="{ props, item }">
                <v-list-item v-bind="props" :subtitle="item.raw?.email || ''" />
              </template>
            </v-autocomplete>
          </v-card-text>
        </v-card>
      </v-dialog>
    </v-container>
  </Layout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useForm, router, usePage } from '@/compat/inertia'
import Layout from '../Layout.vue'

const page = usePage()

const panelPrefix = computed(() => {
  const path = window.location.pathname
  const prefixes = ['admin', 'teacher', 'education', 'registration', 'department', 'faculty', 'student']
  return prefixes.find((item) => path === `/${item}` || path.startsWith(`/${item}/`)) || 'student'
})

const userRole = computed(() => {
  if (panelPrefix.value === 'education') return 'education_department'
  if (panelPrefix.value === 'registration') return 'registration_center'
  return panelPrefix.value
})

const chatUrl = (suffix = '') => `/${panelPrefix.value}/chat${suffix}`

// Props
const props = defineProps({
  chats: {
    type: Array,
    default: () => []
  },
  users: {
    type: Array,
    default: () => []
  }
})

// Состояние
const selectedChatId = ref(null)
const selectedChat = ref(null)
const messages = ref([])
const newMessage = ref('')
const showNewChatDialog = ref(false)
const messagesContainer = ref(null)
const echoChannel = ref(null)
const chats = ref([...props.chats])
// Форма для нового чата
const newChatForm = useForm({
  user_id: null
})

const personName = (person) => {
  if (!person) return ''
  return [person.last_name, person.name, person.middle_name].filter(Boolean).join(' ')
}

const chatUsers = computed(() =>
  (props.users || [])
    .map((user) => ({
      ...user,
      displayName: personName(user),
    }))
    .sort((a, b) => a.displayName.localeCompare(b.displayName, 'ru'))
)

const filterChatUser = (title, query, item) => {
  const needle = `${query || ''}`.toLowerCase().trim()
  if (!needle) {
    return true
  }
  const raw = item?.raw || {}
  const name = `${raw.last_name || ''} ${raw.name || ''} ${raw.middle_name || ''} ${title || ''}`.toLowerCase()
  const email = `${raw.email || ''}`.toLowerCase()
  return name.includes(needle) || email.includes(needle)
}

const closeNewChat = () => {
  showNewChatDialog.value = false
  newChatForm.reset()
}

// Получаем ID текущего пользователя
const currentUserId = computed(() => {
  return page.props.auth?.user?.id || null
})



// Проверяем, является ли сообщение своим
const isOwnMessage = (message) => {
  if (!message || !message.user_id) return false
  
  const userId = currentUserId.value
  if (!userId) return false
  
  // Сравниваем как числа для надежности
  const messageUserId = Number(message.user_id)
  const currentId = Number(userId)
  
  return messageUserId === currentId || message.is_from_current_user === true
}

// Вычисляемые свойства
const currentChat = computed(() => {
  return props.chats.find(chat => chat.id === selectedChatId.value)
})

// Получаем имя и фамилию собеседника
const chatPartnerName = computed(() => {
  if (!selectedChat.value) return ''
  
  // Если это приватный чат, берем первого пользователя из списка (кроме текущего)
  if (selectedChat.value.type === 'private' && selectedChat.value.users && selectedChat.value.users.length > 0) {
    const partner = selectedChat.value.users[0]
    if (partner) {
      return personName(partner) || selectedChat.value.display_name
    }
  }
  
  // Для групповых чатов показываем название чата
  return selectedChat.value.display_name
})

// Методы
const selectChat = (chatId) => {
  // Отписываемся от предыдущего канала
  if (echoChannel.value) {
    window.Echo?.leave(`chat.${selectedChatId.value}`)
    echoChannel.value = null
  }
  
  selectedChatId.value = chatId
  selectedChat.value = chats.value.find(chat => chat.id === chatId)
  loadMessages()
  
  // Подписываемся на новый канал
  subscribeToChatChannel(chatId)
  
  // Отмечаем сообщения как прочитанные
  markMessagesAsRead(chatId)
}

let messagesRequest = 0

const mapMessage = (message) => ({
  ...message,
  is_from_current_user: Number(message.user_id) === Number(currentUserId.value),
})

const mergeMessages = (incoming) => {
  const saved = incoming.map(mapMessage)
  const savedIds = new Set(saved.map((message) => message.id))
  const pending = messages.value.filter((message) => {
    if (!String(message.id).startsWith('tmp-')) return false
    return !saved.some((item) => Number(item.user_id) === Number(message.user_id) && item.message === message.message)
  })
  const known = new Set(messages.value.map((message) => message.id))
  const hasNew = saved.some((message) => !known.has(message.id))
  messages.value = [...saved, ...pending.filter((message) => !savedIds.has(message.id))]
  return hasNew
}

const loadMessages = async () => {
  if (!selectedChatId.value) return

  const chatId = selectedChatId.value
  const requestId = ++messagesRequest
  const messagesRoute = chatUrl(`/${chatId}/messages`)

  try {
    const response = await fetch(messagesRoute, {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-Spa': '1'
      }
    })
    const data = await response.json()
    if (requestId !== messagesRequest || chatId !== selectedChatId.value) return

    const hadMessages = messages.value.length > 0
    const hasNew = mergeMessages(data.data || [])
    if (!hadMessages || hasNew) {
      await nextTick()
      scrollToBottom()
    }
  } catch (error) {
    console.error('Ошибка при загрузке сообщений:', error)
  }
}

// Подписка на канал чата для real-time обновлений
const subscribeToChatChannel = (chatId) => {
  if (!window.Echo || !chatId) return
  
  try {
    echoChannel.value = window.Echo.private(`chat.${chatId}`)
    
    // Слушаем новые сообщения
    echoChannel.value.listen('.message.sent', (data) => {
      const newMsg = data.message

      // Добавляем сообщение только если его еще нет в списке
      if (!messages.value.find(m => m.id === newMsg.id)) {
        messages.value.push(mapMessage(newMsg))
        rememberLastMessage(newMsg.chat_id || chatId, newMsg)
        
        // Прокручиваем к новому сообщению
        nextTick(() => {
          scrollToBottom()
        })
      }
    })
  } catch (error) {
    console.error('Ошибка при подписке на канал чата:', error)
  }
}

// Обновление списка чатов при новом сообщении
const rememberLastMessage = (chatId, message) => {
  const chat = chats.value.find((item) => item.id === chatId)
  if (!chat || !message) return
  chat.last_message = message
  chats.value = [chat, ...chats.value.filter((item) => item.id !== chatId)]
}

// Отметить сообщения как прочитанные
const markMessagesAsRead = async (chatId) => {
  if (!chatId) return
  
  const markRoute = chatUrl(`/${chatId}/read`)
  
  try {
    await fetch(markRoute, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
  } catch (error) {
    console.error('Ошибка при отметке сообщений как прочитанных:', error)
  }
}

const sendMessage = async () => {
  const text = newMessage.value.trim()
  const chatId = selectedChatId.value
  if (!text || !chatId) return

  const tempId = `tmp-${Date.now()}`
  messages.value.push({
    id: tempId,
    chat_id: chatId,
    message: text,
    user_id: currentUserId.value,
    created_at: new Date().toISOString(),
    is_from_current_user: true,
    status: 'sent',
    user: {},
  })
  newMessage.value = ''
  await nextTick()
  scrollToBottom()

  try {
    const response = await fetch(chatUrl(`/${chatId}/messages`), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'X-Requested-With': 'XMLHttpRequest',
        'X-Spa': '1',
      },
      body: JSON.stringify({ message: text }),
    })
    const data = await response.json()
    if (!response.ok || !data.message) {
      messages.value = messages.value.filter((message) => message.id !== tempId)
      newMessage.value = text
      return
    }

    const saved = mapMessage(data.message)
    const index = messages.value.findIndex((message) => message.id === tempId)
    if (index === -1) {
      if (!messages.value.some((message) => message.id === saved.id)) messages.value.push(saved)
    } else {
      messages.value.splice(index, 1, saved)
    }
    rememberLastMessage(chatId, saved)
  } catch (error) {
    messages.value = messages.value.filter((message) => message.id !== tempId)
    newMessage.value = text
    console.error('Ошибка при отправке сообщения:', error)
  }
}

const openChatWith = async (userId) => {
  if (!userId) return

  const existingChat = chats.value.find((chat) => {
    if (chat.type === 'private' && chat.users && chat.users.length > 0) {
      return chat.users.some((user) => user.id === userId)
    }
    return false
  })

  if (existingChat) {
    closeNewChat()
    selectChat(existingChat.id)
    return
  }

  try {
    const response = await fetch(chatUrl(), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'X-Requested-With': 'XMLHttpRequest',
        'X-Spa': '1',
      },
      body: JSON.stringify({ user_id: userId, type: 'private' }),
    })
    const data = await response.json()
    if (!response.ok || !data.chat) return

    chats.value = [data.chat, ...chats.value.filter((chat) => chat.id !== data.chat.id)]
    closeNewChat()
    selectChat(data.chat.id)
  } catch (error) {
    console.error('Ошибка при создании чата:', error)
  }
}

const leaveChat = () => {
  if (!selectedChatId.value) return

  // Определяем правильный роут в зависимости от роли
  const leaveRoute = chatUrl(`/${selectedChatId.value}/leave`)

  if (confirm('Вы уверены, что хотите покинуть этот чат?')) {
    router.delete(leaveRoute, {
      onSuccess: () => {
        // Перезагружаем страницу для обновления списка
        router.reload({ only: ['chats'] })
        selectedChatId.value = null
        selectedChat.value = null
        messages.value = []
      }
    })
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
  if (!name) return '??'
  return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase().slice(0, 2)
}

// Получаем имя и фамилию собеседника для чата
const getChatPartnerName = (chat) => {
  if (!chat) return ''
  
  // Если это приватный чат, берем первого пользователя из списка
  if (chat.type === 'private' && chat.users && chat.users.length > 0) {
    const partner = chat.users[0]
    if (partner) {
      return personName(partner) || chat.display_name
    }
  }
  
  // Для групповых чатов показываем название чата
  return chat.display_name
}

let pollTimer = null

const pollMessages = () => {
  if (!selectedChatId.value || document.hidden) return
  loadMessages()
}

// Жизненный цикл
onMounted(() => {
  // Инициализируем список чатов
  chats.value = [...props.chats]
  
  // Если есть чаты, выбираем первый
  if (chats.value.length > 0) {
    selectChat(chats.value[0].id)
  }

  pollTimer = window.setInterval(pollMessages, 3000)
})

// Отписываемся при размонтировании
onUnmounted(() => {
  if (pollTimer) window.clearInterval(pollTimer)
  if (echoChannel.value && selectedChatId.value) {
    window.Echo?.leave(`chat.${selectedChatId.value}`)
  }
})
</script>

<style scoped>
.messenger-page {
  height: calc(100dvh - 96px);
  padding: 12px !important;
  box-sizing: border-box;
}

.messenger-shell {
  height: 100%;
  display: flex;
  overflow: hidden;
}

.chat-list {
  position: relative;
  display: flex;
  flex-direction: column;
  width: 340px;
  min-width: 260px;
  max-width: 40%;
  border-right: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.chat-list-scroll {
  flex: 1;
  overflow-y: auto;
  padding-bottom: 84px;
}

.chat-list-empty {
  padding: 32px 16px;
  text-align: center;
}

.chat-item-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 6px;
  margin-left: 8px;
}

.new-chat-fab {
  position: absolute;
  right: 16px;
  bottom: 16px;
  z-index: 2;
}

.chat-thread {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  background: rgb(var(--v-theme-surface));
}

.thread-header {
  display: flex;
  align-items: center;
  min-height: 64px;
  padding: 8px 12px 8px 16px;
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  background: rgba(var(--v-theme-on-surface), 0.03);
}

.thread-empty {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 24px;
}

.messages-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 16px;
}

.message-item {
  display: flex;
  width: 100%;
}

.message-item:not(.message-own) {
  justify-content: flex-start;
}

.message-item.message-own {
  justify-content: flex-end;
}

.message-content {
  max-width: min(72%, 520px);
  padding: 8px 12px 6px;
  border-radius: 16px;
  word-wrap: break-word;
}

.message-item:not(.message-own) .message-content {
  background: rgb(var(--v-theme-surface));
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 1px rgba(0, 0, 0, 0.08);
}

.message-item.message-own .message-content {
  background: #d7f0ff;
  color: #0d3550;
  border-bottom-right-radius: 4px;
}

.message-author {
  margin-bottom: 2px;
  font-size: 0.75rem;
  font-weight: 600;
  color: rgb(var(--v-theme-primary));
}

.message-text {
  line-height: 1.4;
  font-size: 0.95rem;
  white-space: pre-wrap;
}

.message-meta {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 4px;
  margin-top: 2px;
}

.message-time {
  font-size: 0.7rem;
  color: #78909c;
}

.composer {
  display: flex;
  align-items: center;
  padding: 10px 12px;
  border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

@media (max-width: 959px) {
  .messenger-shell {
    flex-direction: column;
  }

  .chat-list {
    width: 100%;
    max-width: none;
    height: 42%;
    border-right: none;
    border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  }

  .chat-thread {
    height: 58%;
  }
}
</style>