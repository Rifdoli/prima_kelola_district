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
            answers: {}, // map: question_id -> { achieved_levels: ['A', ...], evidence_files: { level: url } }
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
        // Radio "Terpenuhi" = level ada di achieved_levels; "Belum" = tidak ada.
        isFulfilled(qid, level) {
            return this.answers[qid].achieved_levels.includes(level);
        },
        setFulfilled(qid, level, fulfilled) {
            const list = this.answers[qid].achieved_levels;
            const has = list.includes(level);
            if (fulfilled && !has) list.push(level);
            if (!fulfilled && has) list.splice(list.indexOf(level), 1);
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
                    evidence_files: {}, // map level -> url
                    notes: {}, // map level -> string (lokal, belum persist)
                };
            }
            for (const ans of this.assessment.answers || []) {
                this.answers[ans.assessment_question_id] = {
                    achieved_levels: ans.achieved_levels || [],
                    evidence_files: ans.evidence_file_urls || {},
                    notes: ans.notes || {},
                };
            }
        },
        buildPayload() {
            return Object.entries(this.answers).map(([qid, val]) => ({
                assessment_question_id: Number(qid),
                achieved_levels: val.achieved_levels || [],
                notes: val.notes || {},
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
            const errors = this.validateAnswers();
            if (errors.length) {
                Swal.fire({
                    icon: "error",
                    title: `${errors.length} isian belum lengkap`,
                    html: errors.slice(0, 10).join("<br>") + (errors.length > 10 ? "<br>…" : ""),
                });
                return;
            }
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
        validateAnswers() {
            const errors = [];
            for (const q of this.questions) {
                const a = this.answers[q.assessment_question_id];
                for (const level of this.levels) {
                    const fullfilled = a.achieved_levels.includes(level);
                    if (fullfilled && !a.evidence_files[level]) {
                        errors.push(`${q.question} (${level}): Terpenuhi wajib evidence.`);
                    }
                    if (!fullfilled && !(a.notes[level] || "").trim()) {
                        errors.push(`${q.question} (${level}): Belum terpenuhi wajib catatan.`);
                    }
                }
            }
            return errors;
        }
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
                  <ul class="nav nav-pills flex-nowrap overflow-auto mb-3 domain-tabs">
                      <li class="nav-item" v-for="domain in domains" :key="domain">
                          <a
                              href="#"
                              class="nav-link d-flex align-items-center gap-2 text-nowrap"
                              :class="{ active: activeDomain === domain }"
                              @click.prevent="activeDomain = domain"
                          >
                              <span>{{ domain }}</span>
                              <span class="badge flex-shrink-0" :class="domainAnswered(domain).answered === domainAnswered(domain).total ? 'bg-light-success' : 'bg-light-secondary'">
                                  {{ domainAnswered(domain).answered }}/{{ domainAnswered(domain).total }}
                              </span>
                          </a>
                      </li>
                  </ul>

                  <div class="card mb-3">
                        <div class="card-body">
                          <div v-for="domain in domains" :key="domain" v-show="activeDomain === domain">
                            <h5 class="mb-4">{{ domain }}</h5>
                            <div class="mb-4" v-for="(qs, practiceArea) in groupedQuestions[domain]" :key="practiceArea">
                                <h6 class="text-primary mb-3">{{ practiceArea }}</h6>

                                <div class="mb-4" v-for="q in qs" :key="q.assessment_question_id">
                                    <div class="d-flex align-items-start mb-2 gap-2">
                                        <p class="fw-semibold mb-0">
                                            <span v-if="q.scope" class="text-muted">{{ q.scope }} — </span>{{ q.question }}
                                        </p>
                                        <span class="badge bg-light-primary ms-auto flex-shrink-0">
                                            Skor: {{ questionScore(q.assessment_question_id) }}/{{ levels.length }}
                                        </span>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle mb-0 assessment-table">
                                            <thead>
                                                <tr>
                                                    <th style="width:42%">Kriteria</th>
                                                    <th style="width:16%">Status Pemenuhan</th>
                                                    <th style="width:20%">Evidence</th>
                                                    <th style="width:22%">Catatan District</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="level in levels" :key="level">
                                                    <td><strong>{{ level }}.</strong> {{ criteriaText(q, level) }}</td>

                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                :name="'fulfil_' + q.assessment_question_id + '_' + level"
                                                                :id="'yes_' + q.assessment_question_id + '_' + level"
                                                                :checked="isFulfilled(q.assessment_question_id, level)"
                                                                :disabled="isReadOnly"
                                                                @change="setFulfilled(q.assessment_question_id, level, true)">
                                                            <label class="form-check-label" :for="'yes_' + q.assessment_question_id + '_' + level">Terpenuhi</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                :name="'fulfil_' + q.assessment_question_id + '_' + level"
                                                                :id="'no_' + q.assessment_question_id + '_' + level"
                                                                :checked="!isFulfilled(q.assessment_question_id, level)"
                                                                :disabled="isReadOnly"
                                                                @change="setFulfilled(q.assessment_question_id, level, false)">
                                                            <label class="form-check-label" :for="'no_' + q.assessment_question_id + '_' + level">Belum Terpenuhi</label>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <div
                                                            v-if="isFulfilled(q.assessment_question_id, level)"
                                                            class="d-flex align-items-center gap-2 flex-wrap"
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
                                                        <span v-else class="text-muted">—</span>
                                                    </td>

                                                    <td>
                                                        <textarea class="form-control form-control-sm" rows="2"
                                                            placeholder="Tuliskan catatan self assessment..."
                                                            v-model="answers[q.assessment_question_id].notes[level]"
                                                            :disabled="isReadOnly"></textarea>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
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
                </template>
            </div>
        </BRow>
    </Layout>
</template>

<style scoped>
/* Tab domain horizontal: tetap terlihat saat scroll, geser bila domain banyak. */
.domain-tabs {
    position: sticky;
    top: 70px;
    z-index: 5;
    gap: .25rem;
    padding: .25rem 0;
    background: var(--bs-body-bg);
}
.domain-tabs::-webkit-scrollbar {
    height: 4px;
}
/* Kolom mengikuti lebar yang ditentukan; teks kriteria wrap, tabel pas 100%.
   min-width: di layar sempit tabel tidak mengkerut -> .table-responsive scroll. */
.assessment-table {
    table-layout: fixed;
    width: 100%;
    min-width: 760px;
}
.assessment-table td,
.assessment-table th {
    white-space: normal;
    word-break: break-word;
}
</style>
