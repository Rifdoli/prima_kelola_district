<script>
import Layout from "@/layout/main.vue"
import pageheader from "@/components/page-header.vue"

// The four assessment domains (from AssessmentQuestion.domain).
const DOMAINS = ["LEADERSHIP & STRATEGIC", "POLICY & GUIDENCE", "RESOURCE MANAGEMENT", "INFRA HEALTHINESS"];

export default {
    name: "DASHBOARD",
    data() {
        return {
            selected: null,
            // ponytail: placeholder per-domain scores until a dashboard-summary
            // endpoint exists. Replace domainScores with the API response, keyed
            // by assessment type; everything below is derived from it.
            domainScores: {
                self:   { "LEADERSHIP & STRATEGIC": 82, "POLICY & GUIDENCE": 65, "RESOURCE MANAGEMENT": 74, "INFRA HEALTHINESS": 58 },
                onDesk: { "LEADERSHIP & STRATEGIC": 60, "POLICY & GUIDENCE": 88, "RESOURCE MANAGEMENT": 55, "INFRA HEALTHINESS": 70 },
                onSite: { "LEADERSHIP & STRATEGIC": 55, "POLICY & GUIDENCE": 62, "RESOURCE MANAGEMENT": 90, "INFRA HEALTHINESS": 68 },
            },
            cards: [
                { key: "self", title: "Self Assessment", img: "img-status-4.svg" },
                { key: "onDesk", title: "On Desk Assessment", img: "img-status-5.svg" },
                { key: "onSite", title: "On Site Assessment", img: "img-status-6.svg" },
            ],
        }
    },
    computed: {
        // Overall score per type = average across domains.
        avgScores() {
            const out = {};
            for (const [type, doms] of Object.entries(this.domainScores)) {
                const vals = Object.values(doms);
                out[type] = Math.round(vals.reduce((a, b) => a + b, 0) / vals.length);
            }
            return out;
        },
        selectedDomains() {
            const doms = this.selected ? this.domainScores[this.selected] : null;
            return doms ? DOMAINS.map((name) => ({ name, value: doms[name] })) : [];
        },
        dominantDomain() {
            return this.selectedDomains.reduce((max, d) => (d.value > max.value ? d : max), { name: "", value: -1 });
        },
        chartSeries() {
            return [{ name: "Score", data: this.selectedDomains.map((d) => d.value) }];
        },
        chartOptions() {
            return {
                chart: { toolbar: { show: false } },
                plotOptions: { bar: { horizontal: true, borderRadius: 4, distributed: true } },
                colors: ["#4680ff", "#4680ff", "#4680ff", "#4680ff"],
                dataLabels: { enabled: true },
                legend: { show: false },
                xaxis: { categories: DOMAINS, max: 100 },
                grid: { borderColor: "#e6e9f0" },
            };
        },
    },
    methods: {
        scorePct(v) {
            return `${Math.max(0, Math.min(100, Number(v) || 0))}%`;
        },
    },
    components: {
        Layout, pageheader
    },
}
</script>

<template>
    <Layout>
        <pageheader title="Home" pageTitle="Dashboard" />
        <BRow>
            <BCol v-for="card in cards" :key="card.key" md="4" sm="6">
                <BCard class="statistics-card-1 stat-card"
                    :class="{ 'bg-brand-color-3 active': selected === card.key }"
                    no-body @click="selected = card.key">
                    <BCardBody>
                        <h5 class="mb-4" :class="selected === card.key ? 'text-white' : ''">{{ card.title }}</h5>
                        <img :src="require(`@/assets/images/widget/${card.img}`)" alt="img" class="img-fluid img-bg">
                        <div class="d-flex align-items-center mt-3">
                            <h3 class="f-w-300 d-flex align-items-center m-b-0"
                                :class="selected === card.key ? 'text-white' : ''">{{ avgScores[card.key] }}</h3>
                        </div>
                        <p class="mb-2 text-sm mt-3"
                            :class="selected === card.key ? 'text-white text-opacity-75' : 'text-muted'">
                            Nilai {{ card.title }}</p>
                        <div class="progress bg-white bg-opacity-10" style="height: 7px">
                            <div class="progress-bar" :class="selected === card.key ? 'bg-white' : 'bg-brand-color-3'"
                                role="progressbar" :style="{ width: scorePct(avgScores[card.key]) }"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>

        <!-- Detail per domain, changes with the selected card. -->
        <BRow v-if="selected">
            <BCol md="8">
                <BCard no-body>
                    <BCardHeader>
                        <h5>Score per Domain — {{ cards.find(c => c.key === selected).title }}</h5>
                    </BCardHeader>
                    <BCardBody>
                        <apexchart type="bar" height="320" :series="chartSeries" :options="chartOptions"></apexchart>
                    </BCardBody>
                </BCard>
            </BCol>
            <BCol md="4">
                <BCard no-body class="statistics-card-1 bg-brand-color-3">
                    <BCardBody>
                        <h5 class="text-white mb-4">Domain Paling Dominan</h5>
                        <h3 class="text-white f-w-300 m-b-0">{{ dominantDomain.name }}</h3>
                        <p class="text-white text-opacity-75 mt-2">Score {{ dominantDomain.value }} / 100</p>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

<style>
.card {
    z-index: 0;
}

.stat-card {
    cursor: pointer;
    transition: transform .15s ease, box-shadow .15s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .12);
}

/* Selected card stays lifted to signal it's active. */
.stat-card.active {
    transform: translateY(-4px);
    box-shadow: 0 .5rem 1.5rem rgba(70, 128, 255, .35);
}
</style>
