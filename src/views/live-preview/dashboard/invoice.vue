<script>
import Layout from "@/layout/main.vue"
import pageheader from "@/components/page-header.vue"
import { invoice_chart, total_income_graph_options } from "./data"

export default {
    name: "HELP DASHBOARD",
    data() {
        return {
            // cardBg : cardBg,
            invoice_chart: invoice_chart,
            total_income_graph_options: total_income_graph_options,
        }
    },

    components: {
        Layout, pageheader
    },
}
</script>

<template>
    <Layout>
        <pageheader title="Invoice Dashboard" pageTitle="Dashboard" />
        <BRow class="row">
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-body">
                        <BRow class="row g-3 mb-3">
                            <div class="col-md-6 col-xxl-3">
                                <div class="card border mb-0">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between gap-1">
                                            <h6 class="mb-0">Total</h6>
                                            <p class="mb-0 text-muted d-flex align-items-center gap-1">
                                                <svg class="pc-icon text-warning wid-15 hei-15" viewBox="0 0 24 24">
                                                    <path d="M17.9188 8.17969H11.6888H6.07877C5.11877 8.17969 4.63877 9.33969 5.31877 10.0197L10.4988 15.1997C11.3288 16.0297 12.6788 16.0297 13.5088 15.1997L15.4788 13.2297L18.6888 10.0197C19.3588 9.33969 18.8788 8.17969 17.9188 8.17969Z" fill="currentcolor" />
                                                </svg>
                                                20.3%
                                            </p>
                                        </div>
                                        <h5 class="mb-2 mt-3">£5678.09</h5>
                                        <div class="d-flex align-items-center gap-1">
                                            <h5 class="mb-0">3</h5>
                                            <p class="mb-0 text-muted d-flex align-items-center gap-2">invoices</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xxl-3">
                                <div class="card border mb-0">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between gap-1">
                                            <h6 class="mb-0">Paid</h6>
                                            <p class="mb-0 text-muted d-flex align-items-center gap-1">
                                                <svg class="pc-icon text-danger wid-15 hei-15" viewBox="0 0 24 24">
                                                    <path d="M17.9188 8.17969H11.6888H6.07877C5.11877 8.17969 4.63877 9.33969 5.31877 10.0197L10.4988 15.1997C11.3288 16.0297 12.6788 16.0297 13.5088 15.1997L15.4788 13.2297L18.6888 10.0197C19.3588 9.33969 18.8788 8.17969 17.9188 8.17969Z" fill="currentcolor" />
                                                </svg>
                                                -8.73%
                                            </p>
                                        </div>
                                        <h5 class="mb-2 mt-3">£5678.09</h5>
                                        <div class="d-flex align-items-center gap-1">
                                            <h5 class="mb-0">5</h5>
                                            <p class="mb-0 text-muted d-flex align-items-center gap-2">invoices</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xxl-3">
                                <div class="card border mb-0">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between gap-1">
                                            <h6 class="mb-0">Pending</h6>
                                            <p class="mb-0 text-muted d-flex align-items-center gap-1">
                                                <svg class="pc-icon text-success wid-15 hei-15" viewBox="0 0 24 24">
                                                    <path d="M17.9188 8.17969H11.6888H6.07877C5.11877 8.17969 4.63877 9.33969 5.31877 10.0197L10.4988 15.1997C11.3288 16.0297 12.6788 16.0297 13.5088 15.1997L15.4788 13.2297L18.6888 10.0197C19.3588 9.33969 18.8788 8.17969 17.9188 8.17969Z" fill="currentcolor" />
                                                </svg>
                                                10.73%
                                            </p>
                                        </div>
                                        <h5 class="mb-2 mt-3">£5678.09</h5>
                                        <div class="d-flex align-items-center gap-1">
                                            <h5 class="mb-0">20</h5>
                                            <p class="mb-0 text-muted d-flex align-items-center gap-2">invoices</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xxl-3">
                                <div class="card border mb-0">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between gap-1">
                                            <h6 class="mb-0">Overdue</h6>
                                            <p class="mb-0 text-muted d-flex align-items-center gap-1">
                                                <svg class="pc-icon text-primary wid-15 hei-15" viewBox="0 0 24 24">
                                                    <path d="M17.9188 8.17969H11.6888H6.07877C5.11877 8.17969 4.63877 9.33969 5.31877 10.0197L10.4988 15.1997C11.3288 16.0297 12.6788 16.0297 13.5088 15.1997L15.4788 13.2297L18.6888 10.0197C19.3588 9.33969 18.8788 8.17969 17.9188 8.17969Z" fill="currentcolor" />
                                                </svg>
                                                -4.73%
                                            </p>
                                        </div>
                                        <h5 class="mb-2 mt-3">£5678.09</h5>
                                        <div class="d-flex align-items-center gap-1">
                                            <h5 class="mb-0">5</h5>
                                            <p class="mb-0 text-muted d-flex align-items-center gap-2">invoices</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </BRow>
                        <div id="invoice-chart"></div>
                        <apexchart id="invoice-chart" type="line" height="300" :options="invoice_chart.chartOptions" :series="invoice_chart.series" />

                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <BRow class="row g-3 text-center">
                            <div class="col-6">
                                <div class="card mb-0">
                                    <div class="card-body py-4 px-2">
                                        <div class="avtar bg-primary rounded-circle">
                                            <svg class="pc-icon text-white" viewBox="0 0 24 24">
                                                <path d="M15.7997 2.21048C15.3897 1.80048 14.6797 2.08048 14.6797 2.65048V6.14048C14.6797 7.60048 15.9197 8.81048 17.4297 8.81048C18.3797 8.82048 19.6997 8.82048 20.8297 8.82048C21.3997 8.82048 21.6997 8.15048 21.2997 7.75048C19.8597 6.30048 17.2797 3.69048 15.7997 2.21048Z" fill="currentcolor" />
                                                <path d="M20.5 10.19H17.61C15.24 10.19 13.31 8.26 13.31 5.89V3C13.31 2.45 12.86 2 12.31 2H8.07C4.99 2 2.5 4 2.5 7.57V16.43C2.5 20 4.99 22 8.07 22H15.93C19.01 22 21.5 20 21.5 16.43V11.19C21.5 10.64 21.05 10.19 20.5 10.19ZM11.5 17.75H7.5C7.09 17.75 6.75 17.41 6.75 17C6.75 16.59 7.09 16.25 7.5 16.25H11.5C11.91 16.25 12.25 16.59 12.25 17C12.25 17.41 11.91 17.75 11.5 17.75ZM13.5 13.75H7.5C7.09 13.75 6.75 13.41 6.75 13C6.75 12.59 7.09 12.25 7.5 12.25H13.5C13.91 12.25 14.25 12.59 14.25 13C14.25 13.41 13.91 13.75 13.5 13.75Z" fill="currentcolor" />
                                            </svg>
                                        </div>
                                        <h6 class="mb-0 mt-3 text-muted">All Invoices</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card mb-0">
                                    <div class="card-body py-4 px-2">
                                        <div class="avtar bg-info rounded-circle">
                                            <svg class="pc-icon text-white" viewBox="0 0 24 24">
                                                <path opacity="0.4" d="M21 7V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V7C3 4 4.5 2 8 2H16C19.5 2 21 4 21 7Z" fill="currentcolor" />
                                                <path d="M15.5 2V9.85999C15.5 10.3 14.98 10.52 14.66 10.23L12.34 8.09C12.15 7.91 11.85 7.91 11.66 8.09L9.34003 10.23C9.02003 10.53 8.5 10.3 8.5 9.85999V2H15.5Z" fill="currentcolor" />
                                                <path d="M17.5 14.75H13.25C12.84 14.75 12.5 14.41 12.5 14C12.5 13.59 12.84 13.25 13.25 13.25H17.5C17.91 13.25 18.25 13.59 18.25 14C18.25 14.41 17.91 14.75 17.5 14.75Z" fill="currentcolor" />
                                                <path d="M17.5 18.75H9C8.59 18.75 8.25 18.41 8.25 18C8.25 17.59 8.59 17.25 9 17.25H17.5C17.91 17.25 18.25 17.59 18.25 18C18.25 18.41 17.91 18.75 17.5 18.75Z" fill="currentcolor" />
                                            </svg>
                                        </div>
                                        <h6 class="mb-0 mt-3 text-muted">Reports</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card mb-0">
                                    <div class="card-body py-4 px-2">
                                        <div class="avtar bg-success rounded-circle">
                                            <svg class="pc-icon text-white" viewBox="0 0 24 24">
                                                <path opacity="0.4" d="M12 21.8999C17.5228 21.8999 22 17.4228 22 11.8999C22 6.37705 17.5228 1.8999 12 1.8999C6.47715 1.8999 2 6.37705 2 11.8999C2 17.4228 6.47715 21.8999 12 21.8999Z" fill="currentcolor" />
                                                <path d="M14.2602 12L12.7502 11.47V8.08H13.1102C13.9202 8.08 14.5802 8.79 14.5802 9.66C14.5802 10.07 14.9202 10.41 15.3302 10.41C15.7402 10.41 16.0802 10.07 16.0802 9.66C16.0802 7.96 14.7502 6.58 13.1102 6.58H12.7502V6C12.7502 5.59 12.4102 5.25 12.0002 5.25C11.5902 5.25 11.2502 5.59 11.2502 6V6.58H10.6002C9.12016 6.58 7.91016 7.83 7.91016 9.36C7.91016 11.15 8.95016 11.72 9.74016 12L11.2502 12.53V15.91H10.8902C10.0802 15.91 9.42016 15.2 9.42016 14.33C9.42016 13.92 9.08016 13.58 8.67016 13.58C8.26016 13.58 7.92016 13.92 7.92016 14.33C7.92016 16.03 9.25016 17.41 10.8902 17.41H11.2502V18C11.2502 18.41 11.5902 18.75 12.0002 18.75C12.4102 18.75 12.7502 18.41 12.7502 18V17.42H13.4002C14.8802 17.42 16.0902 16.17 16.0902 14.64C16.0802 12.84 15.0402 12.27 14.2602 12ZM10.2402 10.59C9.73016 10.41 9.42016 10.24 9.42016 9.37C9.42016 8.66 9.95016 8.09 10.6102 8.09H11.2602V10.95L10.2402 10.59ZM13.4002 15.92H12.7502V13.06L13.7602 13.41C14.2702 13.59 14.5802 13.76 14.5802 14.63C14.5802 15.34 14.0502 15.92 13.4002 15.92Z" fill="currentcolor" />
                                            </svg>
                                        </div>
                                        <h6 class="mb-0 mt-3 text-muted">Paid</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card mb-0">
                                    <div class="card-body py-4 px-2">
                                        <div class="avtar bg-warning rounded-circle">
                                            <svg class="pc-icon text-white" viewBox="0 0 24 24">
                                                <path d="M3.22998 1H11.36C12.04 1 12.59 1.56 12.59 2.25V3.62C12.59 4.12 12.28 4.73999 11.97 5.04999L9.32001 7.42001C8.95001 7.73001 8.70001 8.36001 8.70001 8.85001V11.53C8.70001 11.9 8.45002 12.4 8.15002 12.59L7.28998 13.15C6.48998 13.65 5.38 13.09 5.38 12.09V8.78C5.38 8.34 5.13001 7.78 4.89001 7.47L2.54999 4.97C2.23999 4.66 2 4.1 2 3.72V2.29001C2 1.56001 2.54998 1 3.22998 1Z" fill="currentcolor" />
                                                <path opacity="0.4" d="M17 2H14.6C14.32 2 14.1 2.22 14.1 2.5C14.1 2.98 14.1 3.62 14.1 3.62C14.1 4.61 13.57 5.57999 13.05 6.10999L10.33 8.54001C10.3 8.61001 10.25 8.71001 10.22 8.79001V11.54C10.22 12.45 9.68 13.44 8.94 13.89L8.12 14.42C7.66 14.71 7.15001 14.85 6.64001 14.85C6.18001 14.85 5.71999 14.73 5.29999 14.5C4.64999 14.14 4.2 13.55 4 12.87V10.21C4 10.08 3.94998 9.94999 3.84998 9.85999L2.84998 8.85999C2.52998 8.54999 2 8.76999 2 9.20999V17C2 19.76 4.24 22 7 22H17C19.76 22 22 19.76 22 17V7C22 4.24 19.76 2 17 2Z" fill="currentcolor" />
                                                <path d="M18 13.75H13C12.59 13.75 12.25 13.41 12.25 13C12.25 12.59 12.59 12.25 13 12.25H18C18.41 12.25 18.75 12.59 18.75 13C18.75 13.41 18.41 13.75 18 13.75Z" fill="currentcolor" />
                                                <path d="M18 17.75H11C10.59 17.75 10.25 17.41 10.25 17C10.25 16.59 10.59 16.25 11 16.25H18C18.41 16.25 18.75 16.59 18.75 17C18.75 17.41 18.41 17.75 18 17.75Z" fill="currentcolor" />
                                            </svg>
                                        </div>
                                        <h6 class="mb-0 mt-3 text-muted">Pending</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card mb-0">
                                    <div class="card-body py-4 px-2">
                                        <div class="avtar bg-danger rounded-circle">
                                            <svg class="pc-icon text-white" viewBox="0 0 24 24">
                                                <path opacity="0.4" d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill="currentcolor" />
                                                <path d="M13.0594 12.0001L15.3594 9.70011C15.6494 9.41011 15.6494 8.93011 15.3594 8.64011C15.0694 8.35011 14.5894 8.35011 14.2994 8.64011L11.9994 10.9401L9.69937 8.64011C9.40937 8.35011 8.92937 8.35011 8.63938 8.64011C8.34938 8.93011 8.34938 9.41011 8.63938 9.70011L10.9394 12.0001L8.63938 14.3001C8.34938 14.5901 8.34938 15.0701 8.63938 15.3601C8.78938 15.5101 8.97937 15.5801 9.16937 15.5801C9.35937 15.5801 9.54937 15.5101 9.69937 15.3601L11.9994 13.0601L14.2994 15.3601C14.4494 15.5101 14.6394 15.5801 14.8294 15.5801C15.0194 15.5801 15.2094 15.5101 15.3594 15.3601C15.6494 15.0701 15.6494 14.5901 15.3594 14.3001L13.0594 12.0001Z" fill="currentcolor" />
                                            </svg>
                                        </div>
                                        <h6 class="mb-0 mt-3 text-muted">Cancelled</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card mb-0">
                                    <div class="card-body py-4 px-2">
                                        <div class="avtar bg-primary rounded-circle">
                                            <svg class="pc-icon text-white" viewBox="0 0 24 24">
                                                <path opacity="0.4" d="M16.4908 22H7.51082C4.00082 22 3.24082 19.99 3.53082 17.53L4.43081 10.03C4.66081 8.09 5.00082 6.5 8.40082 6.5H15.6008C19.0008 6.5 19.3408 8.09 19.5708 10.03L20.3208 16.28L20.4708 17.53C20.4808 17.61 20.4908 17.69 20.5008 17.77C20.7108 20.12 19.8908 22 16.4908 22Z" fill="currentcolor" />
                                                <path d="M16 8.75C15.59 8.75 15.25 8.41 15.25 8V4.5C15.25 3.42 14.58 2.75 13.5 2.75H10.5C9.42 2.75 8.75 3.42 8.75 4.5V8C8.75 8.41 8.41 8.75 8 8.75C7.59 8.75 7.25 8.41 7.25 8V4.5C7.25 2.59 8.59 1.25 10.5 1.25H13.5C15.41 1.25 16.75 2.59 16.75 4.5V8C16.75 8.41 16.41 8.75 16 8.75Z" fill="currentcolor" />
                                                <path d="M20.5 17.77C20.47 17.78 20.44 17.78 20.41 17.78H8C7.59 17.78 7.25 17.44 7.25 17.03C7.25 16.61 7.59 16.28 8 16.28H20.32L20.47 17.53C20.48 17.61 20.49 17.69 20.5 17.77Z" fill="currentcolor" />
                                            </svg>
                                        </div>
                                        <h6 class="mb-0 mt-3 text-muted">Draft</h6>
                                    </div>
                                </div>
                            </div>
                        </BRow>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-body pb-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">Recent Invoice</h5>
                            <BDropdown variant="link-secondary" class="card-header-dropdown" toggle-class="avtar avtar-s text-reset dropdown-btn arrow-none p-1" menu-class="dropdown-menu-end" aria-haspopup="true" :offset="{ alignmentAxis: -160, crossAxis: 0, mainAxis: 0 }">
                                <template #button-content><span class="text-muted "><i class="ti ti-dots-vertical f-18"></i></span>
                                </template>
                                <BDropdownItem>Today</BDropdownItem>
                                <BDropdownItem>Weekly</BDropdownItem>
                                <BDropdownItem>Monthly</BDropdownItem>
                            </BDropdown>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush border-top-0">
                        <li class="list-group-item border-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <img src="@/assets/images/user/avatar-10.jpg" alt="user-image" class="user-avtar rounded-circle wid-40 hie-40" />
                                </div>
                                <div class="flex-grow-1 mx-2">
                                    <h6 class="mb-1">David Jones <span class="text-muted"> - #790841</span></h6>
                                    <p class="mb-0 text-primary">$329.20</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <p class="text-muted mb-0">5 min ago</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item border-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <img src="@/assets/images/user/avatar-2.jpg" alt="user-image" class="user-avtar rounded-circle wid-40 hie-40" />
                                </div>
                                <div class="flex-grow-1 mx-2">
                                    <h6 class="mb-1">Jenny Jones <span class="text-muted"> - #790841</span></h6>
                                    <p class="mb-0 text-primary">$329.20</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <p class="text-muted mb-0">1 day ago</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item border-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <img src="@/assets/images/user/avatar-3.jpg" alt="user-image" class="user-avtar rounded-circle wid-40 hie-40" />
                                </div>
                                <div class="flex-grow-1 mx-2">
                                    <h6 class="mb-1">Harry Ben <span class="text-muted"> - #790841</span></h6>
                                    <p class="mb-0 text-primary">3 week ago</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <p class="text-muted mb-0">5 min ago</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item border-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <img src="@/assets/images/user/avatar-2.jpg" alt="user-image" class="user-avtar rounded-circle wid-40 hie-40" />
                                </div>
                                <div class="flex-grow-1 mx-2">
                                    <h6 class="mb-1">Jenifer Vintage <span class="text-muted"> - #790841</span></h6>
                                    <p class="mb-0 text-primary">$329.20</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <p class="text-muted mb-0">3 week ago</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item border-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <img src="@/assets/images/user/avatar-3.jpg" alt="user-image" class="user-avtar rounded-circle wid-40 hie-40" />
                                </div>
                                <div class="flex-grow-1 mx-2">
                                    <h6 class="mb-1">Stebin Ben <span class="text-muted"> - #790841</span></h6>
                                    <p class="mb-0 text-primary">$329.20</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <p class="text-muted mb-0">1 month ago</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item border-0">
                            <div class="d-grid">
                                <button class="btn btn-outline-secondary">View All</button>
                            </div>
                        </li>

                    </ul>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-body pb-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">Total Expenses</h5>
                            <BDropdown variant="link-secondary" class="card-header-dropdown" toggle-class="avtar avtar-s  text-reset dropdown-btn arrow-none p-1" menu-class="dropdown-menu-end" aria-haspopup="true" :offset="{ alignmentAxis: -160, crossAxis: 0, mainAxis: 0 }">
                                <template #button-content><span class="text-muted "><i class="ti ti-dots-vertical f-18"></i></span>
                                </template>
                                <BDropdownItem>Today</BDropdownItem>
                                <BDropdownItem>Weekly</BDropdownItem>
                                <BDropdownItem>Monthly</BDropdownItem>
                            </BDropdown>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="total-income-graph"></div>
                        <apexchart id="invoice-chart" type="donut" height="280" :options="total_income_graph_options.chartOptions" :series="total_income_graph_options.series" />

                        <div class="mb-2 mt-3 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 d-flex align-items-center gap-2"><i class="fas fa-circle text-warning f-12"></i>
                                Pending</h6>
                            <p class="mb-0 text-muted">$3,202</p>
                        </div>
                        <div class="mb-2 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 d-flex align-items-center gap-2"><i class="fas fa-circle text-success f-12"></i>
                                Paid</h6>
                            <p class="mb-0 text-muted">$45,050</p>
                        </div>
                        <div class="mb-2 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 d-flex align-items-center gap-2"><i class="fas fa-circle text-danger f-12"></i>
                                Overdue</h6>
                            <p class="mb-0 text-muted">$25,000</p>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 d-flex align-items-center gap-2"><i class="fas fa-circle text-primary text-opacity-25 f-12"></i>
                                Draft</h6>
                            <p class="mb-0 text-muted">$7,694</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-body pb-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">Notification</h5>
                            <BDropdown variant="link-secondary" class="card-header-dropdown" toggle-class="avtar avtar-s text-reset dropdown-btn arrow-none p-1" menu-class="dropdown-menu-end" aria-haspopup="true" :offset="{ alignmentAxis: -160, crossAxis: 0, mainAxis: 0 }">
                                <template #button-content><span class="text-muted "><i class="ti ti-dots-vertical f-18"></i></span>
                                </template>
                                <BDropdownItem>Today</BDropdownItem>
                                <BDropdownItem>Weekly</BDropdownItem>
                                <BDropdownItem>Monthly</BDropdownItem>
                            </BDropdown>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush border-top-0">
                        <li class="list-group-item border-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avtar bg-light-success rounded-circle">
                                        <i class="ti ti-file-download f-26"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 mx-2">
                                    <h6 class="mb-1">Johnny sent you an invoice billed <a href="#!" class="link-primary">$1,000</a>.</h6>
                                    <p class="mb-0 text-muted text-sm">2 August</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="#!" class="avtar avtar-s btn-link-secondary"><i class="ti ti-link"></i></a>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item border-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avtar bg-light-primary rounded-circle">
                                        <i class="ti ti-file-text f-26"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 mx-2">
                                    <h6 class="mb-1">Sent an invoice to Aida Bugg amount of <a href="#!" class="link-primary">$200</a>.</h6>
                                    <p class="mb-0 text-muted text-sm">7 hours ago</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="#!" class="avtar avtar-s btn-link-secondary"><i class="ti ti-link"></i></a>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item border-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avtar bg-light-danger rounded-circle">
                                        <i class="ti ti-adjustments f-26"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 mx-2">
                                    <h6 class="mb-1">There was a failure to your setup</h6>
                                    <p class="mb-0 text-muted text-sm">7 hours ago</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="#!" class="avtar avtar-s btn-link-secondary"><i class="ti ti-link"></i></a>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item border-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avtar bg-light-primary rounded-circle">
                                        <span class="f-24 fw-normal">c</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 mx-2">
                                    <h6 class="mb-1">Cristina danny invited to you join Meeting</h6>
                                    <p class="mb-0 text-muted text-sm">7 hours ago</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="#!" class="avtar avtar-s btn-link-secondary"><i class="ti ti-link"></i></a>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item border-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avtar bg-light-primary rounded-circle">
                                        <span class="f-24 fw-normal">c</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 mx-2">
                                    <h6 class="mb-1">Cristina danny invited to you join Meeting</h6>
                                    <p class="mb-0 text-muted text-sm">7 hours ago</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="#!" class="avtar avtar-s btn-link-secondary"><i class="ti ti-link"></i></a>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item border-0">
                            <div class="d-grid">
                                <button class="btn btn-outline-secondary">View All</button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </BRow>
    </Layout>
</template>

<style>
.card {
    z-index: 0;
}
</style>