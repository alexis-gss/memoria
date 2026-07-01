<template>
  <div
    ref="gamePicturesRef"
    class="position-relative"
  >
    <template v-if="gamePictures.length > 0 && !gridChanging">
      <!-- SELECTS -->
      <div
        class="d-flex flex-row justify-content-start align-items-center m-1 mt-0 gap-2"
        data-aos="fade-up"
      >
        <select
          v-model="currentSort"
          @change="onSortChange"
          class="form-select border-0 cursor-pointer w-auto"
          :aria-label="trans.methods.__('fo_images_sort_aria_label')"
        >
          <option
            v-for="(sortKey, sortIndex) in validSorts"
            :key="sortIndex"
            :value="sortKey"
          >
            {{ trans.methods.__(`fo_images_sort_${sortKey}`) }}
          </option>
        </select>
        <select
          v-model="currentGrid"
          @change="onGridChange"
          class="form-select d-none d-lg-block border-0 cursor-pointer rounded-2 w-auto"
          :aria-label="trans.methods.__('fo_images_grid_aria_label')"
        >
          <option
            v-for="(gridKey, gridIndex) in validGrids"
            :key="gridIndex"
            :value="gridKey"
          >
            {{ trans.methods.__(`fo_images_grid_${gridKey}`) }}
          </option>
        </select>
        <button
          v-if="hasActiveFilters"
          class="d-flex justify-content-center align-items-center btn btn-secondary btn-sm rounded-2 px-2 py-1"
          @click="resetFilters"
          type="button"
          :title="trans.methods.__('fo_images_reset_filters')"
          data-bs-toggle="tooltip"
        >
          <FontAwesomeIcon
            icon="fa-solid fa-eraser"
          />
        </button>
      </div>
      <!-- PICTURES -->
      <template
        v-for="paginateIndex in incrementNumber"
        :key="paginateIndex"
      >
        <template
          v-for="(templateValue, templateIndex) in picturesTemplate"
          :key="templateIndex"
        >
          <div class="row w-100 mx-auto">
            <div
              v-for="(pictureValue, pictureIndex) in templateValue"
              :key="pictureValue"
              class="glightbox-wrapper position-relative col-12 p-0"
              :class="[`col-lg-${gameItems / templateValue}`, (templateValue % 2 === 0) ? `col-sm-6` : `col-sm-12`]"
              data-aos="fade-up"
            >
              <div
                v-if="gamePictures[getPictureNumber(paginateIndex, templateIndex) + pictureIndex]"
                class="p-1"
              >
                <div class="shadow rounded-3">
                  <a
                    :href="getPicturePath(getPictureNumber(paginateIndex, templateIndex) + pictureIndex)"
                    class="btn btn-primary bg-transparent w-100 p-0 border-0 glightbox overflow-hidden"
                    data-gallery="games-pictures"
                  >
                    <div class="ratio ratio-16x9 rounded-3">
                      <img
                        :src="getPicturePath(getPictureNumber(paginateIndex, templateIndex) + pictureIndex)"
                        :alt="'Picture n°' + (getPictureNumber(paginateIndex, templateIndex) + pictureIndex + 1) + ' from the game ' + gameName"
                        :title="'Picture n°' + (getPictureNumber(paginateIndex, templateIndex) + pictureIndex + 1) + ' from the game ' + gameName"
                        class="d-none w-100 z-1"
                        @load="gameImageLazyLoad"
                      >
                      <div class="picture-loader position-absolute top-0 start-0 w-100 h-100">
                        <div class="d-flex justify-content-center align-items-center w-100 h-100 bg-secondary">
                          <div
                            class="spinner-border text-light"
                            role="status"
                          >
                            <span class="visually-hidden">
                              {{ trans.methods.__("global_text_loading") }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </a>
                  <button
                    :class="['picture-ratings btn btn-primary text-dark bg-white border-0 position-absolute bottom-0 end-0 m-1 z-2', {disabled: ratingLoading}]"
                    :disabled="ratingLoading"
                    @click="ajaxPictureRating(gamePictures[getPictureNumber(paginateIndex, templateIndex) + pictureIndex].id, getPictureNumber(paginateIndex, templateIndex) + pictureIndex)"
                    :aria-label="trans.methods.__('Cliquez pour ajouter un like ou l\'enlever')"
                    type="button"
                  >
                    <span
                      :id="`ratings-${gamePictures[getPictureNumber(paginateIndex, templateIndex) + pictureIndex].id}`"
                      :data-picture-id="getPictureNumber(paginateIndex, templateIndex) + pictureIndex"
                      class="me-1"
                    >
                      {{ (gamePictures[getPictureNumber(paginateIndex, templateIndex) + pictureIndex].ratings_count) }}
                    </span>
                    <FontAwesomeIcon
                      icon="fa-regular fa-thumbs-up"
                      :class="[{'d-none': picturesRatings.includes(gamePictures[getPictureNumber(paginateIndex, templateIndex) + pictureIndex].id)}]"
                    />
                    <FontAwesomeIcon
                      icon="fa-solid fa-thumbs-up"
                      :class="[{'d-none': !picturesRatings.includes(gamePictures[getPictureNumber(paginateIndex, templateIndex) + pictureIndex].id)}]"
                    />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </template>
      </template>
    </template>
    <!-- MESSAGES -->
    <div
      class="text-center w-100"
      data-aos="fade-up"
    >
      <template v-if="gameLoading || gridChanging">
        <div
          class="spinner-border my-5 text-primary"
          role="status"
        >
          <span class="visually-hidden">{{ trans.methods.__("global_text_loading") }}</span>
        </div>
      </template>
      <template v-else>
        <div
          v-if="gamePictures.length <= 0"
          class="text-center mb-5 w-100"
        >
          {{ trans.methods.__("fo_images_no_one") }}
        </div>
        <div
          v-else
          class="fst-italic text-secondary text-center my-5 w-100"
          data-aos="fade-up"
        >
          {{ trans.methods.__("fo_images_loaded") }}
        </div>
      </template>
    </div>
    <!-- RELATED GAMES -->
    <template v-if="!gameLoading && !gridChanging && (gamePage >= gameLastPage) && swiperInitialized">
      <GamesRelatedComponent
        :game-slug="gameSlug"
        :related-games-views="relatedGamesViews"
        @display-image="displayImage"
      />
    </template>
  </div>
</template>

<script lang="ts" setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { Toast } from "bootstrap";
import GLightbox from "glightbox";
import { computed, onMounted, ref, useAttrs, watch } from "vue";
import errors from "./../../modules/errors";
import route from "./../../modules/route";
import trans from "./../../modules/trans";
import { Tooltips } from "./../../modules/tooltips";
import GamesRelatedComponent from "./GamesRelatedComponent.vue";

defineOptions({
  name: "GamePicturesComponent"
});

// * ATTRIBUTES
const attrs = useAttrs();

// * DATA
const gameName = ref<string>("");
const gameSlug = ref<string>("");
const gamePage = ref<number>(0);
const gameLastPage = ref<number>(0);
const gameItems = ref<number>(0);
const gameLoading = ref<boolean>(false);
const gameViewer = ref<GLightbox|null>(null);
const gamePictures = ref<Array<{
  id: number,
  uuid: string,
  ratings_count: number,
}>>([]);

/** Filters */
const defaultSort = ref<string>("date");
const defaultGrid = ref<string>("progressive");
const gridPatterns: Record<string, Array<number>> = {
  "progressive": [4, 3, 2, 3],
  "alternate": [2, 4, 2, 4],
  "2columns": [2, 2, 2, 2, 2, 2],
  "3columns": [3, 3, 3, 3],
  "4columns": [4, 4, 4],
};
const currentSort = ref<string>(defaultSort.value);
const currentGrid = ref<string>(defaultGrid.value);
const validSorts: Array<string> = ["date", "likes"];
const validGrids: Array<string> = Object.keys(gridPatterns);

/** Games related component variables */
const relatedGamesViews = ref<LaravelPaginator>();
const swiperInitialized = ref<boolean>(false);

/** Others */
const gamePicturesRef = ref<HTMLDivElement|null>(null);
const picturesTemplate = computed<Array<number>>(() => gridPatterns[currentGrid.value]);
const picturesRatings = ref<Array<number>>([]);
const routeName = ref<string>("");
const ratingLoading = ref<boolean>(false);
const tooltips = ref<Tooltips|null>(null);
const gridChanging = ref<boolean>(false);

// * LIFECYCLE
onMounted((): void => {
  const json = String(attrs.json ?? "{}"),
        data = JSON.parse(json);
  gameName.value = data.gameName;
  gameSlug.value = data.gameSlug;
  gamePage.value = data.pictureModels.current_page;
  gameLastPage.value = data.pictureModels.last_page;
  gameItems.value = data.pictureModels.per_page < 12 ? 12 : data.pictureModels.per_page;
  gamePictures.value = data.pictureModels.data;
  routeName.value = data.routeName;
  picturesRatings.value = data.ratingModels;
  relatedGamesViews.value = data.relatedGamesViews;

  currentSort.value = getValidQueryParam("sort", validSorts, defaultSort.value);
  currentGrid.value = getValidQueryParam("grid", validGrids, defaultGrid.value);

  checkScroll();
  updateGlightbox();
  initTooltips();
});

// * COMPUTED

/**
  * Increment the current page number when the
  * user scroll to the bottom.
  * @return {Array<number>}
  */
const incrementNumber = computed<Array<number>>(() =>
  gamePictures.value.map((_, index) => index)
    .filter((index) => index % gameItems.value === 0));

/**
  * Check if the current sort or grid is different from the default values.
  * @return {boolean}
  */
const hasActiveFilters = computed<boolean>(() =>
  currentSort.value !== defaultSort.value || currentGrid.value !== defaultGrid.value);

/** WATCHERS */

watch(hasActiveFilters, () => {
  initTooltips();
});

// * METHODS

/**
  * Check if all images are loaded,
  * if not, get next pictures.
  * @return {void}
  */
function checkScroll(): void {
  window.addEventListener("scroll", () => {
    if (
      window.innerHeight + window.scrollY >=
      document.body.offsetHeight - 800 &&
      !gameLoading.value
    ) {
      if (gamePage.value < gameLastPage.value) {
        gamePage.value++;
        gameLoading.value = true;
        getPictures();
      } else {
        if (!swiperInitialized.value) {
          swiperInitialized.value = true;
        }
      }
    }
  });
}

/**
  * Load the current page.
  * @param {boolean} replace If true, replace gamePictures instead of appending.
  * @return {void}
  */
function getPictures(replace: boolean = false): void {
  window.axios
    .get(getGamePicturesRoute() + "?page=" + gamePage.value + "&sort=" + currentSort.value)
    .then((response) => {
      if (response.data.data !== undefined) {
        gamePictures.value = replace
          ? Object.values(response.data.data)
          : gamePictures.value.concat(Object.values(response.data.data));
      }
      gameLoading.value = false;
      updateGlightbox();
    })
    .catch(errors.methods.ajaxErrorHandler);
}

/**
  * Get the update rating route.
  * @return {string}
  */
function getGamePicturesRoute(): string {
  const gamePicturesRoute = route.methods.route(routeName.value, {
    SLUG: gameSlug.value
  });
  if (!gamePicturesRoute) {
    throw new Error("Undefined route " + routeName.value);
  }
  let url = new URL(gamePicturesRoute);
  return url.origin + url.pathname;
}

/**
  * Return the number of the picture.
  * @param {number} paginateIndex Number of the picture.
  * @param {number} templateIndex Number of the template.
  * @return {number}
  */
function getPictureNumber(paginateIndex: number, templateIndex: number): number {
  let result = 0;
  if (picturesTemplate.value[templateIndex - 1] !== undefined) {
    for (let index = 0; index <= templateIndex - 1; index++) {
      result += picturesTemplate.value[index];
    }
  }
  return paginateIndex + result;
}

/**
  * Verify when an image of a game are loaded.
  * @param {Event} event
  * @return {void}
  */
function gameImageLazyLoad(event: Event): void {
  const nodeTarget = event.target as HTMLImageElement;
  displayImage(nodeTarget, ".glightbox-wrapper");
}

/**
  * Display a specific image,
  * Then hide placeholder of the image.
  * @param {HTMLImageElement} image
  * @param {string} parentClass
  * @return {void}
  */
function displayImage(image: HTMLImageElement, parentClass: string): void {
  image.classList.remove("d-none");
  const nodeTargetParent = image.closest(parentClass);
  nodeTargetParent?.querySelector(".btn.picture-ratings")?.classList.remove("d-none");
  nodeTargetParent?.querySelector(".picture-loader")?.classList.remove("z-3");
  nodeTargetParent?.querySelector(".picture-loader")?.classList.add("z-0");
}

/**
  * Return the path of the picture.
  * @param {number} number
  * @return {string}
  */
function getPicturePath(number: number): string {
  return `${location.origin}/storage/pictures/${gameSlug.value}/${gamePictures.value[number].uuid}.webp`;
}

/**
  * Update Glightbox elements.
  * @return {void}
  */
function updateGlightbox(): void {
  setTimeout(() => {
    gameViewer.value?.destroy();
    gameViewer.value = new GLightbox({
      selector: ".glightbox",
    });
  }, 800);
}

/**
  * Get the update rating route.
  * @return {string}
  */
function getUpdateRatingRoute(): string {
  const updateRatingRoute = route.methods.route("fo.ratings.update");
  if (!updateRatingRoute) {
    throw new Error("Undefined route fo.ratings.update");
  }
  return updateRatingRoute;
}

/**
  * Update picture ratings.
  * @return {void}
  */
function updatePictureRatings(pictureId: number): void {
  // Picture ratings button
  (picturesRatings.value.includes(pictureId)) ?
    picturesRatings.value.splice(picturesRatings.value.indexOf(pictureId), 1) :
    picturesRatings.value.push(pictureId);
  // Picture ratings count
  const pictureRatingNode = document.getElementById(`ratings-${pictureId}`);
  pictureRatingNode!.textContent = String(
    Number(pictureRatingNode?.textContent) +
      ((picturesRatings.value.includes(pictureId)) ? +1 : -1));
}

/**
  * Create the bootstrap toast from an id.
  * @return {void}
  */
function createBoostrapToastFromId(toastId: string): void {
  const toast = document.getElementById(toastId) as HTMLDivElement|null;
  if (toast) {
    const bootstrapToast = new Toast(toast);
    bootstrapToast?.show();
    toast.addEventListener("hidden.bs.toast", () => {
      document.getElementById(toastId)?.remove();
    });
  }
}

/**
  * Update a specific picture rating.
  * @param {number} id
  * @param {number} place
  * @return {void}
  */
function ajaxPictureRating(id: number, place: number): void {
  ratingLoading.value = true;
  window.axios
    .post(getUpdateRatingRoute(), { picture_id: id, picture_place: place })
    .then((reponse) => {
      let toastContainer = document.querySelector(".toast-container") as HTMLDivElement|null;
      if (toastContainer) {
        // Add the view to the toast wrapper.
        toastContainer!.innerHTML += reponse.data.view;
        updatePictureRatings(reponse.data.pictureId);
        createBoostrapToastFromId(reponse.data.toastId);
      } else {
        throw new Error("Toast wrapper is not present");
      }
    })
    .then(() => { ratingLoading.value = false; })
    .catch(errors.methods.ajaxErrorHandler);
}

/**
  * Triggered when the user changes the sort order.
  * @return {void}
  */
function onSortChange(): void {
  setQueryParam("sort", currentSort.value, defaultSort.value);
  broadcastPictureFilters();
  gamePage.value = 1;
  gameLoading.value = true;
  gamePictures.value = [];
  getPictures(true);
}

/**
  * Triggered when the user changes the display grid.
  * @return {void}
  */
function onGridChange(): void {
  // Hack to force the dom to reorganize before displaying the new grid.
  gridChanging.value = true;
  setQueryParam("grid", currentGrid.value, defaultGrid.value);
  broadcastPictureFilters();
  setTimeout(() => {
    gridChanging.value = false;
    // Animation duration time.
  }, 300);
}

/**
  * Reset all filters (sort + grid) to their default values,
  * clean the URL and reload the first page of pictures.
  * @return {void}
  */
function resetFilters(): void {
  tooltips.value?.refreshTooltips();
  currentSort.value = defaultSort.value;
  currentGrid.value = defaultGrid.value;
  setQueryParam("sort", defaultSort.value, defaultSort.value);
  setQueryParam("grid", defaultGrid.value, defaultGrid.value);
  broadcastPictureFilters();
  gamePage.value = 1;
  gameLoading.value = true;
  gamePictures.value = [];
  getPictures(true);
}

/**
  * Read a query param from the current URL.
  * @param {string} key Query param name.
  * @param {Array<string>} allowedValues Values considered valid.
  * @param {string} fallback Value returned if absent/invalid.
  * @return {string}
  */
function getValidQueryParam(key: string, allowedValues: Array<string>, fallback: string): string {
  const value = new URLSearchParams(window.location.search).get(key);
  return (value && allowedValues.includes(value)) ? value : fallback;
}

/**
  * Update or remove a single query param in the current URL.
  * @param {string} key Query param name.
  * @param {string} value New value (if it equals defaultValue, the param is removed from the URL).
  * @param {string} defaultValue Default value for this param.
  * @return void
  */
function setQueryParam(key: string, value: string, defaultValue: string): void {
  const searchParams = new URLSearchParams(window.location.search);
  if (value === defaultValue) {
    searchParams.delete(key);
  } else {
    searchParams.set(key, value);
  }
  const queryString = searchParams.toString();
  const newUrl = window.location.pathname + (queryString ? `?${queryString}` : "") + window.location.hash;
  history.replaceState(null, "", newUrl);
}

/**
  * Broadcast current sort/grid filters.
  * @return {void}
  */
function broadcastPictureFilters(): void {
  window.dispatchEvent(
    new CustomEvent("game-pictures:filters-changed", {
      detail: {
        sort: currentSort.value !== defaultSort.value ? currentSort.value : "",
        grid: currentGrid.value !== defaultGrid.value ? currentGrid.value : "",
      }
    })
  );
}

/**
  * Initialise all tooltips in the component.
  * @return {void}
  */
function initTooltips(): void {
  setTimeout(() => {
    tooltips.value = Tooltips.make({
      type: "dom",
      elements: gamePicturesRef.value!.querySelectorAll("[data-bs-toggle=\"tooltip\"]")
    });
  }, 500);
}
</script>
