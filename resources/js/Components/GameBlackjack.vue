<template #default>
    <div>
        <div class="dealer-block">
            <div>Dealer Shows: {{game?.dealer?.value ? game?.dealer?.value : game?.dealer?.showing}}</div>
            <ul class="cardHand flex">
                <li v-for="(card,cardIndex) in game?.dealer?.hand">
                    <PlayingCardBack width="70" v-if="!card"/>
                    <PlayingCard width="70" v-else :suit="card.suit" :rank="card.rank"/>
                </li>
            </ul>
        </div>
        <div class="player-block grid gap-4" :class="'grid-cols-'+game?.player?.length">
            <div class="player-hand"
                 v-for="(hand,index) in game?.player" :key="index"
                 :class="(game?.active_hand === index) ? 'active-hand' : ''"
            >
                <span>Value: {{hand.value}}</span>
                <ul class="cardHand">
                    <li v-for="(card,cardIndex) in hand.hand">
                        <PlayingCard width="70" :suit="card.suit" :rank="card.rank"/>
                    </li>
                </ul>
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
import PlayingCard from "@/Components/PlayingCard.vue";
import PlayingCardBack from "@/Components/PlayingCardBack.vue";
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
    padding: 5px;
}
.cardHand {
    height:100px;
    margin: 5px;
    padding-left: 0px;
    display: flex;
    max-width:160px;
    margin-right: -55px;
}

.cardHand li {
    margin-right: -55px;
}

.player-hand {
    max-width: 165px;
}
</style>
