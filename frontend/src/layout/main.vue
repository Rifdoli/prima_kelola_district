<script>
import { layoutComputed } from "@/state/helper.js";

import Vertical from "./vertical.vue";
export default {
    name: "MAIN",
    components: {
        Vertical, 
    },
    data() {
        return {};
    },
    computed: {
        ...layoutComputed,
    }, mounted() {
        // document.body.setAttribute("data-pc-direction", "rtl");
    }
}
</script>

<template>
    <Vertical v-if="layoutType === 'vertical'">
        <slot />
    </Vertical>
</template>