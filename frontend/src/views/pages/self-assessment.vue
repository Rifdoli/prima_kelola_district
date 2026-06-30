<script>
import Swal from "sweetalert2";
import api from "@/services/api";
import Layout from "@/layout/main.vue";
import pageheader from "@/components/page-header.vue";

const LEVELS = ["A", "B", "C", "D", "E"];

export default {
    name: "SELF_ASSESSMENT",
    components: { Layout, pageheader },
    data() {
        const now = new Date();
        return {
            levels: LEVELS,
            loading: false,
            saving: false,
            year: now.getFullYear(),
            quarter: Math.floor(now.getMonth() / 3) + 1, // 1..4
            years: [now.getFullYear() - 1, now.getFullYear(), now.getFullYear() + 1],
            assessment: null, // hasil POST /self-assessments
            questions: [], // hasil GET /assessment-questions
            answers: {}, // map: question_id -> { achieved_levels: ['A', ...], evidence_note, evidence_file_url }
            errorMsg: "",
        };
    },
    computed: {
        period() {
            return `${this.year}-Q${this.quarter}`;
        },
        isReadOnly() {
            return this.assessment?.status === "submitted";
        },
        statusLabel() {
            return { open: "Open", draft: "Draft", submitted: "Submitted" }[this.assessment?.status] || "-";
        },
        statusBadgeClass() {
            return {
                open: "bg-light-secondary",
                draft: "bg-light-warning",
                submitted: "bg-light-success",
            }[this.assessment?.status] || "bg-light-secondary";
        },
        // { [domain]: { [practice_area]: [question, ...] } }, urutan ikut sort_order
        groupedQuestions() {
            const groups = {};
            for (const q of this.questions) {
                groups[q.domain] = groups[q.domain] || {};
                groups[q.domain][q.practice_area] = groups[q.domain][q.practice_area] || [];
                groups[q.domain][q.practice_area].push(q);
            }
            return groups;
        },
    },
    methods: {
        criteriaText(question, level) {
            return question["criteria_" + level.toLowerCase()];
        },
        questionScore(questionId) {
            return this.answers[questionId]?.achieved_levels?.length || 0;
        },
        async fetchQuestions() {
            const { data } = await api.get("/assessment-questions");
            this.questions = data.data;
        },
        async initAssessment() {
            const { data } = await api.post("/self-assessments", { period: this.period });
            this.assessment = data.data;
            this.answers = {};
            for (const q of this.questions) {
                this.answers[q.assessment_question_id] = {
                    achieved_levels: [],
                    evidence_note: "",
                    evidence_file_url: null,
                };
            }
            for (const ans of this.assessment.answers || []) {
                this.answers[ans.assessment_question_id] = {
                    achieved_levels: ans.achieved_levels || [],
                    evidence_note: ans.evidence_note,
                    evidence_file_url: ans.evidence_file_url || null,
                };
            }
        },
        buildPayload() {
            return Object.entries(this.answers).map(([qid, val]) => ({
                assessment_question_id: Number(qid),
                achieved_levels: val.achieved_levels || [],
                evidence_note: val.evidence_note || null,
            }));
        },
        async persistAnswers() {
            await api.put(`/self-assessments/${this.assessment.self_assessment_id}/answers`, {
                answers: this.buildPayload(),
            });
            await this.refresh();
        },
        async saveDraft() {
            this.saving = true;
            this.errorMsg = "";
            try {
                await this.persistAnswers();
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Draft berhasil disimpan",
                    showConfirmButton: false,
                    timer: 1500,
                });
            } catch (error) {
                this.errorMsg = error.response?.data?.message || "Gagal menyimpan.";
            } finally {
                this.saving = false;
            }
        },
        async uploadEvidence(question, event) {
            const file = event.target.files[0];
            if (!file) return;
            this.errorMsg = "";
            const formData = new FormData();
            formData.append("file", file);
            try {
                const { data } = await api.post(
                    `/self-assessments/${this.assessment.self_assessment_id}/questions/${question.assessment_question_id}/evidence`,
                    formData,
                    { headers: { "Content-Type": "multipart/form-data" } }
                );
                this.answers[question.assessment_question_id].evidence_file_url = data.data.evidence_file_url;
                if (this.assessment.status === "open") {
                    this.assessment.status = "draft";
                }
            } catch (error) {
                this.errorMsg = error.response?.data?.message || "Gagal upload file.";
            } finally {
                event.target.value = "";
            }
        },
        async submitAssessment() {
            const result = await Swal.fire({
                icon: "warning",
                title: "Submit Self Assessment?",
                text: "Setelah disubmit, jawaban tidak dapat diubah lagi. Lanjutkan?",
                showCancelButton: true,
                confirmButtonText: "Ya, Submit",
                cancelButtonText: "Batal",
                confirmButtonColor: "#dc3545",
            });
            if (!result.isConfirmed) return;

            this.saving = true;
            this.errorMsg = "";
            try {
                await this.persistAnswers();
                await api.post(`/self-assessments/${this.assessment.self_assessment_id}/submit`);
                await this.refresh();
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Self assessment berhasil disubmit",
                    showConfirmButton: false,
                    timer: 1500,
                });
            } catch (error) {
                this.errorMsg = error.response?.data?.message || "Gagal submit.";
            } finally {
                this.saving = false;
            }
        },
        async refresh() {
            const { data } = await api.get(`/self-assessments/${this.assessment.self_assessment_id}`);
            this.assessment = data.data;
        },
        async reloadForPeriod() {
            this.loading = true;
            this.errorMsg = "";
            try {
                await this.initAssessment();
            } catch (error) {
                this.errorMsg = error.response?.data?.message || "Gagal memuat periode.";
            } finally {
                this.loading = false;
            }
        },
    },
    async mounted() {
        this.loading = true;
        try {
            await this.fetchQuestions();
            await this.initAssessment();
        } catch (error) {
            this.errorMsg = error.response?.data?.message || "Gagal memuat self assessment.";
        } finally {
            this.loading = false;
        }
    },
};
</script>

<template>
    <Layout>
        <pageheader title="Self Assessment" pageTitle="Assessment" />

        <BRow class="mb-3">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body d-flex flex-wrap align-items-end gap-3">
                        <div style="width: 120px">
                            <label class="form-label mb-1">Tahun</label>
                            <select class="form-control" v-model.number="year" :disabled="loading">
                                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                            </select>
                        </div>
                        <div style="width: 100px">
                            <label class="form-label mb-1">Kuartal</label>
                            <select class="form-control" v-model.number="quarter" :disabled="loading">
                                <option v-for="q in [1, 2, 3, 4]" :key="q" :value="q">Q{{ q }}</option>
                            </select>
                        </div>
                        <div>
                            <button class="btn btn-primary" :disabled="loading" @click="reloadForPeriod">
                                Muat
                            </button>
                        </div>
                        <div class="ms-auto d-flex align-items-center" v-if="assessment">
                            <span class="badge" :class="statusBadgeClass">{{ statusLabel }}</span>
                            <span v-if="assessment.total_score !== null && assessment.total_score !== undefined" class="ms-2">
                                Skor: <strong>{{ assessment.total_score }}</strong>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </BRow>

        <BRow>
            <div class="col-sm-12">
                <div class="alert alert-danger" v-if="errorMsg">{{ errorMsg }}</div>
                <div class="text-center text-muted py-5" v-if="loading">Memuat...</div>

                <template v-else-if="assessment">
                    <div class="card mb-3" v-for="(practiceAreas, domain) in groupedQuestions" :key="domain">
                        <div class="card-header">
                            <h5 class="mb-0">{{ domain }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4" v-for="(qs, practiceArea) in practiceAreas" :key="practiceArea">
                                <h6 class="text-primary mb-3">{{ practiceArea }}</h6>

                                <div class="border rounded p-3 mb-3" v-for="q in qs" :key="q.assessment_question_id">
                                    <div class="d-flex align-items-start mb-1">
                                        <p class="fw-semibold mb-0" v-if="q.scope">{{ q.scope }}</p>
                                        <span class="badge bg-light-primary ms-auto">
                                            Skor: {{ questionScore(q.assessment_question_id) }}/{{ levels.length }}
                                        </span>
                                    </div>
                                    <p class="mb-3">{{ q.question }}</p>

                                    <div class="form-check mb-2" v-for="level in levels" :key="level">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            :id="'q' + q.assessment_question_id + '_' + level"
                                            :value="level"
                                            v-model="answers[q.assessment_question_id].achieved_levels"
                                            :disabled="isReadOnly"
                                        >
                                        <label class="form-check-label" :for="'q' + q.assessment_question_id + '_' + level">
                                            <strong>{{ level }}.</strong> {{ criteriaText(q, level) }}
                                        </label>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label mb-1">Catatan Evidence</label>
                                        <textarea
                                            class="form-control"
                                            rows="2"
                                            v-model="answers[q.assessment_question_id].evidence_note"
                                            :disabled="isReadOnly"
                                        ></textarea>
                                    </div>

                                    <div class="mb-1">
                                        <label class="form-label mb-1">Upload Evidence (jpg/png/pdf)</label>
                                        <input
                                            type="file"
                                            class="form-control"
                                            accept=".jpg,.jpeg,.png,.pdf"
                                            :disabled="isReadOnly"
                                            @change="uploadEvidence(q, $event)"
                                        >
                                        <a
                                            v-if="answers[q.assessment_question_id].evidence_file_url"
                                            :href="answers[q.assessment_question_id].evidence_file_url"
                                            target="_blank"
                                            class="d-inline-block mt-1"
                                        >
                                            Lihat file
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mb-4" v-if="!isReadOnly && assessment">
                        <button class="btn btn-outline-secondary" :disabled="saving" @click="saveDraft">
                            Simpan Draft
                        </button>
                        <button class="btn btn-primary" :disabled="saving" @click="submitAssessment">
                            Submit
                        </button>
                    </div>
                </template>
            </div>
        </BRow>
    </Layout>
</template>
