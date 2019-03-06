<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="主机名">
        <el-input v-model="form.name"/>
      </el-form-item>
      <el-form-item label="监控代理">
        <el-input v-model="form.client"/>
      </el-form-item>
      <el-form-item label="SNMP">
        <el-input v-model="form.snmp"/>
      </el-form-item>
      <el-form-item label="JMX">
        <el-input v-model="form.jmx"/>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
export default {
  data() {
    return {
      form: {
        client: '',
        name: '',
        region: '',
        date1: '',
        date2: '',
        delivery: false,
        type: [],
        resource: '',
        desc: ''
      }
    }
  },
  methods: {
    onSubmit() {
      this.$message('submit!')
    },
    onCancel() {
      this.$message({
        message: 'cancel!',
        type: 'warning'
      })
    }
  }
}
</script>

<style scoped>

</style>
