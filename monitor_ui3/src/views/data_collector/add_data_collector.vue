<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="数据收集器名称">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入数据收集器名称"/>
        </el-col>
      </el-form-item>
      <el-form-item label="ip">
        <el-col :span="8">
          <el-input v-model="form.ip" placeholder="请输入ip"/>
        </el-col>
      </el-form-item>
      <el-form-item label="端口">
        <el-col :span="8">
          <el-input v-model="form.port" placeholder="请输入端口"/>
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-button
          type="primary"
          @click="
            addDataCollector(form.name, form.ip, form.port)
          "
        >创建
        </el-button
        >
        <el-button @click="jumpDataCollectorList">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { add_data_collector } from '../../api/data_collector'

export default {
  name: 'AddDataCollector',
  data() {
    return {
      form: {
        name: '',
        ip: '',
        port: ''
      }
    }
  },
  methods: {
    addDataCollector(a, b, c) {
      add_data_collector(a, b, c)
    },
    jumpDataCollectorList() {
      this.$router.push({ path: '/data_collector_list' })
    }
  }
}
</script>

<style scoped>

</style>
