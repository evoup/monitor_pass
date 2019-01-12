<template>
  <el-scrollbar wrap-class="scrollbar-wrapper">
    <div id="sys_name">{{ sysName }}</div>
    <el-menu
      :show-timeout="200"
      :default-active="$route.path"
      :collapse="isCollapse"
      mode="vertical"
      background-color="#304156"
      text-color="#bfcbd9"
      active-text-color="#409EFF"
    >
      <sidebar-item v-for="route in routes" :key="route.path" :item="route" :base-path="route.path"/>
    </el-menu>
  </el-scrollbar>
</template>

<script>
import { mapGetters } from 'vuex'
import SidebarItem from './SidebarItem'

export default {
  components: { SidebarItem },
  data() {
    return {
      sysName: '服务器监控系统2.0'
    }
  },
  computed: {
    ...mapGetters([
      'sidebar'
    ]),
    routes() {
      return this.$router.options.routes
    },
    isCollapse() {
      return !this.sidebar.opened
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
  #sys_name {
    font-size:18px;
    padding:15px 10px 14px 10px;
    background-color: white;
    color: #409EFF;
  }
</style>
