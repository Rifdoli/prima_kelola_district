<template>
    <Layout>
        <pageheader title="Teacher Apply" pageTitle="Online Courses" />
        <BRow>
            <BCol cols="12">
                <BCard class="table-card">
                    <BCardHeader >
                        <h5 class="mb-0">Apply Teacher list</h5>
                    </BCardHeader>
                    <BCardBody class="pt-3">
                        <div class="table-responsive">
                            <table class="table table-hover" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Departments</th>
                                        <th>Qualification</th>
                                        <th>Mobile</th>
                                        <th>Date/Time</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in data" :key="index">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <img :src="item.image" alt="user image" class="img-radius wid-40">
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0">{{ item.name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ item.department }}</td>
                                        <td>{{ item.qualification }}</td>
                                        <td>{{ item.mobile }}</td>
                                        <td>{{ item.dateTime }}</td>
                                        <td>
                                            <a href="#" class="avtar avtar-xs btn-light-success me-1">
                                                <i class="ti ti-check f-20"></i>
                                            </a>
                                            <a href="#" class="avtar avtar-xs btn-light-danger me-1">
                                                <i class="ti ti-x f-20"></i>
                                            </a>
                                            <a href="#" class="avtar avtar-xs btn-light-secondary me-1">
                                                <i class="ti ti-dots-vertical f-20"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

<script>
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
    components: {
        Layout, pageheader,
    },
    data() {
        return {
            
            data: [
                {
                    image: avatar1,
                    name: "Airi Satou",
                    department: "Developer",
                    qualification: "B.COM., M.COM.",
                    mobile: "(123) 4567 890",
                    dateTime: "2023/09/12 09:05 PM"
                },
                {
                    image: avatar2,
                    name: "Ashton Cox",
                    department: "Junior Technical",
                    qualification: "B.COM., M.COM.",
                    mobile: "(123) 4567 890",
                    dateTime: "2023/12/24 09:05 PM"
                },
                {
                    image: avatar3,
                    name: "Bradley Greer",
                    department: "Sales Assistant",
                    qualification: "B.A, B.C.A",
                    mobile: "(123) 4567 890",
                    dateTime: "2022/09/19 09:05 PM"
                },
                {
                    image: avatar4,
                    name: "Brielle Williamson",
                    department: "JavaScript Developer",
                    qualification: "B.A, B.C.A",
                    mobile: "(123) 4567 890",
                    dateTime: "2022/08/22 09:05 PM"
                },
                {
                    image: avatar5,
                    name: "Airi Satou",
                    department: "Developer",
                    qualification: "B.COM., M.COM.",
                    mobile: "(123) 4567 890",
                    dateTime: "2023/09/12 09:05 PM"
                },
                {
                    image: avatar6,
                    name: "Ashton Cox",
                    department: "Junior Technical",
                    qualification: "B.COM., M.COM.",
                    mobile: "(123) 4567 890",
                    dateTime: "2023/12/24 09:05 PM"
                },
                {
                    image: avatar7,
                    name: "Bradley Greer",
                    department: "Sales Assistant",
                    qualification: "B.A, B.C.A",
                    mobile: "(123) 4567 890",
                    dateTime: "2022/09/19 09:05 PM"
                },
                {
                    image: avatar8,
                    name: "Brielle Williamson",
                    department: "JavaScript Developer",
                    qualification: "B.A, B.C.A",
                    mobile: "(123) 4567 890",
                    dateTime: "2022/08/22 09:05 PM"
                },
                {
                    image: avatar9,
                    name: "Brielle Williamson",
                    department: "JavaScript Developer",
                    qualification: "B.A, B.C.A",
                    mobile: "(123) 4567 890",
                    dateTime: "2022/08/22 09:05 PM"
                },
                {
                    image: avatar10,
                    name: "Airi Satou",
                    department: "Developer",
                    qualification: "B.COM., M.COM.",
                    mobile: "(123) 4567 890",
                    dateTime: "2023/09/12 09:05 PM"
                },
                {
                    image: avatar2,
                    name: "Ashton Cox",
                    department: "Junior Technical",
                    qualification: "B.COM., M.COM.",
                    mobile: "(123) 4567 890",
                    dateTime: "2023/12/24 09:05 PM"
                },
                {
                    image: avatar3,
                    name: "Bradley Greer",
                    department: "Sales Assistant",
                    qualification: "B.A, B.C.A",
                    mobile: "(123) 4567 890",
                    dateTime: "2022/09/19 09:05 PM"
                },
                {
                    image: avatar4,
                    name: "Brielle Williamson",
                    department: "JavaScript Developer",
                    qualification: "B.A, B.C.A",
                    mobile: "(123) 4567 890",
                    dateTime: "2022/08/22 09:05 PM"
                }
            ]
            ,
        };
    },
};
</script>

<style>
/* Add any custom styles for your table here */
</style>