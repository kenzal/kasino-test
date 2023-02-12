<template>
    <svg viewBox="0 0 250 350">
        <g>
            <path
                style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:#000000;stroke-width:2;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;stroke-miterlimit:4;stroke-dasharray:none"
                d="M 249,10 C 249,5 245,1 240,1 L 10,1 C 5,1 1,5 1,10 L 1,340 C 1,345 5,349 10,349 L 240,349 C 245,349 249,345 249,340 L 249,10 z "
                id="path3229"/>
            <text
                xml:space="preserve"
                style="font-size:32px;text-align:center;line-height:125%;text-anchor:middle;fill-opacity:1;;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Nimbus Roman No9 L,serif"
                :style="'fill:'+colorString+';stroke:'+colorString"
                x="21"
                y="40"
            >{{ rankString }}</text>
            <text
                xml:space="preserve"
                style="font-size:32px;text-align:center;line-height:125%;text-anchor:middle;fill-opacity:1;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Nimbus Roman No9 L,serif"
                :style="'fill:'+colorString+';stroke:'+colorString"
                x="-229"
                y="-310"
                transform="scale(-1,-1)"
            >{{ rankString }}</text>
        </g>
        <SuitG :suit="suit" transform="translate(7,50) scale(.5,.5)" />
        <SuitG :suit='suit' transform="translate(243,300) scale(-0.5,-0.5)"/>
    </svg>
</template>

<script setup>

import {computed} from "vue";
import SuitG from "@/Components/PlayingCards/SuitG.vue";

const props = defineProps({
    rank: Number,
    suit: String,
});

const rankString = computed(() => {
    switch (props.rank) {
        case 1:
            return 'A';
        case 11:
            return 'J';
        case 12:
            return 'Q';
        case 13:
            return 'K';
        default:
            return props.rank.toString();
    }
})

const colorString = computed(() => {
    switch (props.suit) {
        case 'diamonds':
        case 'hearts':
            return '#FF0000';
        default:
            return '#000000';
    }
})

</script>

<style scoped>

</style>
