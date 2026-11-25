import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    stages: [
        { duration: '30s', target: 50 }, // Плавно поднимаем нагрузку до 50 пользователей
        { duration: '1m', target: 50 },  // Держим нагрузку 1 минуту
        { duration: '30s', target: 0 },  // Плавно снижаем до 0
    ],
    thresholds: {
        http_req_duration: ['p(95)<300'], // 95% запросов должны быть быстрее 300 мс
        http_req_failed: ['rate<0.01'],   // Доля ошибок должна быть меньше 1%
    },
};

export default function () {
    // Запрос к каталогу (главной странице)
    const res = http.get('http://localhost/');
    
    check(res, {
        'status is 200': (r) => r.status === 200,
    });
    
    sleep(1); // Пауза между запросами
}