<script>
import Layout from "@/layout/main.vue"
import pageheader from "@/components/page-header.vue"
import { iconData } from "./data"
export default {
    name: "TABLER",
    data() {
        return {
            iconData
        }
    },
    components: {
        Layout, pageheader,
    }
}
</script>

<template>
    <Layout>
        <pageheader title="Tabler Icons" pageTitle="Icons" />
        <BRow>
            <BCol class="col-sm-12">
                <BCard no-body class="card">
                    <BCardHeader class="card-header">
                        <h5>Phosphor</h5>
                        <p>Use icon with <code>&lt;i class="&lt;&lt; Copied code &gt;&gt;"&gt;</code> in you html code </p>
                    </BCardHeader>
                    <BCardBody class="text-center">
                        <BRow class="row justify-content-center">
                            <BCol class="col-sm-6">
                                <input type="text" id="icon-search" class="form-control mb-4" placeholder="search . . ">
                            </BCol>
                        </BRow>
                        <div class="i-main" id="icon-wrapper">
                            <BCol v-for="(icon, index) in iconData" :key="index" xl="3" lg="4" sm="6" class="i-block">
                                <component :is="icon.component" :size="32" />
                            </BCol>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>