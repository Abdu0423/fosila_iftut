<template>
  <v-app>
    <v-main class="bg-grey-lighten-4">
      <v-container class="py-8">
        <!-- Кнопка назад -->
        <v-btn
          :href="route('login')"
          variant="outlined"
          color="primary"
          class="mb-6"
          prepend-icon="mdi-arrow-left"
        >
          Вернуться к входу
        </v-btn>

        <v-card class="mx-auto" max-width="1200" elevation="2">
          <v-card-title class="bg-primary pa-6">
            <div class="d-flex align-center">
              <v-icon size="40" color="white" class="mr-4">mdi-shield-lock</v-icon>
              <div>
                <h1 class="text-h4 text-white font-weight-bold">Политика конфиденциальности</h1>
                <p class="text-body-2 text-white mt-1 opacity-90">
                  Последнее обновление: {{ lastUpdate }}
                </p>
              </div>
            </div>
          </v-card-title>

          <v-card-text class="pa-8">
            <v-row>
              <v-col cols="12">
                <!-- Введение -->
                <section class="mb-8">
                  <p class="text-body-1 text-justify">
                    Институт Финансов и Технологий Университета Таджикистана (далее - "ИФТУТ", "мы", "нас" или "наш") 
                    уважает вашу конфиденциальность и обязуется защищать ваши персональные данные. Настоящая Политика 
                    конфиденциальности описывает, как мы собираем, используем и защищаем информацию, которую вы предоставляете 
                    при использовании нашей системы управления образованием.
                  </p>
                </section>

                <!-- 1. Сбор информации -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-information</v-icon>
                    1. Какую информацию мы собираем
                  </h2>
                  
                  <h3 class="text-h6 font-weight-bold mb-3">1.1. Персональные данные</h3>
                  <p class="text-body-1 mb-4">Мы можем собирать следующую информацию:</p>
                  <ul class="text-body-1 mb-4">
                    <li>Имя, фамилия, отчество</li>
                    <li>Email адрес</li>
                    <li>Номер телефона (включая контактные номера родителей для студентов)</li>
                    <li>Адрес проживания</li>
                    <li>Учебная группа и курс (для студентов)</li>
                    <li>Фотография профиля (опционально)</li>
                  </ul>

                  <h3 class="text-h6 font-weight-bold mb-3">1.2. Академическая информация</h3>
                  <ul class="text-body-1 mb-4">
                    <li>Оценки и результаты тестов</li>
                    <li>Посещаемость занятий</li>
                    <li>Выполненные задания и проекты</li>
                    <li>Расписание занятий</li>
                    <li>История обучения</li>
                  </ul>

                  <h3 class="text-h6 font-weight-bold mb-3">1.3. Техническая информация</h3>
                  <ul class="text-body-1 mb-4">
                    <li>IP-адрес</li>
                    <li>Тип браузера и устройства</li>
                    <li>Дата и время входа в систему</li>
                    <li>Логи активности в системе</li>
                  </ul>
                </section>

                <!-- 2. Использование информации -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-cog</v-icon>
                    2. Как мы используем информацию
                  </h2>
                  
                  <p class="text-body-1 mb-3">Мы используем собранную информацию для:</p>
                  <ul class="text-body-1 mb-4">
                    <li><strong>Предоставления образовательных услуг:</strong> управление учебным процессом, проведение занятий, оценка успеваемости</li>
                    <li><strong>Коммуникации:</strong> отправка уведомлений о расписании, заданиях, оценках через email и SMS</li>
                    <li><strong>Безопасности:</strong> защита системы от несанкционированного доступа, предотвращение мошенничества</li>
                    <li><strong>Улучшения сервиса:</strong> анализ использования системы для повышения качества образовательных услуг</li>
                    <li><strong>Соблюдения законодательства:</strong> выполнение требований законодательства Республики Таджикистан</li>
                  </ul>
                </section>

                <!-- 3. Защита данных -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-shield-check</v-icon>
                    3. Защита ваших данных
                  </h2>
                  
                  <p class="text-body-1 mb-3">Мы применяем следующие меры безопасности:</p>
                  <ul class="text-body-1 mb-4">
                    <li><strong>Шифрование:</strong> использование SSL/TLS для защиты передачи данных</li>
                    <li><strong>Хеширование паролей:</strong> пароли хранятся в зашифрованном виде с использованием bcrypt</li>
                    <li><strong>Контроль доступа:</strong> разделение прав доступа (администраторы, преподаватели, студенты)</li>
                    <li><strong>Регулярные резервные копии:</strong> создание бэкапов для предотвращения потери данных</li>
                    <li><strong>Мониторинг безопасности:</strong> отслеживание подозрительной активности</li>
                    <li><strong>Обязательная смена пароля:</strong> требование смены временного пароля при первом входе</li>
                  </ul>
                </section>

                <!-- 4. Передача данных третьим лицам -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-share-variant</v-icon>
                    4. Передача данных третьим лицам
                  </h2>
                  
                  <p class="text-body-1 mb-3">Мы не передаем ваши персональные данные третьим лицам, за исключением:</p>
                  <ul class="text-body-1 mb-4">
                    <li><strong>SMS-провайдер (OsonSMS):</strong> для отправки уведомлений на ваш номер телефона</li>
                    <li><strong>Государственные органы:</strong> по официальным запросам в соответствии с законодательством РТ</li>
                    <li><strong>С вашего согласия:</strong> в других случаях только с вашего явного согласия</li>
                  </ul>
                  
                  <v-alert type="info" variant="tonal" class="mt-4">
                    <strong>Важно:</strong> Мы никогда не продаем ваши персональные данные третьим лицам.
                  </v-alert>
                </section>

                <!-- 5. Ваши права -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-account-check</v-icon>
                    5. Ваши права
                  </h2>
                  
                  <p class="text-body-1 mb-3">Вы имеете право:</p>
                  <ul class="text-body-1 mb-4">
                    <li><strong>Доступ к данным:</strong> запросить информацию о том, какие данные мы храним о вас</li>
                    <li><strong>Исправление данных:</strong> обновить или исправить неточную информацию</li>
                    <li><strong>Удаление данных:</strong> запросить удаление ваших данных (с ограничениями, связанными с образовательными записями)</li>
                    <li><strong>Ограничение обработки:</strong> ограничить использование ваших данных</li>
                    <li><strong>Возражение:</strong> возразить против определенных видов обработки данных</li>
                    <li><strong>Переносимость данных:</strong> получить копию ваших данных в машиночитаемом формате</li>
                  </ul>

                  <p class="text-body-1">
                    Для реализации ваших прав, пожалуйста, свяжитесь с администрацией ИФТУТ по email: 
                    <a href="mailto:privacy@iftut.tj" class="text-primary">privacy@iftut.tj</a>
                  </p>
                </section>

                <!-- 6. Cookies -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-cookie</v-icon>
                    6. Использование Cookies
                  </h2>
                  
                  <p class="text-body-1 mb-3">
                    Наша система использует cookies для обеспечения работоспособности и улучшения пользовательского опыта:
                  </p>
                  <ul class="text-body-1 mb-4">
                    <li><strong>Необходимые cookies:</strong> для аутентификации и безопасности сессии</li>
                    <li><strong>Функциональные cookies:</strong> для сохранения ваших предпочтений (например, "Запомнить меня")</li>
                    <li><strong>Аналитические cookies:</strong> для анализа использования системы и улучшения функционала</li>
                  </ul>
                  
                  <p class="text-body-1">
                    Вы можете настроить использование cookies в настройках вашего браузера, однако это может повлиять на функциональность системы.
                  </p>
                </section>

                <!-- 7. Хранение данных -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-database</v-icon>
                    7. Срок хранения данных
                  </h2>
                  
                  <p class="text-body-1 mb-3">Мы храним ваши данные:</p>
                  <ul class="text-body-1 mb-4">
                    <li><strong>Во время обучения:</strong> все данные активно используются в образовательном процессе</li>
                    <li><strong>После окончания обучения:</strong> академические записи хранятся в соответствии с требованиями законодательства РТ (не менее 75 лет)</li>
                    <li><strong>Учетные данные:</strong> удаляются через 3 года после окончания обучения или прекращения трудового договора</li>
                    <li><strong>Логи активности:</strong> хранятся не более 2 лет</li>
                  </ul>
                </section>

                <!-- 8. Несовершеннолетние -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-account-child</v-icon>
                    8. Защита данных несовершеннолетних
                  </h2>
                  
                  <p class="text-body-1 mb-4">
                    Если студенту менее 18 лет, мы требуем согласие родителей или законных представителей на обработку 
                    персональных данных. Контактная информация родителей собирается для связи по вопросам образовательного процесса.
                  </p>
                </section>

                <!-- 9. Изменения в политике -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-update</v-icon>
                    9. Изменения в политике конфиденциальности
                  </h2>
                  
                  <p class="text-body-1 mb-4">
                    Мы можем обновлять данную Политику конфиденциальности. О существенных изменениях мы уведомим вас 
                    через систему или по email. Рекомендуем периодически проверять эту страницу на наличие обновлений.
                  </p>
                </section>

                <!-- 10. Контакты -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-email</v-icon>
                    10. Контактная информация
                  </h2>
                  
                  <v-card variant="outlined" class="pa-4">
                    <p class="text-body-1 mb-3">
                      По вопросам, касающимся настоящей Политики конфиденциальности или обработки ваших персональных данных, 
                      вы можете связаться с нами:
                    </p>
                    
                    <v-list density="compact" class="bg-transparent">
                      <v-list-item prepend-icon="mdi-domain">
                        <v-list-item-title>Институт Финансов и Технологий Университета Таджикистана (ИФТУТ)</v-list-item-title>
                      </v-list-item>
                      <v-list-item prepend-icon="mdi-email">
                        <v-list-item-title>
                          Email: <a href="mailto:privacy@iftut.tj" class="text-primary">privacy@iftut.tj</a>
                        </v-list-item-title>
                      </v-list-item>
                      <v-list-item prepend-icon="mdi-phone">
                        <v-list-item-title>Телефон: +992 (XX) XXX-XX-XX</v-list-item-title>
                      </v-list-item>
                      <v-list-item prepend-icon="mdi-map-marker">
                        <v-list-item-title>Адрес: г. Душанбе, Республика Таджикистан</v-list-item-title>
                      </v-list-item>
                    </v-list>
                  </v-card>
                </section>

                <!-- Согласие -->
                <v-alert type="success" variant="tonal" class="mt-6">
                  <strong>Согласие:</strong> Используя нашу систему, вы подтверждаете, что прочитали и согласны 
                  с настоящей Политикой конфиденциальности.
                </v-alert>
              </v-col>
            </v-row>
          </v-card-text>

          <v-divider></v-divider>

          <v-card-actions class="pa-6">
            <v-btn
              :href="route('login')"
              variant="elevated"
              color="primary"
              size="large"
              prepend-icon="mdi-arrow-left"
            >
              Вернуться к входу
            </v-btn>
            
            <v-spacer></v-spacer>
            
            <v-btn
              :href="route('terms')"
              variant="outlined"
              color="primary"
              size="large"
            >
              Условия использования
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { computed } from 'vue'

const lastUpdate = computed(() => {
  const date = new Date('2025-11-15')
  return date.toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})
</script>

<style scoped>
.text-justify {
  text-align: justify;
}

ul {
  padding-left: 24px;
}

li {
  margin-bottom: 8px;
}

a {
  text-decoration: none;
}

a:hover {
  text-decoration: underline;
}
</style>

