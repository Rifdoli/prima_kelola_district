<script>
import Layout from "@/layout/main.vue"
import pageheader from "@/components/page-header.vue"
import api from "@/services/api"

export default {
    name: "USERS",
    components: {
        Layout, pageheader
    },
    data() {
        return {
            users: [],
            roles: [],
            search: "",
            showFilter: false,
            filter: { role_id: "", active: "", ldap: "" },
            form: {
                user_id: null, name: "", username: "", email: "",
                password: "", phone_number: "", role_id: "", is_active: true,
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

            const q = this.search.trim().toLowerCase();
            if (q) {
                rows = rows.filter(u =>
                    [
                        u.username, u.name, u.role?.name,
                        u.is_ldap ? "ldap yes" : "no",
                        u.is_active ? "active yes" : "inactive no",
                    ].join(" ").toLowerCase().includes(q)
                );
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
        openAdd() {
            this.error = null;
            this.form = {
                user_id: null, name: "", username: "", email: "",
                password: "", phone_number: "", role_id: "", is_active: true,
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
                    phone_number: this.form.phone_number,
                    role_id: this.form.role_id || null,
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
        <pageheader title="Users" pageTitle="User & Role Management" />
        <BRow>
            <div class="col-sm-12">
                <div class="alert alert-danger" v-if="error && !showAdd && !showEdit">{{ error }}</div>
                <div class="card table-card">
                    <div class="card-header">
                        <div class="d-sm-flex align-items-center justify-content-between">
                            <h5 class="mb-3 mb-sm-0">User Management</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <input type="text" class="form-control" style="min-width: 220px"
                                    placeholder="Search..." v-model="search">
                                <button class="btn btn-outline-secondary" @click="showFilter = !showFilter">
                                    <i class="ti ti-filter f-18"></i> Filter
                                </button>
                                <button class="btn btn-primary" @click="openAdd">
                                    <i class="ti ti-plus f-18"></i> Add User
                                </button>
                            </div>
                        </div>
                        <div v-if="showFilter" class="mt-3">
                            <div class="row g-2">
                                <div class="col-sm-4">
                                    <label class="form-label mb-1">Role</label>
                                    <select class="form-control" v-model="filter.role_id">
                                        <option value="">Semua</option>
                                        <option v-for="role in roles" :key="role.role_id" :value="role.role_id">
                                            {{ role.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label mb-1">Status</label>
                                    <select class="form-control" v-model="filter.active">
                                        <option value="">Semua</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label mb-1">LDAP</label>
                                    <select class="form-control" v-model="filter.ldap">
                                        <option value="">Semua</option>
                                        <option value="yes">Ya</option>
                                        <option value="no">Tidak</option>
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
                                        <th>Username</th>
                                        <th>Nama</th>
                                        <th>Organisasi</th>
                                        <th>Role</th>
                                        <th>LDAP</th>
                                        <th>Active</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(user, index) in filteredUsers" :key="user.user_id">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ user.username }}</td>
                                        <td>{{ user.name }}</td>
                                        <!-- TODO: tampilkan nama organisasi setelah modul Organizations dibuat -->
                                        <td>-</td>
                                        <td>{{ user.role?.name }}</td>
                                        <td>
                                            <span v-if="user.is_ldap" class="badge bg-light-success">Ya</span>
                                            <span v-else class="badge bg-light-secondary">Tidak</span>
                                        </td>
                                        <td>
                                            <span v-if="user.is_active" class="badge bg-light-success">Active</span>
                                            <span v-else class="badge bg-light-danger">Inactive</span>
                                        </td>
                                        <td>
                                            <ul class="list-inline mb-0 text-end">
                                                <li class="list-inline-item m-0">
                                                    <a href="#" class="avtar avtar-s btn btn-warning"
                                                        @click.prevent="openView(user)">
                                                        <i class="ti ti-eye f-18"></i>
                                                    </a>
                                                </li>
                                                <li class="list-inline-item m-0">
                                                    <a href="#" class="avtar avtar-s btn btn-primary"
                                                        @click.prevent="openEdit(user)">
                                                        <i class="ti ti-pencil f-18"></i>
                                                    </a>
                                                </li>
                                                <li class="list-inline-item m-0">
                                                    <a href="#" class="avtar avtar-s btn bg-white btn-link-danger"
                                                        @click.prevent="deleteUser(user)">
                                                        <i class="ti ti-trash f-18"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr v-if="!loading && filteredUsers.length === 0">
                                        <td colspan="8" class="text-center text-muted">No users found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
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
    </Layout>
</template>
