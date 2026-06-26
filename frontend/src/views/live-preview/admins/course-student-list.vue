<template>
    <Layout>
        <pageheader title="Student List" pageTitle="Online Courses" />
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-12">
                <div class="card table-card">
                    <div class="card-header">
                        <div class="d-sm-flex align-items-center justify-content-between">
                            <h5 class="mb-3 mb-sm-0">Student list</h5>
                            <div>
                                <router-link to="/admins/course-student-apply" class="btn btn-outline-secondary me-1">Apply Student List</router-link>
                                <router-link to="/admins/course-student-add" class="btn btn-primary">Add Student</router-link>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <div class="table-responsive">
                            <table class="table table-hover" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>Qualification</th>
                                        <th>Email</th>
                                        <th>Admission Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(student, index) in students" :key="index">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <img :src="student.avatar" alt="user image" class="img-radius wid-40">
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0">{{ student.name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ student.mobile }}</td>
                                        <td>{{ student.qualification }}</td>
                                        <td>{{ student.email }}</td>
                                        <td>{{ student.admissionDate }}</td>
                                        <td>
                                            <a href="#" class="avtar avtar-xs btn-link-secondary me-2">
                                                <i class="ti ti-eye f-20"></i>
                                            </a>
                                            <a href="#" class="avtar avtar-xs btn-link-secondary me-2">
                                                <i class="ti ti-edit f-20"></i>
                                            </a>
                                            <a href="#" class="avtar avtar-xs btn-link-secondary me-2">
                                                <i class="ti ti-trash f-20"></i>
                                            </a>
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
import { ref } from 'vue';
import Layout from "@/layout/main.vue"
import pageheader from "@/components/page-header.vue"

import avatar1 from "@/assets/images/user/avatar-1.jpg"
import avatar2 from "@/assets/images/user/avatar-2.jpg"
import avatar3 from "@/assets/images/user/avatar-3.jpg"
import avatar4 from "@/assets/images/user/avatar-4.jpg"
import avatar5 from "@/assets/images/user/avatar-5.jpg"
import avatar6 from "@/assets/images/user/avatar-6.jpg"
import avatar7 from "@/assets/images/user/avatar-7.jpg"
import avatar8 from "@/assets/images/user/avatar-8.jpg"
import avatar9 from "@/assets/images/user/avatar-9.jpg"
import avatar10 from "@/assets/images/user/avatar-10.jpg"

export default {
    name: "StudentAdd",
    components: {
        Layout,
        pageheader
    },
    setup() {
        const students = ref([
            {
                name: "Airi Satou",
                mobile: "(123) 4567 890",
                qualification: "B.COM., M.COM.",
                email: "Info@123.com",
                admissionDate: "2023/09/12",
                avatar: avatar1
            },
            {
                name: "Ashton Cox",
                mobile: "(123) 4567 890",
                qualification: "B.COM., M.COM.",
                email: "Info@123.com",
                admissionDate: "2023/12/24",
                avatar: avatar2
            },
            {
                name: "Bradley Greer",
                mobile: "(123) 4567 890",
                qualification: "B.A, B.C.A",
                email: "Info@123.com",
                admissionDate: "2022/09/19",
                avatar: avatar3
            },
            {
                name: "Brielle Williamson",
                mobile: "(123) 4567 890",
                qualification: "B.A, B.C.A",
                email: "Info@123.com",
                admissionDate: "2022/08/22",
                avatar: avatar4
            },
            {
                name: "Airi Satou",
                mobile: "(123) 4567 890",
                qualification: "B.COM., M.COM.",
                email: "Info@123.com",
                admissionDate: "2023/09/12",
                avatar: avatar5
            },
            {
                name: "Ashton Cox",
                mobile: "(123) 4567 890",
                qualification: "B.COM., M.COM.",
                email: "Info@123.com",
                admissionDate: "2023/12/24",
                avatar: avatar6
            },
            {
                name: "Bradley Greer",
                mobile: "(123) 4567 890",
                qualification: "B.A, B.C.A",
                email: "Info@123.com",
                admissionDate: "2022/09/19",
                avatar: avatar7
            },
            {
                name: "Brielle Williamson",
                mobile: "(123) 4567 890",
                qualification: "B.A, B.C.A",
                email: "Info@123.com",
                admissionDate: "2022/08/22",
                avatar: avatar8
            },
            {
                name: "Brielle Williamson",
                mobile: "(123) 4567 890",
                qualification: "B.A, B.C.A",
                email: "Info@123.com",
                admissionDate: "2022/08/22",
                avatar: avatar9
            },
            {
                name: "Airi Satou",
                mobile: "(123) 4567 890",
                qualification: "B.COM., M.COM.",
                email: "Info@123.com",
                admissionDate: "2023/09/12",
                avatar: avatar10
            }
        ]
);

        return {
            students
        };
    }
};
</script>

<style scoped>
/* Add your scoped styles here */
</style>
