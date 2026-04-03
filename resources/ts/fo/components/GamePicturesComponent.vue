<template>
  <div class="position-relative">
    <!-- PICTURES -->
    <template v-if="pictureModels.length > 0">
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
                class="p-1"
                v-if="pictureModels[getPictureNumber(paginateIndex, templateIndex) + pictureIndex]"
              >
                <div class="shadow rounded-3">
                  <a
                    :href="getPicturePath(getPictureNumber(paginateIndex, templateIndex) + pictureIndex)"
                    class="glightbox"
                    data-gallery="games-pictures"
                  >
                    <div class="ratio ratio-16x9 overflow-hidden rounded-3">
                      <img
                        :src="getPicturePath(getPictureNumber(paginateIndex, templateIndex) + pictureIndex)"
                        :alt="'Picture n°' + (getPictureNumber(paginateIndex, templateIndex) + pictureIndex + 1) + ' from the game ' + gameName"
                        :title="'Picture n°' + (getPictureNumber(paginateIndex, templateIndex) + pictureIndex + 1) + ' from the game ' + gameName"
                        class="d-none w-100 z-1"
                        @load="gameImageLazyLoad"
                      >
                      <div class="picture-loader position-absolute top-0 start-0 w-100 h-100">
                        <div class="d-flex justify-content-center align-items-center w-100 h-100 bg-primary">
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
                    :class="['picture-ratings btn btn-white position-absolute bottom-0 end-0 m-1 z-2', {disabled: ratingLoading}]"
                    :disabled="ratingLoading"
                    @click="ajaxPictureRating(pictureModels[getPictureNumber(paginateIndex, templateIndex) + pictureIndex].id, getPictureNumber(paginateIndex, templateIndex) + pictureIndex)"
                    :aria-label="trans.methods.__('Cliquez pour ajouter un like ou l\'enlever')"
                    type="button"
                  >
                    <span
                      :id="`ratings-${pictureModels[getPictureNumber(paginateIndex, templateIndex) + pictureIndex].id}`"
                      :data-picture-id="getPictureNumber(paginateIndex, templateIndex) + pictureIndex"
                      class="me-1"
                    >
                      {{ (pictureModels[getPictureNumber(paginateIndex, templateIndex) + pictureIndex].ratings_count) }}
                    </span>
                    <FontAwesomeIcon
                      icon="fa-regular fa-thumbs-up"
                      :class="[{'d-none': picturesRatings.includes(pictureModels[getPictureNumber(paginateIndex, templateIndex) + pictureIndex].id)}]"
                    />
                    <FontAwesomeIcon
                      icon="fa-solid fa-thumbs-up"
                      :class="[{'d-none': !picturesRatings.includes(pictureModels[getPictureNumber(paginateIndex, templateIndex) + pictureIndex].id)}]"
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
      <template v-if="gameLoading">
        <div
          class="spinner-border my-5 text-primary"
          role="status"
        >
          <span class="visually-hidden">{{ trans.methods.__("global_text_loading") }}</span>
        </div>
      </template>
      <template v-else>
        <div
          v-if="pictureModels.length <= 0"
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
    <template v-if="!gameLoading && (gamePage >= gameLastPage) && swiperInitialized">
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
import { computed, onMounted, ref, useAttrs } from "vue";
import errors from "./../../modules/errors";
import route from "./../../modules/route";
import trans from "./../../modules/trans";
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
const routeName = ref<string>("");

/** Pictures */
const pictureModels = ref<Array<{
  id: number,
  uuid: string,
  ratings_count: number,
}>>([]);
const picturesTemplate = ref<Array<number>>([4,3,2,3]);
const picturesRatings = ref<Array<number>>([]);
const ratingLoading = ref<boolean>(false);

/** Games related component variables */
const relatedGamesViews = ref<LaravelPaginator>();
const swiperInitialized = ref<boolean>(false);

// * LIFECYCLE
onMounted((): void => {
  const json = String(attrs.json ?? "{}"),
        data = JSON.parse(json);
  gameName.value = data.gameName;
  gameSlug.value = data.gameSlug;
  gamePage.value = data.pictureModels.current_page;
  gameLastPage.value = data.pictureModels.last_page;
  gameItems.value = data.pictureModels.per_page < 12 ? 12 : data.pictureModels.per_page;
  pictureModels.value = data.pictureModels.data;
  routeName.value = data.routeName;
  picturesRatings.value = data.ratingModels;
  relatedGamesViews.value = data.relatedGamesViews;
  checkScroll();
  updateGlightbox();
});

// * COMPUTED

/**
  * Increment the current page number when the
  * user scroll to the bottom.
  * @return Array<number>
  */
const incrementNumber = computed<Array<number>>(() =>
  pictureModels.value.map((_, index) => index)
    .filter((index) => index % gameItems.value === 0));

// * METHODS

/**
  * Check if all images are loaded,
  * if not, get next pictures.
  * @return void
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
  @return void
  */
function getPictures(): void {
  window.axios
    .get(getGamePicturesRoute() + "?page=" + gamePage.value)
    .then((response) => {
      if (response.data.data !== undefined) {
        pictureModels.value = pictureModels.value.concat(
          Object.values(response.data.data)
        );
      }
      gameLoading.value = false;
      updateGlightbox();
    })
    .catch(errors.methods.ajaxErrorHandler);
}

/**
  * Get the update rating route.
  * @return string
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
  * @param paginateIndex Number of the picture.
  * @param templateIndex Number of the template.
  * @return number
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
  * @param e Event
  * @return void
  */
function gameImageLazyLoad(e: Event): void {
  const nodeTarget = e.target as HTMLImageElement;
  displayImage(nodeTarget, ".glightbox-wrapper");
}

/**
  * Display a specific image,
  * Then hide placeholder of the image.
  * @return void
  */
function displayImage(image: HTMLImageElement, parentClass: string): void {
  image.classList.remove("d-none");
  const nodeTargetParent = image.closest(parentClass);
  nodeTargetParent?.querySelector(".picture-loader")?.classList.add("z-0");
  nodeTargetParent?.querySelector(".picture-loader")?.classList.remove("z-3");
  nodeTargetParent?.querySelector(".btn.picture-ratings")?.classList.remove("d-none");
}

/**
  * Return the path of the picture.
  * @param n Number of the picture.
  * @return string
  */
function getPicturePath(n: number): string {
  return `${location.origin}/storage/pictures/${gameSlug.value}/${pictureModels.value[n].uuid}.webp`;
}

/**
  * Update Glightbox elements.
  * @return void
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
  * @return string
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
  * @return void
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
  * @return void
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
  * @param id Picture's id.
  * @return void
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
</script>

<style lang="scss" scopped>
.glightbox img,
.card img {
  transition: .3s;
}
.glightbox-wrapper:hover img,
.glightbox-wrapper:focus img,
.card:hover img,
.card:focus img {
  transform: scale(1.05) !important;
}
.picture-ratings {
  width: fit-content;
  min-width: 55px;
  height: 40px;
  border-radius: calc(var(--bs-border-radius-lg) - 0.08rem);
  border-top-right-radius: 0;
  border-bottom-left-radius: 0;
}
</style>
