<script>
import FooterComponents from "@/components/footer.vue"
import Navbar from "@/components/navbar.vue"
import MenuComponents from "@/components/sidebar.vue"
import pageheader from "@/components/page-header.vue"
export default {
    name: "Layout 2 Column",
    components: {
        Navbar, FooterComponents, MenuComponents, pageheader
    },
    methods: {
        handleOutsideClick(event) {
            if (
                this.$store.state.isMobileSidebarActive &&
                !this.$el.contains(event.target)
            ) {
                this.$store.commit('toggleMobileSidebar');
            }
        },
    },
    computed: {
        isFixedWidth() {
            return this.$store.getters.isFixedWidth;
        },
    },
    mounted() {
        document.body.classList.add('layout-nested')
    }

}
</script>

<template>
    <div class="pc-sidebar" @click="handleOutsideClick" :class="{ 'pc-sidebar-hide': $store.state.isSidebarHidden, 'mob-sidebar-active': $store.state.isMobileSidebarActive }">
        <MenuComponents></MenuComponents>
        <div v-if="$store.state.isMobileSidebarActive" class="pc-menu-overlay"></div>
    </div>
    <!-- [ Header Topbar ] start -->
    <div class="pc-header">

        <Navbar />
    </div>

    <div class="pc-container">
        <div class="pc-submenu-list-wrapper">
            <ul class="pc-submenu-list list-unstyled mb-0">
                <li><a class="active" href="#">Compact</a></li>
                <li><a href="#">Creative</a></li>
                <li><a href="#">Horizontal</a></li>
                <li><a href="#">Tab</a></li>
                <li><a href="#">Vertical</a></li>
                <li><a href="#">Layout 3</a></li>
            </ul>
        </div>
        <div class="pc-content" :class="{ 'container': isFixedWidth }">
            <pageheader title="Layout Moduler" pageTitle="Layout" />
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Hello card</h5>
                        </div>
                        <div class="card-body">
                            <p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna
                                aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis
                                aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat
                                cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Start Content-->

        </div>
    </div>

    <div class="pc-footer">
        <FooterComponents />
    </div>
</template>