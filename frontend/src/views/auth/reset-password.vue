<script>
export default {
    name: "RESET-PASSWORD",
}
</script>

<template>
    <div class="auth-main v1">
        <div class="auth-wrapper">
            <div class="auth-form">
                <div class="card my-5">
                    <div class="card-body">
                        <div class="text-center">
                            <img src="@/assets/images/authentication/img-auth-reset-password.png" alt="images"
                                class="img-fluid mb-3">
                            <h4 class="f-w-500 mb-1">Reset password</h4>
                            <p class="mb-3">Back to <router-link to="/login-v1" class="link-primary ms-1">Log in</router-link>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" id="floatingInput" placeholder="Password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="floatingInput1" placeholder="Confirm Password">
                        </div>
                        <div class="d-grid mt-4">
                            <button type="button" class="btn btn-primary">Reset Password</button>
                        </div>
                        <p class="text-muted text-sm text-center mt-4 mb-0">Created and curated by <span class="fw-semibold">DEFA-One Force Team</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>