<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="登录名">
        <el-col :span="8">
          <el-input v-model="form.login_name" placeholder="请输入用户的登录名"/>
        </el-col>
      </el-form-item>
      <el-form-item label="真实姓名">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入用户的真实姓名"/>
        </el-col>
      </el-form-item>
      <el-form-item label="email">
        <el-col :span="8">
          <el-input v-model="form.email" placeholder="请输入正确的邮箱格式"/>
        </el-col>
      </el-form-item>
      <el-form-item label="手机号码">
        <el-col :span="8">
          <el-input v-model="form.mobile" placeholder="请输入正确的手机号码格式（可选）"/>
        </el-col>
      </el-form-item>
      <el-form-item label="登录密码">
        <el-col :span="8">
          <el-input v-model="form.password" type="password" placeholder="请输入登录密码"/>
        </el-col>
      </el-form-item>
      <el-form-item label="描述">
        <el-col :span="14">
          <el-input v-model="form.desc" class="note" type="textarea" placeholder="可输入描述（可选）"/>
        </el-col>
      </el-form-item>
      <el-form-item label="企业微信id">
        <el-col :span="8">
          <el-input v-model="form.wechat_id" placeholder="请输入企业微信id（可选）"/>
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="addUser(form.login_name, form.name, form.email, form.password, form.desc, form.mobile, form.wechat_id)">创建</el-button>
        <el-button @click="jumpUserList">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<!--suppress JSUnusedGlobalSymbols -->
<script>
import { add_user } from '../../api/user'
export default {
  data() {
    return {
      form: {
        login_name: '',
        name: '',
        email: '',
        mobile: '',
        password: '',
        desc: '',
        wechat_id: '',
        type: [],
        resource: ''
      }
    }
  },
  methods: {
    addUser(a, b, c, d, e, f, g) {
      add_user(a, b, c, d, e, f, g)
    },
    // 跳转到用户列表页面
    jumpUserList() {
      this.$router.push({ path: '/user_list' })
    }
  }
}
</script>

<style scoped>
  .note textarea {
    height: 100px !important;
  }
</style>
