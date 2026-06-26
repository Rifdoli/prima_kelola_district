<script>
import Layout from "@/layout/main.vue"
import pageheader from "@/components/page-header.vue"
import { ref, computed } from 'vue';
import { VueMasonryPlugin } from 'vue-masonry';
import { HeartIcon } from "@zhuowenli/vue-feather-icons";
import img1 from '@/assets/images/gallery-grid/img-grd-gal-1.jpg'
import avtar1 from '@/assets/images/user/avatar-1.jpg'
import img2 from '@/assets/images/gallery-grid/img-grd-gal-2.jpg'
import avtar2 from '@/assets/images/user/avatar-2.jpg'
import img3 from '@/assets/images/gallery-grid/img-grd-gal-3.jpg'
import avtar3 from '@/assets/images/user/avatar-3.jpg'
import img4 from '@/assets/images/gallery-grid/img-grd-gal-4.jpg'
import avtar4 from '@/assets/images/user/avatar-4.jpg'
import img5 from '@/assets/images/gallery-grid/img-grd-gal-5.jpg'
import avtar5 from '@/assets/images/user/avatar-5.jpg'
import img6 from '@/assets/images/gallery-grid/img-grd-gal-6.jpg'
import avtar6 from '@/assets/images/user/avatar-6.jpg'
import img7 from '@/assets/images/gallery-grid/img-grd-gal-7.jpg'
import avtar7 from '@/assets/images/user/avatar-7.jpg'
import img8 from '@/assets/images/gallery-grid/img-grd-gal-8.jpg'
import avtar8 from '@/assets/images/user/avatar-8.jpg'
import avtar9 from '@/assets/images/user/avatar-9.jpg'
import img9 from '@/assets/images/gallery-grid/img-grd-gal-9.jpg'
import img10 from '@/assets/images/gallery-grid/img-grd-gal-10.jpg'
import img11 from '@/assets/images/gallery-grid/img-grd-gal-11.jpg'
import img12 from '@/assets/images/gallery-grid/img-grd-gal-12.jpg'
import img13 from '@/assets/images/gallery-grid/img-grd-gal-13.jpg'
import img14 from '@/assets/images/gallery-grid/img-grd-gal-14.jpg'
import img15 from '@/assets/images/gallery-grid/img-grd-gal-15.jpg'
import img16 from '@/assets/images/gallery-grid/img-grd-gal-16.jpg'
export default {
    name: "GALLERY",
    components: {
        Layout, pageheader, HeartIcon
    },
    data() {
        return {
            img1, avtar1, img2, avtar2, img3, avtar3, img4, avtar4, img5, avtar5, img6, avtar6, img7, avtar7, img8, avtar8, img9, avtar9, img10, img11, img12, img13, img14, img15, img16
        }
    },
    directives: {
        masonry: VueMasonryPlugin
    },
    setup() {
        const activeFilter = ref('*');
        const items = ref([
            { id: 14, category: 'web', image: img1, avatar: avtar1, name: 'Alexander', description: 'Photographer artist' },
            { id: 15, category: 'graphic', image: img2, avatar: avtar2, name: 'Alexander', description: 'Photographer artist' },
            { id: 16, category: 'animation', image: img3, avatar: avtar3, name: 'Alexander', description: 'Photographer artist' },
            { id: 1, category: "uiux", image: img4, avatar: avtar4, name: "Alexander", description: "Photographer artist" },
            { id: 2, category: "product", image: img5, avatar: avtar5, name: "Alexander", description: "Photographer artist" },
            { id: 3, category: "web", image: img6, avatar: avtar6, name: "Alexander", description: "Photographer artist" },
            { id: 4, category: "graphic", image: img7, avatar: avtar7, name: "Alexander", description: "Photographer artist" },
            { id: 5, category: "animation", image: img8, avatar: avtar8, name: "Alexander", description: "Photographer artist" },
            { id: 6, category: "uiux", image: img9, avatar: avtar9, name: "Alexander", description: "Photographer artist" },
            { id: 7, category: "product", image: img10, avatar: avtar5, name: "Alexander", description: "Photographer artist" },
            { id: 8, category: "web", image: img11, avatar: avtar1, name: "Alexander", description: "Photographer artist" },
            { id: 9, category: "graphic", image: img12, avatar: avtar2, name: "Alexander", description: "Photographer artist" },
            { id: 10, category: "animation", image: img13, avatar: avtar3, name: "Alexander", description: "Photographer artist" },
            { id: 11, category: "uiux", image: img14, avatar: avtar4, name: "Alexander", description: "Photographer artist" },
            { id: 12, category: "product", image: img15, avatar: avtar5, name: "Alexander", description: "Photographer artist" },
            { id: 13, category: "web", image: img16, avatar: avtar6, name: "Alexander", description: "Photographer artist" }

            // add more items here...
        ]);
        const filters = ref(['web', 'graphic', 'animation', 'uiux', 'product']);
        const filterLabels = {
            '*': 'Show all',
            'web': 'Web Designer',
            'graphic': 'Graphic Designer',
            'animation': 'Animation Designer',
            'uiux': 'UI/UX Designer',
            'product': 'Product Designer'
        };

        const filterItems = (filter) => {
            activeFilter.value = filter;
        };

        const filteredItems = computed(() => {
            if (activeFilter.value === '*') {
                return items.value;
            }
            return items.value.filter(item => item.category === activeFilter.value);
        });

        return {
            activeFilter,
            filters,
            filterLabels,
            filterItems,
            filteredItems
        };


    }
}
</script>

<template>
    <Layout>
        <pageheader title="Gallery Grid" pageTitle="Application" />
        <BRow>
            <div class="col-12">
                <div class="card">
                    <div class="card-body border-bottom">
                        <div class="row align-items-center justify-content-between g-3">
                            <div class="col-auto">
                                <div id="filters" class="btn-filter btns-gallery">
                                    <button class="btn my-1 btn-sm btn-light-primary me-1" :class="{ active: activeFilter === '*' }" @click="filterItems('*')">
                                        Show all
                                    </button>
                                    <button v-for="filter in filters" :key="filter" class="btn my-1 btn-sm btn-light-primary me-1" :class="{ active: activeFilter === filter }" @click="filterItems(filter)">
                                        {{ filterLabels[filter] }}
                                    </button>
                                </div>
                            </div>
                            <div class="col-auto">
                                <ul class="list-inline d-flex align-items-center ms-auto my-1">
                                    <li class="list-inline-item">
                                        <select class="form-select">
                                            <option>Sort By : Newest</option>
                                            <option>Sort By : Oldest</option>
                                        </select>
                                    </li>
                                    <li class="list-inline-item align-bottom">
                                        <a href="#" class="btn btn-primary">My Collection</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div v-masonry :gutter="{ default: '30px', 700: '20px' }" class="grid row g-3 ">
                            <div v-masonry-tile class="masonry-item" v-for="item in filteredItems" :key="item.id" :class="['col-xxl-3 col-md-4 col-sm-6 element-item', item.category]">
                                <BLink class="card-gallery" data-fslightbox="gallery" :href="item.image">
                                    <img class="img-fluid" :src="item.image" alt="Card image" />
                                    <div class="gallery-hover-data card-body">
                                        <div class="position-relative text-end">
                                            <div class="form-check prod-likes d-inline-block">
                                                <HeartIcon class="prod-likes-icon"></HeartIcon>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="chat-avtar">
                                                    <img class="rounded-circle img-fluid wid-30" :src="item.avatar" alt="User image" />
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 mx-2 text-white">
                                                <p class="mb-0 text-truncate w-100">{{ item.name }}</p>
                                                <span class="mb-0 text-sm text-truncate w-100">{{ item.description }}</span>
                                            </div>
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
</template>
