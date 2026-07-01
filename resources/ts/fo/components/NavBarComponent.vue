<template>
  <template v-if="isVisible">
    <div
      :class="[
        'nav-background position-fixed bottom-0 start-50 translate-middle-x z-1',
        { 'nav-background-extended': activePanel !== null }
      ]"
    />
    <nav
      class="nav position-fixed start-50 translate-middle-x d-flex flex-column justify-content-center align-items-center bg-secondary shadow text-bg-primary rounded-3 p-2 pt-0"
    >
      <!-- Panneau -->
      <div
        :class="['nav-modal position-relative w-100', { 'nav-modal-hidden': activePanel === null }]"
      >
        <GamesSearchComponent
          :class="['active-panel', { 'active-panel-hidden': activePanel !== 'games' }]"
          :is-active="activePanel === 'games'"
          v-bind="gamesProps"
        />
        <MusicPlayerComponent
          ref="musicPlayerRef"
          :class="['active-panel', { 'active-panel-hidden': activePanel !== 'music' }]"
          :is-active="activePanel === 'music'"
          v-bind="musicProps"
          @playing-change="onMusicPlayingChange"
        />
      </div>
      <!-- Boutons -->
      <div
        class="nav-actions d-flex justify-content-start align-items-center bg-primary rounded-2 w-100 mt-2 flex-row p-0"
      >
        <div class="p-2">
          <button
            class="btn btn-primary btn-action btn-games"
            @click="togglePanel('games')"
          >
            <Transition name="icon">
              <FontAwesomeIcon
                :key="activePanel === 'games' ? 'x' : 'bars'"
                :icon="activePanel === 'games' ? 'fa-solid fa-xmark' : 'fa-solid fa-search'"
              />
            </Transition>
          </button>
        </div>
        <div
          class="flex-grow-1"
          v-html="breadcrumbHtml"
        />
        <div
          v-if="hasMusicPlayer"
          class="border-top-0 border-end-0 border-bottom-0 border-secondary border p-2"
        >
          <button
            class="btn btn-primary btn-action btn-music"
            @click="onMusicButtonClick"
          >
            <Transition name="icon">
              <FontAwesomeIcon
                v-if="activePanel === 'music'"
                key="x"
                icon="fa-solid fa-xmark"
              />
              <span
                v-else-if="isMusicPlaying"
                key="music-playing"
                class="music-equalizer"
                aria-hidden="true"
              >
                <span class="music-equalizer__bar" />
                <span class="music-equalizer__bar" />
                <span class="music-equalizer__bar" />
                <span class="music-equalizer__bar" />
              </span>
              <FontAwesomeIcon
                v-else
                key="music"
                icon="fa-solid fa-music"
              />
            </Transition>
          </button>
        </div>
      </div>
    </nav>
    <!-- Backdrop -->
    <div
      :class="['nav-filter position-fixed w-100 h-100 bg-dark bg-opacity-50 start-0 top-0', { 'nav-filter-hidden': activePanel === null }]"
      @click="closeMenu"
    />
  </template>
</template>

<script lang="ts" setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { ref, useAttrs, onMounted } from "vue";
import GamesSearchComponent from "./GamesSearchComponent.vue";
import MusicPlayerComponent from "./MusicPlayerComponent.vue";

defineOptions({
  name: "NavBarComponent",
});

type Panel = "games" | "music";

// * ATTRIBUTES
const attrs = useAttrs();

// * DATA
const activePanel    = ref<Panel | null>(null);
const prevPanel      = ref<Panel | null>(null);
const isVisible      = ref<boolean>(false);
const hasMusicPlayer = ref<boolean>(false);
const breadcrumbHtml = ref<string>("");
const gamesProps     = ref<Record<string, unknown>>({});
const musicProps     = ref<Record<string, unknown>>({});
const musicPlayerRef = ref<InstanceType<typeof MusicPlayerComponent> | null>(null);
const isMusicPlaying = ref<boolean>(false);
const hasAutoPlayedOnce = ref<boolean>(false);

// * LIFECYCLE
onMounted(() => {
  const data = JSON.parse(String(attrs.json ?? "{}"));

  isVisible.value      = Boolean(data.visible);
  hasMusicPlayer.value = Boolean(data.music);
  breadcrumbHtml.value = data.breadcrumbHtml ?? "";
  gamesProps.value     = {
    json: JSON.stringify({
      games:      data.gameModels,
      allFolders: data.folderModels,
      allTags:    data.tagModels,
      params: {
        text:   data.params?.text   ?? null,
        folder: data.params?.folder ?? null,
        tag:    data.params?.tag    ?? null,
      },
    }),
  };

  if (hasMusicPlayer.value) {
    musicProps.value = {
      json: JSON.stringify(data.music),
    };
  }

  // Event to open modal with external action (tag/folder badges etc…).
  window.addEventListener("nav:open-panel", (e) => {
    const panel = (e as CustomEvent<{ panel: Panel }>).detail?.panel;
    if (panel) openTo(panel);
  });
});

/**
 * Toggle the nav modal with specific panel.
 * @param {Panel} panel
 * @return void
 */
function togglePanel(panel: Panel): void {
  activePanel.value === panel ? closeMenu() : openTo(panel);
}

/**
 * Open the nav modal with specific panel.
 * @param {Panel} panel
 * @return void
 */
function openTo(panel: Panel): void {
  prevPanel.value   = activePanel.value;
  activePanel.value = panel;
}

/**
 * Close the nav modal.
 * @return {void}
 */
function closeMenu(): void {
  prevPanel.value   = null;
  activePanel.value = null;
}

/**
 * Show music panel and play the music once.
 * @return {void}
 */
function onMusicButtonClick(): void {
  togglePanel("music");

  if (!hasAutoPlayedOnce.value && activePanel.value === "music") {
    hasAutoPlayedOnce.value = true;
    musicPlayerRef.value?.play();
  }
}

/**
 * Set playing music.
 * @param {boolean} playing
 * @return {void}
 */
function onMusicPlayingChange(playing: boolean): void {
  isMusicPlaying.value = playing;
}
</script>
