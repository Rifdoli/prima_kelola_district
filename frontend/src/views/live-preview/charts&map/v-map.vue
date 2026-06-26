<script>
import Layout from "@/layout/main.vue";
import pageheader from "@/components/page-header.vue";
import * as am4core from '@amcharts/amcharts4/core';
import * as am4maps from '@amcharts/amcharts4/maps';
import am4geodata_worldLow from '@amcharts/amcharts4-geodata/worldLow';
import am4geodata_canadaLow from "@amcharts/amcharts4-geodata/canadaLow";
import am4geodata_iraqLow from "@amcharts/amcharts4-geodata/iraqLow";
import am4geodata_italyLow from "@amcharts/amcharts4-geodata/italyLow";
import am4geodata_russiaLow from "@amcharts/amcharts4-geodata/russiaLow";
import am4geodata_spainLow from "@amcharts/amcharts4-geodata/spainLow";
import am4geodata_usaLow from "@amcharts/amcharts4-geodata/usaLow";

export default {
    name: "MAPS",
    data() {
        return {
            markers: [
                {
                    position: { lat: 10.0, lng: 10.0 },
                },
                {
                    position: { lat: 15.0, lng: 13.0 },
                },
            ],
        };
    },
    components: {
        Layout, pageheader,
    },
    methods: {
        initialMap(divId, geodata, withMarkers) {
            // Create a chart instance
            const chart = am4core.create(divId, am4maps.MapChart);

            // Set map definition
            chart.geodata = geodata;

            // Set projection
            chart.projection = new am4maps.projections.Miller();

            // Create map polygon series
            const polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());

            // Exclude Antarctica for the world map
            if (geodata === am4geodata_worldLow) {
                polygonSeries.exclude = ["AQ"];
            }

            // Make map load polygon data (country shapes and names) from GeoJSON
            polygonSeries.useGeodata = true;

            // Configure series
            const polygonTemplate = polygonSeries.mapPolygons.template;
            polygonTemplate.tooltipText = "{name}";
            polygonTemplate.fill = am4core.color("#d6dae6");

            if (withMarkers) {
                let imageSeries = chart.series.push(new am4maps.MapImageSeries());

                let imageSeriesTemplate = imageSeries.mapImages.template;
                let circle = imageSeriesTemplate.createChild(am4core.Circle);
                circle.radius = 4;
                circle.fill = am4core.color("#374151");
                circle.fillOpacity = 1;
                circle.stroke = am4core.color("#FFF");
                circle.strokeOpacity = 0.5;
                circle.strokeWidth = 5;
                circle.nonScaling = true;
                circle.tooltipText = "{title}";

                imageSeriesTemplate.propertyFields.latitude = "latitude";
                imageSeriesTemplate.propertyFields.longitude = "longitude";

                imageSeries.data = [{
                    "latitude": 56.130367,
                    "longitude": -106.346771,
                    "title": "Canada"
                }, {
                    "latitude": 37.090240,
                    "longitude": -95.712891,
                    "title": "United States"
                }, {
                    "latitude": -14.235004,
                    "longitude": -51.925282,
                    "title": "Brazil"
                }, {
                    "latitude": 44.016521,
                    "longitude": 21.005859,
                    "title": "Serbia"
                }, {
                    "latitude": 61.5240,
                    "longitude": 105.3188,
                    "title": "Russia"
                }];
            }
        },
    },
    mounted() {
        this.initialMap("chartDiv1", am4geodata_worldLow, false); // Initialize world map without markers
        this.initialMap("chartDiv2", am4geodata_worldLow, true);  // Initialize world map with markers
        this.initialMap("chartDiv3", am4geodata_canadaLow, false); // Initialize Canada map without markers
        this.initialMap("chartDiv4", am4geodata_iraqLow, false);  // Initialize Iraq map without markers
        this.initialMap("chartDiv5", am4geodata_italyLow, false); // Initialize Italy map without markers
        this.initialMap("chartDiv6", am4geodata_russiaLow, false); // Initialize Russia map without markers
        this.initialMap("chartDiv7", am4geodata_spainLow, false); // Initialize Spain map without markers
        this.initialMap("chartDiv8", am4geodata_usaLow, false);
    },
}
</script>

<template>
    <Layout>
        <pageheader title="Vector Map" pageTitle="Maps" />
        <BRow>
            <BCol lg="6">
                <BCard no-body>
                    <BCardHeader>Basic Map With Markers</BCardHeader>
                    <BCardBody>
                        <div id="chartDiv2" style="height: 365px"></div>
                    </BCardBody>
                </BCard>
            </BCol>
            <BCol lg="6">
                <BCard no-body>
                    <BCardHeader>World [merc] Map</BCardHeader>
                    <BCardBody>
                        <div id="chartDiv1" style="height: 365px"></div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>

        <BRow>
            <BCol lg="6">
                <BCard no-body>
                    <BCardHeader>Canada Map</BCardHeader>

                    <BCardBody>
                        <div id="chartDiv3" style="height: 365px"></div>
                    </BCardBody>
                </BCard>
            </BCol>
            <BCol lg="6">
                <BCard no-body>
                    <BCardHeader>Iraq Map</BCardHeader>

                    <BCardBody>
                        <div id="chartDiv4" style="height: 365px"></div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>

        <BRow>
            <BCol lg="6">
                <BCard no-body>
                    <BCardHeader>Italy Map</BCardHeader>
                    <BCardBody>
                        <div id="chartDiv5" style="height: 365px"></div>
                    </BCardBody>
                </BCard>
            </BCol>
            <BCol lg="6">
                <BCard no-body>
                    <BCardHeader>Russia Map</BCardHeader>
                    <BCardBody>
                        <div id="chartDiv6" style="height: 365px"></div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
        <BRow>
            <BCol lg="6">
                <BCard no-body>
                    <BCardHeader>Spain Map</BCardHeader>
                    <BCardBody>
                        <div id="chartDiv7" style="height: 365px"></div>
                    </BCardBody>
                </BCard>
            </BCol>
            <BCol lg="6">
                <BCard no-body>
                    <BCardHeader>us-aea-en Map</BCardHeader>
                    <BCardBody>
                        <div id="chartDiv8" style="height: 365px"></div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>