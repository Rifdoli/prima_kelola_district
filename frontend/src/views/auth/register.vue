<script>
import { getAuthBackend } from "@/authutils"

export default {
    name: "REGISTER",
    data() {
        return {
            name: "",
            username: "",
            email: "",
            phoneNumber: "",
            password: "",
            passwordConfirmation: "",
            error: null,
            loading: false,
        }
    },
    methods: {
        async register() {
            this.error = null;
            this.loading = true;

            try {
                await getAuthBackend().registerUser(this.name, this.username, this.email, this.phoneNumber, this.password, this.passwordConfirmation);
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
                            <img src="@/assets/images/authentication/img-auth-register.png" alt="images"
                                class="img-fluid mb-3">
                            <h4 class="f-w-500 mb-1">Register User</h4>
                            <p class="mb-3">Already have an Account? <router-link to="/login-v1"
                                    class="link-primary">Log in</router-link></p>
                        </div>
                        <div class="alert alert-danger" v-if="error">{{ error }}</div>
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Full Name" v-model="name">
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Username" v-model="username">
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Email Address" v-model="email">
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Phone Number (optional)" v-model="phoneNumber">
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control" placeholder="Password" v-model="password">
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control" placeholder="Confirm Password" v-model="passwordConfirmation" @keyup.enter="register">
                        </div>
                        <div class="d-flex mt-1 justify-content-between">
                            <div class="form-check">
                                <input class="form-check-input input-primary" type="checkbox" id="customCheckc1" checked="">
                                <label class="form-check-label text-muted" for="customCheckc1">I agree to all the Terms &
                                    Condition</label>
                            </div>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="button" class="btn btn-primary" :disabled="loading" @click="register">Create Account</button>
                        </div>
                        <p class="text-muted text-sm text-center mt-4 mb-0">Created and curated by <span class="fw-semibold">DEFA-One Force Team</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>