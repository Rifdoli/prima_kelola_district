<script>
// On Desk Assessment (ODA) — kerangka; seluruh logika ada di komponen inti.
import AssessmentVerification from "@/components/assessment-verification.vue";

export default {
    name: "ON_DESK_ASSESSMENT",
    components: { AssessmentVerification },
};
</script>

<template>
    <AssessmentVerification type="on_desk" />
</template>
