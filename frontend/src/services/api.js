import axios from 'axios';

const api = axios.create({
    baseURL: process.env.VUE_APP_API_URL || 'http://localhost:8000/api',
    headers: {
        Accept: 'application/json',
    },
});

api.interceptors.request.use((config) => {
    const token = sessionStorage.getItem('authToken');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            sessionStorage.removeItem('authToken');
            sessionStorage.removeItem('authUser');

            import('../router').then(({ default: router }) => {
                if (router.currentRoute.value.name !== 'login-v1') {
                    router.push({ name: 'login-v1' });
                }
            });
        }

        return Promise.reject(error);
    }
);

export default api;
