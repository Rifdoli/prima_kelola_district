<script>
import Rightbar from "@/components/right-bar.vue"
import { getAuthBackend } from "@/authutils"

export default {
    name: "REGISTER",
    components: {
        Rightbar
    },
    data() {
        return {
            firstName: "",
            lastName: "",
            email: "",
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
                const name = `${this.firstName} ${this.lastName}`.trim();
                await getAuthBackend().registerUser(name, this.email, this.password, this.passwordConfirmation);
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
                            <h4 class="f-w-500 mb-1">Register with your email</h4>
                            <p class="mb-3">Already have an Account? <router-link to="/login-v1"
                                    class="link-primary">Log in</router-link></p>
                        </div>
                        <div class="alert alert-danger" v-if="error">{{ error }}</div>
                        <BRow class="row">
                            <BCol class="col-sm-6">
                                <div class="mb-3">
                                    <input type="text" class="form-control" placeholder="First Name" v-model="firstName">
                                </div>
                            </BCol>
                            <BCol class="col-sm-6">
                                <div class="mb-3">
                                    <input type="text" class="form-control" placeholder="Last Name" v-model="lastName">
                                </div>
                            </BCol>
                        </BRow>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Email Address" v-model="email">
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
                        <div class="saprator my-3">
                            <span>Or continue with</span>
                        </div>
                        <div class="text-center">
                            <ul class="list-inline mx-auto mt-3 mb-0">
                                <li class="list-inline-item">
                                    <a href="https://www.facebook.com/" class="avtar avtar-s rounded-circle bg-facebook"
                                        target="_blank">
                                        <i class="fab fa-facebook-f text-white"></i>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="https://twitter.com/" class="avtar avtar-s rounded-circle bg-twitter"
                                        target="_blank">
                                        <i class="fab fa-twitter text-white"></i>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="https://myaccount.google.com/"
                                        class="avtar avtar-s rounded-circle bg-googleplus" target="_blank">
                                        <i class="fab fa-google text-white"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-sidefooter">
                <img src="@/assets/images/logo-dark.svg" class="img-brand img-fluid" alt="images">
                <hr class="mb-3 mt-4">
                <BRow class="row">
                    <BCol class="col my-1">
                        <p class="m-0">Light Able ♥ crafted by Team <a href="#" target="_blank">
                                themes</a></p>
                    </BCol>
                    <BCol class="col-auto my-1">
                        <ul class="list-inline footer-link mb-0">
                            <li class="list-inline-item"><router-link to="/dashboard">Home</router-link></li>
                            <li class="list-inline-item"><a href="#"
                                    target="_blank">Documentation</a></li>
                            <li class="list-inline-item"><a href="#"
                                    target="_blank">Support</a></li>
                        </ul>
                    </BCol>
                </BRow>
            </div>
        </div>
    </div>
    <Rightbar />
</template>