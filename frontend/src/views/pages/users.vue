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
            newUser: { name: "", username: "", email: "", password: "", phone_number: "", role_id: "" },
            editingUser: null,
            error: null,
            loading: false,
        }
    },
    mounted() {
        this.fetchUsers();
        this.fetchRoles();
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
                // ignore; the role dropdown will just be empty
            }
        },
        async createUser() {
            if (!this.newUser.name || !this.newUser.username || !this.newUser.email || !this.newUser.password) return;
            try {
                await api.post('/users', this.newUser);
                this.newUser = { name: "", username: "", email: "", password: "", phone_number: "", role_id: "" };
                this.fetchUsers();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create user.';
            }
        },
        startEdit(user) {
            this.editingUser = { ...user, role_id: user.role_id ?? "" };
        },
        async saveEdit() {
            try {
                await api.put(`/users/${this.editingUser.user_id}`, {
                    name: this.editingUser.name,
                    username: this.editingUser.username,
                    email: this.editingUser.email,
                    phone_number: this.editingUser.phone_number,
                    is_active: this.editingUser.is_active,
                    role_id: this.editingUser.role_id || null,
                });
                this.editingUser = null;
                this.fetchUsers();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update user.';
            }
        },
        async deleteUser(user) {
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
                <div class="alert alert-danger" v-if="error">{{ error }}</div>
                <div class="card">
                    <div class="card-body">
                        <form class="d-flex mb-3 gap-2 flex-wrap" @submit.prevent="createUser">
                            <input type="text" class="form-control" placeholder="Name"
                                v-model="newUser.name">
                            <input type="text" class="form-control" placeholder="Username"
                                v-model="newUser.username">
                            <input type="email" class="form-control" placeholder="Email"
                                v-model="newUser.email">
                            <input type="password" class="form-control" placeholder="Password (min 8)"
                                v-model="newUser.password">
                            <input type="text" class="form-control" placeholder="Phone"
                                v-model="newUser.phone_number">
                            <select class="form-control" v-model="newUser.role_id">
                                <option value="">— pilih role —</option>
                                <option v-for="role in roles" :key="role.role_id" :value="role.role_id">
                                    {{ role.name }}
                                </option>
                            </select>
                            <button type="submit" class="btn btn-primary">Add User</button>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Active</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in users" :key="user.user_id">
                                        <td>{{ user.user_id }}</td>
                                        <td>
                                            <input v-if="editingUser && editingUser.user_id === user.user_id"
                                                type="text" class="form-control" v-model="editingUser.name">
                                            <span v-else>{{ user.name }}</span>
                                        </td>
                                        <td>
                                            <input v-if="editingUser && editingUser.user_id === user.user_id"
                                                type="text" class="form-control" v-model="editingUser.username">
                                            <span v-else>{{ user.username }}</span>
                                        </td>
                                        <td>
                                            <input v-if="editingUser && editingUser.user_id === user.user_id"
                                                type="email" class="form-control" v-model="editingUser.email">
                                            <span v-else>{{ user.email }}</span>
                                        </td>
                                        <td>
                                            <input v-if="editingUser && editingUser.user_id === user.user_id"
                                                type="text" class="form-control" v-model="editingUser.phone_number">
                                            <span v-else>{{ user.phone_number }}</span>
                                        </td>
                                        <td>
                                            <select v-if="editingUser && editingUser.user_id === user.user_id"
                                                class="form-control" v-model="editingUser.role_id">
                                                <option value="">— pilih role —</option>
                                                <option v-for="role in roles" :key="role.role_id" :value="role.role_id">
                                                    {{ role.name }}
                                                </option>
                                            </select>
                                            <span v-else>{{ user.role?.name }}</span>
                                        </td>
                                        <td>
                                            <input v-if="editingUser && editingUser.user_id === user.user_id"
                                                type="checkbox" v-model="editingUser.is_active">
                                            <span v-else>{{ user.is_active ? 'Yes' : 'No' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <template v-if="editingUser && editingUser.user_id === user.user_id">
                                                <button class="btn btn-sm btn-success me-1" @click="saveEdit">Save</button>
                                                <button class="btn btn-sm btn-secondary" @click="editingUser = null">Cancel</button>
                                            </template>
                                            <template v-else>
                                                <button class="btn btn-sm btn-primary me-1" @click="startEdit(user)">Edit</button>
                                                <button class="btn btn-sm btn-danger" @click="deleteUser(user)">Delete</button>
                                            </template>
                                        </td>
                                    </tr>
                                    <tr v-if="!loading && users.length === 0">
                                        <td colspan="8" class="text-center text-muted">No users yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </BRow>
    </Layout>
</template>
