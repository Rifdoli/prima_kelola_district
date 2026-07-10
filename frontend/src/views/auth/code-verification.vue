<script>
export default {
    name: "CODE-VERIFICATION",
}
</script>

<template>
    <div class="auth-main v1">
        <div class="auth-wrapper">
            <div class="auth-form">
                <div class="card my-5">
                    <div class="card-body">
                        <div class="text-center">
                            <img src="@/assets/images/authentication/img-auth-code-varify.png" alt="images"
                                class="img-fluid mb-3">
                            <h4 class="f-w-500 mb-1">Please confirm with OTP</h4>
                            <p class="mb-0">We`ve send you code on jone. ****@company.com</p>
                            <p class="mb-3">Did not receive the email? <a href="#" class="link-primary ms-1">Resend code</a>
                            </p>
                        </div>
                        <BRow class="row my-4 g-3 text-center">
                            <BCol>
                                <input type="text" class="form-control text-center" placeholder="0">
                            </BCol>
                            <BCol>
                                <input type="text" class="form-control text-center" placeholder="0">
                            </BCol>
                            <BCol>
                                <input type="text" class="form-control text-center" placeholder="0">
                            </BCol>
                            <BCol>
                                <input type="text" class="form-control text-center" placeholder="0">
                            </BCol>
                        </BRow>
                        <div class="d-grid mt-4">
                            <button type="button" class="btn btn-primary">Continue</button>
                        </div>
                        <p class="text-muted text-sm text-center mt-4 mb-0">Created and curated by <span class="fw-semibold">DEFA-One Force Team</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>