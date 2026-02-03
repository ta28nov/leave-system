<script setup>
defineProps({
  status: {
    type: String,
    required: true,
  },
});

const getStatusStyle = (status) => {
  const styles = {
    new: "bg-yellow-100 text-yellow-800",
    pending: "bg-yellow-100 text-yellow-800",
    approved: "bg-green-100 text-green-800",
    rejected: "bg-red-100 text-red-800",
    cancelled: "bg-gray-100 text-gray-800",
  };
  return styles[status] || "bg-gray-100 text-gray-800";
};

const getStatusLabel = (status) => {
  const labels = {
    new: "New",
    pending: "Pending",
    approved: "Approved",
    rejected: "Rejected",
    cancelled: "Cancelled",
  };
  return labels[status] || status;
};
</script>

<template>
  <span
    :class="[
      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize',
      getStatusStyle(status),
    ]"
  >
    {{ getStatusLabel(status) }}
  </span>
</template>
