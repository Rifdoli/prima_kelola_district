<template>
    <Layout>
        <pageheader title="Helpdesk" pageTitle="Create Ticket" />
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-none">
                    <div class="card-header">
                        <h5>Customers</h5>
                        <div class="card-header-right">
                            <button type="button" class="btn btn-light-warning m-0" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                New Customer
                            </button>
                            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel"><i data-feather="user" class="icon-svg-primary wid-20 me-2"></i>Add Customer</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form>
                                            <div class="modal-body ">
                                                <small id="emailHelp" class="form-text text-muted mb-2 mt-0">
                                                    We'll never share your email with anyone else.
                                                </small>
                                                <div class="mb-3">
                                                    <label class="form-label">First Name</label>
                                                    <input type="text" class="form-control" id="fname" aria-describedby="emailHelp" placeholder="Enter First Name" />
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Last Name</label>
                                                    <input type="email" class="form-control" id="lname" aria-describedby="emailHelp" placeholder="Enter Last Name" />
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Email address</label>
                                                    <input type="email" class="form-control" id="emial" aria-describedby="emailHelp" placeholder="Enter email" />
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Password</label>
                                                    <input type="password" class="form-control" id="passwd" placeholder="Password" />
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Confirm Password</label>
                                                    <input type="password" class="form-control" id="cnpasswd" placeholder="Confirm Password" />
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light-danger" data-bs-dismiss="modal">
                                                    Close
                                                </button>
                                                <button type="button" class="btn btn-light-primary">
                                                    Save changes
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body shadow border-0">
                        <div class="table-responsive">
                            <table id="report-table" class="table table-bordered table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th class="border-top-0">Name</th>
                                        <th class="border-top-0">Email</th>
                                        <th class="border-top-0">Account</th>
                                        <th class="border-top-0">Last Login</th>
                                        <th class="border-top-0"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="customer in customers" :key="customer.id">
                                        <td>{{ customer.name }}</td>
                                        <td><a :href="`mailto:${customer.email}`" class="link-secondary">{{ customer.email }}</a></td>
                                        <td>{{ customer.account }}</td>
                                        <td>{{ customer.lastLogin }}</td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-light-success me-1"><i class="feather icon-edit"></i></a>
                                            <a href="#" class="btn btn-sm btn-light-danger"><i class="feather icon-trash-2"></i></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue';
import Layout from '@/layout/main.vue';
import pageheader from '@/components/page-header.vue';

export default defineComponent({
    name: 'Helpdesk',
    components: {
        Layout,
        pageheader
    },
    setup() {
        const customers = ref([
            // { id: 1, name: 'Mark Jason', email: 'mark@mark.com', account: 'N/A', lastLogin: 'January 01, 2019 at 03:35 PM' },
            {
                id: 1,
                name: "Mark Jason",
                email: "mark@mark.com",
                account: "N/A",
                lastLogin: "January 01, 2019 at 03:35 PM"
            },
            {
                id: 2,
                name: "Alice Nicol",
                email: "alice@nicol.com",
                account: "N/A",
                lastLogin: "January 02, 2019 at 04:45 PM"
            },
            {
                id: 3,
                name: "Harry Cook",
                email: "harry@cook.com",
                account: "N/A",
                lastLogin: "January 03, 2019 at 05:55 PM"
            },
            {
                id: 4,
                name: "Tom Hannry",
                email: "tom@hannry.com",
                account: "N/A",
                lastLogin: "January 04, 2019 at 06:55 PM"
            },
            {
                id: 5,
                name: "Martin Frank",
                email: "martin@frank.com",
                account: "N/A",
                lastLogin: "January 05, 2019 at 07:55 PM"
            },
            {
                id: 6,
                name: "Endrew Khan",
                email: "endrew@khan.com",
                account: "N/A",
                lastLogin: "January 06, 2019 at 08:55 PM"
            },
            {
                id: 7,
                name: "Chritina Methewv",
                email: "chritina@methewv.com",
                account: "N/A",
                lastLogin: "January 07, 2019 at 09:55 PM"
            },
            {
                id: 8,
                name: "Jakson Pit",
                email: "jakson@pit.com",
                account: "N/A",
                lastLogin: "January 08, 2019 at 10:55 PM"
            },
            {
                id: 9,
                name: "Nikolas Jons",
                email: "nikolas@jons.com",
                account: "N/A",
                lastLogin: "January 09, 2019 at 11:55 PM"
            },
            {
                id: 10,
                name: "Nik Cage",
                email: "nik@cage.com",
                account: "N/A",
                lastLogin: "January 10, 2019 at 12:55 PM"
            }
            // Add more customer data as needed
        ]);

        // Simulating an API call to fetch customer data
        const fetchCustomers = () => {
            // Here you would typically make an API call to fetch customer data
            // and update the `customers` ref with the response
        };

        onMounted(() => {
            fetchCustomers(); // Fetch customers when the component is mounted
        });

        return { customers };
    },
});
</script>

<style scoped>
/* add your scoped styles here */
</style>
