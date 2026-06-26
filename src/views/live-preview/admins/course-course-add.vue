<script>
import Layout from "@/layout/main.vue"
import pageheader from "@/components/page-header.vue"
import vueDropzone from 'dropzone-vue3'
import 'dropzone/dist/dropzone.css';
export default {
    name: "CREATE",
    components: {
        Layout, pageheader, vueDropzone
    },
    data(){
        return{
            dropzoneOptions: {
                url: 'https://example.com/upload', // Replace with your upload URL
                maxFilesize: 2, // Set the maximum file size in MB
                acceptedFiles: 'image/*', // Accept only images
                // Add other Dropzone.js options here
            },
        }
    }
}
</script>
<style>
.vue-dropzone{
    border: 2px dashed #DBE0E5;
}
</style>
<template>
    <Layout>
        <pageheader title="Online Courses" pageTitle="Course Add" />
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Course Add</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Course Name</label>
                                    <input type="text" class="form-control" placeholder="Enter first name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Course Code</label>
                                    <input type="text" class="form-control" placeholder="Enter Course Code">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Start Form</label>
                                    <input type="date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Course Duration</label>
                                    <input type="text" class="form-control" placeholder="Enter Course Duration">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Course Price</label>
                                    <input type="number" class="form-control" placeholder="Enter course price">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Teacher Name</label>
                                    <input type="text" class="form-control" placeholder="Enter Teacher name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Maximum Students</label>
                                    <input type="number" class="form-control" placeholder="Enter maximum students">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Course Status</label>
                                    <select class="form-select">
                                        <option>Deactive</option>
                                        <option>Active</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Course Details</label>
                                    <textarea class="form-control" rows="3" placeholder="Enter course details"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="fallback">
                                        <vue-dropzone ref="myVueDropzone" id="dropzone" class="dropzone pb-0" :options="dropzoneOptions" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 text-end">
                                <button class="btn btn-primary">Create Course</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>