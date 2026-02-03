<script setup>
import { defineProps, defineEmits, computed } from "vue";

const props = defineProps({
  size: {
    type: String,
    default: "md",
    validator: (v) => ["sm", "md", "lg", "xl"].includes(v),
  },
  color: {
    type: String,
    default: "blue",
  },
  fullScreen: {
    type: Boolean,
    default: false,
  },
});

const sizeClasses = computed(() => {
  const sizes = {
    sm: "w-4 h-4",
    md: "w-8 h-8",
    lg: "w-12 h-12",
    xl: "w-16 h-16",
  };
  return sizes[props.size];
});

const colorClass = computed(() => {
  const colors = {
    blue: "text-blue-600",
    white: "text-white",
    gray: "text-gray-600",
    green: "text-green-600",
    red: "text-red-600",
  };
  return colors[props.color] || "text-blue-600";
});
</script>

<template>
  <!-- Fullscreen overlay -->
  <div
    v-if="fullScreen"
    class="fixed inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center"
  >
    <div class="flex flex-col items-center gap-4">
      <svg
        class="animate-spin"
        :class="[sizeClasses, colorClass]"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
      >
        <circle
          class="opacity-25"
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          stroke-width="4"
        />
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        />
      </svg>
      <p class="text-gray-600 font-medium">Đang tải...</p>
    </div>
  </div>

  <!-- Inline spinner -->
  <svg
    v-else
    class="animate-spin"
    :class="[sizeClasses, colorClass]"
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
  >
    <circle
      class="opacity-25"
      cx="12"
      cy="12"
      r="10"
      stroke="currentColor"
      stroke-width="4"
    />
    <path
      class="opacity-75"
      fill="currentColor"
      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
    />
  </svg>
</template>
