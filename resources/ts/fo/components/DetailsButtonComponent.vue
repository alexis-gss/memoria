<template>
  <div
    ref="detailsButton"
    class="d-inline-flex align-items-center"
  >
    <a
      v-if="url !== null"
      :href="url"
      target="_blank"
      class="btn btn-primary text-decoration-none text-white border-0 rounded-2 px-2 py-0"
      data-bs-tooltip="tooltip"
      :title="trans.methods.__('fo_access_game_details', { gameName: name })"
    >
      {{ trans.methods.__('fo_images_details') }}
      <i class="fa-solid fa-arrow-up-right-from-square fa-xs ms-1" />
    </a>
    <span
      v-else-if="url === null && !isLoading"
      class="btn btn-primary border-0 rounded-2 px-2 py-0 disabled"
      aria-hidden="true"
    >
      {{ trans.methods.__('fo_images_details_no_result') }}
    </span>
    <span
      v-else-if="isLoading"
      class="btn btn-primary border-0 rounded-2 px-2 py-0 disabled"
      aria-hidden="true"
    >
      {{ trans.methods.__('global_text_loading') }}
      <div
        class="icon-loader spinner-border text-secondary ms-1"
        role="status"
      >
        <span class="visually-hidden">{{ trans.methods.__('global_text_loading') }}</span>
      </div>
    </span>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, useAttrs } from "vue";
import trans from "./../../modules/trans";
import errors from "././../../modules/errors";
import { Tooltips } from "../../modules/tooltips";

defineOptions({
  name: "DetailsButtonComponent"
});

// * ATTRIBUTES
const attrs = useAttrs();

// * PROPS
const props = defineProps({
  gameId: {
    type: Number,
    default: 0,
  },
  gameName: {
    type: String,
    default: "",
  }
});

// * DATA
const id = ref<number>(props.gameId);
const name = ref<string>(props.gameName);
const url = ref<string|null>(null);
const isLoading = ref<boolean>(true);
const tooltips = ref<Tooltips | null>(null);

// * REFS
const detailsButton = ref<HTMLDivElement | null>(null);

// * COMPUTED
const isUsedWithProps = computed<boolean>(() => attrs.json === undefined);

// * LIFECYCLE
onMounted((): void => {
  const json = String(attrs.json ?? "{}"),
        data = JSON.parse(json);
  if (!isUsedWithProps.value) {
    id.value = data.gameId;
    name.value = data.gameName;
  }
  getUrl();
});

// * METHODS

/**
  * Fetch the exact IGDB url for the game.
  * @return void
  */
function getUrl(): void {
  isLoading.value = true;

  window.axios
    .post("/api/games/igdb-url", { id: id.value })
    .then(({ data }) => {
      url.value = data.url ?? null;
    })
    .catch(errors.methods.ajaxErrorHandler)
    .finally(() => {
      isLoading.value = false;
      initTooltips();
    });
}

/**
 * Initialise all tooltips in the component.
 * @return {void}
 */
function initTooltips(): void {
  if (!detailsButton.value) return;
  tooltips.value = Tooltips.make({
    type: "dom",
    elements: detailsButton.value.querySelectorAll("[data-bs-tooltip=\"tooltip\"]"),
  });
}
</script>

<style lang="scss" scopped>
.icon-loader {
  width: 1em;
  height: 1em;
}
</style>
