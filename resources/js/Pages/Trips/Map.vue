<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    trip: Object,
    stops: Array,
});

const selectedId = ref(props.stops[0]?.id ?? null);
const selected = computed(() => props.stops.find((stop) => stop.id === selectedId.value));
</script>

<template>
    <Head :title="`${trip.title} map · Morrow`" />

    <div class="map-shell">
        <header class="map-header">
            <a class="map-brand" :href="`/trips/${trip.slug}`">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none"><path d="M7 23V9l9 8 9-8v14"/><path d="M7 9l9 14L25 9"/></svg>
                </span>
                <span>Morrow</span>
            </a>
            <div>
                <Link class="map-refresh" :href="`/trips/${trip.slug}/map`" :only="['stops']" preserve-state>Refresh stops</Link>
                <a class="map-back" :href="`/trips/${trip.slug}`">← Back to itinerary</a>
                <button class="icon-button" type="button" data-theme-toggle aria-label="Switch color theme">
                    <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2"/></svg>
                </button>
            </div>
        </header>

        <main class="map-workspace">
            <aside class="map-list">
                <p class="eyebrow">Journey map</p>
                <h1>{{ trip.title }}</h1>
                <p>{{ trip.destination }} · {{ stops.length }} planned stops</p>

                <div class="map-stop-list">
                    <button
                        v-for="(stop, index) in stops"
                        :key="stop.id"
                        type="button"
                        data-testid="map-stop"
                        :class="{ 'is-selected': stop.id === selectedId }"
                        @click="selectedId = stop.id"
                    >
                        <span>{{ index + 1 }}</span>
                        <span><strong>{{ stop.title }}</strong><small>{{ stop.time }} · {{ stop.location }}</small></span>
                        <i>{{ stop.status }}</i>
                    </button>
                </div>
            </aside>

            <section class="map-canvas" aria-label="Map of Kyoto journey stops">
                <div class="map-water"></div>
                <div class="map-park park-one"></div>
                <div class="map-park park-two"></div>
                <div class="map-road road-one"></div>
                <div class="map-road road-two"></div>
                <div class="map-road road-three"></div>
                <button
                    v-for="(stop, index) in stops"
                    :key="`pin-${stop.id}`"
                    type="button"
                    class="map-pin"
                    :class="[`pin-${(index % 8) + 1}`, { 'is-selected': stop.id === selectedId }]"
                    :aria-label="stop.title"
                    @click="selectedId = stop.id"
                >{{ index + 1 }}</button>

                <div v-if="selected" class="map-detail">
                    <p>{{ selected.type }}</p>
                    <h2>{{ selected.title }}</h2>
                    <span>{{ selected.time }} · {{ selected.provider }}</span>
                    <small>{{ selected.location }}</small>
                </div>

                <div class="map-key"><span></span> Kyoto journey</div>
            </section>
        </main>
    </div>
</template>
