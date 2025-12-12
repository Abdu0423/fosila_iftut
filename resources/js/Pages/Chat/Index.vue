<template>
  <Layout :role="userRole">
    <v-container fluid>
      <!-- Заголовок -->
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Чаты</h1>
              <p class="text-body-1 text-medium-emphasis">Общайтесь с пользователями системы</p>
            </div>
            <v-btn
              color="primary"
              variant="elevated"
              @click="showNewChatDialog = true"
              prepend-icon="mdi-plus"
            >
              Новый чат
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Список чатов -->
      <v-row>
        <v-col cols="12" md="4">
          <v-card>
            <v-card-title class="text-h6">
              <v-icon start>mdi-chat</v-icon>
              Мои чаты
            </v-card-title>
            <v-card-text class="pa-0">
              <v-list>
                <v-list-item
                  v-for="chat in chats"
                  :key="chat.id"
                  :active="selectedChatId === chat.id"
                  @click="selectChat(chat.id)"
                  class="chat-item"
                  :class="{ 'v-list-item--active': selectedChatId === chat.id }"
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

                  <v-list-item-title class="text-subtitle-1">
                    {{ getChatPartnerName(chat) }}
                  </v-list-item-title>
                  
                  <v-list-item-subtitle class="text-body-2">
                    <div v-if="chat.last_message" class="d-flex align-center">
                      <span class="text-truncate mr-2">
                        {{ chat.last_message.user_id === currentUserId ? 'Вы' : chat.last_message.user.name }}: {{ chat.last_message.message }}
                      </span>
                      <span class="text-caption text-medium-emphasis">
                        {{ formatTime(chat.last_message.created_at) }}
                      </span>
                    </div>
                    <span v-else class="text-medium-emphasis">Нет сообщений</span>
                  </v-list-item-subtitle>

                  <template v-slot:append>
                    <v-chip
                      v-if="chat.unread_count > 0"
                      color="primary"
                      size="small"
                      class="ml-2"
                    >
                      {{ chat.unread_count }}
                    </v-chip>
                  </template>
                </v-list-item>

                <v-list-item v-if="chats.length === 0" class="text-center">
                  <v-list-item-title class="text-medium-emphasis">
                    У вас пока нет чатов
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Область чата -->
        <v-col cols="12" md="8">
          <v-card v-if="selectedChat" class="chat-container">
            <v-card-title class="d-flex align-center">
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
                  {{ getInitials(selectedChat.display_name) }}
                </span>
              </v-avatar>
              <div>
                <div class="text-h6">{{ chatPartnerName }}</div>
                <div v-if="selectedChat.type !== 'private'" class="text-caption text-medium-emphasis">
                  Групповой чат
                </div>
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
                  :class="{ 'message-own': isOwnMessage(message) }"
                >
                  <div class="message-content" :class="{ 'message-own': isOwnMessage(message) }">
                    <div class="message-header" v-if="!isOwnMessage(message)">
                      <span class="message-author">{{ message.user.name }}</span>
                      <span class="message-time">{{ formatTime(message.created_at) }}</span>
                    </div>
                    <div class="message-text">{{ message.message }}</div>
                    <div class="message-status" v-if="isOwnMessage(message)">
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

          <!-- Заглушка когда чат не выбран -->
          <v-card v-else class="chat-container">
            <v-card-text class="text-center pa-8">
              <v-icon size="64" color="grey-lighten-1">mdi-chat-outline</v-icon>
              <div class="text-h6 text-medium-emphasis mt-4">Выберите чат</div>
              <div class="text-body-2 text-medium-emphasis">
                Выберите чат из списка слева или создайте новый
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Диалог создания нового чата -->
      <v-dialog v-model="showNewChatDialog" max-width="500">
        <v-card>
          <v-card-title class="text-h6">
            <v-icon start>mdi-plus</v-icon>
            Новый чат
          </v-card-title>
          <v-card-text>
            <v-form @submit.prevent="createChat">
              <!-- Поле поиска с выпадающим списком -->
              <v-autocomplete
                v-model="newChatForm.user_id"
                :items="filteredUsers"
                v-model:search="searchUser"
                item-title="displayName"
                item-value="id"
                label="Поиск собеседника"
                placeholder="Введите имя или email..."
                variant="outlined"
                density="compact"
                prepend-inner-icon="mdi-magnify"
                clearable
                required
                auto-select-first
                :no-data-text="searchUser && filteredUsers.length === 0 ? 'Пользователи не найдены' : 'Начните вводить для поиска'"
              >
                <template v-slot:item="{ props, item }">
                  <v-list-item v-bind="props">
                    <v-list-item-title>{{ item.raw.name }} {{ item.raw.last_name }}</v-list-item-title>
                    <v-list-item-subtitle>{{ item.raw.email }}</v-list-item-subtitle>
                  </v-list-item>
                </template>
              </v-autocomplete>
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="secondary"
              @click="showNewChatDialog = false; searchUser = ''"
            >
              Отмена
            </v-btn>
            <v-btn
              color="primary"
              @click="createChat"
              :disabled="!newChatForm.user_id"
            >
              Создать чат
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import Layout from '../Layout.vue'

const page = usePage()

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
const searchUser = ref('')

// Форма для нового чата
const newChatForm = useForm({
  user_id: null
})

// Получаем ID текущего пользователя
const currentUserId = computed(() => {
  return page.props.auth?.user?.id || null
})

// Фильтруем пользователей по поисковому запросу
const filteredUsers = computed(() => {
  if (!searchUser.value || searchUser.value.trim() === '') {
    return props.users.map(user => ({
      ...user,
      displayName: `${user.name} ${user.last_name}`.trim()
    }))
  }
  
  const search = searchUser.value.toLowerCase().trim()
  return props.users
    .filter(user => {
      const fullName = `${user.name} ${user.last_name}`.toLowerCase()
      const email = (user.email).toLowerCase()
      return fullName.includes(search) || email.includes(search)
    })
    .map(user => ({
      ...user,
      displayName: `${user.name} ${user.last_name}`.trim()
    }))
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
      const parts = []
      if (partner.last_name) parts.push(partner.last_name)
      if (partner.name) parts.push(partner.name)
      return parts.join(' ') || selectedChat.value.display_name
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

const loadMessages = async () => {
  if (!selectedChatId.value) return

  // Определяем правильный роут в зависимости от роли
  const messagesRoute = userRole.value === 'admin' 
    ? `/admin/chat/${selectedChatId.value}/messages` 
    : userRole.value === 'teacher' 
    ? `/teacher/chat/${selectedChatId.value}/messages` 
    : `/student/chat/${selectedChatId.value}/messages`

  try {
    const response = await fetch(messagesRoute)
    const data = await response.json()
    // Добавляем флаг is_from_current_user для каждого сообщения
    const userId = currentUserId.value
    messages.value = (data.data || []).map(msg => ({
      ...msg,
      is_from_current_user: msg.user_id === userId
    }))
    
    // Прокручиваем к последнему сообщению
    await nextTick()
    scrollToBottom()
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
      const userId = currentUserId.value
      
      // Добавляем сообщение только если его еще нет в списке
      if (!messages.value.find(m => m.id === newMsg.id)) {
        messages.value.push({
          ...newMsg,
          is_from_current_user: newMsg.user_id === userId
        })
        
        // Обновляем список чатов
        updateChatsList(newMsg.chat_id || chatId)
        
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
const updateChatsList = (chatId) => {
  const chatIndex = chats.value.findIndex(c => c.id === chatId)
  if (chatIndex !== -1) {
    // Обновляем чат в списке
    router.reload({
      only: ['chats'],
      preserveState: true,
      onSuccess: (page) => {
        chats.value = page.props.chats || []
      }
    })
  }
}

// Отметить сообщения как прочитанные
const markMessagesAsRead = async (chatId) => {
  if (!chatId) return
  
  const markRoute = userRole.value === 'admin' 
    ? `/admin/chat/${chatId}/read` 
    : userRole.value === 'teacher' 
    ? `/teacher/chat/${chatId}/read` 
    : `/student/chat/${chatId}/read`
  
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
  if (!newMessage.value.trim() || !selectedChatId.value) return

  // Определяем правильный роут в зависимости от роли
  const sendRoute = userRole.value === 'admin' 
    ? `/admin/chat/${selectedChatId.value}/messages` 
    : userRole.value === 'teacher' 
    ? `/teacher/chat/${selectedChatId.value}/messages` 
    : `/student/chat/${selectedChatId.value}/messages`

  try {
    const response = await fetch(sendRoute, {
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
      // Добавляем флаг is_from_current_user для нового сообщения
      const userId = currentUserId.value
      const newMsg = {
        ...data.message,
        is_from_current_user: data.message.user_id === userId
      }
      messages.value.push(newMsg)
      newMessage.value = ''
      await nextTick()
      scrollToBottom()
    }
  } catch (error) {
    console.error('Ошибка при отправке сообщения:', error)
  }
}

const createChat = () => {
  if (!newChatForm.user_id) return

  // Проверяем, существует ли уже чат с этим пользователем
  const existingChat = props.chats.find(chat => {
    if (chat.type === 'private' && chat.users && chat.users.length > 0) {
      return chat.users.some(user => user.id === newChatForm.user_id)
    }
    return false
  })

  // Если чат уже существует, выбираем его вместо создания нового
  if (existingChat) {
    showNewChatDialog.value = false
    searchUser.value = ''
    newChatForm.reset()
    selectChat(existingChat.id)
    return
  }

  // Определяем правильный роут в зависимости от роли
  const chatRoute = userRole.value === 'admin' 
    ? '/admin/chat' 
    : userRole.value === 'teacher' 
    ? '/teacher/chat' 
    : '/student/chat'

  newChatForm.post(chatRoute, {
    preserveScroll: true,
    onSuccess: (page) => {
      showNewChatDialog.value = false
      searchUser.value = ''
      newChatForm.reset()
      
      // Перезагружаем страницу полностью для обновления списка чатов
      router.reload()
    },
    onError: (errors) => {
      console.error('Ошибка при создании чата:', errors)
    }
  })
}

const leaveChat = () => {
  if (!selectedChatId.value) return

  // Определяем правильный роут в зависимости от роли
  const leaveRoute = userRole.value === 'admin' 
    ? `/admin/chat/${selectedChatId.value}/leave` 
    : userRole.value === 'teacher' 
    ? `/teacher/chat/${selectedChatId.value}/leave` 
    : `/student/chat/${selectedChatId.value}/leave`

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
      const parts = []
      if (partner.last_name) parts.push(partner.last_name)
      if (partner.name) parts.push(partner.name)
      return parts.join(' ') || chat.display_name
    }
  }
  
  // Для групповых чатов показываем название чата
  return chat.display_name
}

// Жизненный цикл
onMounted(() => {
  // Инициализируем список чатов
  chats.value = [...props.chats]
  
  // Если есть чаты, выбираем первый
  if (chats.value.length > 0) {
    selectChat(chats.value[0].id)
  }
})

// Отписываемся при размонтировании
onUnmounted(() => {
  if (echoChannel.value && selectedChatId.value) {
    window.Echo?.leave(`chat.${selectedChatId.value}`)
  }
})
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
  width: 100%;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.message-item {
  margin-bottom: 0;
  width: 100%;
  display: flex;
  box-sizing: border-box;
}

/* Сообщения собеседника - слева */
.message-item:not(.message-own) {
  justify-content: flex-start;
}

.message-item:not(.message-own) .message-content {
  background-color: #F5F5F5;
  color: #212121;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  text-align: left;
}

/* Свои сообщения - справа */
.message-item.message-own {
  justify-content: flex-end;
}

.message-item.message-own .message-content {
  background-color: #E3F2FD;
  color: #1565C0;
  box-shadow: 0 2px 4px rgba(33, 150, 243, 0.2);
  text-align: left;
}

.message-content {
  max-width: 70%;
  min-width: 120px;
  padding: 12px 16px;
  border-radius: 18px;
  display: inline-block;
  box-sizing: border-box;
  word-wrap: break-word;
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
  color: #424242;
}

.message-time {
  font-size: 0.75rem;
  color: #757575;
  opacity: 0.8;
}

.message-item.message-own .message-time {
  color: #64B5F6;
}

.message-text {
  line-height: 1.5;
  font-size: 0.9375rem;
  word-wrap: break-word;
}

.message-item.message-own .message-text {
  color: #0D47A1;
  font-weight: 500;
}

.message-status {
  display: flex;
  justify-content: flex-end;
  margin-top: 4px;
}

.chat-item {
  border-bottom: 1px solid rgb(var(--v-theme-outline-variant));
}

.chat-item:last-child {
  border-bottom: none;
}
</style>