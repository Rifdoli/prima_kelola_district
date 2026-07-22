<script>
import Swal from "sweetalert2";
import api from "@/services/api";
import Layout from "@/layout/main.vue";
import pageheader from "@/components/page-header.vue";

export default {
    name: "SELF_ASSESSMENT",
    components: { Layout, pageheader },
    data() {
        const now = new Date();
        return {
            loading: false,
            saving: false,
            year: now.getFullYear(),
            quarter: Math.floor(now.getMonth() / 3) + 1, // 1..4
            years: [now.getFullYear() - 1, now.getFullYear(), now.getFullYear() + 1],
            assessment: null, // hasil GET /assessments/sa/{period}
            domains: [], // response.domains
            practiceAreas: [], // response.practice_areas
            questions: [], // response.questions
            criterias: [], // response.criterias
            answers: {}, // map: criteria_id -> { value, evidence_path, evidence_url, note }
            errorMsg: "",
            forbidden: false, // true bila ditolak (403) -> sembunyikan filter
            activeDomain: null, // domain.id yang sedang aktif
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
        // question_id -> [criteria, ...], urutan ikut sort_order
        criteriasByQuestion() {
            const map = {};
            for (const c of this.criterias) {
                (map[c.question_id] = map[c.question_id] || []).push(c);
            }
            for (const list of Object.values(map)) list.sort((a, b) => a.sort_order - b.sort_order);
            return map;
        },
        // practice_area_id -> [question, ...], urutan ikut sort_order
        questionsByPracticeArea() {
            const map = {};
            for (const q of this.questions) {
                (map[q.practice_area_id] = map[q.practice_area_id] || []).push(q);
            }
            for (const list of Object.values(map)) list.sort((a, b) => a.sort_order - b.sort_order);
            return map;
        },
        // domain_id -> [practice_area, ...]
        practiceAreasByDomain() {
            const map = {};
            for (const pa of this.practiceAreas) {
                (map[pa.domain_id] = map[pa.domain_id] || []).push(pa);
            }
            return map;
        },
    },
    methods: {
        achievedCount(questionId) {
            const crits = this.criteriasByQuestion[questionId] || [];
            return crits.filter((c) => this.answers[c.id]?.value).length;
        },
        // Jumlah pertanyaan yang sudah tersentuh (>=1 kriteria terpenuhi) per domain.
        domainAnswered(domainId) {
            const pas = this.practiceAreasByDomain[domainId] || [];
            let answered = 0, total = 0;
            for (const pa of pas) {
                for (const q of this.questionsByPracticeArea[pa.id] || []) {
                    total++;
                    if (this.achievedCount(q.id) > 0) answered++;
                }
            }
            return { answered, total };
        },
        async loadAssessment() {
            const { data } = await api.get(`/assessments/sa/${this.period}`);
            const d = data.data;
            this.assessment = d.assessment;
            this.domains = d.domains;
            this.practiceAreas = d.practice_areas;
            this.questions = d.questions;
            this.criterias = d.criterias;

            this.answers = {};
            for (const c of this.criterias) {
                this.answers[c.id] = { value: false, evidence_path: null, evidence_url: null, note: null };
            }
            for (const a of d.answers) {
                this.answers[a.criteria_id] = {
                    value: a.value,
                    evidence_path: a.evidence_path,
                    evidence_url: a.evidence_url,
                    note: a.note,
                };
            }

            if (!this.activeDomain) {
                this.activeDomain = this.domains[0]?.id ?? null;
            }
        },
        buildPayload() {
            return this.criterias.map((c) => ({
                criteria_id: c.id,
                value: !!this.answers[c.id].value,
                evidence_path: this.answers[c.id].evidence_path,
                note: this.answers[c.id].note,
            }));
        },
        // Dipakai saveDraft, uploadEvidence, deleteEvidence.
        async persistDraft() {
            await api.post(`/assessments/sa/${this.period}/draft`, {
                answers: this.buildPayload(),
            });
            await this.loadAssessment();
        },
        async saveDraft() {
            this.saving = true;
            this.errorMsg = "";
            try {
                await this.persistDraft();
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
        async uploadEvidence(criteria, event) {
            const file = event.target.files[0];
            if (!file) return;
            this.errorMsg = "";
            const formData = new FormData();
            formData.append("file", file);
            try {
                const { data } = await api.post(
                    `/assessments/sa/${this.period}/evidence`,
                    formData,
                    { headers: { "Content-Type": "multipart/form-data" } }
                );
                this.answers[criteria.id].evidence_path = data.data.path;
                this.answers[criteria.id].evidence_url = data.data.url;
                await this.persistDraft(); // baru benar-benar tersimpan & tertaut setelah ini
            } catch (error) {
                this.errorMsg = error.response?.data?.message || "Gagal upload file.";
            } finally {
                event.target.value = "";
            }
        },
        async deleteEvidence(criteria) {
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
            this.answers[criteria.id].evidence_path = null;
            this.answers[criteria.id].evidence_url = null;
            try {
                await this.persistDraft();
            } catch (error) {
                this.errorMsg = error.response?.data?.message || "Gagal menghapus file.";
            }
        },
        async submitAssessment() {
            const incomplete = this.validateAnswers();
            if (incomplete.length) {
                const lines = this.incompleteSummaryByDomain(incomplete)
                    .map((s) => `${s.name} = <strong>${s.count}</strong> Scope/Pertanyaan belum lengkap diisi`)
                    .join("<br>");
                Swal.fire({
                    icon: "error",
                    title: "Isian belum lengkap",
                    html:
                        lines +
                        `<hr class="my-2">` +
                        `<small class="text-muted">Pastikan jika status pemenuhan <strong>Terpenuhi</strong> untuk mengupload evidence, ` +
                        `dan jika status pemenuhan <strong>Belum Terpenuhi</strong> untuk mengisi catatan.</small>`,
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
                // submit langsung membawa jawaban dalam satu panggilan, tidak perlu simpan draft dulu
                await api.post(`/assessments/sa/${this.period}`, { answers: this.buildPayload() });
                await this.loadAssessment();
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
        async reloadForPeriod() {
            this.loading = true;
            this.errorMsg = "";
            try {
                await this.loadAssessment();
            } catch (error) {
                this.errorMsg = error.response?.data?.message || "Gagal memuat periode.";
            } finally {
                this.loading = false;
            }
        },
        // Kriteria yang belum lengkap: Terpenuhi tanpa evidence, atau Belum Terpenuhi tanpa catatan.
        validateAnswers() {
            return this.criterias.filter((c) => {
                const a = this.answers[c.id];
                return (a.value && !a.evidence_path) || (!a.value && !(a.note || "").trim());
            });
        },
        // Ringkas kriteria yang belum lengkap jadi jumlah SOAL (bukan kriteria) per domain,
        // urut mengikuti urutan tab domain.
        incompleteSummaryByDomain(incompleteCriterias) {
            const questionIdsByDomain = {};
            for (const c of incompleteCriterias) {
                (questionIdsByDomain[c.domain_id] = questionIdsByDomain[c.domain_id] || new Set()).add(c.question_id);
            }
            return this.domains
                .filter((d) => questionIdsByDomain[d.id])
                .map((d) => ({ name: d.name, count: questionIdsByDomain[d.id].size }));
        },
    },
    async mounted() {
        this.loading = true;
        try {
            await this.loadAssessment();
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
                      <li class="nav-item" v-for="domain in domains" :key="domain.id">
                          <a
                              href="#"
                              class="nav-link d-flex align-items-center gap-2 text-nowrap"
                              :class="{ active: activeDomain === domain.id }"
                              @click.prevent="activeDomain = domain.id"
                          >
                              <span>{{ domain.name }}</span>
                              <span class="badge flex-shrink-0" :class="domainAnswered(domain.id).answered === domainAnswered(domain.id).total ? 'bg-light-success' : 'bg-light-secondary'">
                                  {{ domainAnswered(domain.id).answered }}/{{ domainAnswered(domain.id).total }}
                              </span>
                          </a>
                      </li>
                  </ul>

                  <div class="card mb-3">
                        <div class="card-body">
                          <div v-for="domain in domains" :key="domain.id" v-show="activeDomain === domain.id">
                            <h5 class="mb-4">{{ domain.name }}</h5>
                            <div class="mb-4" v-for="pa in practiceAreasByDomain[domain.id]" :key="pa.id">
                                <h6 class="text-primary mb-3">{{ pa.name }}</h6>

                                <div class="mb-4" v-for="q in questionsByPracticeArea[pa.id]" :key="q.id">
                                    <div class="d-flex align-items-start mb-2 gap-2">
                                        <p class="fw-semibold mb-0">
                                            <span v-if="q.scope" class="text-muted">{{ q.scope }} — </span>{{ q.question }}
                                        </p>
                                        <span class="badge bg-light-primary ms-auto flex-shrink-0">
                                            Skor: {{ achievedCount(q.id) }}/{{ criteriasByQuestion[q.id].length }}
                                        </span>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle mb-0 assessment-table">
                                            <thead>
                                                <tr>
                                                    <th class="align-top" style="width:42%">Kriteria</th>
                                                    <th class="align-top" style="width:16%">Status Pemenuhan</th>
                                                    <th class="align-top" style="width:20%">
                                                        Evidence
                                                        <br>
                                                        <small class="text-danger fw-normal">
                                                            <i class="ph-duotone ph-warning-circle"></i>
                                                            Wajib diisi jika Terpenuhi
                                                        </small>
                                                    </th>
                                                    <th class="align-top" style="width:22%">
                                                        Catatan District
                                                        <br>
                                                        <small class="text-danger fw-normal">
                                                            <i class="ph-duotone ph-warning-circle"></i>
                                                            Wajib diisi jika Belum Terpenuhi
                                                        </small>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="criteria in criteriasByQuestion[q.id]" :key="criteria.id">
                                                    <td><strong>{{ criteria.code }}.</strong> {{ criteria.title }}</td>

                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                :name="'fulfil_' + criteria.id"
                                                                :id="'yes_' + criteria.id"
                                                                :checked="answers[criteria.id].value === true"
                                                                :disabled="isReadOnly"
                                                                @change="answers[criteria.id].value = true">
                                                            <label class="form-check-label" :for="'yes_' + criteria.id">Terpenuhi</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                :name="'fulfil_' + criteria.id"
                                                                :id="'no_' + criteria.id"
                                                                :checked="answers[criteria.id].value !== true"
                                                                :disabled="isReadOnly"
                                                                @change="answers[criteria.id].value = false">
                                                            <label class="form-check-label" :for="'no_' + criteria.id">Belum Terpenuhi</label>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <div
                                                            v-if="answers[criteria.id].value"
                                                            class="d-flex align-items-center gap-2 flex-wrap"
                                                        >
                                                            <label class="btn btn-sm btn-outline-primary mb-0" :class="{ disabled: isReadOnly }">
                                                                <i class="ph-duotone ph-upload-simple me-1"></i>
                                                                {{ answers[criteria.id].evidence_path ? "Change Evidence" : "Upload Evidence" }}
                                                                <input
                                                                    type="file"
                                                                    hidden
                                                                    accept=".jpg,.jpeg,.png,.pdf"
                                                                    :disabled="isReadOnly"
                                                                    @change="uploadEvidence(criteria, $event)"
                                                                >
                                                            </label>
                                                            <a
                                                                v-if="answers[criteria.id].evidence_url"
                                                                :href="answers[criteria.id].evidence_url"
                                                                target="_blank"
                                                                class="text-nowrap"
                                                            >
                                                                <i class="ph-duotone ph-file-text me-1"></i>View file
                                                            </a>
                                                            <button
                                                                v-if="answers[criteria.id].evidence_path && !isReadOnly"
                                                                type="button"
                                                                class="btn btn-sm btn-outline-danger text-nowrap"
                                                                @click="deleteEvidence(criteria)"
                                                            >
                                                                <i class="ph-duotone ph-trash me-1"></i>Delete
                                                            </button>
                                                        </div>
                                                        <span v-else class="text-muted">—</span>
                                                    </td>

                                                    <td>
                                                        <textarea class="form-control form-control-sm" rows="2"
                                                            placeholder="Tuliskan catatan self assessment..."
                                                            v-model="answers[criteria.id].note"
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