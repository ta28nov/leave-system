<script setup>
import { ref, watch, onMounted, onUnmounted } from "vue";
import { X } from "lucide-vue-next";

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: "",
  },
  size: {
    type: String,
    default: "md",
    validator: (v) => ["sm", "md", "lg", "xl", "full"].includes(v),
  },
  persistent: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:modelValue", "close"]);

const isVisible = ref(props.modelValue);

watch(
  () => props.modelValue,
  (val) => {
    isVisible.value = val;
    if (val) {
      document.body.style.overflow = "hidden";
    } else {
      document.body.style.overflow = "";
    }
  },
);

const close = () => {
  if (!props.persistent) {
    isVisible.value = false;
    emit("update:modelValue", false);
    emit("close");
  }
};

const handleBackdropClick = (e) => {
  if (e.target === e.currentTarget) {
    close();
  }
};

const handleEscape = (e) => {
  if (e.key === "Escape" && isVisible.value) {
    close();
  }
};

onMounted(() => {
  document.addEventListener("keydown", handleEscape);
});

onUnmounted(() => {
  document.removeEventListener("keydown", handleEscape);
  document.body.style.overflow = "";
});

const sizeClasses = {
  sm: "max-w-sm",
  md: "max-w-md",
  lg: "max-w-lg",
  xl: "max-w-xl",
  full: "max-w-full mx-4",
};
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isVisible"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @click="handleBackdropClick"
      >
        <Transition
          enter-active-class="duration-200 ease-out"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="duration-150 ease-in"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div
            v-if="isVisible"
            class="w-full bg-white rounded-xl shadow-2xl overflow-hidden"
            :class="sizeClasses[size]"
          >
            <!-- Header -->
            <div
              v-if="title || $slots.header"
              class="flex items-center justify-between px-6 py-4 border-b border-gray-200"
            >
              <slot name="header">
                <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
              </slot>
              <button
                v-if="!persistent"
                @click="close"
                class="p-2 -mr-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition"
              >
                <X class="w-5 h-5" />
              </button>
            </div>

            <!-- Body -->
            <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
              <slot />
            </div>

            <!-- Footer -->
            <div
              v-if="$slots.footer"
              class="px-6 py-4 border-t border-gray-200 bg-gray-50"
            >
              <slot name="footer" />
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
