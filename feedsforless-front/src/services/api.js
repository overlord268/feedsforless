import axios from 'axios';
import { useAuthStore } from '../stores/auth';

/**
 * API base URL: works in local dev and production without extra config.
 * - VITE_API_URL (env): overrides everything (optional).
 * - npm run dev (Vite DEV): always uses local API.
 * - localhost / 127.0.0.1: local API.
 * - Known production host: uses matching API URL.
 * - Else: local API as fallback (e.g. staging or custom domain set VITE_API_URL).
 */
const getBaseURL = () => {
    const envUrl = import.meta.env.VITE_API_URL;
    if (envUrl && typeof envUrl === 'string' && envUrl.trim()) return envUrl.trim().replace(/\/+$/, '');

    if (import.meta.env.DEV) return 'http://localhost:8000';

    if (typeof window !== 'undefined') {
        const host = window.location.hostname;
        if (host === 'localhost' || host === '127.0.0.1') return 'http://localhost:8000';
        if (host === 'feedsforless-front-production-ab18.up.railway.app') return 'https://feedsforless-api-production-90a0.up.railway.app';
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