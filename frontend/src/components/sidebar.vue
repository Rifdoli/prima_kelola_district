<script>
import { ref, onMounted, onUnmounted } from 'vue';
import { ChevronDownIcon } from "@zhuowenli/vue-feather-icons";
import simplebar from "simplebar-vue";
import logoWhite from "@/assets/images/logo-white.svg";
import logoDark from "@/assets/images/logo-dark.svg";
import { getAuthBackend } from "@/authutils";

export default {
    data() {
        return {
            logoDark: logoDark,
            logoWhite: logoWhite,
           }
    },
    setup() {
        const currentLogo = ref(logoDark);

        const updateLogo = () => {
            const isDarkTheme = document.body.getAttribute("data-pc-theme") === "dark";
            currentLogo.value = isDarkTheme ? logoWhite : logoDark;
        };
        

        onMounted(() => {
            updateLogo();

            const observer = new MutationObserver(() => {
                updateLogo();
            });

            observer.observe(document.body, {
                attributes: true,
                attributeFilter: ['data-pc-theme']
            });

            onUnmounted(() => {
                observer.disconnect();
            });
        });

        return { currentLogo };
    },
    components: {
        ChevronDownIcon, simplebar
    },
    methods: {
        changeLayoutType(layoutType) {
            // Update the layout type in the store
            this.$store.commit('changeLayoutType', { layoutType });
            // Set the layout attribute based on the layout type
            document.body.setAttribute('data-pc-layout', layoutType);
        },
    },
    computed: {
        layoutType: {
            get() {
                return this.$store.state.layout.layoutType;
            },
            set(layoutType) {
                this.$store.commit('changeLayoutType', { layoutType });
            },
        },
        isSuperAdmin() {
            return getAuthBackend()?.isSuperAdmin() === true;
        },
        currentUser() {
            return getAuthBackend()?.getAuthenticatedUser();
        },
    },
    watch: {
        layoutType: {
            immediate: true,
            deep: true,
            handler(newVal, oldVal) {
                if (newVal !== oldVal) {
                    switch (newVal) {
                        case "horizontal":
                            document.body.setAttribute(
                                "data-pc-layout",
                                "horizontal"
                            );
                            break;
                        case "vertical":
                            document.body.setAttribute("data-pc-layout", "vertical");
                    }
                }
            },
        },
    },
    mounted() {
        const activeListItem = document.querySelector("li.active");
        if (activeListItem) {
            const parentElementOrSelf = activeListItem?.parentElement ? activeListItem.parentElement : activeListItem;
            if (parentElementOrSelf && !parentElementOrSelf.classList.contains("pc-navbar")) {
                const closestItem = parentElementOrSelf.parentElement.closest(".pc-item");
                if (closestItem) {
                    closestItem.classList.add("active");
                    closestItem.children[1].classList.add('show')
                }
            }
          
            /**
             * Sidebar menu collapse
             */
            if (document.querySelectorAll(".navbar-content .collapse")) {
                let collapses = document.querySelectorAll(".navbar-content .collapse");

                collapses.forEach((collapse) => {
                    // Hide sibling collapses on `show.bs.collapse`
                    collapse.addEventListener("show.bs.collapse", (e) => {
                        e.stopPropagation();
                        let closestCollapse = collapse.parentElement.closest(".collapse");
                        if (closestCollapse) {
                            let siblingCollapses =
                                closestCollapse.querySelectorAll(".collapse");
                            siblingCollapses.forEach((siblingCollapse) => {
                                if (siblingCollapse.classList.contains("show")) {
                                    siblingCollapse.classList.remove("show");
                                    siblingCollapse.parentElement.firstChild.setAttribute("aria-expanded", "false");
                                }
                            });
                        } else {
                            let getSiblings = (elem) => {
                                // Setup siblings array and get the first sibling
                                let siblings = [];
                                let sibling = elem.parentNode.firstChild;
                                // Loop through each sibling and push to the array
                                while (sibling) {
                                    if (sibling.nodeType === 1 && sibling !== elem) {
                                        siblings.push(sibling);
                                    }
                                    sibling = sibling.nextSibling;
                                }
                                return siblings;
                            };
                            let siblings = getSiblings(collapse.parentElement);
                            siblings.forEach((item) => {
                                if (item.childNodes.length > 2) {
                                    item.firstElementChild.setAttribute("aria-expanded", "false");
                                    item.firstElementChild.classList.remove("active");
                                }
                                let ids = item.querySelectorAll("*[id]");
                                ids.forEach((item1) => {
                                    item1.classList.remove("show");
                                    item1.parentElement.firstChild.setAttribute("aria-expanded", "false");
                                    item1.parentElement.firstChild.classList.remove("active");
                                    if (item1.childNodes.length > 2) {
                                        let val = item1.querySelectorAll("ul li a");

                                        val.forEach((subitem) => {
                                            if (subitem.hasAttribute("aria-expanded"))
                                                subitem.setAttribute("aria-expanded", "false");
                                        });
                                    }
                                });
                            });
                        }
                    });

                    // Hide nested collapses on `hide.bs.collapse`
                    collapse.addEventListener("hide.bs.collapse", (e) => {
                        e.stopPropagation();
                        let childCollapses = collapse.querySelectorAll(".collapse");
                        childCollapses.forEach((childCollapse) => {
                            let childCollapseInstance = childCollapse;
                            childCollapseInstance.classList.remove("show");
                            childCollapseInstance.parentElement.firstChild.setAttribute("aria-expanded", "false");
                        });
                    });
                });
            }


        } else {
            console.error("No list item with class 'active' found.");
        }
    }
};
</script>

<template>
    <div class="navbar-wrapper" id="navbar-wrapper">
        <div class="m-header">
            <router-link to="/" class="b-brand text-primary">
                <!-- ========   Change your logo from here   ============ -->
                <img v-if="currentLogo === logoDark" :src="logoDark" alt="logo image" class="logo-lg custom_logo">
                <img v-else :src="logoWhite" alt="logo image" class="logo-lg custom_logo">
                <img src="@/assets/images/favicon.svg" alt="" class="logo logo-sm"> <span class="badge bg-brand-color-2 rounded-pill ms-1 theme-version">v1.0</span>
            </router-link>
        </div>
        <simplebar data-simplebar class="navbar-content pc-trigger">
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label>{{$t("Menu Utama")}}</label>
                </li>
                <li class="pc-item pc-hasmenu">
                    <BLink class="pc-link" data-bs-toggle="collapse" href="#pcDashboard" role="button" aria-expanded="false" aria-controls="pcDashboard">
                        <span class="pc-micon">
                            <i class="ph-duotone ph-gauge"></i>
                        </span> <span class="pc-mtext">{{$t("Dashboard")}}</span>
                        <span class="pc-arrow">
                            <ChevronDownIcon></ChevronDownIcon>
                        </span>
                    </BLink>
                    <div class="collapse" id="pcDashboard">
                        <ul class="pc-submenu">
                            <li class="pc-item" :class="{ 'active': $route.path === '/dashboard' }">
                                <router-link class="pc-link" to="/dashboard">{{$t("PRIMA Dashboard")}}</router-link>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="pc-item pc-hasmenu">
                    <BLink class="pc-link" data-bs-toggle="collapse" href="#pcAssessment" role="button" aria-expanded="false" aria-controls="pcAssessment">
                        <span class="pc-micon">
                            <i class="ph-duotone ph-clipboard-text"></i>
                        </span> <span class="pc-mtext">{{$t("Assessment")}}</span>
                        <span class="pc-arrow">
                            <ChevronDownIcon></ChevronDownIcon>
                        </span>
                    </BLink>
                    <div class="collapse" id="pcAssessment">
                        <ul class="pc-submenu">
                            <li class="pc-item" :class="{ 'active': $route.path === '/assessment/self' }">
                                <router-link class="pc-link" to="/assessment/self">{{$t("Self Assessment")}}</router-link>
                            </li>
                            <li class="pc-item" :class="{ 'active': $route.path === '/assessment/on-desk' }">
                                <router-link class="pc-link" to="/assessment/on-desk">{{$t("On Desk Assessment")}}</router-link>
                            </li>
                            <li class="pc-item" :class="{ 'active': $route.path === '/assessment/on-site' }">
                                <router-link class="pc-link" to="/assessment/on-site">{{$t("On Site Assessment")}}</router-link>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="pc-item pc-hasmenu" v-if="isSuperAdmin">
                    <BLink class="pc-link" data-bs-toggle="collapse" href="#pcAdministration" role="button" aria-expanded="false" aria-controls="pcAdministration">
                        <span class="pc-micon">
                            <i class="ph-duotone ph-shield-check"></i>
                        </span> <span class="pc-mtext">{{$t("Administration")}}</span>
                        <span class="pc-arrow">
                            <ChevronDownIcon></ChevronDownIcon>
                        </span>
                    </BLink>
                    <div class="collapse" id="pcAdministration">
                        <ul class="pc-submenu">
                            <li class="pc-item" :class="{ 'active': $route.path === '/users' }">
                                <router-link class="pc-link" to="/users">{{$t("User Management")}}</router-link>
                            </li>
                            <li class="pc-item" :class="{ 'active': $route.path === '/roles' }">
                                <router-link class="pc-link" to="/roles">{{$t("Role Management")}}</router-link>
                            </li>
                            <li class="pc-item" :class="{ 'active': $route.path === '/organizations' }">
                                <router-link class="pc-link" to="/organizations">{{$t("Organization Management")}}</router-link>
                            </li>
                            <li class="pc-item" :class="{ 'active': $route.path === '/locations' }">
                                <router-link class="pc-link" to="/locations">{{$t("Location Management")}}</router-link>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </simplebar>
        <BCard no-body class="pc-user-card" v-if="currentUser">
            <BCardBody>
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 me-2">
                        <h6 class="mb-0">{{ currentUser.name }}</h6>
                        <small>{{ currentUser.role?.name }}</small>
                        <small class="d-block" v-if="currentUser.organization">{{ currentUser.organization.name }}</small>
                    </div>
                    <BDropdown variant="purple" dropup no-caret toggle-class="p-0">
                        <template v-slot:button-content>
                            <span class="btn btn-icon btn-link-secondary avtar arrow-none p-0 dropdown-toggle">
                                <i class="ph-duotone ph-windows-logo"></i>
                            </span>
                        </template>
                        <BRow xl="6">
                            <BCol xl="6">
                                <BDropdownItem class="pc-user-links p-0">
                                        <i class="ph-duotone ph-user"></i>
                                        <br>
                                        <span>My Account</span>
                                </BDropdownItem>
                                <BDropdownDivider />
                                <BDropdownItem class="pc-user-links p-0">
                                    <i class="ph-duotone ph-lock-key"></i> <br>
                                    <span>Lock Screen</span>
                                </BDropdownItem>
                                <BDropdownDivider />
                            </BCol>
                            <BCol xl="6">
                                <BDropdownItem class="pc-user-links p-0">
                                    <i class="ph-duotone ph-gear"></i> <br>
                                    <span>Settings</span>
                                </BDropdownItem>
                                <BDropdownDivider />
                                <BDropdownItem class="pc-user-links p-0">
                                    <i class="ph-duotone ph-power"></i> <br>
                                    <span>Logout</span>
                                </BDropdownItem>
                                <BDropdownDivider />
                            </BCol>
                        </BRow>
                    </BDropdown>
                </div>
            </BCardBody>
        </BCard>
    </div>
</template>

