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
      <el-form-item label="登录密码">
        <el-col :span="8">
          <el-input v-model="form.password" type="password" placeholder="请输入登录密码"/>
        </el-col>
      </el-form-item>
      <el-form-item label="描述">
        <el-col :span="14">
          <el-input v-model="form.desc" type="textarea" placeholder="可输入描述"/>
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="addUser(form.login_name, form.name, form.email, form.password, form.desc)">创建</el-button>
        <el-button @click="jumpUserList">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { add_user } from '@/api/user'
export default {
  data() {
    return {
      form: {
        login_name: '',
        name: '',
        email: '',
        password: '',
        desc: '',
        type: [],
        resource: ''
      }
    }
  },
  methods: {
    onSubmit() {
      add_user('a', 'b', 'c', 'd', 'e')
      this.$message('submit!')
    },
    addUser(a, b, c, d, e) {
      add_user(a, b, c, d, e)
    },
    // 跳转到用户列表页面
    jumpUserList() {
      this.$router.push({ path: '/user_list' })
    }
  }
}
</script>

<style scoped>

</style>
