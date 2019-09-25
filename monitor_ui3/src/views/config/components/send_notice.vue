<template>
  <div class="param-form">
    <el-form ref="operationForm" :model="operationForm" label-width="120px">
      <el-form-item label="指定的轮次：">
        <el-col :span="6">
          <el-input v-model="step" placeholder=""/>
        </el-col>
        <span style="font-size: 10px;color: dimgrey;padding-left:10px;">（a-b代表从第a次到第b次，b为0代表无限次）</span>
      </el-form-item>
      <el-form-item label="发送间隔：">
        <el-col :span="6">
          <el-input v-model="send_interval" placeholder=""/>
        </el-col>
      </el-form-item>
      <el-form-item label="发送给用户组：">
        <el-col :span="24">
          <el-select
            v-model="userGroupSelectModel"
            placeholder=""
            style="width: 80%"
            multiple
          >
            <el-option
              v-for="item in userGroupListData"
              :key="item.id"
              :label="item.name"
              :aria-selected="true"
              :value="item.id"
            />
          </el-select>
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
import { user_group_list } from '../../../api/user'

export default {
  name: 'SendNotice',
  data() {
    return {
      send_interval: 3600,
      step: '1-1',
      checkedSendTypes: ['邮件', '企业微信'],
      sendTypes: ['邮件', '企业微信'],
      operationForm: null,
      userGroupSelectModel: null,
      userGroupListData: []
    }
  },
  created() {
    this.fetchData()
  },
  methods: {
    fetchData() {
      this.listLoading = true
      user_group_list().then(response => {
        this.userGroupListData = response.data.items
      })
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
