<template>
  <div class="word-counter-varchar d-flex flex-row justify-content-around align-items-center w-100">
    <span>
      {{ trans.methods.__("bo_other_word_counter_words") }}&nbsp;:&nbsp;<b>{{ wordsCount }}</b>
    </span>
    <span>
      {{ trans.methods.__("bo_other_word_counter_characters") }}&nbsp;:&nbsp;<b>{{ charactersCount }}</b>
    </span>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, useAttrs, watch } from "vue";
import trans from "./../../modules/trans";

defineOptions({
  name: "WordCounterComponent"
});

// * ATTRIBUTES
const attrs = useAttrs();

// * PROPS
const props = defineProps({
  id: {
    type: String,
    default: String(Math.pow(10, 16) / Math.random())
  },
});

// * DATA
const id = ref<string>(props.id);
const wordsCount = ref<number>(0);
const charactersCount = ref<number>(0);
const input = ref<HTMLInputElement|HTMLTextAreaElement|null>(null);

// * COMPUTED
const isUsedWithProps = computed<boolean>(() => attrs.json === undefined);

// * LIFECYCLE
onMounted((): void => {
  const json = String(attrs.json ?? "{}"),
        data = JSON.parse(json);
  if (!isUsedWithProps.value) {
    id.value = data.id;
  }
  input.value = checkInput(document.getElementById(id.value));
});

// * WATCHERS

/**
  * Update counters when the value of input change.
  * @return void
  */
watch(input, (): void => {
  input.value?.addEventListener("input", (e) => {
    updateValues(checkInput(e.target).value);
  });
  updateValues(input.value?.value ?? "");
});

// * METHODS

/**
  * Check if there is an input or textearea.
  * @return HTMLInputElement|HTMLTextAreaElement
  */
function checkInput(input: HTMLElement|EventTarget|null): HTMLInputElement|HTMLTextAreaElement {
  if (
    !input ||
    !(
      input instanceof HTMLInputElement ||
      input instanceof HTMLTextAreaElement
    )
  ) {
    throw new Error(`${id.value} is not a input or textarea`);
  }
  return input;
}

/**
  * Update counters values.
  * @return void
  */
function updateValues(string: string): void {
  let chars, words;
  [words, chars] = calculateValues(string);
  wordsCount.value = words;
  charactersCount.value = chars;
}

/**
  * Calculate number of words and length of the string.
  * @return Array<number>
  */
function calculateValues(string: string): Array<number> {
  const arr = string.split(" ");
  return [arr.filter((word) => word !== "").length, string.length];
}
</script>

<style lang="scss" scopped>
#word-counter + input[type="text"],
.word-counter + input[type="text"],
#word-counter + textarea,
.word-counter + textarea,
#word-counter + .input-group > input[type="password"],
.word-counter + .input-group > input[type="password"],
#word-counter-varchar + input[type="text"],
.word-counter-varchar + input[type="text"],
#word-counter-varchar + textarea,
.word-counter-varchar + textarea,
#word-counter-varchar + .input-group > input[type="password"],
.word-counter-varchar + .input-group > input[type="password"] {
  border-radius: 0 0 0.375rem 0.375rem;
}
.word-counter-varchar {
  color: var(--bs-body-color);
  border: var(--bs-border-width) solid var(--bs-border-color);
  border-bottom: 0;
  background-color: rgb(var(--bs-secondary-bg-rgb));
  border-top-left-radius: 0.375rem;
  border-top-right-radius: 0.375rem;
}
</style>
