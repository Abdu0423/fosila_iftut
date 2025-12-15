// Service Worker для кэширования ресурсов
// Фильтруем chrome-extension и другие неподдерживаемые схемы

const CACHE_NAME = 'iftut-cache-v1';
const urlsToCache = [
  '/',
  '/css/app.css',
  '/js/app.js'
];

// Функция для проверки, является ли URL валидным для кэширования
function isValidCacheUrl(url) {
  if (!url || typeof url !== 'string') {
    return false;
  }
  
  // Проверяем, что это не chrome-extension, chrome, moz-extension, edge и т.д.
  const unsupportedProtocols = [
    'chrome-extension:',
    'chrome:',
    'moz-extension:',
    'edge:',
    'safari-extension:',
    'opera-extension:'
  ];
  
  const lowerUrl = url.toLowerCase();
  for (const protocol of unsupportedProtocols) {
    if (lowerUrl.startsWith(protocol)) {
      return false;
    }
  }
  
  // Разрешаем только http://, https:// или относительные пути
  return url.startsWith('http://') || 
         url.startsWith('https://') || 
         url.startsWith('/');
}

// Установка Service Worker
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        // Фильтруем и добавляем только валидные URL
        const validUrls = urlsToCache.filter(isValidCacheUrl);
        
        if (validUrls.length === 0) {
          console.warn('Нет валидных URL для кэширования');
          return Promise.resolve();
        }
        
        // Используем Promise.allSettled для обработки ошибок отдельных URL
        return Promise.allSettled(
          validUrls.map(url => {
            try {
              return cache.add(url).catch(err => {
                console.warn(`Не удалось добавить в кэш: ${url}`, err);
                return null; // Игнорируем ошибки для отдельных URL
              });
            } catch (err) {
              console.warn(`Ошибка при обработке URL: ${url}`, err);
              return Promise.resolve(null);
            }
          })
        );
      })
      .catch((error) => {
        // Игнорируем ошибки, связанные с неподдерживаемыми схемами
        if (error.message && 
            !error.message.includes('chrome-extension') && 
            !error.message.includes('unsupported') &&
            !error.message.includes('Request scheme') &&
            !error.message.includes('Request failed')) {
          console.error('Ошибка при установке кэша:', error);
        }
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
  
  // Ранняя проверка - если request.url не валиден, сразу выходим
  if (!request || !request.url) {
    return; // Не обрабатываем запросы без URL
  }
  
  const requestUrlString = request.url;
  
  // Проверяем URL строкой перед созданием URL объекта
  if (!isValidCacheUrl(requestUrlString)) {
    return; // Не обрабатываем неподдерживаемые схемы
  }
  
  // Пытаемся создать URL объект только для валидных URL
  let url;
  try {
    url = new URL(request.url, self.location.origin);
  } catch (e) {
    // Если не удалось создать URL объект, игнорируем запрос
    return;
  }
  
  // Дополнительная проверка протокола
  if (!url.protocol.startsWith('http')) {
    return; // Не обрабатываем не-HTTP запросы
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
          
          // Финальная проверка перед кэшированием
          if (!isValidCacheUrl(request.url)) {
            return response; // Не кэшируем неподдерживаемые схемы
          }
          
          // Клонируем ответ для кэширования
          const responseToCache = response.clone();
          
          // Кэшируем только успешные ответы с правильной схемой
          caches.open(CACHE_NAME)
            .then((cache) => {
              // Последняя проверка перед cache.put
              if (isValidCacheUrl(request.url)) {
                cache.put(request, responseToCache).catch((error) => {
                  // Игнорируем ошибки кэширования для неподдерживаемых схем
                  if (error.message && 
                      !error.message.includes('chrome-extension') && 
                      !error.message.includes('unsupported') &&
                      !error.message.includes('Request scheme')) {
                    console.error('Ошибка при кэшировании:', error);
                  }
                });
              }
            })
            .catch((error) => {
              // Игнорируем ошибки открытия кэша
              if (error.message && 
                  !error.message.includes('chrome-extension') && 
                  !error.message.includes('unsupported')) {
                console.error('Ошибка при открытии кэша:', error);
              }
            });
          
          return response;
        }).catch((error) => {
          // Игнорируем ошибки для неподдерживаемых схем
          if (error.message && 
              !error.message.includes('chrome-extension') && 
              !error.message.includes('unsupported')) {
            console.error('Ошибка при запросе:', error);
          }
          // Возвращаем ошибку только для валидных запросов
          throw error;
        });
      })
      .catch((error) => {
        // Если кэш не доступен, просто делаем обычный fetch
        return fetch(request).catch((err) => {
          // Игнорируем ошибки для неподдерживаемых схем
          if (err.message && 
              !err.message.includes('chrome-extension') && 
              !err.message.includes('unsupported')) {
            console.error('Ошибка при запросе:', err);
          }
          throw err;
        });
      })
  );
});

