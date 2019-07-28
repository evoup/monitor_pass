<template>
  <div class="dashboard-container">
    <el-row>
      <el-col :span="8">
        <component :is="get_current_tab"/>
      </el-col>
      <el-col :span="8">
        <component :is="get_current_tab"/>
      </el-col>
      <el-col :span="8">
        <component :is="get_current_tab"/>
      </el-col>
    </el-row>
    <hello/>
  </div>
</template>

<script>
import draggable from 'vuedraggable'
import Hello from './components/hello'
import Host from './components/host'
import Event from './components/event'

export default {
  name: 'Dashboard',
  components: {
    draggable,
    Hello,
    Host,
    Event
  },
  data() {
    return {
      componentName: 'host'
    }
  },
  computed: {
    get_current_tab() {
      return 'Host'
    }
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
