<script>
import simplebar from "simplebar-vue";
import { getAuthBackend } from "@/authutils";

export default {
    name: "NAVBAR",
    components: {
        simplebar,
    },
    data() {
        return {
            currentMode: 'light',
        };
    },
    computed: {
        currentUser() {
            return getAuthBackend().getAuthenticatedUser();
        },
    },
    mounted() {
        document.addEventListener('keydown', this.handleKeyDown);
    },
    beforeUnmount() {
        // Remove event listener when component is unmounted
        document.removeEventListener('keydown', this.handleKeyDown);
    },
    methods: {
        async logout() {
            try {
                await getAuthBackend().logout();
            } finally {
                this.$router.push({ name: "login-v1" });
            }
        },
        handleSearch(event) {
            event.preventDefault();
        },
        handleKeyDown(event) {
            if (event.ctrlKey && event.key === 'k') {
                event.preventDefault();
                document.querySelector('#serchFildid')?.focus();
            }
        },
        changeMode(mode) {
            this.currentMode = mode;
            if (mode === "dark") {
                document.body.setAttribute("data-pc-theme", "dark");
                document.body.setAttribute("data-topbar", "dark");
                document.body.classList.remove("mode-auto");
            } else if (mode === "auto") {
                document.body.setAttribute("data-pc-theme", "light");
                document.body.setAttribute("data-topbar", "light");
                document.body.classList.add("mode-auto");
            } else {
                document.body.setAttribute("data-pc-theme", "light");
                document.body.setAttribute("data-topbar", "light");
                document.body.classList.remove("mode-auto");
            }
        },
        toggleSidebar() {
            this.$store.commit('toggleSidebar');
        },
        toggleMobileSidebar() {
            this.$store.commit('toggleMobileSidebar');
        },
    },
};
</script>

<template>
    <div class="header-wrapper"> <!-- [Mobile Media Block] start -->
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <!-- ======= Menu collapse Icon ===== -->
                <li class="pc-h-item pc-sidebar-collapse">
                    <a href="#" class="pc-head-link ms-0" id="sidebar-hide" @click="toggleSidebar">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link ms-0" id="mobile-collapse" @click="toggleMobileSidebar">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="dropdown pc-h-item d-inline-flex d-md-none">
                    <a class="pc-head-link dropdown-toggle arrow-none m-0" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <i class="ph-duotone ph-magnifying-glass"></i>
                    </a>
                    <div class="dropdown-menu pc-h-dropdown drp-search">
                        <form class="px-3">
                            <div class="form-group mb-0 d-flex align-items-center">
                                <input type="search" class="form-control border-0 shadow-none"
                                    placeholder="Search here. . ." />
                                <button class="btn btn-light-secondary btn-search"><kbd>ctrl+k</kbd></button>
                            </div>
                        </form>
                    </div>
                </li>

                <li class="pc-h-item d-none d-md-inline-flex">
                    <form class="form-search">
                        <i class="ph-duotone ph-magnifying-glass icon-search"></i>
                        <input type="search" ref="searchInput" class="form-control" placeholder="Search. . ."
                            id="serchFildid">
                        <button class="btn btn-light-secondary btn-search" style="padding: 0"
                            @click.prevent="handleSearch"><kbd>ctrl+k</kbd></button>
                    </form>
                </li>
            </ul>
        </div>
        <div class="ms-auto">
            <ul class="list-unstyled">
                <BDropdown variant="transparent" class="pc-h-item d-none d-md-inline-flex"
                    toggle-class="text-reset dropdown-btn pc-head-link arrow-none me-0" menu-class="dropdown-menu-end"
                    aria-haspopup="true" :offset="{ alignmentAxis: -150, crossAxis: 0, mainAxis: 20 }">
                    <template #button-content><span class="text-muted pc-head-link"><i
                                :class="currentMode === 'dark' ? 'ph-duotone ph-moon' : (currentMode === 'light' ? 'ph-duotone ph-sun-dim' : 'ph-duotone ph-cpu')"></i></span>
                    </template>
                    <a href="#!" class="dropdown-item" @click="changeMode('dark')">
                        <i class="ph-duotone ph-moon"></i>
                        <span>Dark</span>
                    </a>
                    <a href="#!" class="dropdown-item" @click="changeMode('light')">
                        <i class="ph-duotone ph-sun-dim"></i>
                        <span>Light</span>
                    </a>
                    <a href="#!" class="dropdown-item" @click="changeMode('auto')">
                        <i class="ph-duotone ph-cpu"></i>
                        <span>Default</span>
                    </a>
                </BDropdown>
                <BDropdown variant="transparent" auto-close="outside" class="pc-h-item card-header-dropdown pb-0"
                    toggle-class="text-reset dropdown-btn pc-head-link arrow-none me-0" menu-class="dropdown-menu-end"
                    aria-haspopup="true" :offset="{ alignmentAxis: -140, crossAxis: 0, mainAxis: 20 }">
                    <template #button-content><span class="text-muted pc-head-link"><i
                                class="ph-duotone ph-diamonds-four"></i></span>
                    </template>
                    <a href="#!" class="dropdown-item">
                        <i class="ph-duotone ph-user"></i>
                        <span>My Account</span>
                    </a>
                    <a href="#!" class="dropdown-item">
                        <i class="ph-duotone ph-gear"></i>
                        <span>Settings</span>
                    </a>
                    <a href="#!" class="dropdown-item" @click.prevent="logout">
                        <i class="ph-duotone ph-power"></i>
                        <span>Logout</span>
                    </a>
                </BDropdown>
                <BDropdown variant="transparent" auto-close="outside"
                    class="pc-h-item header-user-profile card-header-dropdown py-0"
                    toggle-class="text-reset dropdown-btn pc-head-link arrow-none me-0"
                    menu-class="dropdown-menu-end dropdown-user-profile dropdown-menu-end pc-h-dropdown"
                    aria-haspopup="true" :offset="{ alignmentAxis: -145, crossAxis: 0, mainAxis: 20 }">
                    <template #button-content><span class="text-muted"> <img src="@/assets/images/user/avatar-2.jpg"
                                alt="user-image" class="user-avtar">
                        </span>
                    </template>
                    <div class="dropdown-header d-flex align-items-center justify-content-between">
                        <h4 class="m-0">Profile</h4>
                    </div>
                    <div class="dropdown-body">
                        <simplebar data-simplebar class="profile-notification-scroll position-relative"
                            style="max-height: calc(100vh - 235px)">
                            <ul class="list-group list-group-flush w-100">
                                <li class="list-group-item" v-if="currentUser">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <img src="@/assets/images/user/avatar-2.jpg" alt="user-image"
                                                class="wid-50 rounded-circle">
                                        </div>
                                        <div class="flex-grow-1 mx-3">
                                            <h5 class="mb-0">{{ currentUser.name }}</h5>
                                            <p class="mb-0 text-muted text-sm">{{ currentUser.role?.name }}</p>
                                            <p class="mb-0 text-muted text-sm" v-if="currentUser.organization">{{ currentUser.organization.name }}</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <a href="#" class="dropdown-item">
                                        <span class="d-flex align-items-center">
                                            <i class="ph-duotone ph-user-circle"></i>
                                            <span>My Account</span>
                                        </span>
                                    </a>
                                    <a href="#" class="dropdown-item">
                                        <span class="d-flex align-items-center">
                                            <i class="ph-duotone ph-gear-six"></i>
                                            <span>Settings</span>
                                        </span>
                                    </a>
                                    <a href="#" class="dropdown-item" @click.prevent="logout">
                                        <span class="d-flex align-items-center">
                                            <i class="ph-duotone ph-power"></i>
                                            <span>Logout</span>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </simplebar>
                    </div>
                </BDropdown>
            </ul>
        </div>
    </div>
</template>