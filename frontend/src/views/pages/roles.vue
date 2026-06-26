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
            newRoleName: "",
            editingRole: null,
            error: null,
            loading: false,
        }
    },
    mounted() {
        this.fetchRoles();
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
        async createRole() {
            if (!this.newRoleName) return;
            try {
                await api.post('/roles', { name: this.newRoleName });
                this.newRoleName = "";
                this.fetchRoles();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create role.';
            }
        },
        startEdit(role) {
            this.editingRole = { ...role };
        },
        async saveEdit() {
            try {
                await api.put(`/roles/${this.editingRole.id}`, { name: this.editingRole.name });
                this.editingRole = null;
                this.fetchRoles();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update role.';
            }
        },
        async deleteRole(role) {
            try {
                await api.delete(`/roles/${role.id}`);
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
                <div class="alert alert-danger" v-if="error">{{ error }}</div>
                <div class="card">
                    <div class="card-body">
                        <form class="d-flex mb-3 gap-2" @submit.prevent="createRole">
                            <input type="text" class="form-control" placeholder="New role name" v-model="newRoleName">
                            <button type="submit" class="btn btn-primary">Add Role</button>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="role in roles" :key="role.id">
                                        <td>{{ role.id }}</td>
                                        <td>
                                            <input v-if="editingRole && editingRole.id === role.id" type="text"
                                                class="form-control" v-model="editingRole.name">
                                            <span v-else>{{ role.name }}</span>
                                        </td>
                                        <td class="text-end">
                                            <template v-if="editingRole && editingRole.id === role.id">
                                                <button class="btn btn-sm btn-success me-1" @click="saveEdit">Save</button>
                                                <button class="btn btn-sm btn-secondary" @click="editingRole = null">Cancel</button>
                                            </template>
                                            <template v-else>
                                                <button class="btn btn-sm btn-primary me-1" @click="startEdit(role)">Edit</button>
                                                <button class="btn btn-sm btn-danger" @click="deleteRole(role)">Delete</button>
                                            </template>
                                        </td>
                                    </tr>
                                    <tr v-if="!loading && roles.length === 0">
                                        <td colspan="3" class="text-center text-muted">No roles yet.</td>
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
