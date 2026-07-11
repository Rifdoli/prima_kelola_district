<script>
import api from "@/services/api";
import Layout from "@/layout/main.vue";
import pageheader from "@/components/page-header.vue";

export default {
    name: "TRACKING_ASSESSMENT",
    components: { Layout, pageheader },
    data() {
        return {
            loading: false,
            errorMsg: "",
            list: [],
            expandedId: null, // baris yang stepper-nya terbuka
        };
    },
    methods: {
        phaseBadgeClass(phase) {
            if (phase === "Selesai") return "bg-light-success";
            if (phase.startsWith("Proses")) return "bg-light-warning";
            if (phase === "Pengisian Self") return "bg-light-info";
            return "bg-light-secondary"; // Menunggu ...
        },
        // Status satu tahap -> tampilan ikon di stepper.
        stepState(status) {
            return {
                submitted: { icon: "ph-check-circle", cls: "text-success", label: "Selesai" },
                draft: { icon: "ph-spinner-gap", cls: "text-warning", label: "Proses" },
                open: { icon: "ph-dots-three-circle", cls: "text-secondary", label: "Dibuka" },
                not_started: { icon: "ph-circle-dashed", cls: "text-muted", label: "Menunggu" },
            }[status] || { icon: "ph-circle-dashed", cls: "text-muted", label: "Menunggu" };
        },
        steps(row) {
            return [
                { name: "Self", ...this.stepState(row.self_status), score: row.self_score },
                { name: "On Desk", ...this.stepState(row.oda_status), score: row.oda_score },
                { name: "On Site", ...this.stepState(row.osa_status), score: row.osa_score },
            ];
        },
        toggle(id) {
            this.expandedId = this.expandedId === id ? null : id;
        },
        async fetchTracking() {
            this.loading = true;
            this.errorMsg = "";
            try {
                const { data } = await api.get("/assessment-tracking");
                this.list = data.data;
            } catch (error) {
                this.errorMsg = error.response?.data?.message || "Gagal memuat tracking.";
            } finally {
                this.loading = false;
            }
        },
    },
    mounted() {
        this.fetchTracking();
    },
};
</script>

<template>
    <Layout>
        <pageheader title="Tracking Assessment" pageTitle="Assessment" />

        <div class="alert alert-danger" v-if="errorMsg">{{ errorMsg }}</div>
        <div class="text-center text-muted py-5" v-if="loading">Memuat...</div>

        <div class="card" v-else>
            <div class="card-header">
                <h5 class="mb-0">Progres Assessment</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px"></th>
                                <th>District</th>
                                <th>Periode</th>
                                <th class="text-center">Fase Sekarang</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="row in list" :key="row.self_assessment_id">
                                <tr style="cursor: pointer" @click="toggle(row.self_assessment_id)">
                                    <td class="text-center">
                                        <i class="ph-duotone" :class="expandedId === row.self_assessment_id ? 'ph-caret-down' : 'ph-caret-right'"></i>
                                    </td>
                                    <td>{{ row.organization?.name }}</td>
                                    <td>{{ row.period }}</td>
                                    <td class="text-center">
                                        <span class="badge" :class="phaseBadgeClass(row.phase)">{{ row.phase }}</span>
                                    </td>
                                </tr>
                                <tr v-if="expandedId === row.self_assessment_id">
                                    <td colspan="4" class="bg-light">
                                        <div class="d-flex align-items-center justify-content-around flex-wrap gap-3 py-3">
                                            <template v-for="(step, i) in steps(row)" :key="step.name">
                                                <div class="text-center" style="min-width: 110px">
                                                    <i class="ph-duotone" :class="[step.icon, step.cls]" style="font-size: 2.2rem"></i>
                                                    <div class="fw-semibold mt-1">{{ step.name }}</div>
                                                    <small :class="step.cls">{{ step.label }}</small>
                                                    <div><small class="text-muted">Skor: {{ step.score ?? '—' }}</small></div>
                                                </div>
                                                <div v-if="i < 2" class="flex-grow-1 border-top" style="max-width: 80px; min-width: 30px"></div>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr v-if="!list.length">
                                <td colspan="4" class="text-center text-muted py-4">Belum ada assessment.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </Layout>
</template>
