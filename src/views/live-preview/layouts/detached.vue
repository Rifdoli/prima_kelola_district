<script>
import Layout from "@/layout/main.vue"
import pageheader from "@/components/page-header.vue"

export default {
    name: "SAMPLE-PAGE",
    components: {
        Layout, pageheader
    },
    mounted() {
        document.body.classList.add("layout-modern")
    }
}
</script>

<template>
    <Layout>
        <pageheader title="Layout Detached" pageTitle="Layout" />

        <BRow>
            <BCol sm="12">
                <BCard no-body>
                    <BCardHeader>
                        <h5>Hello card</h5>
                    </BCardHeader>
                    <BCardBody>
                        <p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna
                            aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis
                            aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat
                            cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."
                        </p>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>