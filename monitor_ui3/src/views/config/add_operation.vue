<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="操作名称：">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入操作名称"/>
        </el-col>
      </el-form-item>
      <el-form-item label="通知主题：">
        <el-col :span="8">
          <el-input v-model="form.subject" placeholder="请输入通知主题"/>
        </el-col>
      </el-form-item>
      <el-form-item label="通知内容：">
        <el-col :span="8">
          <el-input
            v-model="form.message"
            class="note"
            placeholder="请输入通知内容"
            type="textarea"
          />
        </el-col>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
export default {
  name: 'AddOperation',
  data() {
    return {
      form: {
        name: '',
        subject: '',
        message: ''
      }
    }
  }
}
</script>

<style scoped>

</style>
