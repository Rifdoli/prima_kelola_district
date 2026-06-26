<script>
import FooterComponents from "@/components/footer.vue"
import Navbar from "@/components/navbar.vue"
import MenuComponents from "@/components/sidebar.vue"
import pageheader from "@/components/page-header.vue"
export default {
    name: "VERTICAL",
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
        document.body.classList.add('layout-moduler')
    }

}
</script>

<template>
    <div class="pc-sidebar" @click="handleOutsideClick" :class="{ 'pc-sidebar-hide': $store.state.isSidebarHidden, 'mob-sidebar-active': $store.state.isMobileSidebarActive }">
        <div class="navbar-wrapper">
            <div class="m-header">
                <router-link to="#" class="b-brand text-primary">
                    <!-- ========   Change your logo from here   ============ -->
                    <img src="@/assets/images/logo-dark.svg" alt="logo image" class="logo-lg" />
                    <span class="badge bg-brand-color-2 rounded-pill ms-2 theme-version">1.0</span>

                    <BDropdown variant="link-secondary" class="dropdown ms-auto" no-caret>
                        <template #button-content>
                            <i class="ph-duotone ph-caret-circle-down f-20"></i>
                        </template>
                        <BDropdownItem href="#!">
                            <template #default>
                                <i class="ph-duotone ph-caret-circle-down f-20"></i>
                                <span>E-commerce</span>
                            </template>
                        </BDropdownItem>
                        <BDropdownItem href="#!">
                            <template #default>
                                <i class="ph-duotone ph-lifebuoy"></i>
                                <span>Helpdesk</span>
                            </template>
                        </BDropdownItem>
                        <BDropdownItem href="#!">
                            <template #default>
                                <i class="ph-duotone ph-scroll"></i>
                                <span>Invoice</span>
                            </template>
                        </BDropdownItem>
                        <BDropdownItem href="#!">
                            <template #default>
                                <i class="ph-duotone ph-books"></i>
                                <span>Online Courses</span>
                            </template>
                        </BDropdownItem>
                    </BDropdown>

                </router-link>
            </div>
            <div class="navbar-content">
                <div class="card mt-0 pb-2">
                    <form class="form-search">
                        <i class="ph-duotone ph-magnifying-glass icon-search"></i>
                        <input type="search" class="form-control" placeholder="Search something">
                    </form>
                </div>
                <hr>
                <div class="card mb-0">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 me-2">
                            <h6 class="mb-0">Module</h6>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="#" class="avtar avtar-s btn-link-primary">
                                <i class="ph-duotone ph-plus-circle f-20"></i>
                            </a>
                            <div class="avtar avtar-s btn-link-secondary">
                                <i class="ph-duotone ph-caret-circle-right f-20"></i>
                            </div>
                        </div>
                    </div>
                </div>
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
                <ul class="pc-navbar">
                    <MenuComponents></MenuComponents>
                </ul>
                <div class="card nav-action-card bg-brand-color-4">
                    <div class="card-body" :style="{ 'background-image': 'url(' + require('@/assets/images/layout/nav-card-bg.svg') + ')' }">
                        <h5 class="text-dark">Help Center</h5>
                        <p class="text-dark text-opacity-75">Please contact us for more questions.</p>
                        <a href="https://phoenixcoded.support-hub.io/" class="btn btn-primary" target="_blank">Go to help Center</a>
                    </div>
                </div>
            </div>
            <div class="card pc-user-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <img src="@/assets/images/user/avatar-1.jpg" alt="user-image" class="user-avtar wid-45 rounded-circle" />
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="dropdown">
                                <a href="#" class="arrow-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" data-bs-offset="0,20">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 me-2">
                                            <h6 class="mb-0">Jonh Smith</h6>
                                            <small class="text-body">Administrator</small>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <div class="btn btn-icon btn-link-secondary avtar">
                                                <i class="ph-duotone ph-windows-logo"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <div class="dropdown-menu">
                                    <ul>
                                        <li><a class="pc-user-links">
                                                <i class="ph-duotone ph-user"></i>
                                                <span>My Account</span>
                                            </a></li>
                                        <li><a class="pc-user-links">
                                                <i class="ph-duotone ph-gear"></i>
                                                <span>Settings</span>
                                            </a></li>
                                        <li><a class="pc-user-links">
                                                <i class="ph-duotone ph-lock-key"></i>
                                                <span>Lock Screen</span>
                                            </a></li>
                                        <li><a class="pc-user-links">
                                                <i class="ph-duotone ph-power"></i>
                                                <span>Logout</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div v-if="$store.state.isMobileSidebarActive" class="pc-menu-overlay"></div>
    </div>
    <div class="pc-header" >
        <Navbar />
    </div>

    <div class="pc-container">
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