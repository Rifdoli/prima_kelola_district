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
            answers: {}, // map: question_id -> { achieved_levels: ['A', ...], evidence_note, evidence_files: { level: url } }
            errorMsg: "",
            forbidden: false, // true bila ditolak (403) -> sembunyikan filter
            activeDomain: null, // domain tab yang sedang aktif
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
        domains() {
            return Object.keys(this.groupedQuestions);
        },
    },
    methods: {
        // Jumlah pertanyaan yang sudah dijawab (min. 1 level) per domain.
        domainAnswered(domain) {
            const areas = this.groupedQuestions[domain] || {};
            let answered = 0, total = 0;
            for (const qs of Object.values(areas)) {
                for (const q of qs) {
                    total++;
                    if (this.questionScore(q.assessment_question_id) > 0) answered++;
                }
            }
            return { answered, total };
        },
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
                    evidence_files: {}, // map level -> url
                };
            }
            for (const ans of this.assessment.answers || []) {
                this.answers[ans.assessment_question_id] = {
                    achieved_levels: ans.achieved_levels || [],
                    evidence_note: ans.evidence_note,
                    evidence_files: ans.evidence_file_urls || {},
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
        async uploadEvidence(question, level, event) {
            const file = event.target.files[0];
            if (!file) return;
            this.errorMsg = "";
            const formData = new FormData();
            formData.append("file", file);
            try {
                const { data } = await api.post(
                    `/self-assessments/${this.assessment.self_assessment_id}/questions/${question.assessment_question_id}/evidence/${level}`,
                    formData,
                    { headers: { "Content-Type": "multipart/form-data" } }
                );
                this.answers[question.assessment_question_id].evidence_files = data.data.evidence_file_urls || {};
                if (this.assessment.status === "open") {
                    this.assessment.status = "draft";
                }
            } catch (error) {
                this.errorMsg = error.response?.data?.message || "Gagal upload file.";
            } finally {
                event.target.value = "";
            }
        },
        async deleteEvidence(question, level) {
            const result = await Swal.fire({
                icon: "warning",
                title: "Hapus evidence?",
                showCancelButton: true,
                confirmButtonText: "Ya, Hapus",
                cancelButtonText: "Batal",
                confirmButtonColor: "#dc3545",
            });
            if (!result.isConfirmed) return;
            this.errorMsg = "";
            try {
                const { data } = await api.delete(
                    `/self-assessments/${this.assessment.self_assessment_id}/questions/${question.assessment_question_id}/evidence/${level}`
                );
                this.answers[question.assessment_question_id].evidence_files = data.data?.evidence_file_urls || {};
            } catch (error) {
                this.errorMsg = error.response?.data?.message || "Gagal menghapus file.";
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
            this.activeDomain = this.domains[0] || null;
            await this.initAssessment();
        } catch (error) {
            this.errorMsg = error.response?.data?.message || "Gagal memuat self assessment.";
            this.forbidden = error.response?.status === 403;
        } finally {
            this.loading = false;
        }
    },
};
</script>

<template>
    <Layout>
        <pageheader title="Self Assessment" pageTitle="Assessment" />

        <div class="alert alert-danger" v-if="errorMsg">{{ errorMsg }}</div>

        <BRow class="mb-3" v-if="!forbidden">
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
                <div class="text-center text-muted py-5" v-if="loading">Memuat...</div>

                <template v-else-if="assessment">
                  <BRow>
                    <BCol lg="3">
                        <div class="card mb-3 domain-nav">
                            <div class="list-group list-group-flush">
                                <a
                                    v-for="domain in domains"
                                    :key="domain"
                                    href="#"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-2"
                                    :class="{ active: activeDomain === domain }"
                                    @click.prevent="activeDomain = domain"
                                >
                                    <span>{{ domain }}</span>
                                    <span class="badge flex-shrink-0" :class="domainAnswered(domain).answered === domainAnswered(domain).total ? 'bg-light-success' : 'bg-light-secondary'">
                                        {{ domainAnswered(domain).answered }}/{{ domainAnswered(domain).total }}
                                    </span>
                                </a>
                            </div>
                        </div>
                    </BCol>
                    <BCol lg="9">
                        <div class="card mb-3">
                        <div class="card-body">
                          <div v-for="domain in domains" :key="domain" v-show="activeDomain === domain">
                            <h5 class="mb-4">{{ domain }}</h5>
                            <div class="mb-4" v-for="(qs, practiceArea) in groupedQuestions[domain]" :key="practiceArea">
                                <h6 class="text-primary mb-3">{{ practiceArea }}</h6>

                                <div class="border rounded p-3 mb-3" v-for="q in qs" :key="q.assessment_question_id">
                                    <div class="d-flex align-items-start mb-1">
                                        <p class="fw-semibold mb-0" v-if="q.scope">{{ q.scope }}</p>
                                        <span class="badge bg-light-primary ms-auto">
                                            Skor: {{ questionScore(q.assessment_question_id) }}/{{ levels.length }}
                                        </span>
                                    </div>
                                    <p class="mb-3">{{ q.question }}</p>

                                    <div class="mb-2" v-for="level in levels" :key="level">
                                        <div class="form-check">
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

                                        <!-- Evidence file khusus kriteria ini, hanya saat dicentang. -->
                                        <div
                                            v-if="answers[q.assessment_question_id].achieved_levels.includes(level)"
                                            class="d-flex align-items-center gap-2 mt-1 ms-4 flex-wrap"
                                        >
                                            <label class="btn btn-sm btn-outline-primary mb-0" :class="{ disabled: isReadOnly }">
                                                <i class="ph-duotone ph-upload-simple me-1"></i>
                                                {{ answers[q.assessment_question_id].evidence_files[level] ? "Change Evidence" : "Upload Evidence" }}
                                                <input
                                                    type="file"
                                                    hidden
                                                    accept=".jpg,.jpeg,.png,.pdf"
                                                    :disabled="isReadOnly"
                                                    @change="uploadEvidence(q, level, $event)"
                                                >
                                            </label>
                                            <a
                                                v-if="answers[q.assessment_question_id].evidence_files[level]"
                                                :href="answers[q.assessment_question_id].evidence_files[level]"
                                                target="_blank"
                                                class="text-nowrap"
                                            >
                                                <i class="ph-duotone ph-file-text me-1"></i>View file
                                            </a>
                                            <button
                                                v-if="answers[q.assessment_question_id].evidence_files[level] && !isReadOnly"
                                                type="button"
                                                class="btn btn-sm btn-outline-danger text-nowrap"
                                                @click="deleteEvidence(q, level)"
                                            >
                                                <i class="ph-duotone ph-trash me-1"></i>Delete
                                            </button>
                                        </div>
                                    </div>

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
                    </BCol>
                  </BRow>
                </template>
            </div>
        </BRow>
    </Layout>
</template>

<style scoped>
/* Keep the domain list visible while scrolling long question lists. */
.domain-nav {
    position: sticky;
    top: 90px;
}
</style>
