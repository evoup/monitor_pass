<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="180px">
      <el-form-item label="Grafana的api key">
        <el-col :span="8">
          <el-input v-model="form.api_key" placeholder="请输入api key"/>
        </el-col>
      </el-form-item>
      <el-form-item label="是否在服务端故障时告警">
        <el-col :span="8">
          <el-switch
            v-model="form.switchValue"
            active-value="1"
            inactive-value="0"
          />
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="addTemplate(form.name, serverGroupSelectModel, templateSelectModel)">更新</el-button>
        <el-button @click="jumpTemplateList">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
export default {
  name: 'GeneralConfig',
  data() {
    return {
      form: {
        api_key: null,
        switchValue: '1'
      }
    }
  },
  methods: {
    addTemplate(a, b, c) {
      // add_template(a, b, c)
    },
    jumpTemplateList() {
      this.$router.push({ path: '/template_list' })
    }
  }
}
</script>

<style scoped>

</style>
