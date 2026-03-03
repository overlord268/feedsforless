import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem('token') || null);
    const user = ref(null);
    const router = useRouter();

    const setToken = (newToken) => {
        token.value = newToken;
        localStorage.setItem('token', newToken);
    };

    const clearAuth = () => {
        token.value = null;
        user.value = null;
        localStorage.removeItem('token');
    };

    const login = async (credentials) => {
        const response = await api.post('/auth/login', credentials);
        setToken(response.data.token);
        user.value = response.data.user;
    };

    const register = async (userData) => {
        const response = await api.post('/auth/register', userData);
        setToken(response.data.token);
        await fetchUser();
    };

    const fetchUser = async () => {
        const response = await api.get('/auth/me');
        user.value = response.data.user;
    };

    const logout = async () => {
        await api.post('/auth/logout');
        clearAuth();
        router.push({ name: 'Login' });
    };

    return {
        token,
        user,
        login,
        register,
        fetchUser,
        logout
    };
});