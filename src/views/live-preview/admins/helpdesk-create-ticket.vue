<script>
import Layout from "@/layout/main.vue"
import pageheader from "@/components/page-header.vue"
import vueDropzone from 'dropzone-vue3'
import 'dropzone/dist/dropzone.css';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
export default {
    name: "Helpdesk",
    components: {
        Layout, pageheader, vueDropzone, QuillEditor
    },
    data() {
        return {
            dropzoneOptions: {
                url: 'https://example.com/upload', // Replace with your upload URL
                maxFilesize: 2, // Set the maximum file size in MB
                acceptedFiles: 'image/*', // Accept only images
                // Add other Dropzone.js options here
            },
            content: '', // Initialize the content
            editorOptions: {
                placeholder: 'Compose an epic...',
                theme: 'snow', // You can also use 'bubble' here
                modules: {
                    toolbar: [
                        [{ header: [1, 2, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link', 'image']
                    ]
                }
            }
        };
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
        <pageheader title="Helpdesk" pageTitle="Create Ticket" />
        <!-- [ Main Content ] start -->
        <BRow>
            <BCol sm="12">
                <BCard no-body>
                    <BCardBody>
                        <div class="container">
                            <BRow>
                                <BCol sm="6">
                                    <div class="mb-3">
                                        <label class="form-label">Customer</label>
                                        <select class="mb-3 form-select">
                                            <option>Default select</option>
                                        </select>
                                    </div>
                                </BCol>
                                <BCol sm="6">
                                    <div class="mb-3">
                                        <label class="form-label">Category</label>
                                        <select class="mb-3 form-select">
                                            <option>Default select</option>
                                        </select>
                                    </div>
                                </BCol>
                            </BRow>
                            <div class="mb-3">
                                <label class="form-label" for="exampleInputPassword1">Subject</label>
                                <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Enter Subject">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="exampleInputPassword1">Description</label>
                                <QuillEditor v-model="content" :options="editorOptions" style="height: 150px"/>
                            </div>
                            <div class="fallback">
                                <vue-dropzone ref="myVueDropzone" id="dropzone" class="dropzone" :options="dropzoneOptions" />
                            </div>
                            <!-- <form action="@/assets/json/file-upload.php" >
                                    <input name="file" type="file" multiple>
                            </form> -->
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-outline-secondary">Clear</button>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>