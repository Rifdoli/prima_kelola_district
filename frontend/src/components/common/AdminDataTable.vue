<script>
export default {
    name: "ADMIN-DATA-TABLE",
    props: {
        title: { type: String, default: "" },
        columns: { type: Array, required: true },     // [{ key, label, sortable }]
        rows: { type: Array, required: true },
        searchKeys: { type: Array, default: null },    // defaults to columns[].key
        perPageOptions: { type: Array, default: () => [10, 25, 50] },
        loading: { type: Boolean, default: false },
    },
    data() {
        return {
            search: "",
            sortKey: null,
            sortDir: "asc",
            page: 1,
            perPage: this.perPageOptions[0],
        }
    },
    computed: {
        effectiveSearchKeys() {
            return this.searchKeys && this.searchKeys.length
                ? this.searchKeys
                : this.columns.map(c => c.key);
        },
        searchedRows() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.rows;
            return this.rows.filter(row =>
                this.effectiveSearchKeys
                    .map(key => this.cellText(row, key))
                    .join(" ")
                    .toLowerCase()
                    .includes(q)
            );
        },
        sortedRows() {
            if (!this.sortKey) return this.searchedRows;
            const key = this.sortKey;
            const dir = this.sortDir === "desc" ? -1 : 1;
            return [...this.searchedRows].sort((a, b) => {
                const av = this.rawValue(a, key);
                const bv = this.rawValue(b, key);
                if (av == null && bv == null) return 0;
                if (av == null) return -1 * dir;
                if (bv == null) return 1 * dir;
                if (typeof av === "number" && typeof bv === "number") {
                    return (av - bv) * dir;
                }
                return String(av).localeCompare(String(bv), undefined, { numeric: true }) * dir;
            });
        },
        filteredCount() {
            return this.sortedRows.length;
        },
        totalPages() {
            return Math.max(1, Math.ceil(this.filteredCount / this.perPage));
        },
        pageStartIndex() {
            return (this.page - 1) * this.perPage;
        },
        pagedRows() {
            return this.sortedRows.slice(this.pageStartIndex, this.pageStartIndex + this.perPage);
        },
        rangeFrom() {
            return this.filteredCount === 0 ? 0 : this.pageStartIndex + 1;
        },
        rangeTo() {
            return Math.min(this.pageStartIndex + this.perPage, this.filteredCount);
        },
    },
    watch: {
        search() { this.page = 1; },
        sortKey() { this.page = 1; },
        sortDir() { this.page = 1; },
        rows() {
            if (this.page > this.totalPages) this.page = 1;
        },
    },
    methods: {
        rawValue(row, key) {
            return key.split(".").reduce((obj, part) => (obj == null ? obj : obj[part]), row);
        },
        cellText(row, key) {
            const value = this.rawValue(row, key);
            if (value === null || value === undefined) return "";
            if (typeof value === "boolean") return value ? "yes true" : "no false";
            return String(value);
        },
        toggleSort(column) {
            if (!column.sortable) return;
            if (this.sortKey !== column.key) {
                this.sortKey = column.key;
                this.sortDir = "asc";
            } else if (this.sortDir === "asc") {
                this.sortDir = "desc";
            } else {
                this.sortKey = null;
                this.sortDir = "asc";
            }
        },
        sortIcon(column) {
            if (!column.sortable) return "";
            if (this.sortKey !== column.key) return "ti ti-arrows-sort";
            return this.sortDir === "asc" ? "ti ti-sort-ascending" : "ti ti-sort-descending";
        },
        slotName(column) {
            return `cell-${column.key.replace(/\./g, "-")}`;
        },
    },
}
</script>

<template>
    <div class="card table-card">
        <div class="card-header">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h5 class="mb-3 mb-sm-0">{{ title }}</h5>
                <div class="d-flex flex-wrap gap-2">
                    <slot name="header-actions"></slot>
                </div>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="d-sm-flex align-items-center justify-content-between mb-3 table-toolbar">
                <div class="d-flex align-items-center gap-2 mb-2 mb-sm-0">
                    <select class="form-control form-control-sm" style="width: 75px" v-model.number="perPage">
                        <option v-for="opt in perPageOptions" :key="opt" :value="opt">{{ opt }}</option>
                    </select>
                    <span class="text-muted">entries per page</span>
                </div>
                <input type="text" class="form-control form-control-sm" style="max-width: 220px"
                    placeholder="Search..." v-model="search">
            </div>
            <div class="table-responsive table-toolbar">
                <table class="table table-hover table-bordered align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px">#</th>
                            <th v-for="column in columns" :key="column.key" class="text-center"
                                :class="{ 'cursor-pointer user-select-none': column.sortable }"
                                @click="toggleSort(column)">
                                {{ column.label }}
                                <i v-if="column.sortable" :class="sortIcon(column) + ' f-14 ms-1'"></i>
                            </th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, index) in pagedRows" :key="index">
                            <td class="text-center">{{ pageStartIndex + index + 1 }}</td>
                            <td v-for="column in columns" :key="column.key" class="text-center">
                                <slot :name="slotName(column)" :row="row">
                                    {{ rawValue(row, column.key) }}
                                </slot>
                            </td>
                            <td class="text-center">
                                <slot name="actions" :row="row"></slot>
                            </td>
                        </tr>
                        <tr v-if="!loading && pagedRows.length === 0">
                            <td :colspan="columns.length + 2" class="text-center text-muted">
                                No data found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="d-sm-flex align-items-center justify-content-between mt-2 table-toolbar"
                v-if="filteredCount > 0">
                <span class="text-muted mb-2 mb-sm-0">
                    Showing {{ rangeFrom }} to {{ rangeTo }} of {{ filteredCount }} entries
                </span>
                <BPagination v-model="page" :total-rows="filteredCount" :per-page="perPage"
                    class="mb-0" />
            </div>
        </div>
    </div>
</template>

<style scoped>
@media (min-width: 576px) {
    .table-toolbar {
        padding-left: 20px;
        padding-right: 20px;
    }
}

.table thead th {
    text-transform: none;
}
</style>
