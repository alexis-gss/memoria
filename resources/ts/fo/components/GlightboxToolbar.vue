<template>
  <div
    class="glightbox-toolbar d-flex gap-2"
    @click.stop
    @mousedown.stop
    @mouseup.stop
    @pointerdown.stop
  >
    <button
      v-if="showLike"
      class="picture-ratings d-flex flex-row justify-content-center align-items-center gap-2 gclose gbtn"
      :disabled="busy"
      :aria-label="likeLabel"
      type="button"
      @click="emit('like')"
    >
      <FontAwesomeIcon :icon="liked ? faThumbsUpSolid : faThumbsUpRegular" />
      <span>{{ count }}</span>
    </button>
    <div
      v-else
      class="icon-loader position-relative top-0 start-0 d-flex flex-row justify-content-center align-items-center gap-2 gclose gbtn"
    >
      <div
        class="spinner-border"
        role="status"
      >
        <span class="visually-hidden">{{ trans.methods.__('global_text_loading') }}</span>
      </div>
    </div>
    <button
      class="d-flex flex-row justify-content-center align-items-center gclose gbtn"
      :aria-label="fullscreenLabel"
      type="button"
      @click="emit('fullscreen')"
      @disabled="fullscreenSupported"
    >
      <FontAwesomeIcon :icon="fullscreen ? faCompress : faExpand" />
    </button>
  </div>
</template>

<script lang="ts" setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faThumbsUp as faThumbsUpRegular } from "@fortawesome/free-regular-svg-icons";
import { faCompress, faExpand, faThumbsUp as faThumbsUpSolid } from "@fortawesome/free-solid-svg-icons";
import trans from "./../../modules/trans";

defineOptions({
  name: "GlightboxToolbar"
});

defineProps<{
  showLike: boolean,
  liked: boolean,
  count: number,
  busy: boolean,
  fullscreen: boolean,
  fullscreenSupported: boolean,
  likeLabel: string,
  fullscreenLabel: string,
}>();

const emit = defineEmits<{
  (event: "like"): void,
  (event: "fullscreen"): void,
}>();
</script>
