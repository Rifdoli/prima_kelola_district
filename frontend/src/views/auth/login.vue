<script>
import { getAuthBackend } from "@/authutils"

export default {
    name: "LOGIN",
    data() {
        return {
            username: "",
            password: "",
            error: null,
            loading: false,
        }
    },
    methods: {
        async login() {
            this.error = null;
            this.loading = true;

            try {
                await getAuthBackend().loginUser(this.username, this.password);
                this.$router.push({ name: "dashboard" });
            } catch (error) {
                this.error = error;
            } finally {
                this.loading = false;
            }
        },
    },
}
</script>

<template>
    <div class="auth-main v1">
        <div class="auth-wrapper">
            <div class="auth-form">
                <div class="card my-5">
                    <div class="card-body">
                        <div class="text-center">
                            <img src="@/assets/images/authentication/img-auth-login.png" alt="images"
                                class="img-fluid mb-3">
                            <p class="mb-3">Don't have an Account? <router-link to="/register-v1"
                                    class="link-primary ms-1">Create Account</router-link></p>
                        </div>
                        <div class="alert alert-danger" v-if="error">{{ error }}</div>
                        <div class="mb-3">
                            <input type="text" class="form-control" id="floatingInput" placeholder="Username" v-model="username">
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control" id="floatingInput1" placeholder="Password" v-model="password" @keyup.enter="login">
                        </div>
                        <div class="d-flex mt-1 justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input input-primary" type="checkbox" id="customCheckc1" checked="">
                                <label class="form-check-label text-muted" for="customCheckc1">Remember me?</label>
                            </div>
                            <router-link to="/forgot-password-v1">
                                <h6 class="f-w-400 mb-0">Forgot Password?</h6>
                            </router-link>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="button" class="btn btn-primary" :disabled="loading" @click="login">Login</button>
                        </div>
                        <p class="text-muted text-sm text-center mt-4 mb-0">Created and curated by <span class="fw-semibold">DEFA-One Force Team</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
