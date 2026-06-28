<script>
import Layout from "@/layout/main.vue"
import pageheader from "@/components/page-header.vue"
import AdminDataTable from "@/components/common/AdminDataTable.vue"
import RowActions from "@/components/common/RowActions.vue"
import api from "@/services/api"

export default {
    name: "ORGANIZATIONS",
    components: {
        Layout, pageheader, AdminDataTable, RowActions,
    },
    data() {
        return {
            organizations: [],
            types: [],
            columns: [
                { key: "name", label: "Name", sortable: true },
                { key: "sname", label: "Short Name", sortable: true },
                { key: "type.name", label: "Organization Type", sortable: true },
                { key: "parent.name", label: "Organization Parent", sortable: true },
            ],
            form: {
                organization_id: null, name: "", sname: "",
                organization_type_id: "", parent_organization_id: "", is_active: true,
            },
            viewRow: null,
            showAdd: false,
            showEdit: false,
            showView: false,
            error: null,
            loading: false,
        }
    },
    mounted() {
        this.fetchOrganizations();
        this.fetchTypes();
    },
    methods: {
        async fetchOrganizations() {
            this.loading = true;
            try {
                const { data } = await api.get('/organizations');
                this.organizations = data.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to load organizations.';
            } finally {
                this.loading = false;
            }
        },
        async fetchTypes() {
            try {
                const { data } = await api.get('/organization-types');
                this.types = data.data;
            } catch (error) {
                // ignore; the type dropdown will just be empty
            }
        },
        openAdd() {
            this.error = null;
            this.form = {
                organization_id: null, name: "", sname: "",
                organization_type_id: "", parent_organization_id: "", is_active: true,
            };
            this.showAdd = true;
        },
        openEdit(organization) {
            this.error = null;
            this.form = { ...organization, organization_type_id: organization.organization_type_id ?? "" };
            this.showEdit = true;
        },
        openView(organization) {
            this.viewRow = organization;
            this.showView = true;
        },
        async createOrganization() {
            if (!this.form.name || !this.form.sname || !this.form.organization_type_id) {
                this.error = 'Name, short name, and organization type are required.';
                return;
            }
            try {
                await api.post('/organizations', {
                    name: this.form.name,
                    sname: this.form.sname,
                    organization_type_id: this.form.organization_type_id,
                    parent_organization_id: this.form.parent_organization_id || null,
                    is_active: this.form.is_active,
                });
                this.showAdd = false;
                this.fetchOrganizations();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create organization.';
            }
        },
        async saveEdit() {
            try {
                // parent_organization_id is intentionally NOT sent on update
                // (backend rejects it) — re-parenting uses a dedicated
                // move() endpoint, not the regular update.
                await api.put(`/organizations/${this.form.organization_id}`, {
                    name: this.form.name,
                    sname: this.form.sname,
                    organization_type_id: this.form.organization_type_id,
                    is_active: this.form.is_active,
                });
                this.showEdit = false;
                this.fetchOrganizations();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update organization.';
            }
        },
        async deleteOrganization(organization) {
            if (!confirm(`Delete organization "${organization.name}"?`)) return;
            try {
                await api.delete(`/organizations/${organization.organization_id}`);
                this.fetchOrganizations();
            } catch (error) {
                if (error.response?.status === 422) {
                    const msg = error.response.data.message + "\n\nLanjut hapus beserta seluruh sub-organisasinya?";
                    if (confirm(msg)) {
                        try {
                            await api.delete(`/organizations/${organization.organization_id}?cascade=true`);
                            this.fetchOrganizations();
                        } catch (cascadeError) {
                            this.error = cascadeError.response?.data?.message || 'Failed to delete organization.';
                        }
                    }
                } else {
                    this.error = error.response?.data?.message || 'Failed to delete organization.';
                }
            }
        },
    },
}
</script>

<template>
    <Layout>
        <pageheader title="Organization Management" pageTitle="Administration" />
        <BRow>
            <div class="col-sm-12">
                <div class="alert alert-danger" v-if="error && !showAdd && !showEdit">{{ error }}</div>

                <AdminDataTable title="Organization Table" :columns="columns" :rows="organizations" :loading="loading"
                    :search-keys="['name', 'sname', 'type.name', 'parent.name']">
                    <template #header-actions>
                        <button class="btn btn-primary" @click="openAdd">
                            <i class="ti ti-plus f-18"></i> Add Organization
                        </button>
                    </template>
                    <template #cell-type-name="{ row }">
                        {{ row.type?.name }}
                    </template>
                    <template #cell-parent-name="{ row }">
                        {{ row.parent?.name || '-' }}
                    </template>
                    <template #actions="{ row }">
                        <RowActions :row="row" @view="openView" @edit="openEdit" @delete="deleteOrganization" />
                    </template>
                </AdminDataTable>
            </div>
        </BRow>

        <!-- Add Organization -->
        <BModal v-model="showAdd" title="Add Organization" hide-footer>
            <div class="alert alert-danger" v-if="error">{{ error }}</div>
            <div class="mb-2">
                <label class="form-label mb-1">Name</label>
                <input type="text" class="form-control" v-model="form.name">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Short Name</label>
                <input type="text" class="form-control" v-model="form.sname">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Organization Type</label>
                <select class="form-control" v-model="form.organization_type_id">
                    <option value="">— pilih tipe —</option>
                    <option v-for="type in types" :key="type.organization_type_id" :value="type.organization_type_id">
                        {{ type.name }}
                    </option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Organization Parent</label>
                <select class="form-control" v-model="form.parent_organization_id">
                    <option value="">— None (root) —</option>
                    <option v-for="org in organizations" :key="org.organization_id" :value="org.organization_id">
                        {{ org.name }}
                    </option>
                </select>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="addOrgActive" v-model="form.is_active">
                <label class="form-check-label" for="addOrgActive">Active</label>
            </div>
            <div class="text-end">
                <button class="btn btn-link-secondary" @click="showAdd = false">Cancel</button>
                <button class="btn btn-primary" @click="createOrganization">Save</button>
            </div>
        </BModal>

        <!-- Edit Organization -->
        <BModal v-model="showEdit" title="Edit Organization" hide-footer>
            <div class="alert alert-danger" v-if="error">{{ error }}</div>
            <div class="mb-2">
                <label class="form-label mb-1">Name</label>
                <input type="text" class="form-control" v-model="form.name">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Short Name</label>
                <input type="text" class="form-control" v-model="form.sname">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Organization Type</label>
                <select class="form-control" v-model="form.organization_type_id">
                    <option value="">— pilih tipe —</option>
                    <option v-for="type in types" :key="type.organization_type_id" :value="type.organization_type_id">
                        {{ type.name }}
                    </option>
                </select>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="editOrgActive" v-model="form.is_active">
                <label class="form-check-label" for="editOrgActive">Active</label>
            </div>
            <div class="text-end">
                <button class="btn btn-link-secondary" @click="showEdit = false">Cancel</button>
                <button class="btn btn-primary" @click="saveEdit">Save</button>
            </div>
        </BModal>

        <!-- View Organization -->
        <BModal v-model="showView" title="Organization Detail" hide-footer>
            <div v-if="viewRow">
                <p class="mb-2"><strong>Name:</strong> {{ viewRow.name }}</p>
                <p class="mb-2"><strong>Short Name:</strong> {{ viewRow.sname }}</p>
                <p class="mb-2"><strong>Organization Type:</strong> {{ viewRow.type?.name || '-' }}</p>
                <p class="mb-2"><strong>Organization Parent:</strong> {{ viewRow.parent?.name || '-' }}</p>
                <p class="mb-2"><strong>Active:</strong> {{ viewRow.is_active ? 'Yes' : 'No' }}</p>
            </div>
            <div class="text-end">
                <button class="btn btn-link-secondary" @click="showView = false">Close</button>
            </div>
        </BModal>
    </Layout>
</template>
