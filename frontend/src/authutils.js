const API_URL = process.env.VUE_APP_API_URL || 'http://localhost:8000/api';

class LaravelAuthBackend {
    async _request(path, options = {}) {
        const token = this.getToken();

        const response = await fetch(`${API_URL}${path}`, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                ...(token ? { Authorization: `Bearer ${token}` } : {}),
                ...options.headers,
            },
        });

        const body = await response.json().catch(() => null);

        if (!response.ok) {
            throw this._handleError(body);
        }

        return body;
    }

    /**
     * Registers the user with given details
     */
    async registerUser(name, email, password, passwordConfirmation) {
        const { data } = await this._request('/register', {
            method: 'POST',
            body: JSON.stringify({
                name,
                email,
                password,
                password_confirmation: passwordConfirmation,
            }),
        });

        this.setLoggedInUser(data.token, data.user);
        return data.user;
    }

    /**
     * Login user with given details
     */
    async loginUser(email, password) {
        const { data } = await this._request('/login', {
            method: 'POST',
            body: JSON.stringify({ email, password }),
        });

        this.setLoggedInUser(data.token, data.user);
        return data.user;
    }

    /**
     * Logout the user
     */
    async logout() {
        try {
            await this._request('/logout', { method: 'POST' });
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
     * Handle the error
     * @param {*} body
     */
    _handleError(body) {
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
