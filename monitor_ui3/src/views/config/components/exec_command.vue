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
    </el-form>
  </div>
</template>

<script>
class OperationForm {
  constructor() {
    this.send_interval = 3600
    this.step = '1-1'
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

</style>
