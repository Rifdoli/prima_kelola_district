<script>
export default {
    name: "FORGOT-PASSWORD",
    data() {
        return {
            username: "",
            email: "",
        }
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
                            <img src="@/assets/images/authentication/img-auth-fporgot-password.png" alt="images"
                                class="img-fluid mb-3">
                            <h4 class="f-w-500 mb-1">Forgot Password</h4>
                            <p class="mb-3">Back to <router-link to="/login-v1" class="link-primary ms-1">Log in</router-link>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" placeholder="Username" v-model="username">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" placeholder="Email Address" v-model="email">
                        </div>
                        <div class="d-grid mt-3">
                            <button type="button" class="btn btn-primary">Send reset email</button>
                        </div>
                        <p class="text-muted text-sm text-center mt-4 mb-0">Created and curated by <span class="fw-semibold">DEFA-One Force Team</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>