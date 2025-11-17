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
              <v-icon size="40" color="white" class="mr-4">mdi-file-document</v-icon>
              <div>
                <h1 class="text-h4 text-white font-weight-bold">Условия использования</h1>
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
                  <p class="text-body-1 text-justify mb-4">
                    Добро пожаловать в систему управления образованием Института Финансов и Технологий Университета 
                    Таджикистана (далее - "Система", "ИФТУТ", "мы" или "наш"). Используя данную Систему, вы соглашаетесь 
                    соблюдать и быть связанными настоящими Условиями использования.
                  </p>
                  
                  <v-alert type="warning" variant="tonal">
                    <strong>Важно:</strong> Пожалуйста, внимательно прочитайте настоящие Условия. Если вы не согласны 
                    с какими-либо положениями, не используйте Систему.
                  </v-alert>
                </section>

                <!-- 1. Принятие условий -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-handshake</v-icon>
                    1. Принятие условий
                  </h2>
                  
                  <p class="text-body-1 mb-3">
                    Используя Систему, вы подтверждаете, что:
                  </p>
                  <ul class="text-body-1 mb-4">
                    <li>Вы прочитали и поняли настоящие Условия использования</li>
                    <li>Вы согласны соблюдать все положения настоящих Условий</li>
                    <li>Вы являетесь студентом, преподавателем или сотрудником ИФТУТ</li>
                    <li>Вы обладаете правоспособностью для принятия обязательств (если вам менее 18 лет - с согласия родителей)</li>
                  </ul>
                </section>

                <!-- 2. Описание сервиса -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-information-outline</v-icon>
                    2. Описание сервиса
                  </h2>
                  
                  <p class="text-body-1 mb-3">Система предоставляет следующие функции:</p>
                  <ul class="text-body-1 mb-4">
                    <li><strong>Для студентов:</strong> доступ к расписанию, заданиям, оценкам, учебным материалам, тестам, чату с преподавателями</li>
                    <li><strong>Для преподавателей:</strong> управление расписанием, создание заданий и тестов, выставление оценок, общение со студентами</li>
                    <li><strong>Для администраторов:</strong> управление пользователями, предметами, группами, системными настройками</li>
                  </ul>
                </section>

                <!-- 3. Регистрация и учетные записи -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-account-key</v-icon>
                    3. Учетные записи пользователей
                  </h2>
                  
                  <h3 class="text-h6 font-weight-bold mb-3">3.1. Создание учетной записи</h3>
                  <ul class="text-body-1 mb-4">
                    <li>Учетные записи создаются администраторами ИФТУТ</li>
                    <li>Вы получите временный пароль, который должны изменить при первом входе</li>
                    <li>Предоставленная вами информация должна быть точной и актуальной</li>
                  </ul>

                  <h3 class="text-h6 font-weight-bold mb-3">3.2. Безопасность учетной записи</h3>
                  <p class="text-body-1 mb-2">Вы обязуетесь:</p>
                  <ul class="text-body-1 mb-4">
                    <li>Сохранять конфиденциальность своего пароля</li>
                    <li>Не передавать доступ к учетной записи третьим лицам</li>
                    <li>Немедленно уведомить администрацию о любом несанкционированном доступе</li>
                    <li>Использовать надежный пароль (минимум 8 символов, включая буквы и цифры)</li>
                    <li>Регулярно менять пароль (рекомендуется каждые 90 дней)</li>
                  </ul>

                  <v-alert type="error" variant="tonal">
                    <strong>Предупреждение:</strong> Вы несете полную ответственность за все действия, совершенные 
                    с использованием вашей учетной записи.
                  </v-alert>
                </section>

                <!-- 4. Правила использования -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-shield-check</v-icon>
                    4. Правила использования системы
                  </h2>
                  
                  <h3 class="text-h6 font-weight-bold mb-3">4.1. Разрешенное использование</h3>
                  <p class="text-body-1 mb-4">
                    Система предназначена исключительно для образовательных целей в рамках учебного процесса ИФТУТ.
                  </p>

                  <h3 class="text-h6 font-weight-bold mb-3">4.2. Запрещенные действия</h3>
                  <p class="text-body-1 mb-2">Вам запрещается:</p>
                  <ul class="text-body-1 mb-4">
                    <li>Использовать Систему для незаконных целей</li>
                    <li>Пытаться получить несанкционированный доступ к Системе или данным других пользователей</li>
                    <li>Распространять вирусы, вредоносное ПО или другой вредоносный код</li>
                    <li>Проводить атаки типа "отказ в обслуживании" (DDoS)</li>
                    <li>Копировать, изменять или распространять материалы Системы без разрешения</li>
                    <li>Использовать автоматизированные средства для доступа к Системе (боты, скрипты)</li>
                    <li>Выдавать себя за другого пользователя</li>
                    <li>Размещать оскорбительный, дискриминационный или неприемлемый контент</li>
                    <li>Нарушать академическую честность (списывание, плагиат)</li>
                    <li>Продавать или передавать свою учетную запись третьим лицам</li>
                  </ul>
                </section>

                <!-- 5. Интеллектуальная собственность -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-copyright</v-icon>
                    5. Интеллектуальная собственность
                  </h2>
                  
                  <p class="text-body-1 mb-3">
                    Все материалы, размещенные в Системе (тексты, изображения, видео, программный код, дизайн), 
                    являются интеллектуальной собственностью ИФТУТ или их законных правообладателей и защищены 
                    законодательством Республики Таджикистан об авторском праве.
                  </p>
                  
                  <ul class="text-body-1 mb-4">
                    <li>Вы можете просматривать и скачивать материалы только для личного образовательного использования</li>
                    <li>Запрещается коммерческое использование материалов без письменного разрешения</li>
                    <li>При цитировании материалов необходимо указывать источник</li>
                  </ul>
                </section>

                <!-- 6. Контент пользователей -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-file-upload</v-icon>
                    6. Контент, размещаемый пользователями
                  </h2>
                  
                  <p class="text-body-1 mb-3">
                    Размещая контент в Системе (задания, комментарии, файлы), вы:
                  </p>
                  <ul class="text-body-1 mb-4">
                    <li>Подтверждаете, что обладаете правами на размещаемый контент</li>
                    <li>Предоставляете ИФТУТ неисключительную лицензию на использование контента в образовательных целях</li>
                    <li>Гарантируете, что контент не нарушает права третьих лиц</li>
                    <li>Несете ответственность за размещаемый контент</li>
                  </ul>

                  <p class="text-body-1">
                    ИФТУТ оставляет за собой право удалять контент, нарушающий настоящие Условия или законодательство.
                  </p>
                </section>

                <!-- 7. Академическая честность -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-school</v-icon>
                    7. Академическая честность
                  </h2>
                  
                  <p class="text-body-1 mb-3">Все пользователи обязаны соблюдать принципы академической честности:</p>
                  <ul class="text-body-1 mb-4">
                    <li>Самостоятельное выполнение заданий и тестов</li>
                    <li>Корректное цитирование источников</li>
                    <li>Запрет на списывание и использование чужих работ</li>
                    <li>Честное сотрудничество в групповых проектах</li>
                  </ul>

                  <v-alert type="error" variant="tonal">
                    <strong>Нарушение академической честности</strong> может привести к дисциплинарным мерам, 
                    включая отчисление из учебного заведения.
                  </v-alert>
                </section>

                <!-- 8. Уведомления -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-bell</v-icon>
                    8. Уведомления
                  </h2>
                  
                  <p class="text-body-1 mb-4">
                    Используя Систему, вы соглашаетесь получать уведомления через email и SMS о:
                  </p>
                  <ul class="text-body-1 mb-4">
                    <li>Новых заданиях и тестах</li>
                    <li>Изменениях в расписании</li>
                    <li>Выставленных оценках</li>
                    <li>Важных объявлениях администрации</li>
                    <li>Изменениях в Условиях использования</li>
                  </ul>
                  
                  <p class="text-body-1">
                    Вы можете управлять настройками уведомлений в личном кабинете, однако критически важные 
                    уведомления будут отправляться независимо от ваших настроек.
                  </p>
                </section>

                <!-- 9. Ограничение ответственности -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-alert</v-icon>
                    9. Ограничение ответственности
                  </h2>
                  
                  <p class="text-body-1 mb-3">
                    Система предоставляется "как есть". ИФТУТ не несет ответственности за:
                  </p>
                  <ul class="text-body-1 mb-4">
                    <li>Перерывы в работе Системы, вызванные техническими сбоями</li>
                    <li>Потерю данных в результате форс-мажорных обстоятельств</li>
                    <li>Ошибки или неточности в контенте</li>
                    <li>Действия третьих лиц (хакерские атаки, DDoS)</li>
                    <li>Косвенные убытки, связанные с использованием Системы</li>
                  </ul>

                  <p class="text-body-1 mb-4">
                    Мы прилагаем все усилия для обеспечения стабильной работы Системы, но не можем гарантировать 
                    ее бесперебойную работу 24/7.
                  </p>

                  <v-alert type="info" variant="tonal">
                    <strong>Важно:</strong> Рекомендуем регулярно создавать резервные копии важных материалов.
                  </v-alert>
                </section>

                <!-- 10. Приостановка и прекращение доступа -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-cancel</v-icon>
                    10. Приостановка и прекращение доступа
                  </h2>
                  
                  <p class="text-body-1 mb-3">
                    ИФТУТ оставляет за собой право приостановить или прекратить ваш доступ к Системе:
                  </p>
                  <ul class="text-body-1 mb-4">
                    <li>При нарушении настоящих Условий использования</li>
                    <li>При попытках несанкционированного доступа</li>
                    <li>При размещении неприемлемого контента</li>
                    <li>По требованию государственных органов</li>
                    <li>После окончания обучения или увольнения</li>
                  </ul>

                  <p class="text-body-1">
                    При прекращении доступа ваши образовательные записи будут сохранены в соответствии с 
                    требованиями законодательства.
                  </p>
                </section>

                <!-- 11. Техническая поддержка -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-lifebuoy</v-icon>
                    11. Техническая поддержка
                  </h2>
                  
                  <p class="text-body-1 mb-4">
                    При возникновении технических проблем или вопросов вы можете обратиться в службу поддержки:
                  </p>
                  
                  <v-card variant="outlined" class="pa-4">
                    <v-list density="compact" class="bg-transparent">
                      <v-list-item prepend-icon="mdi-email">
                        <v-list-item-title>
                          Email: <a href="mailto:support@iftut.tj" class="text-primary">support@iftut.tj</a>
                        </v-list-item-title>
                      </v-list-item>
                      <v-list-item prepend-icon="mdi-phone">
                        <v-list-item-title>Телефон: +992 (XX) XXX-XX-XX</v-list-item-title>
                      </v-list-item>
                      <v-list-item prepend-icon="mdi-clock">
                        <v-list-item-title>Время работы: Пн-Пт, 9:00-18:00</v-list-item-title>
                      </v-list-item>
                    </v-list>
                  </v-card>
                </section>

                <!-- 12. Изменения условий -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-update</v-icon>
                    12. Изменения в условиях использования
                  </h2>
                  
                  <p class="text-body-1 mb-4">
                    ИФТУТ оставляет за собой право изменять настоящие Условия в любое время. О существенных изменениях 
                    мы уведомим вас через Систему или по email за 30 дней до вступления изменений в силу. Продолжение 
                    использования Системы после внесения изменений означает ваше согласие с обновленными Условиями.
                  </p>
                </section>

                <!-- 13. Применимое право -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-gavel</v-icon>
                    13. Применимое право и разрешение споров
                  </h2>
                  
                  <p class="text-body-1 mb-4">
                    Настоящие Условия регулируются законодательством Республики Таджикистан. Все споры, возникающие 
                    из настоящих Условий или в связи с ними, подлежат разрешению в соответствии с законодательством РТ.
                  </p>
                </section>

                <!-- 14. Контактная информация -->
                <section class="mb-8">
                  <h2 class="text-h5 font-weight-bold text-primary mb-4">
                    <v-icon color="primary" class="mr-2">mdi-email</v-icon>
                    14. Контактная информация
                  </h2>
                  
                  <v-card variant="outlined" class="pa-4">
                    <p class="text-body-1 mb-3">
                      По вопросам, касающимся настоящих Условий использования:
                    </p>
                    
                    <v-list density="compact" class="bg-transparent">
                      <v-list-item prepend-icon="mdi-domain">
                        <v-list-item-title>Институт Финансов и Технологий Университета Таджикистана (ИФТУТ)</v-list-item-title>
                      </v-list-item>
                      <v-list-item prepend-icon="mdi-email">
                        <v-list-item-title>
                          Email: <a href="mailto:legal@iftut.tj" class="text-primary">legal@iftut.tj</a>
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
                  <strong>Соглашение:</strong> Входя в Систему, вы подтверждаете, что прочитали, поняли и согласны 
                  соблюдать настоящие Условия использования, а также Политику конфиденциальности ИФТУТ.
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
              :href="route('privacy')"
              variant="outlined"
              color="primary"
              size="large"
            >
              Политика конфиденциальности
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

