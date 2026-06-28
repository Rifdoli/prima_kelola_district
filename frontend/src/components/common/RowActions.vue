<script>
export default {
    name: "ROW-ACTIONS",
    props: {
        row: { type: Object, required: true },
        actions: { type: Array, default: () => ['view', 'edit', 'delete'] },
    },
    emits: ['view', 'edit', 'delete', 'pairing'],
    data() {
        return {
            actionMap: {
                view:   { icon: 'ti ti-eye',    textClass: 'text-warning' },
                edit:   { icon: 'ti ti-pencil', textClass: 'text-primary' },
                delete: { icon: 'ti ti-trash',  textClass: 'text-danger' },
            },
        }
    },
}
</script>

<template>
      <ul class="list-inline mb-0 d-flex justify-content-center align-items-center gap-3">
        <li v-for="action in actions" :key="action" class="list-inline-item m-0">
            <a href="#" :class="actionMap[action]?.textClass"
                @click.prevent="$emit(action, row)">
                <i :class="actionMap[action]?.icon + ' f-20'"></i>
            </a>
        </li>
        <slot name="extra" :row="row"></slot>
    </ul>  
</template>