<script>
import Layout from "@/layout/main.vue"
import pageheader from "@/components/page-header.vue"
import vueDropzone from 'dropzone-vue3'
import 'dropzone/dist/dropzone.css';
export default {
    name: "SETTING",
    components: {
        Layout, pageheader, vueDropzone
    },
    data() {
        return {
            dropzoneOptions: {
                url: 'https://example.com/upload', // Replace with your upload URL
                maxFilesize: 2, // Set the maximum file size in MB
                acceptedFiles: 'image/*', // Accept only images
            },
        }
    }
}
</script>
<style>
.vue-dropzone {
    border: 2px dashed #DBE0E5;
}
</style>
<template>
    <Layout>
        <pageheader title="Site Settings" pageTitle="Pages" />
        <BRow>
            <div class="col-sm-12">
                <BCard no-body>
                    <BCardHeader>
                        <h5>Site setting</h5>
                    </BCardHeader>
                    <BCardBody>
                        <div class="mb-3 row">
                            <label class="col-form-label col-sm-3 text-sm-end">Site name</label>
                            <div class="col-lg-6 col-sm-7">
                                <input type="text" class="form-control" placeholder="Enter site name">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-form-label col-sm-3 text-sm-end">Unique ID</label>
                            <div class="col-lg-6 col-sm-7">
                                <input type="text" class="form-control" placeholder="Enter unique id">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-form-label col-sm-3 text-sm-end">Site Url</label>
                            <div class="col-lg-6 col-sm-7">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Enter site url">
                                    <button class="btn btn-primary" type="button"><i class="ti ti-copy align-middle"></i>
                                        Copy</button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-form-label col-sm-3 text-sm-end">Affiliate ID</label>
                            <div class="col-lg-6 col-sm-7">
                                <input type="text" class="form-control" placeholder="Enter affiliate id">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-form-label col-sm-3 text-sm-end">Favicon
                                <span class="link-secondary" data-bs-toggle="tooltip" data-bs-html="true" data-bs-title='<span class="mb-2 d-block">Upload a 32 x 32 pixels PNG, JPG or SVG to display in the browser tabs during the website experience.</span><span class="d-block">This element is going to enable your website visitors to get an idea of the content or brand of your site</span>'>
                                    <i class="ph-duotone ph-question"></i>
                                </span>
                            </label>
                            <div class="col-lg-6 col-sm-7">
                                <div class="fallback">
                                    <vue-dropzone ref="myVueDropzone" id="dropzone" class="dropzone pb-0" :options="dropzoneOptions" />
                                </div>
                                <a href="#" class="link-primary text-sm ms-2">Need help?</a>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-form-label col-sm-3 text-sm-end">Webclip
                                <span class="link-secondary" data-bs-toggle="tooltip" data-bs-html="true" data-bs-title='<span class="mb-2 d-block">Upload a 32 x 32 pixels PNG, JPG or SVG to display in the browser tabs during the website experience.</span><span class="d-block">This element is going to enable your website visitors to get an idea of the content or brand of your site</span>'>
                                    <i class="ph-duotone ph-question"></i>
                                </span>
                            </label>
                            <div class="col-lg-6 col-sm-7">
                                <div class="fallback">
                                    <vue-dropzone ref="myVueDropzone" id="dropzone" class="dropzone pb-0" :options="dropzoneOptions" />
                                </div>
                                <a href="#" class="link-primary text-smin">Need help?</a>
                            </div>
                        </div>
                        <div class="row mb-0">
                            <span class="col-form-label col-sm-3 text-sm-end"></span>
                            <div class="col-lg-6 col-sm-7">
                                <button class="btn btn-primary me-2">Apply Change</button>
                                <button class="btn btn-outline-secondary">Cancel</button>
                            </div>
                        </div>
                    </BCardBody>
                </BCard>
            </div>
        </BRow>
    </Layout>
</template>