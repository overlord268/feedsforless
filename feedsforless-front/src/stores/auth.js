import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem('token') || null);
    const user = ref(null);

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
        const response = await api.post('/api/v1/auth/login', credentials);
        setToken(response.data.token);
        user.value = response.data.user;
    };

    const register = async (userData) => {
        const response = await api.post('/api/v1/auth/register', userData);
        setToken(response.data.token);
        await fetchUser();
    };

    const fetchUser = async () => {
        const response = await api.get('/api/v1/auth/me');
        user.value = response.data.user;
    };

    const logout = async () => {
        try {
            await api.post('/api/v1/auth/logout');
        } catch (e) {
            // Ignore errors on logout (e.g. token already expired)
        }
        clearAuth();
    };
    if (token.value) {
        fetchUser().catch(() => {
            clearAuth();
        });
    }

    return {
        token,
        user,
        login,
        register,
        fetchUser,
        logout
    };
});