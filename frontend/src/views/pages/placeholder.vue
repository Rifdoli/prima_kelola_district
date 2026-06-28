<script>
import Layout from "@/layout/main.vue"
import pageheader from "@/components/page-header.vue"

export default {
    name: "PLACEHOLDER",
    components: {
        Layout, pageheader
    },
    computed: {
        title() {
            return this.$route.meta.title || "Coming Soon"
        },
        group() {
            return this.$route.meta.group || "PRIMA"
        }
    }
}
</script>

<template>
    <Layout>
        <pageheader :title="title" :pageTitle="group" />
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                {{ title }} — halaman dalam pengembangan.
            </div>
        </div>
    </Layout>
</template>
