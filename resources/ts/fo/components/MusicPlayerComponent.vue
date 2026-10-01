<template>
  <div
    ref="musicPlayer"
    class="music-player"
  >
    <!-- Player -->
    <div>
      <div class="d-flex flex-row justify-content-between -align-items-center gap-2 h-100">
        <div class="d-flex flex-column justify-content-between align-items-center gap-1 w-100">
          <!-- Cover -->
          <div class="position-relative ratio ratio-1x1 overflow-hidden rounded-3">
            <img
              v-if="musicCover"
              :src="musicCover"
              class="object-fit-cover"
              :class="{ 'opacity-0': isImageLoading }"
              :alt="trans.methods.__('fo_music_cover')"
              @load="onImageLoad"
              @error="onImageError"
            >
            <!-- Loader overlay -->
            <Transition name="now-playing">
              <div
                v-if="isLoading && !hasError"
                class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-secondary"
              >
                <div
                  class="spinner-border text-light"
                  role="status"
                >
                  <span class="visually-hidden">
                    {{ trans.methods.__("global_text_loading") }}
                  </span>
                </div>
              </div>
            </Transition>
            <!-- Error overlay -->
            <Transition name="now-playing">
              <div
                v-if="hasError"
                class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-dark bg-opacity-75 text-white small gap-1"
              >
                <span class="no-result-icon">
                  <FontAwesomeIcon
                    icon="fa-solid fa-triangle-exclamation"
                    class="w-100 h-100"
                  />
                </span>
                <p class="text-center m-0 pt-2">
                  {{ trans.methods.__('fo_music_error') }}
                </p>
              </div>
            </Transition>
          </div>
          <!-- Title / Artist -->
          <div
            ref="wrapperRef"
            class="player-title-wrapper position-relative d-flex flex-row justify-content-center align-items-center overflow-hidden w-100"
          >
            <div class="position-absolute top-0 start-0 h-100" />
            <p
              ref="titleRef"
              class="m-0"
              :class="[
                shouldScroll ? 'position-absolute top-0 start-0' : 'position-relative',
                { 'is-scrolling': isScrollActive }
              ]"
              :style="{ '--scroll-duration': scrollDuration + 's' }"
            >
              <span>{{ musicTitle }}</span>
              <span
                class="small opacity-75"
                v-if="musicArtist"
              >
                &middot;&nbsp;{{ musicArtist }}
              </span>
            </p>
            <div class="position-absolute top-0 end-0 h-100" />
          </div>
          <!-- Progress bar -->
          <div class="w-75 d-flex justify-content-center aligm-items-center mx-auto gap-2">
            <span class="mb-0 small opacity-75">{{ formatTime(currentTime) }}</span>
            <input
              type="range"
              class="form-range progress-range"
              min="0"
              :max="duration"
              step="0.1"
              v-model.number="currentTime"
              @input="onSeekInput"
              @change="onSeekEnd"
              :disabled="hasError || isLoading"
              :style="progressRangeStyle"
              :title="trans.methods.__('fo_music_seek')"
              data-bs-toggle="tooltip"
            >
            <span class="mb-0 small opacity-75">{{ formatTime(duration) }}</span>
          </div>
        </div>
        <!-- Buttons -->
        <div class="d-flex flex-column justify-content-end align-items-center bg-primary rounded-3 gap-2 p-2">
          <div class="vertical-range-wrapper">
            <input
              type="range"
              class="form-range music-range"
              :class="{ 'is-muted': isMuted }"
              min="0"
              max="1"
              step="0.01"
              v-model.number="volume"
              @change="onVolumeChange"
              :disabled="hasError || isLoading"
              :style="volumeRangeStyle"
              :title="trans.methods.__('fo_music_volume')"
              data-bs-toggle="tooltip"
            >
          </div>
          <button
            class="btn btn-primary btn-action"
            @click="toggleMute"
            :disabled="hasError || isLoading"
            :title="isMuted ? trans.methods.__('fo_music_unmute') : trans.methods.__('fo_music_mute')"
            data-bs-toggle="tooltip"
            type="button"
          >
            <Transition name="icon">
              <FontAwesomeIcon
                :key="volumeIcon"
                :icon="volumeIcon"
              />
            </Transition>
          </button>
          <!-- Skip back 10s -->
          <button
            class="btn btn-primary btn-action"
            @click="skip(-SKIP_SECONDS)"
            :disabled="hasError || isLoading"
            :title="trans.methods.__('fo_music_skip_back')"
            data-bs-toggle="tooltip"
            type="button"
          >
            <FontAwesomeIcon icon="fa-solid fa-rotate-left" />
          </button>
          <!-- Skip forward 10s -->
          <button
            class="btn btn-primary btn-action"
            @click="skip(SKIP_SECONDS)"
            :disabled="hasError || isLoading"
            :title="trans.methods.__('fo_music_skip_forward')"
            data-bs-toggle="tooltip"
            type="button"
          >
            <FontAwesomeIcon icon="fa-solid fa-rotate-right" />
          </button>
          <!-- Playback speed -->
          <button
            class="btn btn-primary btn-action small"
            @click="cyclePlaybackRate"
            :disabled="hasError || isLoading"
            :title="trans.methods.__('fo_music_speed')"
            data-bs-toggle="tooltip"
            type="button"
          >
            <Transition name="icon">
              <span :key="playbackRate">{{ playbackRate }}x</span>
            </Transition>
          </button>
          <!-- Loop -->
          <button
            class="btn btn-primary btn-action"
            :class="{ 'active': isLooping }"
            @click="toggleLoop"
            :disabled="hasError || isLoading"
            :title="trans.methods.__('fo_music_loop')"
            data-bs-toggle="tooltip"
            type="button"
          >
            <FontAwesomeIcon icon="fa-solid fa-repeat" />
          </button>
          <!-- Play / Pause -->
          <button
            class="btn btn-primary btn-action p-0"
            :class="{ 'active': isPlaying }"
            @click="togglePlay"
            :disabled="hasError || isLoading"
            :title="isPlaying ? trans.methods.__('fo_music_pause') : trans.methods.__('fo_music_play')"
            data-bs-toggle="tooltip"
            type="button"
          >
            <Transition name="icon">
              <div
                v-if="isLoading"
                class="spinner-border spinner-border-sm text-light"
                role="status"
              >
                <span class="visually-hidden">
                  {{ trans.methods.__("global_text_loading") }}
                </span>
              </div>
              <FontAwesomeIcon
                v-else
                :key="isPlaying ? 'pause' : 'play'"
                :icon="isPlaying ? 'fa-solid fa-pause' : 'fa-solid fa-play'"
              />
            </Transition>
          </button>
        </div>
      </div>
    </div>
    <audio
      ref="audioEl"
      :src="musicSrc"
      :loop="isLooping"
      @loadstart="onAudioLoadStart"
      @canplay="onAudioCanPlay"
      @timeupdate="onTimeUpdate"
      @loadedmetadata="onLoadedMetadata"
      @ended="onEnded"
      @playing="onPlayingEvent"
      @error="onAudioError"
      style="display: none;"
    />
  </div>
</template>

<script lang="ts" setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { onBeforeUnmount, ref, computed, useAttrs, onMounted, watch, nextTick } from "vue";
import trans from "../../modules/trans";
import route from "../../modules/route";
import errors from "../../modules/errors";
import { Tooltips } from "../../modules/tooltips";
import { MarqueeScroll } from "../../modules/marqueeScroll";

defineOptions({
  name: "MusicPlayerComponent",
});

// * ATTRIBUTES
const attrs = useAttrs();

// Émet l'état de lecture vers le parent (NavBarComponent) pour piloter
// l'icône du bouton music sans dupliquer l'état de lecture.
const emit = defineEmits<{
  (e: "playing-change", isPlaying: boolean): void;
}>();

// * CONSTANTS
const PLAYBACK_RATES = [1, 1.25, 1.5, 2, 0.5, 0.75] as const;
const SKIP_SECONDS = 10;

const _data = parseAttrsJson();
const _meta = _data.data ?? {};
const _opts = _data.options ?? {};

// * REFS
const musicPlayer = ref<HTMLDivElement | null>(null);
const audioEl = ref<HTMLAudioElement | null>(null);
const wrapperRef = ref<HTMLDivElement | null>(null);
const titleRef = ref<HTMLParagraphElement | null>(null);
const scrollDuration = ref<number>(8);
const shouldScroll = ref<boolean>(false);
const isAudioLoading = ref<boolean>(Boolean(_meta.src));
const isImageLoading = ref<boolean>(Boolean(_meta.cover));

const props = defineProps<{
  isActive?: boolean;
}>();

/**
 * Parse the JSON passed through the "json" attribute.
 * @return {Record<string, any>}
 */
function parseAttrsJson(): Record<string, any> {
  try {
    return JSON.parse(String(attrs.json ?? "{}"));
  } catch {
    return {};
  }
}

// Music metadata (issues des tags ID3 extraits côté backend).
const musicTitle = ref<string>(_meta.title ?? "");
const musicArtist = ref<string>(_meta.artist ?? "");
const musicSrc = ref<string>(_meta.src ?? "");
const musicCover = ref<string>(_meta.cover ?? "");

// Options.
const isLooping = ref<boolean>(Boolean(_opts.loop));
const isMuted = ref<boolean>(Boolean(_opts.muted));
const volume = ref<number>(typeof _opts.volume === "number" ? _opts.volume : 1);
const isPlaying = ref<boolean>(false);
const shouldResumePlaying = ref<boolean>(Boolean(_opts.play));
const currentTime = ref<number>(0);
const duration = ref<number>(0);
const hasError = ref<boolean>(false);
const playbackRate = ref<number>(Number(_opts.speed) || 1);

// Other.
const tooltips = ref<Tooltips | null>(null);
const isSeeking = ref<boolean>(false);
let marquee: MarqueeScroll | null = null;

// * LIFECYCLE
onMounted((): void => {
  if (audioEl.value) {
    audioEl.value.muted = isMuted.value;
    audioEl.value.volume = volume.value;
    audioEl.value.playbackRate = playbackRate.value;
  }

  // PLay the music by default (blocked by browsers).
  if (shouldResumePlaying.value) {
    play();
  }

  if (wrapperRef.value && titleRef.value) {
    marquee = new MarqueeScroll({
      wrapper: wrapperRef.value,
      title: titleRef.value,
      pauseStart: 4,
      startOffset: 16,
      tolerance: 1,
      onChange: ({ shouldScroll: scroll, duration }) => {
        shouldScroll.value = scroll;
        scrollDuration.value = duration;
      },
    });
    marquee.start();
  }
  initTooltips();
});

onBeforeUnmount((): void => {
  audioEl.value?.pause();
  marquee?.destroy();
});

// * COMPUTED
const volumeIcon = computed<string>(() => {
  if (isMuted.value || volume.value === 0) return "fa-solid fa-volume-xmark";
  if (volume.value < 0.5) return "fa-solid fa-volume-low";
  return "fa-solid fa-volume-high";
});

const progressRangeStyle = computed<Record<string, string>>(() => {
  const pct = duration.value > 0 ? (currentTime.value / duration.value) * 100 : 0;
  return { "--range-fill": `${pct}%` };
});

const volumeRangeStyle = computed(() => {
  const pct = volume.value * 100;
  return { "--range-fill": `${pct}%` };
});

const isScrollActive = computed<boolean>(() => shouldScroll.value && Boolean(props.isActive));

const isLoading = computed<boolean>(() => isAudioLoading.value || isImageLoading.value);

// * WATCHERS
watch(volume, (val) => {
  if (audioEl.value) audioEl.value.volume = val;
  if (val === 0) {
    isMuted.value = true;
  } else if (isMuted.value) {
    isMuted.value = false;
    if (audioEl.value) audioEl.value.muted = false;
  }
});

// Notifie le parent à chaque changement d'état de lecture
// (utilisé pour animer l'icône du bouton music dans la navbar).
watch(isPlaying, (val) => {
  emit("playing-change", val);
});

watch([() => musicTitle.value, () => musicArtist.value], () => {
  nextTick(() => marquee?.update());
});

watch(() => props.isActive, (active) => {
  if (active) nextTick(() => marquee?.update());
});

watch([isMuted, isPlaying], () => {
  nextTick(() => tooltips.value?.refreshTooltips());
});

// * METHODS

/**
 * Ajax call to save options in cookie.
 * @return {void}
 */
function saveCookiePreferences(): void {
  window.axios
    .post(getMusicOptionsRoute(), {
      muted: isMuted.value,
      volume: volume.value,
      loop: isLooping.value,
      speed: playbackRate.value,
    })
    .catch(errors.methods.ajaxErrorHandler);
}

/**
 * Get the music options route.
 * @return {string}
 */
function getMusicOptionsRoute(): string {
  const musicOptionsRoute = route.methods.route("fo.music.options");
  if (!musicOptionsRoute) throw new Error("Undefined route fo.music.options");
  return musicOptionsRoute;
}

/**
 * Toggle playback.
 * @return {void}
 */
function togglePlay(): void {
  if (!audioEl.value || hasError.value) return;
  if (isPlaying.value) {
    pause();
  } else {
    play();
  }
}

/**
 * Start playback.
 * @return {void}
 */
function play(): void {
  if (!audioEl.value || hasError.value || isPlaying.value) return;
  audioEl.value.play().catch(() => {
    hasError.value = true;
    isPlaying.value = false;
  });
  isPlaying.value = true;
}

/**
 * Pause playback.
 * @return {void}
 */
function pause(): void {
  if (!audioEl.value) return;
  audioEl.value.pause();
  isPlaying.value = false;
}

/**
 * Toggle loop mode and save the preference.
 * @return {void}
 */
function toggleLoop(): void {
  isLooping.value = !isLooping.value;
  saveCookiePreferences();
}

/**
 * Toggle mute and save the preference.
 * @return {void}
 */
function toggleMute(): void {
  if (!audioEl.value) return;
  isMuted.value = !isMuted.value;
  audioEl.value.muted = isMuted.value;
  saveCookiePreferences();
}

/**
 * Save the preferences when the volume slider is released.
 * @return {void}
 */
function onVolumeChange(): void {
  saveCookiePreferences();
}

/**
 * Flag the progress bar as being dragged.
 * Prevents timeupdate from overwriting the slider value.
 * @return {void}
 */
function onSeekInput(): void {
  isSeeking.value = true;
}

/**
 * Apply the chosen position to the audio when the progress bar is released.
 * @return {void}
 */
function onSeekEnd(): void {
  if (!audioEl.value) return;
  audioEl.value.currentTime = currentTime.value;
  isSeeking.value = false;
}

/**
 * Skip forward or backward in the track, clamped between 0 and the duration.
 * @param {number} seconds Seconds to skip (negative to go back).
 * @return {void}
 */
function skip(seconds: number): void {
  if (!audioEl.value) return;
  const next = Math.min(Math.max(audioEl.value.currentTime + seconds, 0), duration.value || 0);
  audioEl.value.currentTime = next;
  currentTime.value = next;
}

/**
 * Switch to the next playback speed and save the preference.
 * @return {void}
 */
function cyclePlaybackRate(): void {
  if (!audioEl.value) return;
  const idx = PLAYBACK_RATES.indexOf(playbackRate.value as typeof PLAYBACK_RATES[number]);
  const next = PLAYBACK_RATES[(idx + 1) % PLAYBACK_RATES.length];
  playbackRate.value = next;
  audioEl.value.playbackRate = next;
  saveCookiePreferences();
}

/**
 * Sync the current time with the audio element (unless the user is seeking).
 * @return {void}
 */
function onTimeUpdate(): void {
  if (!audioEl.value || isSeeking.value) return;
  currentTime.value = audioEl.value.currentTime;
}

/**
 * Store the track duration once the metadata is loaded.
 * @return {void}
 */
function onLoadedMetadata(): void {
  if (!audioEl.value) return;
  duration.value = audioEl.value.duration;
  hasError.value = false;
}

/**
 * Reset the playback state when the track ends (unless looping).
 * @return {void}
 */
function onEnded(): void {
  if (!isLooping.value) {
    isPlaying.value = false;
    currentTime.value = 0;
  }
}

/**
 * Clear the error state when playback actually starts.
 * @return {void}
 */
function onPlayingEvent(): void {
  hasError.value = false;
}

/**
 * Handle an audio loading/playback error.
 * @return {void}
 */
function onAudioError(): void {
  hasError.value = true;
  isPlaying.value = false;
  isAudioLoading.value = false;
}

/**
 * Format a time in seconds as mm:ss.
 * @param {number} time Time in seconds.
 * @return {string}
 */
function formatTime(time: number): string {
  if (isNaN(time)) return "00:00";
  const minutes = Math.floor(time / 60);
  const secondes = Math.floor(time % 60);
  return `${String(minutes).padStart(2, "0")}:${String(secondes).padStart(2, "0")}`;
}

/**
 * Show the loader when the audio starts loading.
 * @return {void}
 */
function onAudioLoadStart(): void {
  isAudioLoading.value = true;
}

/**
 * Hide the audio loader once the audio can be played.
 * @return {void}
 */
function onAudioCanPlay(): void {
  isAudioLoading.value = false;
}

/**
 * Hide the image loader once the cover is loaded.
 * @return {void}
 */
function onImageLoad(): void {
  isImageLoading.value = false;
}

/**
 * Hide the image loader if the cover failed to load.
 * @return {void}
 */
function onImageError(): void {
  isImageLoading.value = false;
}

/**
 * Initialise all tooltips in the component.
 * @return {void}
 */
function initTooltips(): void {
  setTimeout(() => {
    if (!musicPlayer.value) return;
    tooltips.value = Tooltips.make({
      type: "dom",
      elements: musicPlayer.value.querySelectorAll("[data-bs-toggle=\"tooltip\"]"),
    });
  }, 500);
}

// Expose play function to run from the parent.
defineExpose({ play });
</script>
