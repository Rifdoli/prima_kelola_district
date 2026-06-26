<script>
import Layout from "@/layout/main.vue"
import pageheader from "@/components/page-header.vue"
// import { DataTable, convertCSV } from '@/assets/js/plugins/module.js';
export default {
    name: "DYNAMIC",
    components: {
        Layout, pageheader
    },
    // mounted() {
    //     const dataTable = new DataTable('#pc-dt-dynamic-import');
    //     const convertedData = convertCSV({
    //         type: 'csv',
    //         data: 'Name,Ext.,City,Start Date|Hammett Gordon,8101,Wah,1998/06/09|Kyra Moses,3796,Quenast,1998/07/07|Kelly Cameron,4836,Fontaine-Valmont,1999/02/07|Theodore Duran,8971,Dhanbad,1999/04/07|Hammett Gordon,8101,Wah,1998/06/09|Kyra Moses,3796,Quenast,1998/07/07|Kelly Cameron,4836,Fontaine-Valmont,1999/02/07|Theodore Duran,8971,Dhanbad,1999/04/07|Hammett Gordon,8101,Wah,1998/06/09|Kyra Moses,3796,Quenast,1998/07/07|Kelly Cameron,4836,Fontaine-Valmont,1999/02/07|Theodore Duran,8971,Dhanbad,1999/04/07|Hammett Gordon,8101,Wah,1998/06/09|Kyra Moses,3796,Quenast,1998/07/07|Kelly Cameron,4836,Fontaine-Valmont,1999/02/07|Theodore Duran,8971,Dhanbad,1999/04/07',
    //         headings: true,
    //         columnDelimiter: ',',
    //         lineDelimiter: '|',
    //     });
    //     dataTable.insert(convertedData);
    // },
}
</script>

<template>
    <Layout>
        <pageheader title="Dynamic Import" pageTitle="Table" />
        <BRow>
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Dynamic Import</h5>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table" id="pc-dt-dynamic-import"> </table>
                        </div>
                    </div>
                </div>
            </div>
        </BRow>
    </Layout>
</template>