<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { CheckCircle, XCircle, AlertCircle, Info, X } from "lucide-vue-next";

const props = defineProps({
  id: {
    type: [String, Number],
    required: true,
  },
  type: {
    type: String,
    default: "info",
    validator: (v) => ["success", "error", "warning", "info"].includes(v),
  },
  title: {
    type: String,
    default: "",
  },
  message: {
    type: String,
    required: true,
  },
  duration: {
    type: Number,
    default: 5000,
  },
  dismissible: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["dismiss"]);

const isVisible = ref(true);
let timer = null;

const config = computed(() => {
  const configs = {
    success: {
      icon: CheckCircle,
      bg: "bg-green-50",
      border: "border-green-200",
      iconColor: "text-green-500",
      textColor: "text-green-800",
      titleColor: "text-green-900",
    },
    error: {
      icon: XCircle,
      bg: "bg-red-50",
      border: "border-red-200",
      iconColor: "text-red-500",
      textColor: "text-red-800",
      titleColor: "text-red-900",
    },
    warning: {
      icon: AlertCircle,
      bg: "bg-amber-50",
      border: "border-amber-200",
      iconColor: "text-amber-500",
      textColor: "text-amber-800",
      titleColor: "text-amber-900",
    },
    info: {
      icon: Info,
      bg: "bg-blue-50",
      border: "border-blue-200",
      iconColor: "text-blue-500",
      textColor: "text-blue-800",
      titleColor: "text-blue-900",
    },
  };
  return configs[props.type];
});

const dismiss = () => {
  isVisible.value = false;
  setTimeout(() => {
    emit("dismiss", props.id);
  }, 300);
};

onMounted(() => {
  if (props.duration > 0) {
    timer = setTimeout(dismiss, props.duration);
  }
});

onUnmounted(() => {
  if (timer) clearTimeout(timer);
});
</script>

<template>
  <Transition
    enter-active-class="duration-300 ease-out"
    enter-from-class="opacity-0 translate-x-full"
    enter-to-class="opacity-100 translate-x-0"
    leave-active-class="duration-200 ease-in"
    leave-from-class="opacity-100 translate-x-0"
    leave-to-class="opacity-0 translate-x-full"
  >
    <div
      v-if="isVisible"
      class="w-full max-w-sm p-4 rounded-lg shadow-lg border"
      :class="[config.bg, config.border]"
    >
      <div class="flex items-start gap-3">
        <component
          :is="config.icon"
          class="w-5 h-5 flex-shrink-0 mt-0.5"
          :class="config.iconColor"
        />

        <div class="flex-1 min-w-0">
          <p
            v-if="title"
            class="font-semibold text-sm"
            :class="config.titleColor"
          >
            {{ title }}
          </p>
          <p class="text-sm" :class="config.textColor">
            {{ message }}
          </p>
        </div>

        <button
          v-if="dismissible"
          @click="dismiss"
          class="flex-shrink-0 p-1 rounded hover:bg-black/5 transition"
          :class="config.textColor"
        >
          <X class="w-4 h-4" />
        </button>
      </div>
    </div>
  </Transition>
</template>
