<template>
  <div class="dashboard-container">
    <blocks/>
  </div>
</template>

<script>
import Blocks from './components/blocks'

export default {
  name: 'Dashboard',
  components: {
    Blocks
  }
}
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.dashboard {
  &-container {
    margin: 30px;
  }
  &-text {
    font-size: 30px;
    line-height: 46px;
  }
}
</style>
