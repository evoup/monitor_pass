<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="登录名">
        <el-col :span="8">
          <el-input v-model="form.login_name" :disabled="true" placeholder="请输入用户的登录名"/>
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
      <el-form-item label="企业微信id">
        <el-col :span="8">
          <el-input v-model="form.wechat_id" placeholder="请输入企业微信id（可选）"/>
        </el-col>
      </el-form-item>
      <el-form-item label="旧的登录密码">
        <el-col :span="8">
          <el-input v-model="form.old_password" type="password" placeholder="请输入旧的登录密码"/>
        </el-col>
      </el-form-item>
      <el-form-item label="新的登录密码">
        <el-col :span="8">
          <el-input v-model="form.new_password" type="password" placeholder="请输入新的登录密码"/>
        </el-col>
      </el-form-item>
      <el-form-item label="描述">
        <el-col :span="14">
          <el-input v-model="form.desc" class="note" type="textarea" placeholder="可输入描述"/>
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-button
          type="primary"
          @click="changeUser(form.name, form.email, form.old_password, form.new_password, form.desc, form.mobile, form.wechat_id)">更新</el-button>
        <el-button @click="jumpUserList">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<!--suppress JSUnusedGlobalSymbols -->
<script>
import { read_user, change_user } from '../../api/user'
export default {
  data() {
    return {
      form: {
        login_name: '',
        name: '',
        email: '',
        mobile: '',
        old_password: '',
        new_password: '',
        desc: '',
        wechat_id: '',
        type: []
      }
    }
  },
  mounted() {
    this.getUser({ id: this.$route.query.id })
  },
  methods: {
    // 获取用户
    getUser(a) {
      read_user(a).then(response => {
        this.form.login_name = response.data.item.username
        this.form.email = response.data.item.email
        this.form.mobile = response.data.item.profile.mobile
        this.form.name = response.data.item.first_name
        this.form.desc = response.data.item.profile.desc
        this.form.wechat_id = response.data.item.profile.wechat_id
      })
    },
    // 更新用户
    changeUser(b, c, d, e, f, g, h) {
      const a = this.$route.query.id
      change_user(a, b, c, d, e, f, g, h)
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
