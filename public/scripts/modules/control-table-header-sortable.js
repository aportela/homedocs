const template = `
    <th class="f-cursor-pointer" v-on:click.prevent="$emit('sortClicked')">
        <i v-if="isSorted" class="fas" v-bind:class="{ 'fa-sort-alpha-up': sortOrder == 'ASC',  'fa-sort-alpha-down': sortOrder == 'DESC' }"></i>
        {{ name }}
    </th>
`;


export default {
    name: 'homedocs-table-header-sortable',
    template: template,
    props: [
        'name',
        'isSorted',
        'sortOrder'
    ]
}