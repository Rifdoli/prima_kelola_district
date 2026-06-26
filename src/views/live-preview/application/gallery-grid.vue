<template>
    <div>
        <Layout>
            <pageheader title="Gallery Grid" pageTitle="Application" />
            <BRow>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>New Publish</h5>
                        </div>
                        <div class="card-body">
                            <div class="grid row g-3">
                                <div v-for="(image, index) in images" :key="index" class="col-xl-3 col-md-4 col-sm-6">
                                    <BLink class="card-gallery" @click="openLightbox(index, 'images')">
                                        <img class="img-fluid" :src="image.src" :alt="image.alt">
                                        <div class="gallery-hover-data card-body justify-content-end">
                                            <div>
                                                <p class="text-white mb-0 text-truncate w-100">{{ image.name }}</p>
                                                <span class="text-white text-opacity-75 mb-0 text-sm text-truncate w-100">{{ image.date }}</span>
                                            </div>
                                        </div>
                                    </BLink>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Old Publish</h5>
                        </div>
                        <div class="card-body">
                            <div class="grid row g-3">
                                <div class="col-xl-3 col-md-4 col-sm-6" v-for="(oldImage, index) in oldImages" :key="index">
                                    <BLink class="card-gallery" @click="openLightbox(index, 'oldImages')">
                                        <img class="img-fluid" :src="oldImage.src" :alt="oldImage.alt">
                                        <div class="gallery-hover-data card-body justify-content-end">
                                            <div>
                                                <p class="text-white mb-0 text-truncate w-100">{{ oldImage.name }}</p>
                                                <span class="text-white text-opacity-75 mb-0 text-sm text-truncate w-100">{{ oldImage.date }}</span>
                                            </div>
                                        </div>
                                    </BLink>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </BRow>
        </Layout>

        <div v-show="lightboxVisible" class="vue-easy-lightbox" ref="lightbox">
            <div class="vue-easy-lightbox-overlay" @click="closeLightbox"></div>
            <div class="vue-easy-lightbox-container">
                <div class="vue-easy-lightbox-content">
                    <img :src="lightboxImage.src" :alt="lightboxImage.alt" />
                    <div class="vue-easy-lightbox-caption">
                        <p>{{ lightboxImage.name }}</p>
                        <span>{{ lightboxImage.date }}</span>
                    </div>
                    <div class="vue-easy-lightbox-controls">
                        <button class="prev-btn" @click="prevImage" v-show="lightboxArray.length > 1">Prev</button>
                        <button class="next-btn" @click="nextImage" v-show="lightboxArray.length > 1">Next</button>
                        <button class="close-btn" @click="closeLightbox">X</button>
                        <button class="fullscreen-btn" @click="toggleFullscreen">⤢</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Layout from "@/layout/main.vue";
import pageheader from "@/components/page-header.vue";

export default {
    name: "GalleryGrid",
    components: {
        Layout,
        pageheader,
    },
    data() {
        return {
            images: [
                { src: require("@/assets/images/pages/img-gallery-1.jpg"), alt: "Mountain.jpg", name: "Mountain.jpg", date: "12-Aug-2023" },
                { src: require("@/assets/images/pages/img-gallery-2.jpg"), alt: "Lily Garden.jpg", name: "Lily Garden.jpg", date: "12-Aug-2023" },
                { src: require("@/assets/images/pages/img-gallery-3.jpg"), alt: "Boat Zeel.jpg", name: "Boat Zeel.jpg", date: "12-Aug-2023" }
            ],
            oldImages: [
                { src: require("@/assets/images/pages/img-gallery-4.jpg"), alt: "Card image", name: "lily.jpg", date: "12-Aug-2023" },
                { src: require("@/assets/images/pages/img-gallery-5.jpg"), alt: "Card image", name: "Mountain Climbing.jpg", date: "12-Aug-2023" },
                { src: require("@/assets/images/pages/img-gallery-6.jpg"), alt: "Card image", name: "Hill Station.jpg", date: "12-Aug-2023" },
                { src: require("@/assets/images/pages/img-gallery-7.jpg"), alt: "Card image", name: "bungee jumping.jpg", date: "12-Aug-2023" },
                { src: require("@/assets/images/pages/img-gallery-8.jpg"), alt: "Card image", name: "Party Time.jpg", date: "12-Aug-2023" },
                { src: require("@/assets/images/pages/img-gallery-9.jpg"), alt: "Card image", name: "Creative Glass Click.jpg", date: "12-Aug-2023" },
            ],
            lightboxVisible: false,
            lightboxImage: {},
            currentImageIndex: 0,
            lightboxArray: [],
            isFullscreen: false,
        };
    },
    methods: {
        openLightbox(index, arrayType) {
            this.lightboxVisible = true;
            this.currentImageIndex = index;
            this.lightboxArray = this[arrayType];
            this.lightboxImage = this.lightboxArray[this.currentImageIndex];
        },
        closeLightbox() {
            this.lightboxVisible = false;
            this.exitFullscreen();
        },
        nextImage() {
            this.currentImageIndex = (this.currentImageIndex + 1) % this.lightboxArray.length;
            this.lightboxImage = this.lightboxArray[this.currentImageIndex];
        },
        prevImage() {
            this.currentImageIndex = (this.currentImageIndex - 1 + this.lightboxArray.length) % this.lightboxArray.length;
            this.lightboxImage = this.lightboxArray[this.currentImageIndex];
        },
        toggleFullscreen() {
            if (!this.isFullscreen) {
                this.enterFullscreen();
            } else {
                this.exitFullscreen();
            }
        },
        enterFullscreen() {
            const lightbox = this.$refs.lightbox;
            if (lightbox.requestFullscreen) {
                lightbox.requestFullscreen();
            } else if (lightbox.webkitRequestFullscreen) { // Safari
                lightbox.webkitRequestFullscreen();
            } else if (lightbox.msRequestFullscreen) { // IE11
                lightbox.msRequestFullscreen();
            }
            this.isFullscreen = true;
        },
        exitFullscreen() {
            if (document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) { // Safari
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) { // IE11
                    document.msExitFullscreen();
                }
                this.isFullscreen = false;
            }
        }
    },
};
</script>


<style scoped>
.vue-easy-lightbox {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 99999;
}

.vue-easy-lightbox-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.2 );
}

.vue-easy-lightbox-container {
    max-width: 80%;
    max-height: auto;
}

.vue-easy-lightbox-content {
    position: relative;
}

.vue-easy-lightbox-content img {
    max-width: 100%;
    max-height: 100%;
}

.vue-easy-lightbox-caption {
    position: absolute;
    bottom: 10px;
    left: 10px;
    color: #fff;
    background-color: rgba(0, 0, 0, 0.5);
    padding: 5px;
}

.vue-easy-lightbox-controls {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.vue-easy-lightbox-controls button {
    background: linear-gradient(rgba(30, 30, 30, .9), #000 1810%);
    border: none;
    color: #fff;
    font-size: 16px;
    cursor: pointer;
    padding: 4px 10px;
    border-radius: 6px;
}

.vue-easy-lightbox-controls button.prev-btn,
.vue-easy-lightbox-controls button.next-btn,
.vue-easy-lightbox-controls button.close-btn,
.vue-easy-lightbox-controls button.fullscreen-btn {
    position: absolute;
}

.vue-easy-lightbox-controls button.prev-btn {
    left: -60px;
}

.vue-easy-lightbox-controls button.next-btn {
    right: -64px;
}

.vue-easy-lightbox-controls button.close-btn {
    top: -46px;
    right: -38px;
}

.vue-easy-lightbox-controls button.fullscreen-btn {
    top: -46px;
    right: 10px;
}

.vue-easy-lightbox-controls button:focus {
    outline: none;
}

/* Fullscreen styles */
.vue-easy-lightbox:fullscreen .vue-easy-lightbox-overlay,
.vue-easy-lightbox:-webkit-full-screen .vue-easy-lightbox-overlay,
.vue-easy-lightbox:-ms-fullscreen .vue-easy-lightbox-overlay {
    background-color: rgba(0, 0, 0, 0.2);
}
</style>
