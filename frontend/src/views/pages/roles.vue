<script>
import Layout from "@/layout/main.vue"
import pageheader from "@/components/page-header.vue"
import api from "@/services/api"

export default {
    name: "ROLES",
    components: {
        Layout, pageheader
    },
    data() {
        return {
            roles: [],
            search: "",
            showFilter: false,
            filter: { active: "" },
            form: { role_id: null, name: "", sname: "", description: "", is_active: true },
            viewRow: null,
            showAdd: false,
            showEdit: false,
            showView: false,
            error: null,
            loading: false,
        }
    },
    mounted() {
        this.fetchRoles();
    },
    computed: {
        filteredRoles() {
            let rows = this.roles;

            if (this.filter.active === "active") {
                rows = rows.filter(r => r.is_active);
            } else if (this.filter.active === "inactive") {
                rows = rows.filter(r => !r.is_active);
            }

            const q = this.search.trim().toLowerCase();
            if (q) {
                rows = rows.filter(r =>
                    [r.name, r.description, r.is_active ? "active yes" : "inactive no"]
                        .join(" ").toLowerCase().includes(q)
                );
            }
            return rows;
        }
    },
    methods: {
        async fetchRoles() {
            this.loading = true;
            try {
                const { data } = await api.get('/roles');
                this.roles = data.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to load roles.';
            } finally {
                this.loading = false;
            }
        },
        openAdd() {
            this.error = null;
            this.form = { role_id: null, name: "", sname: "", description: "", is_active: true };
            this.showAdd = true;
        },
        openEdit(role) {
            this.error = null;
            this.form = { ...role };
            this.showEdit = true;
        },
        openView(role) {
            this.viewRow = role;
            this.showView = true;
        },
        async createRole() {
            if (!this.form.name || !this.form.sname) {
                this.error = 'Name and sname are required.';
                return;
            }
            try {
                await api.post('/roles', {
                    name: this.form.name,
                    sname: this.form.sname,
                    description: this.form.description,
                    is_active: this.form.is_active,
                });
                this.showAdd = false;
                this.fetchRoles();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create role.';
            }
        },
        async saveEdit() {
            try {
                // sname is intentionally NOT sent on update (backend rejects it,
                // and it is a stable authorization identifier).
                await api.put(`/roles/${this.form.role_id}`, {
                    name: this.form.name,
                    description: this.form.description,
                    is_active: this.form.is_active,
                });
                this.showEdit = false;
                this.fetchRoles();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update role.';
            }
        },
        async deleteRole(role) {
            if (!confirm(`Delete role "${role.name}"?`)) return;
            try {
                await api.delete(`/roles/${role.role_id}`);
                this.fetchRoles();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to delete role.';
            }
        },
    },
}
</script>

<template>
    <Layout>
        <pageheader title="Roles" pageTitle="User & Role Management" />
        <BRow>
            <div class="col-sm-12">
                <div class="alert alert-danger" v-if="error && !showAdd && !showEdit">{{ error }}</div>
                <div class="card table-card">
                    <div class="card-header">
                        <div class="d-sm-flex align-items-center justify-content-between">
                            <h5 class="mb-3 mb-sm-0">Role Management</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <input type="text" class="form-control" style="min-width: 220px"
                                    placeholder="Search..." v-model="search">
                                <button class="btn btn-outline-secondary" @click="showFilter = !showFilter">
                                    <i class="ti ti-filter f-18"></i> Filter
                                </button>
                                <button class="btn btn-primary" @click="openAdd">
                                    <i class="ti ti-plus f-18"></i> Add Role
                                </button>
                            </div>
                        </div>
                        <div v-if="showFilter" class="mt-3">
                            <div class="row g-2">
                                <div class="col-sm-4">
                                    <label class="form-label mb-1">Status</label>
                                    <select class="form-control" v-model="filter.active">
                                        <option value="">Semua</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 60px">#</th>
                                        <th>Nama</th>
                                        <th>Description</th>
                                        <th>Active</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(role, index) in filteredRoles" :key="role.role_id">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ role.name }}</td>
                                        <td>{{ role.description }}</td>
                                        <td>
                                            <span v-if="role.is_active" class="badge bg-light-success">Active</span>
                                            <span v-else class="badge bg-light-danger">Inactive</span>
                                        </td>
                                        <td>
                                            <ul class="list-inline mb-0 text-end">
                                                <li class="list-inline-item m-0">
                                                    <a href="#" class="avtar avtar-s btn btn-warning"
                                                        @click.prevent="openView(role)">
                                                        <i class="ti ti-eye f-18"></i>
                                                    </a>
                                                </li>
                                                <li class="list-inline-item m-0">
                                                    <a href="#" class="avtar avtar-s btn btn-primary"
                                                        @click.prevent="openEdit(role)">
                                                        <i class="ti ti-pencil f-18"></i>
                                                    </a>
                                                </li>
                                                <li class="list-inline-item m-0">
                                                    <a href="#" class="avtar avtar-s btn bg-white btn-link-danger"
                                                        @click.prevent="deleteRole(role)">
                                                        <i class="ti ti-trash f-18"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr v-if="!loading && filteredRoles.length === 0">
                                        <td colspan="5" class="text-center text-muted">No roles found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </BRow>

        <!-- Add Role -->
        <BModal v-model="showAdd" title="Add Role" hide-footer>
            <div class="alert alert-danger" v-if="error">{{ error }}</div>
            <div class="mb-2">
                <label class="form-label mb-1">Name</label>
                <input type="text" class="form-control" placeholder="e.g. ADMIN AREA" v-model="form.name">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Sname (identifier, tidak bisa diubah nanti)</label>
                <input type="text" class="form-control" placeholder="e.g. admin_are" v-model="form.sname">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Description</label>
                <input type="text" class="form-control" placeholder="Description" v-model="form.description">
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="addRoleActive" v-model="form.is_active">
                <label class="form-check-label" for="addRoleActive">Active</label>
            </div>
            <div class="text-end">
                <button class="btn btn-link-secondary" @click="showAdd = false">Cancel</button>
                <button class="btn btn-primary" @click="createRole">Save</button>
            </div>
        </BModal>

        <!-- Edit Role -->
        <BModal v-model="showEdit" title="Edit Role" hide-footer>
            <div class="alert alert-danger" v-if="error">{{ error }}</div>
            <div class="mb-2">
                <label class="form-label mb-1">Name</label>
                <input type="text" class="form-control" v-model="form.name">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Description</label>
                <input type="text" class="form-control" v-model="form.description">
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="editRoleActive" v-model="form.is_active">
                <label class="form-check-label" for="editRoleActive">Active</label>
            </div>
            <div class="text-end">
                <button class="btn btn-link-secondary" @click="showEdit = false">Cancel</button>
                <button class="btn btn-primary" @click="saveEdit">Save</button>
            </div>
        </BModal>

        <!-- View Role -->
        <BModal v-model="showView" title="Role Detail" hide-footer>
            <div v-if="viewRow">
                <p class="mb-2"><strong>Nama:</strong> {{ viewRow.name }}</p>
                <p class="mb-2"><strong>Description:</strong> {{ viewRow.description || '-' }}</p>
                <p class="mb-2"><strong>Active:</strong> {{ viewRow.is_active ? 'Yes' : 'No' }}</p>
            </div>
            <div class="text-end">
                <button class="btn btn-link-secondary" @click="showView = false">Close</button>
            </div>
        </BModal>
    </Layout>
</template>
