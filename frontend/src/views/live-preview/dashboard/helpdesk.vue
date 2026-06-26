<script>
import Layout from "@/layout/main.vue"
import pageheader from "@/components/page-header.vue"
// import * as am4core from '@amcharts/amcharts4/core';
// import * as am4maps from '@amcharts/amcharts4/maps';
// import am4geodata_worldLow from '@amcharts/amcharts4-geodata/worldLow';
import { support_chart, support_chart1, support_chart2, setisfaction } from "./data"
// import cardBg from '@/assets/images/widget/img-card-bg.svg'
import simplebar from 'simplebar-vue';

export default {
    name: "HELP DASHBOARD",
    data() {
        return {

            support_chart: support_chart,
            support_chart1: support_chart1,
            support_chart2: support_chart2,
            setisfaction: setisfaction
        }
    },

    components: {
        Layout, pageheader, simplebar
    },
}
</script>

<template>
    <Layout>
        <pageheader pageTitle="Helpdesk" title="Dashboard" />
        <BRow class="">
            <!-- support-section start -->
            <BCol class="col-xl-4 col-md-6">
                <BCard no-body class=" support-bar">
                    <BCardBody class=" pb-0">
                        <h2 class="m-0">350</h2>
                        <span class="text-info">Support Requests</span>
                        <p class="mb-3 mt-3">Total number of support requests that come in.</p>
                    </BCardBody>
                    <apexchart id="support-chart" type="area" height="100" :options="support_chart.chartOptions" :series="support_chart.series" />
                    <div class="card-footer bg-info text-white">
                        <BRow class=" text-center">
                            <BCol class="border-end">
                                <h4 class="m-0 text-white">10</h4>
                                <span>Open</span>
                            </BCol>
                            <BCol class="border-end">
                                <h4 class="m-0 text-white">5</h4>
                                <span>Running</span>
                            </BCol>
                            <BCol class="col">
                                <h4 class="m-0 text-white">3</h4>
                                <span>Solved</span>
                            </BCol>
                        </BRow>
                    </div>
                </BCard>
            </BCol>
            <!-- support-section end -->
            <!-- support1-section start -->
            <BCol class="col-xl-4 col-md-6">
                <BCard no-body class=" support-bar">
                    <BCardBody class=" pb-0">
                        <h2 class="m-0">500</h2>
                        <span class="text-primary">Agent Response</span>
                        <p class="mb-3 mt-3">Total number of support requests that come in.</p>
                    </BCardBody>
                    <apexchart id="support-chart1" type="area" height="100" :options="support_chart1.chartOptions" :series="support_chart1.series" />
                    <div class="card-footer bg-primary text-white">
                        <BRow class=" text-center">
                            <BCol class="border-end">
                                <h4 class="m-0 text-white">50</h4>
                                <span>Open</span>
                            </BCol>
                            <BCol class="border-end">
                                <h4 class="m-0 text-white">75</h4>
                                <span>Running</span>
                            </BCol>
                            <BCol class="col">
                                <h4 class="m-0 text-white">30</h4>
                                <span>Solved</span>
                            </BCol>
                        </BRow>
                    </div>
                </BCard>
            </BCol>
            <!-- support-section1 end -->
            <!-- support2-section start -->
            <BCol class="col-xl-4 col-md-12">
                <BCard no-body class=" support-bar">
                    <BCardBody class=" pb-0">
                        <h2 class="m-0">800</h2>
                        <span class="text-success">Support Resolved</span>
                        <p class="mb-3 mt-3">Total number of support requests that come in.</p>
                    </BCardBody>
                    <apexchart id="support-chart1" type="area" height="100" :options="support_chart2.chartOptions" :series="support_chart2.series" />
                    <div class="card-footer bg-success text-white">
                        <BRow class=" text-center">
                            <BCol class="border-end">
                                <h4 class="m-0 text-white">80</h4>
                                <span>Open</span>
                            </BCol>
                            <BCol class="border-end">
                                <h4 class="m-0 text-white">60</h4>
                                <span>Running</span>
                            </BCol>
                            <BCol class="col">
                                <h4 class="m-0 text-white">90</h4>
                                <span>Solved</span>
                            </BCol>
                        </BRow>
                    </div>
                </BCard>
            </BCol>
            <!-- support-section2 end -->
            <!-- customer-section start -->
            <BCol class="col-xl-7 col-md-6">
                <BCard no-body class="">
                    <BCardBody class="">
                        <h6>Customer Satisfaction</h6>
                        <span>It takes continuous effort to maintain high customer satisfaction levels.Internal and external
                            quality measures are often tied together.,as the opinion...</span>
                        <a href="#!" class="text-primary d-block">Learn more..</a>
                        <BRow class=" d-flex justify-content-center align-items-center">
                            <BCol class="col">
                                <apexchart id="satisfaction-chart" type="pie" height="260" :options="setisfaction.chartOptions" :series="setisfaction.series" />

                            </BCol>
                        </BRow>
                    </BCardBody>
                </BCard>
                <!-- rating-section start -->
                <BRow class="">
                    <BCol class="col-xl-6 col-md-12">
                        <BCard no-body class=" social-res-card">
                            <div class="card-header">
                                <h5>Facebook Source</h5>
                            </div>
                            <BCardBody class="">
                                <p class="m-b-10">Page Profile</p>
                                <div class="progress m-b-25" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width:25%"></div>
                                </div>
                                <p class="m-b-10">Favorite</p>
                                <div class="progress m-b-25" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width:85%"></div>
                                </div>
                                <p class="m-b-10">Like Story</p>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width:65%"></div>
                                </div>
                            </BCardBody>
                        </BCard>
                    </BCol>
                    <BCol class="col-xl-6 col-md-12">
                        <BCard no-body class=" social-res-card">
                            <div class="card-header">
                                <h5>Twitter Source</h5>
                            </div>
                            <BCardBody class="">
                                <p class="m-b-10">Wall Profile</p>
                                <div class="progress m-b-25" style="height: 6px;">
                                    <div class="progress-bar bg-danger" style="width:85%"></div>
                                </div>
                                <p class="m-b-10">Favorite</p>
                                <div class="progress m-b-25" style="height: 6px;">
                                    <div class="progress-bar bg-danger" style="width:25%"></div>
                                </div>
                                <p class="m-b-10">Like Tweets</p>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-danger" style="width:65%"></div>
                                </div>
                            </BCardBody>
                        </BCard>
                    </BCol>
                </BRow>
                <!-- rating-section end -->
            </BCol>
            <!-- customer-section end -->
            <!-- latest-section start -->
            <BCol class="col-xl-5 col-md-6">
                <BCard no-body class=" feed-card">
                    <div class="card-header">
                        <h5>Latest Activity</h5>
                        <div class="card-header-right">
                            <div class="btn-group card-option">
                                <BDropdown variant="link-secondary" class="card-header-dropdown" toggle-class="text-reset dropdown-btn arrow-none p-1" menu-class="dropdown-menu-end" aria-haspopup="true">
                                    <template #button-content><span class="text-muted "><i class="feather icon-more-horizontal"></i></span>
                                    </template>
                                    <BDropdownItem><template #default>
                                            <BLink href="#">
                                                <span><i class="feather icon-maximize"></i>
                                                    maximize</span><span style="display:none"><i class="feather icon-minimize"></i>
                                                    Restore</span>
                                            </BLink>
                                        </template></BDropdownItem>
                                    <BDropdownItem><template #default>
                                            <BLink href="#"><span><i class="feather icon-minus"></i>
                                                    collapse</span><span style="display:none"><i class="feather icon-plus"></i> expand</span>
                                            </BLink>
                                        </template></BDropdownItem>
                                    <BDropdownItem><template #default>
                                            <BLink href="#"><i class="feather icon-refresh-cw"></i>reload</BLink>
                                        </template></BDropdownItem>
                                    <BDropdownItem><template #default><BLink href="#"><i class="feather icon-trash"></i> remove</BLink></template></BDropdownItem>
                                </BDropdown>
                            </div>
                        </div>
                    </div>
                    <simplebar class="latest-scroll" style="height:400px;position:relative;">
                        <BCardBody class="">
                            <BRow class=" align-items-center m-b-30">
                                <BCol class="col-auto p-r-0">
                                    <i class="feather icon-bell bg-light-primary feed-icon text-primary"></i>
                                </BCol>
                                <BCol class="col">
                                    <a href="#!">
                                        <h6>You have 3 pending tasks. <span class="text-muted float-end f-13">Just Now</span></h6>
                                    </a>
                                </BCol>
                            </BRow>
                            <BRow class=" align-items-center m-b-30">
                                <BCol class="col-auto p-r-0">
                                    <i class="feather icon-shopping-cart bg-light-danger feed-icon text-danger"></i>
                                </BCol>
                                <BCol class="col">
                                    <a href="#!">
                                        <h6>New order received <span class="text-muted float-end f-13">Just Now</span></h6>
                                    </a>
                                </BCol>
                            </BRow>
                            <BRow class=" align-items-center m-b-30">
                                <BCol class="col-auto p-r-0">
                                    <i class="feather icon-file-text bg-light-success feed-icon text-success"></i>
                                </BCol>
                                <BCol class="col">
                                    <a href="#!">
                                        <h6>You have 3 pending tasks. <span class="text-muted float-end f-13">Just Now</span></h6>
                                    </a>
                                </BCol>
                            </BRow>
                            <BRow class=" align-items-center m-b-30">
                                <BCol class="col-auto p-r-0">
                                    <i class="feather icon-shopping-cart bg-light-warning feed-icon text-warning"></i>
                                </BCol>
                                <BCol class="col">
                                    <a href="#!">
                                        <h6>New order received <span class="text-muted float-end f-13">Just Now</span></h6>
                                    </a>
                                </BCol>
                            </BRow>
                            <BRow class=" align-items-center m-b-30">
                                <BCol class="col-auto p-r-0">
                                    <i class="feather icon-bell bg-light-primary feed-icon text-primary"></i>
                                </BCol>
                                <BCol class="col">
                                    <a href="#!">
                                        <h6>You have 3 pending tasks. <span class="text-muted float-end f-13">Just Now</span></h6>
                                    </a>
                                </BCol>
                            </BRow>
                            <BRow class=" align-items-center m-b-30">
                                <BCol class="col-auto p-r-0">
                                    <i class="feather icon-shopping-cart bg-light-danger feed-icon text-danger"></i>
                                </BCol>
                                <BCol class="col">
                                    <a href="#!">
                                        <h6>New order received <span class="text-muted float-end f-13">Just Now</span></h6>
                                    </a>
                                </BCol>
                            </BRow>
                            <BRow class=" align-items-center m-b-30">
                                <BCol class="col-auto p-r-0">
                                    <i class="feather icon-file-text bg-light-success feed-icon text-success"></i>
                                </BCol>
                                <BCol class="col">
                                    <a href="#!">
                                        <h6>You have 3 pending tasks. <span class="text-muted float-end f-13">Just Now</span></h6>
                                    </a>
                                </BCol>
                            </BRow>
                            <BRow class=" align-items-center m-b-20">
                                <BCol class="col-auto p-r-0">
                                    <i class="feather icon-shopping-cart bg-light-warning feed-icon text-warning"></i>
                                </BCol>
                                <BCol class="col">
                                    <a href="#!">
                                        <h6>New order received <span class="text-muted float-end f-13">Just Now</span></h6>
                                    </a>
                                </BCol>
                            </BRow>
                        </BCardBody>
                    </simplebar>
                    <div class="card-footer text-center">
                        <a href="#!" class="b-b-primary text-primary">View all Feeds</a>
                    </div>
                </BCard>
                <BCard no-body class=" feed-card">
                    <BCardBody class=" p-t-0 p-b-0">
                        <BRow class="">
                            <BCol class="col-4 bg-primary border-feed">
                                <i class="material-icons-two-tone d-block f-46">menu_book</i>
                            </BCol>
                            <BCol class="col-8">
                                <div class="p-t-25 p-b-25">
                                    <h2 class="f-w-400 m-b-10">379</h2>
                                    <p class="text-muted m-0">Tickets <span class="text-primary f-w-400">Answered</span></p>
                                </div>
                            </BCol>
                        </BRow>
                    </BCardBody>
                </BCard>
            </BCol>
            <!-- latest-section end -->
        </BRow>
    </Layout>
</template>

<style>
.card {
    z-index: 0;
}
</style>