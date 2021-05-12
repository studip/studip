<template>
    <div class="cw-progress-circle" :class="['p' + value, value > 50 ? 'over50' : '']">
        <span>{{ value }}%</span>
        <div class="left-half-clipper">
            <div class="first50-bar"></div>
            <div class="value-bar"></div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'courseware-progress-circle',
    props: {
        value: Number,
    },
};
</script>
