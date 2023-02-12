<template #default>
    <div>
        <div class="dealer-block">Dealer Shows:
            <div>Value: {{game?.dealer?.value}}</div>
            <ul><li v-for="card in game?.dealer?.hand">{{card}}</li></ul>
        </div>
        <div class="player-block grid gap-4" :class="'grid-cols-'+game?.player?.length">
            <div class="player-hand"
                 v-for="(hand,index) in game?.player" :key="index"
                 :class="(game?.active_hand === index) ? 'active-hand' : ''"
            >
                <span>Value: {{hand.value}}</span>
                <ul><li v-for="card in hand.hand">{{card}}</li></ul>
                <div class="action-buttons" v-if="!game?.actions?.isEmpty && index===game?.active_hand">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full"
                            v-for="action in game?.actions"
                            @click="doAction(action)"
                    >{{ action }}</button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import {ref,onBeforeMount} from 'vue'
const emit = defineEmits(['refresh'])
const game = ref(null)

const props = defineProps({
    endpoint: {
        type: String,
        default: "",
    }
});

const doAction = async (action) => {
    const url = game.value.href + '/' + action;
    await fetch(url, {method:'POST'})
        .then(ref=>ref.json())
        .then(gameObj=>game.value=gameObj);
    emit('refresh')
}

onBeforeMount(async () => {
    await fetch(props.endpoint)
        .then(ref=>ref.json())
        .then(gameObj=>game.value=gameObj);
})

</script>

<style scoped>
.active-hand {
    border: double black 3px;
}

</style>
