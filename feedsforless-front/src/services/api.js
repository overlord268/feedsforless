import axios from 'axios';
import { useAuthStore } from '../stores/auth';

// Production: use env var or infer API from frontend host (Railway)
const getBaseURL = () => {
    if (import.meta.env.VITE_API_URL) return import.meta.env.VITE_API_URL;
    const host = typeof window !== 'undefined' ? window.location.hostname : '';
    if (host === 'feedsforless-front-production.up.railway.app') {
        return 'https://feedsforless-api-production.up.railway.app';
    }
    return 'http://localhost:8000';
};

const api = axios.create({
    baseURL: getBaseURL(),
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    withCredentials: true
});

api.interceptors.request.use(config => {
    const authStore = useAuthStore();
    if (authStore.token) {
        config.headers.Authorization = `Bearer ${authStore.token}`;
    }
    return config;
});

api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            const authStore = useAuthStore();
            authStore.logout();
            window.location.href = '/';
        }
        return Promise.reject(error);
    }
);

export default api;