import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  vus: Number(__ENV.VUS || 1),
  duration: __ENV.DURATION || '60s',

  thresholds: {
    http_req_failed: ['rate<0.05']
  }
};

export default function () {
  const baseUrl = __ENV.BASE_URL;

  const response = http.get(`${baseUrl}/`);

  check(response, {
    'homepage returns 200': (res) => res.status === 200
  });

  sleep(1);
}