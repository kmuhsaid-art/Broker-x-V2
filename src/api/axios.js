import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor - tambahkan token auth
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('broker-x-token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor - handle error global
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expired, logout user
      localStorage.removeItem('broker-x-token');
      localStorage.removeItem('broker-x-user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
