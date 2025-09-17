<template>
  <div class="card h-100 shadow-sm" data-aos="fade-up">
    <img :src="item.image" class="card-img-top" alt="" style="aspect-ratio: 3 / 2; object-fit: cover;">
    <div class="card-body">
      <h6 class="card-title">{{ item.title }}</h6>
      <p class="card-text mb-2">
        <strong>{{ item.price }} {{ item.currency }}</strong>
        <small v-if="item.shipping" class="text-muted"> • Shipping: {{ item.shipping }}</small>
      </p>
      <a :href="item.url" target="_blank" class="btn btn-outline-primary btn-sm">View on eBay</a>
    </div>
  </div>
</template>

<script>
export default { props: { item: { type: Object, required: true } } }
</script>

