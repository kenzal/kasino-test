<template>
    <g v-if="suit==='spades'">
        <g transform="rotate(225,30,30)">
            <rect width="30" height="30" x="20" y="20"/>
            <circle cx="20" cy="35" r="15"/>
            <circle cx="35" cy="20" r="15"/>
        </g>

        <path d="M30,30 Q30,50 20,60 H40 Q30,50 30,30"/>
    </g>
    <g v-else-if="suit==='diamonds'">
        <rect x="10" y="10" width="40" height="40" style="fill:red" transform="rotate(45,30,30)"/>
    </g>
    <g v-else-if="suit==='clubs'">
        <circle cx="18" cy="35" r="14" />
        <circle cx="30" cy="15" r="14" />
        <circle cx="42" cy="35" r="14" />

        <path d="M30,30 Q 30,50 20,60 H40 Q30,50 30,30" />
    </g>
    <g v-else-if="suit==='hearts'">
        <g transform="rotate(45,30,30)" fill="red">
            <rect x="15" y="15" height="35" width="35" />
            <circle cx="15.0" cy="32.5" r="17.5" />
            <circle cx="32.5" cy="15.0" r="17.5" />
        </g>
    </g>
</template>

<script setup>
const props = defineProps({
    suit: String,
});
</script>

<style scoped>

</style>
