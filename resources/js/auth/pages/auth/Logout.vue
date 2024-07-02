<template>
</template>
  
<script setup>
  
  import { useStore } from 'vuex';
  import { Auth } from "../../helpers/apiAuth.js";

  const store = useStore();
  const apiAuth = new Auth(store);

  apiAuth.logout();
  
</script>