<template>
    <Toaster :toastOptions="{
        style: { padding: '10px' },
      }"
       position="top-right" richColors />
    <router-view></router-view>
</template>

<script>
import { Toaster } from 'vue-sonner'

export default {
    components: {
        Toaster
    }
}

</script>