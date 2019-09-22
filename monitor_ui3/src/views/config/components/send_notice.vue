<template>
  <div class="param-form">
    <el-form ref="operationForm" :model="operationForm" label-width="120px">
      <el-form-item label="指定的轮次：">
        <el-col :span="8">
          <el-input v-model="step" placeholder=""/>
        </el-col>
      </el-form-item>
      <el-form-item label="发送间隔：">
        <el-col :span="8">
          <el-input v-model="send_interval" placeholder=""/>
        </el-col>
      </el-form-item>
      <el-form-item label="接收：">
        <el-col :span="24">
          <el-checkbox-group
            v-model="checkedSendTypes"
            :min="0"
            :max="2">
            <el-checkbox v-for="sendType in sendTypes" :label="sendType" :key="sendType">{{ sendType }}</el-checkbox>
          </el-checkbox-group>
        </el-col>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
export default {
  name: 'SendNotice',
  data() {
    return {
      send_interval: 3600,
      step: '1-1',
      checkedSendTypes: ['邮件', '企业微信'],
      sendTypes: ['邮件', '企业微信'],
      operationForm: null
    }
  }
}
</script>

<style scoped>
  .param-form {}
  .param-form input {
    width: 50px;
  }
  [v-cloak] {
    display:none;
  }
</style>
