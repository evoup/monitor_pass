<template>
  <div class="param-form">
    <el-form ref="operationForm" :model="operationForm" label-width="120px">
      <el-form-item label="指定的轮次：">
        <el-col :span="6">
          <el-input v-model="operationForm.step" placeholder=""/>
        </el-col>
        <span style="font-size: 10px;color: dimgrey;padding-left:10px;">（a-b代表从第a次到第b次，b为0代表无限次）</span>
      </el-form-item>
      <el-form-item label="发送间隔：">
        <el-col :span="6">
          <el-input v-model="operationForm.send_interval" placeholder=""/>
        </el-col>
      </el-form-item>
      <el-form-item label="执行目标：">
        <el-col :span="6"/>
      </el-form-item>
      <el-form-item label="ssh用户名：">
        <el-col :span="6">
          <el-input v-model="operationForm.ssh_user" placeholder=""/>
        </el-col>
      </el-form-item>
      <el-form-item label="ssh密码：">
        <el-col :span="6">
          <el-input v-model="operationForm.ssh_user" placeholder=""/>
        </el-col>
      </el-form-item>
      <el-form-item label="命令：">
        <el-col :span="17">
          <el-input
            v-model="operationForm.command"
            class="note"
            placeholder="请输入命令"
            type="textarea"
          />
        </el-col>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
class OperationForm {
  constructor() {
    this.send_interval = 3600
    this.step = '1-1'
    this.ssh_user = ''
    this.command = ''
  }
}
export default {
  name: 'ExecCommand',
  data() {
    return {
      operationForm: new OperationForm()
    }
  }
}
</script>

<style scoped>
  .param-form /deep/ .el-form-item {
    margin-bottom: 5px;
  }

  .param-form /deep/ textarea {
    height: 150px;
  }
</style>
