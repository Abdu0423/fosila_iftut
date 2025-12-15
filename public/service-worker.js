// Service Worker для кэширования ресурсов
// Фильтруем chrome-extension и другие неподдерживаемые схемы

const CACHE_NAME = 'iftut-cache-v1';
const urlsToCache = [
  '/',
  '/css/app.css',
  '/js/app.js'
];

// Установка Service Worker
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        return cache.addAll(urlsToCache.filter(url => {
          // Фильтруем только http/https запросы
          return url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/');
        }));
      })
      .catch((error) => {
        console.error('Ошибка при установке кэша:', error);
      })
  );
});

// Активация Service Worker
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Перехват запросов
self.addEventListener('fetch', (event) => {
  const request = event.request;
  const url = new URL(request.url);
  
  // Пропускаем chrome-extension, chrome, и другие неподдерживаемые схемы
  if (url.protocol === 'chrome-extension:' || 
      url.protocol === 'chrome:' || 
      url.protocol === 'moz-extension:' ||
      url.protocol === 'edge:' ||
      !url.protocol.startsWith('http')) {
    return; // Не обрабатываем эти запросы
  }
  
  // Обрабатываем только GET запросы
  if (request.method !== 'GET') {
    return;
  }
  
  event.respondWith(
    caches.match(request)
      .then((response) => {
        // Возвращаем из кэша, если есть
        if (response) {
          return response;
        }
        
        // Иначе делаем сетевой запрос
        return fetch(request).then((response) => {
          // Проверяем валидность ответа
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
          }
          
          // Клонируем ответ для кэширования
          const responseToCache = response.clone();
          
          // Кэшируем только успешные ответы
          caches.open(CACHE_NAME)
            .then((cache) => {
              // Проверяем, что URL поддерживается для кэширования
              if (url.protocol.startsWith('http')) {
                cache.put(request, responseToCache).catch((error) => {
                  console.error('Ошибка при кэшировании:', error);
                });
              }
            });
          
          return response;
        }).catch((error) => {
          console.error('Ошибка при запросе:', error);
          throw error;
        });
      })
  );
});

