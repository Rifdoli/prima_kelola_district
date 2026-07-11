<script>
// On Site Assessment (OSA) — kerangka; seluruh logika ada di komponen inti.
import AssessmentVerification from "@/components/assessment-verification.vue";

export default {
    name: "ON_SITE_ASSESSMENT",
    components: { AssessmentVerification },
};
</script>

<template>
    <AssessmentVerification type="on_site" />
</template>
