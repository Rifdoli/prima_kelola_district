<script>
import FooterComponents from "@/components/footer.vue"
import Navbar from "@/components/navbar.vue"
import MenuComponents from "@/components/sidebar.vue"

export default {
    name: "VERTICAL",
    components: {
        Navbar,  FooterComponents, MenuComponents
    },
    methods: {
        handleOutsideClick(event) {
            // Only the overlay backdrop (not the actual menu content) should
            // close the mobile sidebar. Checking against the menu's real DOM
            // root avoids relying on this.$el, which is unreliable here since
            // this component renders multiple sibling root elements.
            if (
                this.$store.state.isMobileSidebarActive &&
                !event.target.closest('#navbar-wrapper')
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
   
}
</script>

<template>
    <div class="pc-sidebar" @click="handleOutsideClick"
        :class="{ 'pc-sidebar-hide': $store.state.isSidebarHidden, 'mob-sidebar-active': $store.state.isMobileSidebarActive }">
        <MenuComponents></MenuComponents>
        <div v-if="$store.state.isMobileSidebarActive" class="pc-menu-overlay"></div>
    </div>
    <div class="pc-header">
        <Navbar />
    </div>

    <div class="pc-container">
        <div class="pc-content" :class="{ 'container': isFixedWidth }">
            <!-- Start Content-->
            <div>
                                <slot />
            </div>
        </div>
    </div>
    <div class="pc-footer">
        <FooterComponents />
    </div>
</template>