<script>
import Layout from "@/layout/main.vue"
import pageheader from "@/components/page-header.vue"
import AdminDataTable from "@/components/common/AdminDataTable.vue"
import RowActions from "@/components/common/RowActions.vue"
import api from "@/services/api"

export default {
    name: "USERS",
    components: {
        Layout, pageheader, AdminDataTable, RowActions,
    },
    data() {
        return {
            users: [],
            roles: [],
            organizations: [],
            showFilter: false,
            filter: { role_id: "", active: "", ldap: "" },
            columns: [
                { key: "username", label: "Username", sortable: true },
                { key: "name", label: "Nama", sortable: true },
                { key: "organisasi", label: "Organisasi", sortable: false },
                { key: "role.name", label: "Role", sortable: true },
                { key: "is_ldap", label: "LDAP", sortable: true },
                { key: "is_active", label: "Active", sortable: true },
            ],
            form: {
                user_id: null, name: "", username: "", email: "",
                password: "", phone_number: "", role_id: "", is_active: true,
                nik: "", is_ldap: false, organization_id: "",
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
        this.fetchUsers();
        this.fetchRoles();
        this.fetchOrganizations();
    },
    computed: {
        filteredUsers() {
            let rows = this.users;

            if (this.filter.role_id) {
                rows = rows.filter(u => String(u.role_id) === String(this.filter.role_id));
            }
            if (this.filter.active === "active") {
                rows = rows.filter(u => u.is_active);
            } else if (this.filter.active === "inactive") {
                rows = rows.filter(u => !u.is_active);
            }
            if (this.filter.ldap === "yes") {
                rows = rows.filter(u => u.is_ldap);
            } else if (this.filter.ldap === "no") {
                rows = rows.filter(u => !u.is_ldap);
            }

            return rows;
        }
    },
    methods: {
        async fetchUsers() {
            this.loading = true;
            try {
                const { data } = await api.get('/users');
                this.users = data.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to load users.';
            } finally {
                this.loading = false;
            }
        },
        async fetchRoles() {
            try {
                const { data } = await api.get('/roles');
                this.roles = data.data;
            } catch (error) {
                // ignore; the role dropdown/filter will just be empty
            }
        },
        async fetchOrganizations() {
            try {
                const { data } = await api.get('/organizations');
                this.organizations = data.data;
            } catch (error) {
                // ignore; the organization dropdown will just be empty
            }
        },
        openAdd() {
            this.error = null;
            this.form = {
                user_id: null, name: "", username: "", email: "",
                password: "", phone_number: "", role_id: "", is_active: true,
                nik: "", is_ldap: false, organization_id: "",
            };
            this.showAdd = true;
        },
        openEdit(user) {
            this.error = null;
            this.form = { ...user, role_id: user.role_id ?? "" };
            this.showEdit = true;
        },
        openView(user) {
            this.viewRow = user;
            this.showView = true;
        },
        async createUser() {
            if (!this.form.name || !this.form.username || !this.form.email || !this.form.password) {
                this.error = 'Name, username, email, and password are required.';
                return;
            }
            try {
                await api.post('/users', {
                    name: this.form.name,
                    username: this.form.username,
                    email: this.form.email,
                    password: this.form.password,
                    nik: this.form.nik || null,
                    phone_number: this.form.phone_number,
                    is_ldap: this.form.is_ldap,
                    role_id: this.form.role_id || null,
                    organization_id: this.form.organization_id || null,
                });
                this.showAdd = false;
                this.fetchUsers();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create user.';
            }
        },
        async saveEdit() {
            try {
                // password is intentionally NOT sent on update (backend rejects it).
                await api.put(`/users/${this.form.user_id}`, {
                    name: this.form.name,
                    username: this.form.username,
                    email: this.form.email,
                    phone_number: this.form.phone_number,
                    is_active: this.form.is_active,
                    role_id: this.form.role_id || null,
                });
                this.showEdit = false;
                this.fetchUsers();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update user.';
            }
        },
        resetFilter() {
            this.filter = { role_id: "", active: "", ldap: "" };
        },
        async deleteUser(user) {
            if (!confirm(`Delete user "${user.username}"?`)) return;
            try {
                await api.delete(`/users/${user.user_id}`);
                this.fetchUsers();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to delete user.';
            }
        },
    },
}
</script>

<template>
    <Layout>
        <pageheader title="User Management" pageTitle="Administration" />
        <BRow>
            <div class="col-sm-12">
                <div class="alert alert-danger" v-if="error && !showAdd && !showEdit">{{ error }}</div>

                <AdminDataTable title="User Table" :columns="columns" :rows="filteredUsers" :loading="loading"
                    :search-keys="['username', 'name', 'role.name']">
                    <template #header-actions>
                        <button class="btn btn-outline-secondary" @click="showFilter = true">
                            <i class="ti ti-filter f-18"></i> Filter
                        </button>
                        <button class="btn btn-primary" @click="openAdd">
                            <i class="ti ti-plus f-18"></i> Add User
                        </button>
                    </template>
                    <template #cell-organisasi>
                        <!-- TODO: tampilkan nama organisasi setelah modul Organizations dibuat -->
                        -
                    </template>
                    <template #cell-role-name="{ row }">
                        {{ row.role?.name }}
                    </template>
                    <template #cell-is_ldap="{ row }">
                        <span v-if="row.is_ldap" class="badge bg-light-success">Ya</span>
                        <span v-else class="badge bg-light-secondary">Tidak</span>
                    </template>
                    <template #cell-is_active="{ row }">
                        <span v-if="row.is_active" class="badge bg-light-success">Active</span>
                        <span v-else class="badge bg-light-danger">Inactive</span>
                    </template>
                    <template #actions="{ row }">
                        <RowActions :row="row" @view="openView" @edit="openEdit" @delete="deleteUser" />
                    </template>
                </AdminDataTable>
            </div>
        </BRow>

        <!-- Add User -->
        <BModal v-model="showAdd" title="Add User" hide-footer>
            <div class="alert alert-danger" v-if="error">{{ error }}</div>
            <div class="mb-2">
                <label class="form-label mb-1">Username</label>
                <input type="text" class="form-control" v-model="form.username">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Name</label>
                <input type="text" class="form-control" v-model="form.name">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Email</label>
                <input type="email" class="form-control" v-model="form.email">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">NIK</label>
                <input type="text" class="form-control" v-model="form.nik" maxlength="16">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Password (min 8)</label>
                <input type="password" class="form-control" v-model="form.password">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Phone</label>
                <input type="text" class="form-control" v-model="form.phone_number">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Role</label>
                <select class="form-control" v-model="form.role_id">
                    <option value="">— pilih role —</option>
                    <option v-for="role in roles" :key="role.role_id" :value="role.role_id">{{ role.name }}</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Organisasi</label>
                <select class="form-control" v-model="form.organization_id">
                    <option value="">— pilih organisasi —</option>
                    <option v-for="org in organizations" :key="org.organization_id" :value="org.organization_id">
                        {{ org.name }}
                    </option>
                </select>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="addUserLdap" v-model="form.is_ldap">
                <label class="form-check-label" for="addUserLdap">LDAP</label>
            </div>
            <div class="text-end">
                <button class="btn btn-link-secondary" @click="showAdd = false">Cancel</button>
                <button class="btn btn-primary" @click="createUser">Save</button>
            </div>
        </BModal>

        <!-- Edit User -->
        <BModal v-model="showEdit" title="Edit User" hide-footer>
            <div class="alert alert-danger" v-if="error">{{ error }}</div>
            <div class="mb-2">
                <label class="form-label mb-1">Username</label>
                <input type="text" class="form-control" v-model="form.username">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Name</label>
                <input type="text" class="form-control" v-model="form.name">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Email</label>
                <input type="email" class="form-control" v-model="form.email">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Phone</label>
                <input type="text" class="form-control" v-model="form.phone_number">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Role</label>
                <select class="form-control" v-model="form.role_id">
                    <option value="">— pilih role —</option>
                    <option v-for="role in roles" :key="role.role_id" :value="role.role_id">{{ role.name }}</option>
                </select>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="editUserActive" v-model="form.is_active">
                <label class="form-check-label" for="editUserActive">Active</label>
            </div>
            <div class="text-end">
                <button class="btn btn-link-secondary" @click="showEdit = false">Cancel</button>
                <button class="btn btn-primary" @click="saveEdit">Save</button>
            </div>
        </BModal>

        <!-- View User -->
        <BModal v-model="showView" title="User Detail" hide-footer>
            <div v-if="viewRow">
                <p class="mb-2"><strong>Username:</strong> {{ viewRow.username }}</p>
                <p class="mb-2"><strong>Nama:</strong> {{ viewRow.name }}</p>
                <p class="mb-2"><strong>Email:</strong> {{ viewRow.email }}</p>
                <p class="mb-2"><strong>Phone:</strong> {{ viewRow.phone_number || '-' }}</p>
                <p class="mb-2"><strong>Role:</strong> {{ viewRow.role?.name || '-' }}</p>
                <p class="mb-2"><strong>LDAP:</strong> {{ viewRow.is_ldap ? 'Ya' : 'Tidak' }}</p>
                <p class="mb-2"><strong>Active:</strong> {{ viewRow.is_active ? 'Yes' : 'No' }}</p>
            </div>
            <div class="text-end">
                <button class="btn btn-link-secondary" @click="showView = false">Close</button>
            </div>
        </BModal>

        <!-- Filter -->
        <BModal v-model="showFilter" title="Filter" hide-footer>
            <div class="mb-2">
                <label class="form-label mb-1">Role</label>
                <select class="form-control" v-model="filter.role_id">
                    <option value="">Semua</option>
                    <option v-for="role in roles" :key="role.role_id" :value="role.role_id">
                        {{ role.name }}
                    </option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Status</label>
                <select class="form-control" v-model="filter.active">
                    <option value="">Semua</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">LDAP</label>
                <select class="form-control" v-model="filter.ldap">
                    <option value="">Semua</option>
                    <option value="yes">Ya</option>
                    <option value="no">Tidak</option>
                </select>
            </div>
            <div class="text-end">
                <button class="btn btn-link-secondary" @click="resetFilter">Reset</button>
                <button class="btn btn-primary" @click="showFilter = false">Apply</button>
            </div>
        </BModal>
    </Layout>
</template>
