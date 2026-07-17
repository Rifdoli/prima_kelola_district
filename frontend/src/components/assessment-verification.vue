<script>
import Swal from "sweetalert2";
import api from "@/services/api";
import Layout from "@/layout/main.vue";
import pageheader from "@/components/page-header.vue";

const LEVELS = ["A", "B", "C", "D", "E"];

// Komponen inti verifikasi berjenjang, dipakai dua page:
// - on-desk-assessment.vue  -> type="on_desk" (ODA, oleh Regional)
// - on-site-assessment.vue  -> type="on_site" (OSA, oleh Area/Nasional)
export default {
    name: "ASSESSMENT_VERIFICATION",
    components: { Layout, pageheader },
    props: {
        type: { type: String, required: true }, // "on_desk" | "on_site"
    },
    data() {
        return {
            levels: LEVELS,
            loading: false,
            saving: false,
            errorMsg: "",
            forbidden: false, // true bila ditolak (403) -> sembunyikan tabel
            questions: [],
            list: [],            // district siap diverifikasi
            verification: null,  // detail yang sedang dikerjakan
            activeDomain: null,
            // verify[qid][level] = { is_valid, note, evidence_file_url }
            verify: {},
            // klaim Self district: selfClaims[qid] = { levels: [], evidence: {level:url} }
            selfClaims: {},
            // level valid ODA (referensi OSA): parentValid[qid] = { level: true }
            parentValid: {},
        };
    },
    computed: {
        isOnSite() {
            return this.type === "on_site";
        },
        typeLabel() {
            return this.isOnSite ? "On Site Assessment" : "On Desk Assessment";
        },
        // Label singkat untuk kolom verifikasi tahap ini.
        shortLabel() {
            return this.isOnSite ? "OSA" : "ODA";
        },
        // Jumlah kolom tabel (untuk colspan baris kosong).
        colCount() {
            return this.isOnSite ? 9 : 7;
        },
        isReadOnly() {
            return this.verification?.status === "submitted";
        },
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
        criteriaText(question, level) {
            return question["criteria_" + level.toLowerCase()];
        },
        // Skor pertanyaan = jumlah level yang divalidasi valid.
        questionScore(qid) {
            return LEVELS.filter((l) => this.verify[qid]?.[l]?.is_valid).length;
        },
        domainValidated(domain) {
            const areas = this.groupedQuestions[domain] || {};
            let valid = 0, total = 0;
            for (const qs of Object.values(areas)) {
                for (const q of qs) {
                    total += LEVELS.length;
                    valid += this.questionScore(q.assessment_question_id);
                }
            }
            return { valid, total };
        },
        statusBadgeClass(status) {
            return {
                not_started: "bg-light-secondary",
                open: "bg-light-secondary",
                draft: "bg-light-warning",
                submitted: "bg-light-success",
            }[status] || "bg-light-secondary";
        },
        async fetchQuestions() {
            const { data } = await api.get("/assessment-questions");
            this.questions = data.data;
            this.activeDomain = this.domains[0] || null;
        },
        async fetchList() {
            this.loading = true;
            this.errorMsg = "";
            this.list = [];
            this.forbidden = false;
            try {
                const { data } = await api.get(`/verifications/${this.type}`);
                this.list = data.data;
            } catch (error) {
                this.errorMsg = error.response?.data?.message || "Gagal memuat daftar.";
                this.forbidden = error.response?.status === 403;
            } finally {
                this.loading = false;
            }
        },
        async openVerification(selfAssessmentId) {
            this.loading = true;
            this.errorMsg = "";
            try {
                const { data } = await api.post(`/verifications/${this.type}`, {
                    self_assessment_id: selfAssessmentId,
                });
                this.verification = data.data;
                this.buildMaps();
            } catch (error) {
                this.errorMsg = error.response?.data?.message || "Gagal membuka verifikasi.";
            } finally {
                this.loading = false;
            }
        },
        buildMaps() {
            const verify = {}, selfClaims = {}, parentValid = {};

            // Default semua level per pertanyaan.
            for (const q of this.questions) {
                verify[q.assessment_question_id] = {};
                for (const l of LEVELS) {
                    verify[q.assessment_question_id][l] = { is_valid: false, note: "", evidence_file_url: null };
                }
            }

            // Hasil verifikasi tersimpan (termasuk carry-forward dari backend).
            for (const lvl of this.verification.levels || []) {
                const slot = verify[lvl.assessment_question_id]?.[lvl.level];
                if (slot) {
                    slot.is_valid = !!lvl.is_valid;
                    slot.note = lvl.note || "";
                    slot.evidence_file_url = lvl.evidence_file_url || null;
                }
            }

            // Klaim Self district (read-only).
            for (const ans of this.verification.self_assessment?.answers || []) {
                selfClaims[ans.assessment_question_id] = {
                    levels: ans.achieved_levels || [],
                    evidence: ans.evidence_file_urls || {},
                };
            }

            // Level valid ODA sebagai referensi OSA.
            for (const lvl of this.verification.parent?.levels || []) {
                if (lvl.is_valid) {
                    parentValid[lvl.assessment_question_id] = parentValid[lvl.assessment_question_id] || {};
                    parentValid[lvl.assessment_question_id][lvl.level] = true;
                }
            }

            this.verify = verify;
            this.selfClaims = selfClaims;
            this.parentValid = parentValid;
        },
        selfClaimed(qid, level) {
            return (this.selfClaims[qid]?.levels || []).includes(level);
        },
        selfEvidence(qid, level) {
            return this.selfClaims[qid]?.evidence?.[level] || null;
        },
        parentValidLevel(qid, level) {
            return this.parentValid[qid]?.[level] === true;
        },
        buildPayload() {
            const out = [];
            for (const [qid, byLevel] of Object.entries(this.verify)) {
                for (const [level, val] of Object.entries(byLevel)) {
                    out.push({
                        assessment_question_id: Number(qid),
                        level,
                        is_valid: !!val.is_valid,
                        note: val.note || null,
                    });
                }
            }
            return out;
        },
        async persist() {
            const { data } = await api.put(
                `/verifications/detail/${this.verification.assessment_verification_id}/levels`,
                { levels: this.buildPayload() }
            );
            this.verification = data.data;
        },
        async saveDraft() {
            this.saving = true;
            this.errorMsg = "";
            try {
                await this.persist();
                Swal.fire({ icon: "success", title: "Draft disimpan", showConfirmButton: false, timer: 1500 });
            } catch (error) {
                this.errorMsg = error.response?.data?.message || "Gagal menyimpan.";
            } finally {
                this.saving = false;
            }
        },
        async uploadEvidence(qid, level, event) {
            const file = event.target.files[0];
            if (!file) return;
            this.errorMsg = "";
            const formData = new FormData();
            formData.append("file", file);
            try {
                const { data } = await api.post(
                    `/verifications/detail/${this.verification.assessment_verification_id}/questions/${qid}/evidence/${level}`,
                    formData,
                    { headers: { "Content-Type": "multipart/form-data" } }
                );
                this.verify[qid][level].evidence_file_url = data.data.evidence_file_url;
            } catch (error) {
                this.errorMsg = error.response?.data?.message || "Gagal upload file.";
            } finally {
                event.target.value = "";
            }
        },
        async deleteEvidence(qid, level) {
            const result = await Swal.fire({
                icon: "warning", title: "Hapus evidence?", showCancelButton: true,
                confirmButtonText: "Ya, Hapus", cancelButtonText: "Batal", confirmButtonColor: "#dc3545",
            });
            if (!result.isConfirmed) return;
            try {
                await api.delete(
                    `/verifications/detail/${this.verification.assessment_verification_id}/questions/${qid}/evidence/${level}`
                );
                this.verify[qid][level].evidence_file_url = null;
            } catch (error) {
                this.errorMsg = error.response?.data?.message || "Gagal menghapus file.";
            }
        },
        async submitVerification() {
            const result = await Swal.fire({
                icon: "warning", title: `Submit ${this.typeLabel}?`,
                text: "Setelah disubmit tidak dapat diubah lagi. Lanjutkan?",
                showCancelButton: true, confirmButtonText: "Ya, Submit", cancelButtonText: "Batal",
                confirmButtonColor: "#dc3545",
            });
            if (!result.isConfirmed) return;
            this.saving = true;
            this.errorMsg = "";
            try {
                await this.persist();
                await api.post(`/verifications/detail/${this.verification.assessment_verification_id}/submit`);
                await this.openVerification(this.verification.self_assessment_id);
                Swal.fire({ icon: "success", title: `${this.typeLabel} disubmit`, showConfirmButton: false, timer: 1500 });
            } catch (error) {
                this.errorMsg = error.response?.data?.message || "Gagal submit.";
            } finally {
                this.saving = false;
            }
        },
        backToList() {
            this.verification = null;
            this.fetchList();
        },
    },
    async mounted() {
        this.loading = true;
        try {
            await this.fetchQuestions();
            await this.fetchList();
        } catch (error) {
            this.errorMsg = error.response?.data?.message || "Gagal memuat.";
        } finally {
            this.loading = false;
        }
    },
};
</script>

<template>
    <Layout>
        <pageheader :title="typeLabel" pageTitle="Assessment" />

        <div class="alert alert-danger" v-if="errorMsg">{{ errorMsg }}</div>
        <div class="text-center text-muted py-5" v-if="loading">Memuat...</div>

        <!-- Daftar district untuk diverifikasi -->
        <div class="card" v-else-if="!verification && !forbidden">
            <div class="card-header">
                <h5 class="mb-0">District untuk diverifikasi</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>District</th>
                                <th>Periode</th>
                                <th class="text-center">Status Self</th>
                                <th class="text-center">Skor Self</th>
                                <th v-if="isOnSite" class="text-center">Status ODA</th>
                                <th v-if="isOnSite" class="text-center">Skor ODA</th>
                                <th class="text-center">Status {{ shortLabel }}</th>
                                <th class="text-center">Skor {{ shortLabel }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in list" :key="row.self_assessment_id">
                                <td>{{ row.organization?.name }}</td>
                                <td>{{ row.period }}</td>
                                <td class="text-center">
                                    <span class="badge" :class="statusBadgeClass(row.self_status)">{{ row.self_status }}</span>
                                </td>
                                <td class="text-center">{{ row.self_score ?? '—' }}</td>
                                <td v-if="isOnSite" class="text-center">
                                    <span class="badge" :class="statusBadgeClass(row.oda_status || 'not_started')">
                                        {{ row.oda_status || 'not_started' }}
                                    </span>
                                </td>
                                <td v-if="isOnSite" class="text-center">{{ row.oda_score ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="badge" :class="statusBadgeClass(row.verification_status)">
                                        {{ row.verification_status }}
                                    </span>
                                </td>
                                <td class="text-center">{{ row.verification_score ?? '—' }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-primary" @click="openVerification(row.self_assessment_id)">
                                        {{ row.verification_status === 'submitted' ? 'Lihat' : 'Verifikasi' }}
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!list.length">
                                <td :colspan="colCount" class="text-center text-muted py-4">Belum ada yang bisa diverifikasi.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Detail verifikasi -->
        <template v-else-if="verification">
            <div class="card mb-3">
                <div class="card-body d-flex flex-wrap align-items-center gap-3">
                    <button class="btn btn-outline-secondary btn-sm" @click="backToList">
                        <i class="ph-duotone ph-arrow-left me-1"></i>Kembali
                    </button>
                    <div>
                        <h6 class="mb-0">{{ verification.self_assessment?.organization?.name }}</h6>
                        <small class="text-muted">{{ verification.self_assessment?.period }}</small>
                    </div>
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <span class="badge" :class="statusBadgeClass(verification.status)">{{ verification.status }}</span>
                        <span v-if="verification.total_score != null">Skor: <strong>{{ verification.total_score }}</strong></span>
                    </div>
                </div>
            </div>

            <ul class="nav nav-pills flex-nowrap overflow-auto mb-3 domain-tabs">
                <li class="nav-item" v-for="domain in domains" :key="domain">
                    <a
                        href="#"
                        class="nav-link d-flex align-items-center gap-2 text-nowrap"
                        :class="{ active: activeDomain === domain }"
                        @click.prevent="activeDomain = domain"
                    >
                        <span>{{ domain }}</span>
                        <span class="badge flex-shrink-0 bg-light-primary">
                            {{ domainValidated(domain).valid }}/{{ domainValidated(domain).total }}
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
                                        <span class="badge bg-light-success ms-auto flex-shrink-0">
                                            Valid: {{ questionScore(q.assessment_question_id) }}/{{ levels.length }}
                                        </span>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle mb-0 assessment-table">
                                            <thead>
                                                <tr>
                                                    <th style="width:34%">Kriteria</th>
                                                    <th style="width:26%">Hasil Self-Assessment District</th>
                                                    <th style="width:20%">Verifikasi {{ shortLabel }}</th>
                                                    <th style="width:20%">Catatan {{ shortLabel }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="level in levels" :key="level">
                                                    <td><strong>{{ level }}.</strong> {{ criteriaText(q, level) }}</td>

                                                    <td>
                                                        <div v-if="selfClaimed(q.assessment_question_id, level)">
                                                            <span class="badge bg-light-success">Terpenuhi</span>
                                                            <a v-if="selfEvidence(q.assessment_question_id, level)"
                                                               :href="selfEvidence(q.assessment_question_id, level)" target="_blank" class="ms-2 small">
                                                                <i class="ph-duotone ph-file-text me-1"></i>Lihat Evidence
                                                            </a>
                                                            <span v-if="isOnSite && parentValidLevel(q.assessment_question_id, level)"
                                                                  class="badge bg-light-primary ms-2">ODA: valid</span>
                                                        </div>
                                                        <span v-else class="text-muted">Belum Terpenuhi</span>
                                                    </td>

                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                :name="'valid_' + q.assessment_question_id + '_' + level"
                                                                :id="'vyes_' + q.assessment_question_id + '_' + level"
                                                                :checked="verify[q.assessment_question_id][level].is_valid"
                                                                :disabled="isReadOnly"
                                                                @change="verify[q.assessment_question_id][level].is_valid = true">
                                                            <label class="form-check-label" :for="'vyes_' + q.assessment_question_id + '_' + level">Terpenuhi</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                :name="'valid_' + q.assessment_question_id + '_' + level"
                                                                :id="'vno_' + q.assessment_question_id + '_' + level"
                                                                :checked="!verify[q.assessment_question_id][level].is_valid"
                                                                :disabled="isReadOnly"
                                                                @change="verify[q.assessment_question_id][level].is_valid = false">
                                                            <label class="form-check-label" :for="'vno_' + q.assessment_question_id + '_' + level">Belum Terpenuhi</label>
                                                        </div>

                                                        <!-- Evidence verifikator, muncul saat level ditandai Terpenuhi. -->
                                                        <div v-if="verify[q.assessment_question_id][level].is_valid" class="d-flex align-items-center gap-2 flex-wrap mt-2">
                                                            <label class="btn btn-sm btn-outline-primary mb-0" :class="{ disabled: isReadOnly }">
                                                                <i class="ph-duotone ph-upload-simple me-1"></i>
                                                                {{ verify[q.assessment_question_id][level].evidence_file_url ? "Change Evidence" : "Upload Evidence" }}
                                                                <input type="file" hidden accept=".jpg,.jpeg,.png,.pdf" :disabled="isReadOnly"
                                                                    @change="uploadEvidence(q.assessment_question_id, level, $event)">
                                                            </label>
                                                            <a v-if="verify[q.assessment_question_id][level].evidence_file_url"
                                                               :href="verify[q.assessment_question_id][level].evidence_file_url" target="_blank" class="text-nowrap">
                                                                <i class="ph-duotone ph-file-text me-1"></i>View file
                                                            </a>
                                                            <button v-if="verify[q.assessment_question_id][level].evidence_file_url && !isReadOnly"
                                                                type="button" class="btn btn-sm btn-outline-danger text-nowrap"
                                                                @click="deleteEvidence(q.assessment_question_id, level)">
                                                                <i class="ph-duotone ph-trash me-1"></i>Delete
                                                            </button>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <textarea class="form-control form-control-sm" rows="2"
                                                            placeholder="Tuliskan hasil verifikasi..."
                                                            v-model="verify[q.assessment_question_id][level].note"
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

                    <div class="text-end mb-4" v-if="!isReadOnly">
                        <button class="btn btn-outline-secondary" :disabled="saving" @click="saveDraft">Simpan Draft</button>
                        <button class="btn btn-primary" :disabled="saving" @click="submitVerification">Submit</button>
                    </div>
        </template>
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
    min-width: 820px;
}
.assessment-table td,
.assessment-table th {
    white-space: normal;
    word-break: break-word;
}
</style>
