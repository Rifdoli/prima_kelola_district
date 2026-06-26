<script>
import Layout from "@/layout/main.vue"
import pageheader from "@/components/page-header.vue"
import { ref } from 'vue';
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
    name: "Notifications",
    components: {
        Layout, pageheader
    },
    setup() {
        const teachers = ref([
            {
                id: 1,
                name: "Airi Satou",
                image: avatar1,
                department: "Developer",
                qualification: "B.COM., M.COM.",
                mobile: "(123) 4567 890",
                joiningDate: "2023/09/12"
            },
            {
                id: 2,
                name: "Ashton Cox",
                image: avatar2,
                department: "Junior Technical",
                qualification: "B.COM., M.COM.",
                mobile: "(123) 4567 890",
                joiningDate: "2023/12/24"
            },
            {
                id: 3,
                name: "Bradley Greer",
                image: avatar3,
                department: "Sales Assistant",
                qualification: "B.A, B.C.A",
                mobile: "(123) 4567 890",
                joiningDate: "2022/09/19"
            },
            {
                id: 4,
                name: "Brielle Williamson",
                image: avatar4,
                department: "JavaScript Developer",
                qualification: "B.A, B.C.A",
                mobile: "(123) 4567 890",
                joiningDate: "2022/08/22"
            },
            {
                id: 5,
                name: "Airi Satou",
                image: avatar5,
                department: "Developer",
                qualification: "B.COM., M.COM.",
                mobile: "(123) 4567 890",
                joiningDate: "2023/09/12"
            },
            {
                id: 6,
                name: "Ashton Cox",
                image: avatar6,
                department: "Junior Technical",
                qualification: "B.COM., M.COM.",
                mobile: "(123) 4567 890",
                joiningDate: "2023/12/24"
            },
            {
                id: 7,
                name: "Bradley Greer",
                image: avatar7,
                department: "Sales Assistant",
                qualification: "B.A, B.C.A",
                mobile: "(123) 4567 890",
                joiningDate: "2022/09/19"
            },
            {
                id: 8,
                name: "Brielle Williamson",
                image: avatar8,
                department: "JavaScript Developer",
                qualification: "B.A, B.C.A",
                mobile: "(123) 4567 890",
                joiningDate: "2022/08/22"
            },
            {
                id: 9,
                name: "Brielle Williamson",
                image: avatar9,
                department: "JavaScript Developer",
                qualification: "B.A, B.C.A",
                mobile: "(123) 4567 890",
                joiningDate: "2022/08/22"
            },
            {
                id: 10,
                name: "Brielle Williamson",
                image: avatar10,
                department: "JavaScript Developer",
                qualification: "B.A, B.C.A",
                mobile: "(123) 4567 890",
                joiningDate: "2022/08/22"
            },
            {
                id: 11,
                name: "Brielle Williamson",
                image: avatar9,
                department: "JavaScript Developer",
                qualification: "B.A, B.C.A",
                mobile: "(123) 4567 890",
                joiningDate: "2022/08/22"
            },
            {
                id: 12,
                name: "Airi Satou",
                image: avatar10,
                department: "Developer",
                qualification: "B.COM., M.COM.",
                mobile: "(123) 4567 890",
                joiningDate: "2023/09/12"
            },
            {
                id: 13,
                name: "Ashton Cox",
                image: avatar2,
                department: "Junior Technical",
                qualification: "B.COM., M.COM.",
                mobile: "(123) 4567 890",
                joiningDate: "2023/12/24"
            },
            {
                id: 14,
                name: "Bradley Greer",
                image: avatar3,
                department: "Sales Assistant",
                qualification: "B.A, B.C.A",
                mobile: "(123) 4567 890",
                joiningDate: "2022/09/19"
            },
            {
                id: 15,
                name: "Brielle Williamson",
                image: avatar4,
                department: "JavaScript Developer",
                qualification: "B.A, B.C.A",
                mobile: "(123) 4567 890",
                joiningDate: "2022/08/22"
            },
            {
                id: 16,
                name: "Airi Satou",
                image: avatar5,
                department: "Developer",
                qualification: "B.COM., M.COM.",
                mobile: "(123) 4567 890",
                joiningDate: "2023/09/12"
            },
            {
                id: 17,
                name: "Ashton Cox",
                image: avatar6,
                department: "Junior Technical",
                qualification: "B.COM., M.COM.",
                mobile: "(123) 4567 890",
                joiningDate: "2023/12/24"
            },
            {
                id: 18,
                name: "Bradley Greer",
                image: avatar7,
                department: "Sales Assistant",
                qualification: "B.A, B.C.A",
                mobile: "(123) 4567 890",
                joiningDate: "2022/09/19"
            }

        ]);

        return { teachers };
    }
}
</script>

<template>
    <Layout>
        <pageheader title="Notifications" pageTitle="Online Courses" />
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-12">
                <div class="card table-card">
                    <div class="card-header">
                        <div class="d-sm-flex align-items-center justify-content-between">
                            <h5 class="mb-3 mb-sm-0">Teacher list</h5>
                            <div>
                                <a href="/admins/course-teacher-apply" class="btn btn-outline-secondary me-1">Apply Teacher List</a>
                                <a href="/admins/course-teacher-add" class="btn btn-primary">Add Teacher</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <div class="table-responsive">
                            <table class="table table-hover" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Departments</th>
                                        <th>Qualification</th>
                                        <th>Mobile</th>
                                        <th>Joining Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="teacher in teachers" :key="teacher.id">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <img :src="teacher.image" alt="user image" class="img-radius wid-40">
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0">{{ teacher.name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ teacher.department }}</td>
                                        <td>{{ teacher.qualification }}</td>
                                        <td>{{ teacher.mobile }}</td>
                                        <td>{{ teacher.joiningDate }}</td>
                                        <td>
                                            <a href="#" class="avtar avtar-xs btn-link-secondary">
                                                <i class="ti ti-eye f-20"></i>
                                            </a>
                                            <a href="#" class="avtar avtar-xs btn-link-secondary">
                                                <i class="ti ti-edit f-20"></i>
                                            </a>
                                            <a href="#" class="avtar avtar-xs btn-link-secondary">
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