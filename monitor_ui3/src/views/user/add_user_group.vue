<template>
  <div class="app-container">
    <el-tabs type="border-card">
      <el-tab-pane label="基本信息">
        <el-form ref="form" :model="form" label-width="120px">
          <el-form-item label="组名：">
            <el-col :span="8">
              <el-input v-model="form.name" placeholder="请输入组名"/>
            </el-col>
          </el-form-item>
          <el-form-item label="备注：">
            <el-col :span="8">
              <el-input v-model="form.desc" placeholder="请输入备注" type="textarea" />
            </el-col>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="addUserGroup(form.name, form.desc, form.desc, form.desc)">创建</el-button>
            <el-button @click="jumpUserGroupList">取消</el-button>
          </el-form-item>
        </el-form>
      </el-tab-pane>
      <el-tab-pane label="权限">权限</el-tab-pane>
      <el-tab-pane label="成员">成员</el-tab-pane>
    </el-tabs>
  </div>
</template>

<script>
import { add_user_group } from '@/api/user'
export default {
  data() {
    return {
      options: [{
        value: '1',
        label: '所有告警'
      }, {
        value: '2',
        label: '严重告警'
      }, {
        value: '3',
        label: '普通告警'
      }, {
        value: '4',
        label: '不接收'
      }],
      optionValue: '1',
      form: {
        name: '',
        desc: '',
        priv: '',
        members: ''
      }
    }
  },
  methods: {
    addUserGroup(a, b, c, d) {
      add_user_group(a, b, c, d)
    },
    // 跳转到用户组列表页面
    jumpUserGroupList() {
      this.$router.push({ path: '/user_group_list' })
    }
  }
}
</script>

<style scoped>

</style>
