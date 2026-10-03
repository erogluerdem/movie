<script setup>
defineProps({
  code: {
    type: String,
    required: true,
  },
});
</script>

<template>
  <!-- Turkish Flag -->
  <svg
    v-if="code === 'tr'"
    v-bind="$attrs"
    viewBox="0 0 1200 800"
    xmlns="http://www.w3.org/2000/svg"
    aria-hidden="true"
    class="w-4 h-2.5 rounded-[2px] shadow-xs flex-shrink-0 object-cover inline-block"
  >
    <rect width="1200" height="800" fill="#E30A17" />
    <circle cx="400" cy="400" r="200" fill="#ffffff" />
    <circle cx="450" cy="400" r="160" fill="#E30A17" />
    <polygon
      points="480.0,400.0 549.1,377.5 549.1,304.9 591.8,363.7 660.9,341.2 618.2,400.0 660.9,458.8 591.8,436.3 549.1,495.1 549.1,422.5"
      fill="#ffffff"
    />
  </svg>

  <!-- British / English Flag -->
  <svg
    v-else
    v-bind="$attrs"
    viewBox="0 0 60 30"
    xmlns="http://www.w3.org/2000/svg"
    aria-hidden="true"
    class="w-4 h-2.5 rounded-[2px] shadow-xs flex-shrink-0 object-cover inline-block"
  >
    <path d="M0,0 v30 h60 v-30 z" fill="#012169" />
    <path d="M0,0 L60,30 M60,0 L0,30" stroke="#ffffff" stroke-width="6" />
    <path d="M0,0 L60,30 M60,0 L0,30" stroke="#C8102E" stroke-width="4" />
    <path d="M30,0 v30 M0,15 h60" stroke="#ffffff" stroke-width="10" />
    <path d="M30,0 v30 M0,15 h60" stroke="#C8102E" stroke-width="6" />
  </svg>
</template>
