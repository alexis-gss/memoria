<template>
  <div
    v-if="relatedGamesViews.length"
    class="row w-100 mx-auto pb-5"
    data-aos="fade-up"
  >
    <div class="col-12">
      <h2 class="title-font-regular position-relative mx-auto mb-3 w-fit px-5 py-1 text-center">
        {{ trans.methods.__('fo_slide_title') }}
      </h2>
    </div>
    <div :class="['col-12 position-relative px-1 px-md-5']">
      <button
        class="swiper-button swiper-games-related-button-prev btn btn-primary position-absolute rounded-circle border-0 z-2"
        :title="trans.methods.__('fo_slide_target', {'target': trans.methods.__('fo_prev')})"
        :aria-label="trans.methods.__('fo_slide_target_aria', {'target': trans.methods.__('fo_prev')})"
        type="button"
      >
        <FontAwesomeIcon icon="fa-solid fa-chevron-left" />
      </button>
      <div
        id="swiper-games-related"
        class="swiper swiper-games-related overflow-hidden px-3 pt-1 pb-4"
      >
        <div class="swiper-wrapper align-items-stretch w-fit">
          <div
            v-for="(relatedGameView, relatedGameViewIndex) in relatedGamesViews"
            :key="relatedGameViewIndex"
            v-html="relatedGameView"
            class="swiper-slide"
          />
          <template v-if="paginationParameters.page < paginationParameters.lastPage">
            <div class="swiper-slide">
              <div class="card placeholder-glow text-center shadow border-0 h-100">
                <template v-if="paginationParameters.loading">
                  <div class="ratio ratio-16x9 card-img-top overflow-hidden">
                    <div class="placeholder placeholder-img w-100" />
                  </div>
                  <div class="card-body d-flex justify-content-center align-items-center">
                    <span class="placeholder col-8" />
                  </div>
                  <div class="card-footer">
                    <span class="placeholder col-6" />
                  </div>
                </template>
                <button
                  v-else
                  class="btn btn-primary d-flex flex-column justify-content-center align-items-center title-font-regular fs-4 w-100 h-100"
                  @click="getNextRelatedGamesViews"
                  type="button"
                >
                  <span class="position-relative">
                    {{ trans.methods.__('fo_slide_load_next') }}
                    <FontAwesomeIcon
                      icon="fa-solid fa-arrow-right"
                      class="fa-animation position-absolute top-100"
                    />
                  </span>
                </button>
              </div>
            </div>
          </template>
        </div>
      </div>
      <button
        class="swiper-button swiper-games-related-button-next btn btn-primary position-absolute rounded-circle border-0 z-2"
        :title="trans.methods.__('fo_slide_target', {'target': trans.methods.__('fo_next')})"
        :aria-label="trans.methods.__('fo_slide_target_aria', {'target': trans.methods.__('fo_next')})"
        type="button"
      >
        <FontAwesomeIcon icon="fa-solid fa-chevron-right" />
      </button>
      <div
        v-if="relatedGamesViews.length > 1"
        class="swiper-pagination swiper-games-related-pagination position-relative bottom-0"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { defineOptions, ref, reactive, onMounted } from "vue";
import type { PropType } from "vue";
import Swiper from "swiper";
import { Keyboard, Navigation, Pagination } from "swiper/modules";
import errors from "./../../modules/errors";
import trans from "../../modules/trans";
import route from "../../modules/route";
import type { AxiosResponse } from "~/axios";

defineOptions({
  name: "GamesRelatedComponent"
});

// * EMITS
const emits = defineEmits<{
  displayImage: [HTMLImageElement, string],
}>();

// * PROPS
const props = defineProps({
  gameSlug: {
    type: String,
    default: "",
  },
  relatedGamesViews: {
    type: Array as unknown as PropType<LaravelPaginator>,
    default: () => { return []; }
  },
});

// * DATA
const gameSlug = ref<string>(props.gameSlug);
const relatedGamesViews = ref<Array<string>>(props.relatedGamesViews.data);
const slider = ref<Swiper|null>(null);

/** Pagination parameters. */
const paginationParameters = reactive<{
  page: number,
  itemsPerPage: number,
  lastPage: number,
  loading: boolean,
}>({
  page: props.relatedGamesViews.current_page,
  itemsPerPage: 8,
  lastPage: props.relatedGamesViews.last_page,
  loading: false,
});

// * LIFECYCLE
onMounted((): void => {
  gameSlug.value = props.gameSlug;
  relatedGamesViews.value = props.relatedGamesViews.data;
  setSwiper();
  gamesRelatedImageLazyLoad();
});

// * METHODS

/**
  * Create the slider for related games.
  * @return void
  */
function setSwiper(): void {
  slider.value = new Swiper(".swiper-games-related", {
    modules: [Navigation, Keyboard, Pagination],
    grabCursor: true,
    slidesPerView: 1.3,
    initialSlide: 0,
    centeredSlides: true,
    spaceBetween: 10,
    navigation: {
      nextEl: ".swiper-games-related-button-next",
      prevEl: ".swiper-games-related-button-prev",
    },
    keyboard: {
      enabled: true,
    },
    pagination: {
      el: ".swiper-games-related-pagination",
      dynamicBullets: true,
      clickable: true,
      renderBullet: function (index: number, className: string) {
        return `<button class="${className} btn btn-primary rounded-circle border-0 mx-2 p-0" title="${trans.methods.__("fo_slide_id",{"slideId": String(index + 1)})}" aria-label="${trans.methods.__("fo_slide_id_aria",{"slideId": String(index + 1)})}" type="button"></button>`;
      },
    },
    breakpoints: {
      576: {
        centeredSlides: true,
        slidesPerView: 1.3,
        spaceBetween: 25,
      },
      768: {
        centeredSlides: true,
        slidesPerView: 2,
        spaceBetween: 35,
      },
      992: {
        centeredSlides: true,
        slidesPerView: 3,
        spaceBetween: 35,
      },
    },
  });
}

/**
  * Verify when images of related games are loaded.
  * @return void
  */
function gamesRelatedImageLazyLoad(): void {
  const nodeTargets = document.querySelectorAll("#swiper-games-related .card .img-fluid") as NodeListOf<HTMLImageElement>;
  nodeTargets.forEach(nodeTarget => {
    if (nodeTarget.complete) {
      emits("displayImage", nodeTarget, ".card-img-top");
      return;
    }
    nodeTarget.addEventListener("load", function () {
      emits("displayImage", nodeTarget, ".card-img-top");
    });
  });
}

/**
  * Get the related games route.
  * @return string
  */
function getGamesRelatedRoute(): string {
  const gamesRalatedRoute = route.methods.route("fo.games.related", {
    SLUG: gameSlug.value
  });
  if (!gamesRalatedRoute) {
    throw new Error("Undefined route fo.games.related");
  }
  return gamesRalatedRoute;
}

/**
  * Get next pagination related games.
  * @return void
  */
function getNextRelatedGamesViews(): void {
  paginationParameters.loading = true;
  window.axios
    .get(getGamesRelatedRoute() + "?page=" + (paginationParameters.page + 1))
    .then((response: AxiosResponse<any, any>) => {
      relatedGamesViews.value = relatedGamesViews.value.concat(response.data.data);
      paginationParameters.page = response.data.current_page;
      paginationParameters.lastPage = response.data.last_page;
      paginationParameters.loading = false;
    })
    .then(() => {
      gamesRelatedImageLazyLoad();
      slider.value?.update();
    })
    .catch(errors.methods.ajaxErrorHandler);
}
</script>

<style lang="scss" scopped>
@import "bootstrap/scss/functions";
@import "bootstrap/scss/variables";
@import "bootstrap/scss/mixins";
@import "bootstrap/scss/placeholders";
@import "./../../../sass/fo/utilities/variables";

.swiper {
  &-wrapper {
    height: 100% !important;
  }
  &-button-disabled {
    visibility: hidden;
  }
  &-slide {
    height: auto;
  }
}
.swiper-pagination {
  &-bullet {
    opacity: 1 !important;
  }
  &-bullet-active {
    background-color: rgb(var(--bs-secondary-rgb)) !important;
  }
  button {
    width: 1rem;
    height: 1rem;
    &:hover {
      background-color: rgb(var(--bs-primary-rgb)) !important;
    }
  }
}
.swiper-button {
  width: 36px;
  height: fit-content !important;
  &:first-of-type,
  &:last-of-type {
    bottom: -0.6rem;
    @include media-breakpoint-up(md) {
      top: 50%;
      transform: translateY(-50%);
    }
  }
  &:first-of-type {
    left: calc(var(--bs-gutter-x) * .1);
  }
  &:last-of-type {
    right: calc(var(--bs-gutter-x) * .1);
  }
}
#swiper-games-related .ratio {
  max-width: 366px;
}
.fa-animation {
  animation: translation 1.5s cubic-bezier(.17,.84,.44,1) infinite;
}
@keyframes translation {
  0% { left: 0%;opacity: 0; }
  30% { opacity: 1; }
  70% { opacity: 1; }
  100% { left: calc(100% - 20.5px);opacity: 0; }
}
.games-related-hidden {
  visibility: hidden;
  height: 0;
}
</style>
