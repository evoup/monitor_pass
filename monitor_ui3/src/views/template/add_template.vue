<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="模板名">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入模板名"/>
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="addTemplate(form.name)">创建</el-button>
        <el-button @click="onCancel">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { add_template } from '../../api/template'
export default {
  data() {
    return {
      form: {
        name: '',
        type: [],
        resource: ''
      }
    }
  },
  methods: {
    onSubmit() {
      add_template('a')
      this.$message('submit!')
    },
    onCancel() {
      this.$message({
        message: 'cancel!',
        type: 'warning'
      })
    },
    addTemplate(a) {
      add_template(a)
    }
  }
}
</script>

<style scoped>

</style>
