import { ref, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const STORAGE_KEY = 'ffl_newsletter_welcome';

function loadStored() {
    try {
        const raw = sessionStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
}

export function useNewsletterWelcome() {
    const route = useRoute();
    const router = useRouter();
    const authStore = useAuthStore();
    const welcome = ref(loadStored());

    const persist = (patch) => {
        const next = { source: 'newsletter', dismissed: false, email: '', ...welcome.value, ...patch };
        welcome.value = next;
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(next));
    };

    const captureFromRoute = () => {
        if (route.query.from !== 'newsletter') {
            return;
        }

        const email = typeof route.query.email === 'string' ? route.query.email.trim() : '';
        persist({ email, dismissed: false, source: 'newsletter' });

        const query = { ...route.query };
        delete query.from;
        delete query.email;
        router.replace({ path: route.path, query, hash: route.hash });
    };

    watch(() => route.query.from, captureFromRoute, { immediate: true });

    watch(
        () => authStore.token,
        (token) => {
            if (token && welcome.value && !welcome.value.dismissed) {
                persist({ dismissed: true });
            }
        },
    );

    const showBanner = computed(
        () => !authStore.token && welcome.value?.source === 'newsletter' && !welcome.value?.dismissed,
    );

    const welcomeEmail = computed(() => welcome.value?.email || '');

    const registerTo = computed(() => {
        const query = { from: 'newsletter' };
        if (welcomeEmail.value) {
            query.email = welcomeEmail.value;
        }
        return { name: 'Register', query };
    });

    const dismiss = () => persist({ dismissed: true });

    return { showBanner, welcomeEmail, registerTo, dismiss };
}
