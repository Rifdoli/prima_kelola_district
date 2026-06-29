import api from '@/services/api';

class LaravelAuthBackend {
    /**
     * Registers the user with given details
     */
    async registerUser(name, email, password, passwordConfirmation) {
        try {
            const { data } = await api.post('/register', {
                name,
                email,
                password,
                password_confirmation: passwordConfirmation,
            });

            this.setLoggedInUser(data.data.token, data.data.user);
            return data.data.user;
        } catch (error) {
            throw this._handleError(error);
        }
    }

    /**
     * Login user with given details
     */
    async loginUser(email, password) {
        try {
            const { data } = await api.post('/login', { email, password });

            this.setLoggedInUser(data.data.token, data.data.user);
            return data.data.user;
        } catch (error) {
            throw this._handleError(error);
        }
    }

    /**
     * Logout the user
     */
    async logout() {
        try {
            await api.post('/logout');
        } finally {
            sessionStorage.removeItem('authUser');
            sessionStorage.removeItem('authToken');
        }
    }

    setLoggedInUser(token, user) {
        sessionStorage.setItem('authToken', token);
        sessionStorage.setItem('authUser', JSON.stringify(user));
    }

    /**
     * Returns the authenticated user
     */
    getAuthenticatedUser() {
        if (!sessionStorage.getItem('authUser'))
            return null;
        return JSON.parse(sessionStorage.getItem('authUser'));
    }

    /**
     * Returns the current auth token, if any
     */
    getToken() {
        return sessionStorage.getItem('authToken');
    }

    /**
     * Returns the sname of the logged-in user's role, or null.
     */
    getRoleSname() {
        const user = this.getAuthenticatedUser();
        return user?.role?.sname ?? null;
    }

    isSuperAdmin() {
        return this.getRoleSname() === 'admin_sup';
    }

    /**
     * Handle the error
     * @param {*} error
     */
    _handleError(error) {
        const body = error.response?.data;
        return (body && (body.message || Object.values(body.errors || {})[0]?.[0])) || 'Something went wrong.';
    }
}

let _authBackend = null;

/**
 * Initialize the backend
 */
const initAuthBackend = () => {
    if (!_authBackend) {
        _authBackend = new LaravelAuthBackend();
    }
    return _authBackend;
};

/**
 * Returns the auth backend
 */
const getAuthBackend = () => {
    return _authBackend;
};

export { initAuthBackend, getAuthBackend };
