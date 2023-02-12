<template>
    <table>
        <thead>
            <tr>
                <th>Game</th>
                <th v-if="!hideUser">User</th>
                <th>Time</th>
                <th>Amount</th>
                <th>Multiplier</th>
                <th>Result</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(game,index) in gamesArray.data" :key="game.id" @click="showGameModal(game,index)">
                <td>{{ game.name }}</td>
                <td v-if="!game.completed_at" v-date-show="game.timestamp" :title="game.timestamp"></td>
                <td v-if="!game.completed_at" colspan="3" class="text-center">In Progress</td>
                <td v-if="game.completed_at" v-date-show="game.completed_at" :title="game.completed_at"></td>
                <td v-if="game.completed_at" >{{ game.currency.symbol }} {{game.amount?.toFixed(2) }}</td>
                <td v-if="game.completed_at" >{{game.multiplier?.toFixed(2)}}x</td>
                <td v-if="game.completed_at" :class="game.is_winner ? 'winner' : 'loser'">{{game.currency.symbol }} {{game.result?.toFixed(2)}}</td>
            </tr>
        </tbody>
        <Modal :show="showGame!==false" @close="closeModal">
            <h1 class="capitalize text-center">{{showGame?.name}}</h1>
            <div class="capitalize text-center">Game ID: {{showGame?.id}}</div>
            <div v-if="showGame.completed_at" class="grid grid-cols-3 results">
                 <div><h2>Amount</h2><span>{{ showGame?.currency?.symbol }} {{showGame?.amount?.toFixed(2) }}</span></div>
                <div><h2>Multiplier</h2><span>{{ showGame?.multiplier?.toFixed(2) }}x</span></div>
                <div><h2>Result</h2><span :class="showGame?.is_winner ? 'winner' : 'loser'">{{showGame?.currency?.symbol }} {{showGame?.result?.toFixed(2)}}</span></div>
            </div>
            <Component v-if="showGame!==false" :is="GameBlackjack" :endpoint="showGame.href" @refresh="refreshModal"></Component>

        </Modal>
    </table>
<!--    <Link v-for="link in games.meta.links" :href="link.url" v-html="link.label"/>-->
</template>

<script setup>
import moment from "moment";
import {Link} from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import {nextTick, ref} from "vue";
import GameBlackjack from "@/Components/GameBlackjack.vue";

const props = defineProps({
    games: {
        type: Object
    },
    hideUser: {
        type: Boolean,
        default: false,
    }
});

const gamesArray = ref(props.games);

const vDateShow = {
    beforeMount: (el, binding) => {
        el.innerText=moment(binding.value).fromNow();
    }
}
const showGame = ref(false);
const activeIndex = ref(false);

const showGameModal = (game, index) => {
    showGame.value = game;
    activeIndex.value = index;
};
const closeModal = () => {
    showGame.value = false;
};

const refreshModal = async () => {
    await fetch(showGame.value.href)
        .then(res=>res.json())
        .then((gameObj)=>{
            showGame.value = gameObj;
            gamesArray.value.data[activeIndex.value] = gameObj;
        });


}

</script>

<style scoped>
.winner {
    color: green;
}
.loser {
    color: red;
}
</style>
