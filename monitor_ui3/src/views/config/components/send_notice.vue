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
      <el-form-item label="发送给：">
        <el-col :span="24">
          <el-tree ref="tree1" :data="tree_data" show-checkbox @check-change="handleClick"/>
        </el-col>
      </el-form-item>
      <el-form-item label="接收：">
        <el-col :span="24">
          <el-checkbox-group
            v-model="operationForm.checkedSendTypes"
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

class OperationForm {
  constructor() {
    this.send_interval = 3600
    this.step = '1-1'
    this.checkedSendTypes = ['邮件', '企业微信']
  }
}

export default {
  name: 'SendNotice',
  data() {
    return {
      operationForm: new OperationForm(),
      sendTypes: ['邮件', '企业微信'],
      tree_data: []
    }
  },
  created() {
    this.fetchData()
  },
  methods: {
    buildTree(data) {
      this.tree_data = []
      for (const i in data) {
        const user_group_id = 'user_group|' + data[i].id
        const label = data[i].name
        const members = []
        for (const j in data[i].member_list) {
          members.push({ id: 'user|' + data[i].member_list[j].id, label: data[i].member_list[j].first_name })
        }
        const usergroup_member = { id: user_group_id, label: label, children: members }
        this.tree_data.push(usergroup_member)
      }
    },
    handleClick(data) {
      const checkedNodes = this.$refs.tree1.getCheckedNodes()
      const user_group_user_ids = new Set([])
      for (const i in checkedNodes) {
        user_group_user_ids.add(checkedNodes[i].id)
      }
      this.operationForm.userGroupSelectModel = user_group_user_ids
    },
    fetchData() {
      this.listLoading = true
      user_group_list().then(response => {
        this.buildTree(response.data.items)
      })
    }
  }
}
</script>

<style scoped>
  [v-cloak] {
    display: none;
  }
</style>
