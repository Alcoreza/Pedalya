import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  stages: [
    { duration: '30s', target: 1 },
    { duration: '30s', target: 5 },
    { duration: '30s', target: 10 },
    { duration: '30s', target: 0 }
  ],

  thresholds: {
    http_req_failed: ['rate<0.05'],
    http_req_duration: ['p(95)<2000']
  }
};

export default function () {
  const baseUrl = __ENV.BASE_URL;

  const responses = http.batch([
    ['GET', `${baseUrl}/`],
    ['GET', `${baseUrl}/login`],
    ['GET', `${baseUrl}/register`]
  ]);

  check(responses[0], {
    'homepage returns 200': (response) => response.status === 200
  });

  check(responses[1], {
    'login page returns 200': (response) => response.status === 200
  });

  check(responses[2], {
    'register page returns 200': (response) => response.status === 200
  });

  sleep(1);
}